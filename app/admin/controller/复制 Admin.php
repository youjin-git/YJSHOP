<?php
namespace app\admin\controller;
class Admin extends Think
{
	private $model_name = 'Admin';	
	public function lists(){
			$data = db('admin')->paginate(10);
			foreach($data as $key => &$v){
						if(is_administrator($v['uid'])){
								$v['identity']='超级管理员';
						}else{
								//$a = db()->query('select b.title from config().auth_group_access a left join onethink_auth_group b  on a.group_id = b.id where  a.uid = '.$v['uid'].'');
								$a = db('auth_group_access')->alias('a')->join('auth_group b','a.group_id = b.id','LEFT')->select();
								$v['identity']= isset($a[0]['title'])?$a[0]['title']:'';
						}
						$data[$key] = $v;
			}
			$this->assign('data',$data);
			return $this->fetch();
	}
	public function add(){
		return	parent::base_add($this->model_name);
	}
	public function edit(){
		return parent::base_edit($this->model_name);
	}
		public function update(){
			return parent::base_update($this->model_name);
		}
		public function delete(){
			return parent::base_del($this->model_name);
		}
}
