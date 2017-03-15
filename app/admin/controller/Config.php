<?php 
namespace app\admin\controller;
class Config extends Think{
		private $model_name = 'config';
		public function lists(){
       			$model =  db('model')->getByName($this->model_name);
       			$model || $this->error('模型不存在');
       			$data = db($model['name'])->find(1);
       			$fields = get_model_attribute($model['id']);
       			$this->assign('data',$data);
       			$this->assign('model',$model);
       			$this->assign('fields',$fields);
       			return $this->fetch('edit');
		}
		public function add(){
			return	parent::base_add($this->model_name);
		}
		public function edit(){
			return	parent::base_edit($this->model_name);
		}
		public function update(){
			return parent::base_update($this->model_name);
		}
		public function delete(){
			return parent::base_del($this->model_name);
		}
}

?>