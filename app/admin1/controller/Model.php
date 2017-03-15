<?php
namespace app\admin\controller;
use think\Request;
use think\Validate;
use think\Db;
class Model extends Admin
{
	public function lists(){
			//查询
		$data = db('model')->field('id,name,title,create_time,status')->paginate(10);
		$this->assign('data',$data);
		return $this->fetch();
	}
	 protected function parseDocumentList(&$list,$model_id=null){
        $model_id = $model_id ? $model_id : 1;
        $attrList = $this->get_model_attribute($model_id,false,'id,name,type,extra');
        // 对列表数据进行显示处理
        if(is_array($list)){
            foreach ($list as $k=>$data){
                foreach($data as $key=>$val){
                    if(isset($attrList[$key])){
                        $extra      =   $attrList[$key]['extra'];
                        $type       =   $attrList[$key]['type'];
                        if('select'== $type || 'checkbox' == $type || 'radio' == $type || 'bool' == $type) {
                            // 枚举/多选/单选/布尔型
                            $options    =   parse_field_attr($extra);
                            if($options && array_key_exists($val,$options)){
                            	    $data[$key]    =   $options[$val];
                            }
                        }elseif('date'==$type){ // 日期型
                            $data[$key]    =   date('Y-m-d',$val);
                        }elseif('datetime' == $type){ // 时间型
                            $data[$key]    =   date('Y-m-d H:i',$val);
                        }
                        
                    }
                }
                $data['model_id'] = $model_id;
                $list[$k]   =   $data;
            }
        }
        //return $list;
    }
	public function get_model_attribute($model_id = 0,$group,$field){
			static $list;
			if(empty($model_id)||!is_numeric($model_id)){
					return false;
			}
			//获取属性
			if(!isset($list[$model_id])){
					$data = db('attribute')->where('model_id',$model_id)->field($field)->select();
					$list[$model_id] = $data;
			}
			
			if($group){
				
			
			}else{
					foreach($list[$model_id] as $v){
							$attr[$v['name']] =$v;
					}
				
			}
				return $attr;		
	}
	public function add($model=0){
		if($this->request->isPost()){
				$model = model('Mo');
				if($model->validate(true)->save(input('post.'))){
					$this->success('新增成功',url('model/lists'));
				}else{
					$this->error($model->getError());
				}
		}else{
			return $this->fetch();
		}
	}
	public function edit(){
		
			$id = input('id');
			if(empty($id)){
				$this->error('参数不能为空');
			}
			$data = db('model')->field(true)->find($id);
			$fields = db('Attribute')->where(array('model_id'=>$data['id']))->column('id,name,title,is_show');
		
			
			 // 获取模型排序字段
      		$field_sort = json_decode($data['field_sort'], true);
      		
      		 if(!empty($field_sort)){
      		 		
      		   		foreach($field_sort as $group=>$ids){
      		   			
      		   				foreach($ids as $key =>$val){
      		   						if(isset($fields[$val])){
	      		   						$fields[$val]['group'] = $group;
	      		   						$fields[$val]['sort'] = $key;
      		   						}
      		   				}
      		   		}
      		}
      		
      		$fields = list_sort_by($fields,'sort');
      		
			$this->assign('fields',$fields);
		
			$this->assign('data',$data);
			return $this->fetch();
		
	}
	 protected function checkAttr($Model,$model_id){
        $fields     =   get_model_attribute($model_id,false);
        $validate   =   $auto   =   array();
        foreach($fields as $key=>$attr){
            if($attr['is_must']){// 必填字段
                $validate[]  =  array($attr['name'],'require',$attr['title'].'必须!');
            }
            // 自动验证规则
            if(!empty($attr['validate_rule'])) {
                $validate[]  =  array($attr['name'],$attr['validate_rule'],$attr['error_info']?$attr['error_info']:$attr['title'].'验证错误',0,$attr['validate_type'],$attr['validate_time']);
            }
            // 自动完成规则
            if(!empty($attr['auto_rule'])) {
                $auto[]  =  array($attr['name'],$attr['auto_rule'],$attr['auto_time'],$attr['auto_type']);
            }elseif('checkbox'==$attr['type']){ // 多选型
                $auto[] =   array($attr['name'],'arr2str',3,'function');
            }elseif('date' == $attr['type']){ // 日期型
                $auto[] =   array($attr['name'],'strtotime',3,'function');
            }elseif('datetime' == $attr['type']){ // 时间型
                $auto[] =   array($attr['name'],'strtotime',3,'function');
            }
        }
        return $Model->validate($validate)->auto($auto);
    }
    public function upload(){
    		$model = model('Mo');
			$post = input('post.');
			$post['field_sort'] = isset($post['field_sort'])?json_encode($post['field_sort']):'';
    		if($result = $model->isUpdate($post['id'])->validate(true)->save($post)){
    			
    			$this->success('更新成功');
    		}else{
    			$this->error($model->getError());
    		}
    		
    }
    public function delete(){
    		$ids = input('post.ids/a');
    		empty($ids) && $this->error('参数不能为空');
    		foreach($ids as $v){
    				$res = model('Mo')->del($v);
    				if(!$res){
    					break;
    				}
    			}
    			if(!$res){
    				$this->error('删除模型失败,只支持删除文档模型和独立模型');
    			}else{
    				$this->success('删除模型成功!');
    			}
    }
    public function generate(){
    		if($this->request->isPost()){
    			($name = input('name'))||$this->error('模型名称不存在');
    			$table = input('tables');
    			$pk = Db::getPk($table);
    			Db::startTrans();
				try{
						
						$model['title']  = $name;
						$model['name'] = substr($table, strlen(config('database.prefix')));
						$model['need_pk'] = $pk;
						$model['create_time'] = time();
						$model_id = Db::name('model')->where('name',$model['name'])->value('id');
						if(!$model_id){
							Db::name('model')->insert($model);
							$model_id  = Db::getLastInsID();
						}
						
						$Attribute = model('Attribute');
						$fields = Db::query('SHOW FULL COLUMNS FROM '.$table);
		    			foreach($fields as $value){
		    				$value  =   array_change_key_case($value);
		    				//删除主键
		    				if($value['field'] == $pk){
		    					continue;
		    				}
		    				//添加model
		    				//数据
		    				$data['title']= $data['name'] = $value['field'];
		    				$data['type'] = 'string';
		    				$data['field'] = $value['type'];
		    				$data['extra'] = $value['extra'];
		    				$data['model_id'] = $model_id;
		   				
		    				$Attribute->update_info($data,false);
		    			}
						Db::commit();
					
				} catch (\Exception $e) {
    				// 回滚事务
    				
 			   		Db::rollback();
 			   		$this->error('添加失败');
				}
				
					$this->success('添加成功');
			
    		}else{
    			
    			 $tables = \think\Db::connect()->getTables();
    			 $this->assign('tables',$tables);
    		 	 return $this->fetch();
    			
    		}
    		
    }
   	public function action($id){
   			$id = input('id');
			if(empty($id)){
				$this->error('参数不能为空');
			}
			$model_name = db('model')->where('id',$id)->value('name');
			$create = new \CreateActionView\Create();
			if($create->action($model_name)){
				$this->success('生成成功');
			}else{
				$this->error($create->getError());
			}
   	}
   	public function view($id){
   			$id = input('id');
			if(empty($id)){
				$this->error('参数不能为空');
			}
			$model_name = db('model')->where('id',$id)->value('name');
			$create = new \CreateActionView\Create();
			if($create->view($model_name)){
				$this->success('生成成功');
			}else{
				$this->error($create->getError());
			}
   	}
}
