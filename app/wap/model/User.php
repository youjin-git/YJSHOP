<?php
namespace app\index\model;
use think\Model;
use think\Session;
class Member extends Model
{
		
		protected $createTime =  'reg_time';
		protected $updateTime = 'last_login_time';
		protected $auto  = ['status' => 1];
	 
	   	// 验证邮箱格式 是否符合指定的域名
	    protected function checkMail($value, $rule)
	    {
	    	   return 1 === preg_match('/^\w+([-+.]\w+)*@' . $rule . '$/', $value);	   
	    }
	    protected function setPasswordAttr($value){
	    			return md5($value);
	    }
	    public function check($data){
	    		$map['username'] = $data['username'];
	    		$user = $this->where($map)->find();
	    		if(empty($user)){
	    			return -1;
	    		}
	    		if($user['status']==0){
	    			return -2;
	    		}
	    		if(md5($data['password']) === $user['password']){
						//$this->updateLogin($user['id']); //更新用户登录信息
						return $user['uid']; //登录成功，返回用户ID
					} else {
						return -3; //密码错误
					}
	    }
	    public function login($uid){
	    		$user = $this->find($uid);
	    	
	    		 if(empty($user) || 1 != $user['status']) {
		            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
		            return false;
       			 }
	    	    /* 更新登录信息 */
		        $data = array(
		          
		            'login'           => array('exp', '`login`+1'),
		            'last_login_time' => time(),
		            'last_login_ip'   => getIp(),
		        );
		        $this->save($data,['uid'=>$user['uid']]);
		        
		        /* 记录登录SESSION和COOKIES */
		        $auth = array(
		            'uid'             => $user['uid'],
		            'username'        => $user['nickname'],
		            'last_login_time' => $user['last_login_time'],
		        );
		        session('admin_user', $auth);
		        
		        //session('user_auth_sign', data_auth_sign($auth));
	    }
	  
	    
	   	
}