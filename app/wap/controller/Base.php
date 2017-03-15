<?php
namespace app\wap\controller;
use think\Controller;
use think\Db;
//不需要token的类
class Base extends controller
{
	protected function _initialize(){
		header('Access-Control-Allow-Origin: *');
	}
	//普通注册
	public function reg(){
					($phone = input('phone'))||$this->err('手机号码为空');
					is_phone($phone)||$this->err('手机号码不对');
					($code = input('code'))||$this->err('验证码为空');
					$this->checkCode($phone,$code,1)||$this->err('验证码错误');
					$Member = model('Member');
					if($member_id = $Member->reg($phone,input('password'))){
							($token = $Member->login($member_id))?$this->succ($token):$this->err();
					}else{
							$this->err($Member->getError());
					}
	}
	public function class_lists($name='menu',$val=0,$id='id',$pid='pid',$child ='_child'){
			 $data = db($name)->where('status',1)->order('sort')->select();
			 $data = list_to_tree($data,$val,$id,$pid,$child);
			 succ($data);
	}
	
	public function table($table='',$limit=10,$fields='*',$member_fields='',$json='0',$where=[]){
   			$where['member_id'] = UID;
   			switch($table){
   				case 'member_earnings':
   				break;
   				case 'member_tx_record':
   				break;
   				case 'member_balance':
   				break;
   				case 'order':
   				break;
   				default:
   				$this->err('table is Error');
   				break;
   			}
   			$table_join = isset($join)?Db::name($table)->join($join):Db::name($table);
   			$result = $table_join->field($fields)->where($where)->paginate($limit);
   			$data['lists'] = $result->toArray();
   			if($member_fields){
   					$array = $this->info($member_fields,1);	
   					$data['fields'] = is_array($array)?array_values($array):[$array];
   			}
   			if($json){
   				return $data;
   			}
   			$this->succ($data);
   	} 	
   	//查询分页
   	//table--表名
   	public function lists($table='',$where=[],$field=true,$order="",$limit=10,$json='0'){
   			switch($table){
   				case 'goods':
				$where['state'] = 1;
				($where['class_id_2'] = input('class_id_2')) || err('class_id_2 is empty');
   				break;
   				case 'order':
   				break;
   				default:
   				$this->err('table is Error');
   				break;
   			}
   			$data = Db::name($table)->field($field)->where($where)->paginate($limit);
   			if($json){
   				return $data;
   			}
   			$this->succ($data);
   	}
	//手机号登录注册
	public function login($phone='',$password='',$code=''){
			$phone || $this->err('手机号为空');
			is_phone($phone)||$this->err('手机号码不对');
			$member_id = db('member')->where(array('username'=>$phone))->value('id');
			//密码登陆
			$Member = model('Member');
			if($password){
						$password || err('密码为空');
						$data['phone'] = $phone;
						$data['password'] = $password;
						$result = $Member->check($data);
						if($result>0){
							($token = $Member->login($member_id))?$this->succ($token,'登录成功'):$this->err();	
						}else{
							switch($result){
								case -1:
								$this->err('不存在该用户');
								break;
								case -2:
								$this->err('该用户被禁用');
								break; 
								case -3:
								$this->err('密码错误');
							}
						}
						
					//验证码登陆	
			}else{
					$this->checkCode($phone,$code,1)|| err('验证码错误');
					if($member_id){
						($token = $Member->login($member_id))?succ($token,'登录成功'):err('登陆失败');	
							
					}else{	//自动注册
						$this->checkCode($phone,$code,1)||err('验证码错误');
						if($member_id = $Member->reg($phone, 'daojia_'.rand(99999,100000))){
								($token = $Member->login($member_id))?succ($token):err('登录失败');
						}else{
							err($Member->getError());
						}
					}
					
				
			}
			
	}
	//忘记密码
	public function forget_password($phone='',$code='',$password=''){
		$this->checkCode($phone,$code,1);
		$Member = model('Member');
		$Member->update_info(['member_mobile'=>$phone],['member_passwd'=>$password],'Member.forget_password')!==false?$this->succ():$this->err($Member->getError());
	}
	//第三方注册登录
	public function quick_login(){
			($type = input('type')) || $this->err('type is Empty');
			switch($type){
					case 'member_qqopenid':
					case 'member_wxopenid':
					($map[$type] = input($type)) || $this->err($type.' is empty');
					$map['member_name'] = $map[$type];
					break;
					case 'phone':
					$this->login(input($type),input('password'),input('code'));
					break;
					default:
					$this->err('Type Is Wrong');
					break;
			}
			//查询是否有该用户
			$Member = model('Member');
			$member_id = db('member')->where($map)->value('member_id');
			if($member_id){
					($token = $Member->login($member_id))?$this->succ($token):$this->err('登录失败');
			}else{
				//注册这个用户
					//member_avatar
					$this->request->has('member_avatar')&&$map['member_avatar'] = input('member_avatar');
					$this->request->has('member_nickname')&&$map['member_nickname'] = input('member_nickname');
				    $member_id =$Member ->quick_reg($map);
				    if($member_id){
						($token = $Member->login($member_id))?$this->succ($token):$this->err('登录失败');
					}else{
						$this->err('注册失败');
					}
			}
	}
	//发送验证码
	public function sendCode($rules=''){
			 $phone = input('phone');
			 is_phone($phone)||$this->err('手机号码不对');
			 if(!empty($rules))
			 foreach(explode(',',$rules) as $rule){
			 		$count = db('member')->where('member_mobile',$phone)->count();
			 		switch($rule){
			 				case 'exist':
			 				$count||$this->err('手机号码没有注册!');
			 				break;
			 				case 'unexist':
			 				$count&&$this->err('手机号码已经注册!');
			 				break;
			 				default:
			 				$this->err('rules is wrong!');
			 				break;
			 		}
			 }
			 $code = rand(1000,9999);
			 sendSMS($phone,$code);
			 $data['code'] = $code;
			 $data['mobile'] = $phone; 
			 $result = model('Code')->save($data);
		     if($result){
		     	$this->succ([],'验证码发送成功');
		     }else{
		     	$this->err('发送失败');
		     }
	}
	//检测验证码
	public function checkCode($phone='',$code="",$is_bool=0){
			($phone=is_phone($phone)?$phone:0)||$this->err('手机号码不对');
			$code ||$this->err('code不存在');
			$where['mobile'] = $phone;
			$where['code'] = $code;
			$where['addtime'] = array('>',time()-1800);
			$result = db('code')->where($where)->find();
			if($code==9999){
				$result = true;
			}
			if($is_bool){
				return $result?true:$this->err('无效验证码');
			}
			if($result){
				$this->succ();
			}else{
				$this->err('无效验证码');
			}
	}
	protected function err($msg='',$code=404){
				  header("Content-type: text/json");
				  $array = array();
				  $array['code'] = $code;
				  $array['msg'] = $msg;
				  echo json_encode($array);
				  exit;
	}
	protected function succ($data=array(),$msg='SUCC',$code=0){
				  //清除null
				  $array = array();
				  $array['code'] = $code;
				  $array['msg'] = $msg;
				  $array['data'] = $data;
				  header("Content-type: text/json");
				  echo json_encode($array);
				  exit;
	}
	public function query($biao='member'){
			if($phone = input('phone')){
					$a = db('member')->where('member_mobile',$phone)->delete();
					var_dump($a);
			}
			$result = db($biao)->select();
			dump($result);
	}
	public function add_earning(){
			for($i=0;$i<7;$i++){
   			$data['member_id'] = 34;
   			$data['earnings'] = 0.3554+rand(0,100)/1000;
   			$data['order_price'] = '1230';
   			$data['order_id'] = '12,30';
   			$data['rate'] = '0.05';
   			$data['add_time'] = time()-3600*24*$i;
   			$data['add_date'] = date("ymd",time()-3600*24*$i);
   			$a = db('member_earnings')->insert($data);
   			var_dump($a); 
   		}
   	}
   	//二进制上传图片
   	public function uploadImg2(){
       		$data = input('content');
       		isset($data)||$this->err('不存在数据picture');
       		//整理数据
       		 $byte = str_replace(' ','',$data);
      		 $byte = str_replace("<",'',$byte);
     	 	 $byte = str_replace(">",'',$byte);
       		try{
            	$byte=pack("H*",$byte);//要是16进制
       		}catch (Exception $e){
           		$this->err('转换图片失败');
        	}
       		$filename = time().rand(1000,9999).'.jpg';
       		$path = ROOT_PATH.'public'.DS.'uploads'.DS.'images'.DS.'touxiang'.DS.date('Y-m',time()).DS.date('d',time()).DS;
       		createFolder($path)===false&&$this->err('创建文件夹失败');
       		$save_name = $path.$filename;
			$res=file_put_contents($save_name,$byte);
			if($res){
				$this->succ(strchr($save_name,DS.'public'));
			}else{
				$this->err('图片上传失败');
			}
   	}
   	public function uploadImgView(){
   		return $this->fetch();
   	}
   	public function table_lists($table='',$limit=10,$fields=true){
   			$where = [];
   			switch($table){
   				case 'message':
   				$fields = $fields===true?'id,title,picture,time,des,type':'content';
   				$this->request->has('type')||$this->err('type is empty');
   				$where['type'] = input('type');
   				break;
   				case 'goods_class':
   				$fields = $fields===true?'id,name':$fields;
   				break;
   				case 'goods':
   				$order = 'id desc';
   				($where['class_id_2'] = input('class_id_2'))||$this->err('class_id_2 is empty');
   				break;
   				default:
   				$this->err('table is Error');
   				break;
   			}
   			
   			$result = Db::name($table)->field($fields)->where($where)->order($order)->paginate($limit);
   			$data = $result->toArray();
   			$this->succ($data);
   	}
}
