<?php
namespace app\index\model;
use think\Model;
use think\Session;
class Member extends Model
{
		protected $createTime =  'member_reg_time';
		protected $updateTime = 'member_login_time';
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
	    			$map['member_mobile'] = $data['phone'];
	    			break;
	    		}
	    		
	    		$user = $this->where($map)->find();
	    	
	    		if(empty($user)){
	    			return -1;
	    		}
	    		if($user['member_state']==0){
	    			return -2;
	    		}
	    		if(md5($data['password']) === $user['member_passwd']){
						//$this->updateLogin($user['id']); //更新用户登录信息
						return $user['member_id']; //登录成功，返回用户ID
					} else {
						return -3; //密码错误
					}
	    }
	    public function login($member_id){
	    	  	 $user = $this->find($member_id);
	    		 if(empty($user) || 1 != $user['member_state']) {
		       	    	 return false;
       			 }
	    	    /* 更新登录信息 */
		        $data = array(
		            'member_login_num'           => array('exp', '`member_login_num`+1'),
		            'member_login_time' => time(),
		            'member_login_ip'   => getIp(),
		        );
		        $this->save($data,['member_id'=>$user['member_id']]);
		        /* 记录登录SESSION和COOKIES */
		        $auth = array(
		            'member_id'             => $user['member_id'],
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
					return 	$this->reg_active($this->getLastInsID());
			
		}
		//快速注册
		public function quick_reg($data){
					return $this->save($data)?$this->reg_active($this->getLastInsID()):false;
		}
		public function reg_active($member_id){
					$time = time();
					$data['order_code'] = $time.$member_id.rand(1000,9999);
					$data['order_price'] = 200.00;
					$data['xd_time'] = $time;
					$data['record_time']=$time;
					$data['into_time'] =$time;
					$data['member_id'] = $member_id;
					$data['order_state'] =10;
					$data['platform'] = 'taobao';
					$goods_info['detail_order_id'] = $data['order_code'];
					$goods_info['auction_id'] = 0;
					$goods_info['real_pay'] = 200;
					$goods_info['auction_pict_url'] = 'http://blttest.bianlidaojia.com.cn/static/public/hb.jpg';
					$goods_info['auction_title'] = '【新手红包】你通过便利淘去淘宝,天猫网购的订单，都会出现在这里！';
					$goods_info['auction_amount'] = 1;
					$data['auction_infos'] =json_encode([$goods_info]);
					db('order')->insert($data);
					return $member_id;
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