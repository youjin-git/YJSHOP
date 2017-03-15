<?php
namespace app\admin\controller;
class Attribute extends Admin
{
	public function lists($model=0){
		$model || $this->error('不存在model_id');
		$model = db('model')->where('id',$model)->find();
		$data = db('Attribute')->where('model_id',$model['id'])->paginate(10);
		$this->assign('data',$data);
		$this->assign('model',$model);
		return $this->fetch();
	}
	public function add($model=0){
		if($this->request->isPost()){
			$data = input('post.');
			//判断是否存在model
			$Attribute = model('Attribute');
			
			$result = $Attribute->update_info($data);
			if($result){
				
			}else{
				$this->error($Attribute->getError());
			}
		}else{
			$model = db('model')->where('id',$model)->find();
			//查询是否存在
			$this->assign('model',$model);
			return $this->fetch();
		}
		
	}
	public function edit(){
			$id = input('id');
			if(empty($id)){
				$this->error('参数不能为空');
			}
			$data = db('Attribute')->find($id);
			
			$model  =   db('Model')->field('title,name,field_group')->find($data['model_id']);
			//$data = model('Attribute')->find($id);
			
			$this->assign('model',$model);
			$this->assign('data',$data);
			return $this->fetch();	
	}
	public function update(){
			$data = input('post.');
			//判断是否存在model
			$Attribute = model('Attribute');
			$result = $Attribute->update_info($data);
			
			if($result){
				$this->success('修改成功',url('lists',array('model'=>$data['model_id'])));
			}else{
				$this->error($Attribute->getError());
			}
			
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
    			$this->success('更新成功',url('lists'));
    		}else{
    			$this->error($model->getError());
    		}
    }
    public function delete(){
    		($ids = input('ids/a'))||$this->error('ids不存在');
    		$Model = model('Attribute');
    		foreach($ids as $v){
    			$info = $Model::get($v);
    			//删除数据库
    			if(!empty($info)){
    					//删除该字段
						$Model->deleteField($info);
    					   //删除属性数据
        				$res = $Model->where(array('id'=>$v))->delete(); 
    					if(!$res){
    						$this->error($info->name.'删除失败');
    					}
    			}
    		}
    		$this->success('删除成功');
    					
    }
	public function remove(){
	        $ids = input('ids/a');
	        empty($ids) && $this->error('参数错误！');
	        $Model = model('Attribute');
	        foreach($ids as $id){
	        	 	$info = $Model->getById($id);
	        		empty($info) && $this->error('该字段不存在！');
	        		$res = $Model->where(array('id'=>$id))->delete(); 
	        		$Model->deleteField($info);
			        if(!$res){
			            $this->error(model('Attribute')->getError());
			        }else{
			            //记录行为
			            $this->success('删除成功');
			        }
	        }
	}
}
