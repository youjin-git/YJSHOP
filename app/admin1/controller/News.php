<?php 
namespace app\admin\controller;
class news extends Think{
		public function lists(){
				return	parent::base_lists(32);
		}
		public function add(){
			return	parent::base_add(32);
		}
		public function edit(){
			return parent::base_edit(32);
		}
		public function delete(){
			return parent::base_delete(32);
		}
		
}

?>