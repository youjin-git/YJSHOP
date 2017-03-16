<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use app\admin\model\Member;
class Base extends controller
{
	
	public function login(Request $request){
			if($request::instance()->isPost()){
			    //检测验证码
			    // $captcha = new \think\captcha\Captcha();
			    // $verify = input('post.verify');
			    // if (!$captcha->check($verify)) {
           				 // $this->error('验证码错误');
      			// }
			    $Member = new Member;
 			    $data['username'] = input('post.username');
			    $data['password'] = input('post.password');
			    $status = $Member->check($data);
			    if($status>0){
			    	$Member->login($status);
			    	$this->success('登录成功',url('Index/index'));
			    }else{
			    	$this->getError($status);
			    }
			 
			}else{
				if(is_login()){
                	 $this->redirect('Index/index');
           		 }else{
				 	return $this->fetch();
				 }
			
			}
	}
	public function getError($status){
		switch($status){
			case -1:
			$msg = '用户名不存在';
			break;
			case -3:
			$msg  = '密码错误';
			break;
			case -2:
			$msg = '用户被禁止';
			break;
		}
		$this->error($msg);
	}
	  public function logout(){
        if(is_login()){
            session(null);
            $this->success('退出成功！', url('login'));
        } else {
            $this->redirect('login');
        }
    }
}
