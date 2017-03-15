<?php
namespace app\Wap\model;
use think\Model;
use think\Session;
class Member extends Model
{
		protected $createTime =  'reg_time';
		protected $updateTime = 'login_time';
		protected $insert = ['member_reg_ip'];
	    protected function setMemberPasswdAttr($value){
	    			return md5($value);
	    }
	    protected function setMemberRegIpAttr(){
	    			return getIp();
	    }
	    public function check($data,$type=0){
	    		switch($type){
	    			case 0:
	    			default:
	    			$map['username'] = $data['phone'];
	    			break;
	    		}
	    		$user = $this->where($map)->find();
	    		if(empty($user)){
	    			return -1;
	    		}
	    		if($user['state']==0){
	    			return -2;
	    		}
	    		if(md5($data['password']) === $user['password']){
						//$this->updateLogin($user['id']); //更新用户登录信息
						return $user['id']; //登录成功，返回用户ID
					} else {
						return -3; //密码错误
					}
	    }
	    public function login($member_id){
	    	  	 $user = $this->find($member_id);
	    		 if(empty($user) || 1 != $user['state']) {
		       	    	 return false;
       			 }
	    	    /* 更新登录信息 */
		        $data = array(
		            'login_num'           => array('exp', '`login_num`+1'),
		            'login_time' => time(),
		            'login_ip'   => getIp(),
		        );
		        $this->save($data,['id'=>$user['id']]);
		        /* 记录登录SESSION和COOKIES */
		        $auth = array(
		            'id'=> $user['id'],
		        );
		        session($auth);
		        return $this->token($member_id);
		     
	    }
	    //token 添加
		public function token($member_id){
				if($member_id){
						  $tokenInfo = array();
						  $time=time();
			              $tokenInfo['token'] = md5($member_id.$time);
			              $tokenInfo['deviceId'] = $member_id.$time;
			              $tokenInfo['member_id'] = $member_id;
			              $tokenInfo['starttime'] = $time;
			              $tokenInfo['status'] = 0;
			              $result = db('member_token')->insert($tokenInfo);
			              return $result?$tokenInfo['token']:err('生成token失败');
				}else{
					err('不存在member_id');
				}
		}
		//手机注册
		public function reg($phone,$password){
					//开始注册
					$data['member_name'] = $phone;
					$data['member_mobile'] = $phone;
					$data['member_passwd'] = $password;
					$data['member_reg_ip'] = getIp();
					$data['member_mobile_bind'] = 1;
					$member_id = $this->validate(true)->save($data);
				
					return 	$this->getLastInsID();
			
		}
		//快速注册
		public function quick_reg($data){
					return $this->save($data)?$this->getLastInsID():false;
					
		}
	  	public function info($member_id=0,$fields='*'){
    		$member_id || err('不存在id');
    		if(strpos($fields,',')||$fields=='*'){
    			$result = db('member')->where('member_id',$member_id)->field($fields)->find();
    		}else{
    			$result = db('member')->where('member_id',$member_id)->value($fields);
    		}
    		return $result;
    	}
	    public function update_info($where,$data,$validate=true){
	    	
	    	return $this->validate($validate)->save($data,$where);
	  
	    }
	   	
}