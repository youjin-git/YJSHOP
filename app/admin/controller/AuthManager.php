<?php
namespace app\admin\controller;

class AuthManager extends Yj
{
	public function lists(){
			$data = db('auth_group')->paginate(10);
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
	public function access(){
		($id = input('id')) || $this->error('不存在id');
		if($this->request->isPost()){
			
				$post = input();
				var_dump($post['rules']);
				$rules = implode(',',$post['rules']);
				$result = db('auth_group')->where('id',$id)->update(['rules'=>$rules]);
				if($result){
						$this->success('修改成功');
				}else{
						$this->error('修改失败');
				}
		}else{
			//查看用户
			
			//获取权限
			$rules = db('auth_group')->where('id',$id)->value('rules');
			$this->assign('rules',$rules);
			//获取分类
			$lists = model('Menu')->lists();
			$this->assign('lists',$lists);
			return $this->fetch();
		}
	}
	public function group(){
			if($this->request->isPost()){
					$group = input('group');
					$this->request->has('uid')||$this->error('uid不存在');
					$uid = db('auth_group_access')->where('uid',input('uid'))->find();
					if($uid){
						
						$result = db('auth_group_access')->where('uid',input('uid'))->update(['group_id'=>$group]);	
					}else{
						$result = db('auth_group_access')->insert(['uid'=>input('uid'),'group_id'=>$group]);
					}
					$result!==false?$this->success('保存成功',url('member/lists')):$this->error('保持失败');
					
			}else{
				$group_id = db('auth_group_access')->where('uid',input('id'))->value('group_id');
				$this->assign('uid',input('id'));
				$group = db('auth_group')->field('id,title')->select();
				$this->assign('group_id',$group_id);
				$this->assign('group',$group);
				return $this->fetch();
			}
			
			 
	}
}
