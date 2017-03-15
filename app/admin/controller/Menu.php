<?php
namespace app\admin\controller;
class Menu extends Yj{
	
	public function lists(){
			 $data = model('Menu')->lists();
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
                            if($options && array_key_exists($val,$options)) {
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
				$post = input('post.');
				$Menu = model('Menu');
				if($Menu->validate(true)->save($post)){
					$this->success('添加成功');
				}else{
					$this->error($Menu->getError());
				}
		}else{
			$data = model('Menu')->lists();
		 	$this->assign('data',$data);
			return $this->fetch();
		}
		
	}
	public function edit(){
		($id =input('id'))||$this->error('不存在id');
		if($this->request->isPost()){
				$post = input();
				$Menu = model('Menu');
				if($Menu->validate(true)->save($post,['id'=>$post['id']])!==false){
						$this->success('修改成功',url('lists'));
				}else{
					$this->error($Menu->getError());
				}
		}else{
			    $info = model('Menu')->get($id);
				$data = model('Menu')->lists();
		 		$this->assign('info',$info);
		 		$this->assign('data',$data);
				return $this->fetch();
			
		}
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
    public function delete(){
    		($id = input('id')) || $this->error('不存在id');
    		//查看是否存在子栏目
    		
    		$Menu = model('Menu');
    		if($Menu->del($id)){
    			$this->success('删除成功');
    		}else{
    			$this->error($Menu->getError());
    		}
    }
    public function change_info($id=0,$field='',$value=''){
    			$Menu = model('Menu');
    			if($Menu->update_info([$field=>$value],$id)){
    					$this->success('修改成功');
    			}else{
    				$this->error($Menu->getError());
    			}
    }
}
