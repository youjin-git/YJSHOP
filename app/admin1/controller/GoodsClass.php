<?php 
namespace app\admin\controller;
class GoodsClass extends Think{
		private $model_name = 'goods_class';
		public function lists(){
			 $model =  db('model')->getByName($this->model_name);
			$Model = model('GoodsClass');
			$data = $Model->lists();
			$this->assign('lists',$data);
			 $this->assign('model',$model);
            return $this->fetch();
		}
		public function add(){
			return	parent::base_add($this->model_name);
		}
		public function edit(){
			return parent::base_edit($this->model_name);
		}
		public function delete(){
			return parent::base_del($this->model_name);
		}
}
?>