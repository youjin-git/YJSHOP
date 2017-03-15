<?php 
namespace app\admin\controller;
class MemberTxRecord extends Think{
		private $model_name = 'Member_tx_record'; 
		public function lists(){
			return	parent::base_lists($this->model_name);
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