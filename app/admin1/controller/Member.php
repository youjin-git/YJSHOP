<?php
namespace app\admin\controller;
class Member extends Admin
{
	public function lists(){
			$data = db('admin')->paginate(10);
			$Auth = new \app\Auth();
		
			foreach($data as $key => &$v){
						if(is_administrator($v['uid'])){
								$v['identity']='超级管理员';
						}else{
								$a = db()->query('select b.title from onethink_auth_group_access a left join onethink_auth_group b  on a.group_id = b.id where  a.uid = '.$v['uid'].'');
								
								$v['identity']= isset($a[0]['title'])?$a[0]['title']:'';
						}
						$data[$key] = $v;
			}
			$this->assign('data',$data);
			return $this->fetch();
	}
	public function add(){
		if($this->request->isPost()){
				$post = input();
				$Member = model('Member');
				if($Member->validate(true)->add($post)){
					$this->success('新增成功',url('member/lists'));
				}else{
					$this->error($Member->getError());
				}
		}else{
				return $this->fetch();
		}
	}
	public function edit(){
			return $this->fetch();
	}
}
