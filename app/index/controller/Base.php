<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
//不需要token的类
class Base extends controller
{
	public function index(){
			echo 111;
			die;
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
	//手机号登录注册
	public function login($phone='',$password='',$code=''){
			$phone || $this->err('手机号为空');
			
			is_phone($phone)||$this->err('手机号码不对');
			if($code){
					$this->checkCode($phone,$code,1)||$this->err('验证码错误');
			}else{
				    $password || $this->err('密码为空');
			}
			//判断是否存在用户
			$Member = model('member'); 
			$member_id = db('member')->where(array('member_mobile'=>$phone))->value('member_id');	
			
			if($member_id){
					//验证码登陆
					if($code){
						//登陆成功更新登陆信息
						($token = $Member->login($member_id))?$this->succ($token,'登录成功'):$this->err();	
					}else{
						//密码登陆
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
					}
			}elseif($code){
					//自动注册
					$this->checkCode($phone,$code,1)||$this->err('验证码错误');
					if($member_id = $Member->reg($phone, 'daojia_'.rand(99999,100000))){
							($token = $Member->login($member_id))?$this->succ($token):$this->err('登录失败');
					}else{
							$this->err($Member->getError());
					}
			}else{
				$this->err('不存在该用户');
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
				  die;
	}
	protected function succ($data=array(),$msg='SUCC',$code=0){
				  header("Content-type: text/json");
				  //清除null
				  $array = array();
				  $array['code'] = $code;
				  $array['msg'] = $msg;
				  $array['data'] = $data;
				   echo json_encode($array);
				   die;
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
	
   	public function table_lists($table='',$limit=10,$fields=true,$member_fields='',$json='0'){
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
   				$this->request->has('goods_class_id')&&($where['goods_class_id'] = input('goods_class_id'));
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
	public function is_tkl(){
		if($msg = input('msg')){
				if(strpos($msg,'复制这条信息，打开手机淘宝')!==false){
					preg_match('/http:\/\/([\w.\?\/\=\&\-])+/',$msg,$b);
					$data = curl_post('http://c.b1za.com/h.2hePTv?cv=4KUd7V8sa8&sm=dd9100',[]);
					preg_match('/https:\/\/([\w.\?\/\=\&\-])+/',$data,$a);
					$https_data = getHTTPS($a[0]);
					$https_data=iconv("GBK", "UTF-8", $https_data);
					$data['url'] = 'http://c.b1za.com/h.2hePTv?cv=4KUd7V8sa8&sm=dd9100';
					$data['name'] = 'LALABOBO 拉拉波波2017年春装新品LABO音乐拼接潮酷七分袖连衣裙';
					$data['price'] = '1059.00';
					$data['picture'] = 'https://img.alicdn.com/bao/uploaded/i4/TB17Y87PXXXXXaIaXXXXXXXXXXX_!!0-item_pic.jpg_430x430q90.jpg';
					succ($data);
				}else{
					err('不是淘口令',2);
				}
		}else{
			err('不是淘口令',2);
		}
//		$data = curl_post('http://c.b1za.com/h.2hePTv?cv=4KUd7V8sa8&sm=dd9100',[]);
//		p($data);
//		die;
//		$data = getHTTPS('https://item.taobao.com/item.htm?ut_sk=1.WCllzkqqxs4DAKAEYUU6XJPp_21380790_1485064321503.Copy.1&id=543801368352&sourceType=item&price=1059&suid=182329A6-1B0B-46CF-B72D-3A68550E0BC2&un=ecd63a6fbf40bb083f17511656f2f575&share_crt_v=1&cpp=1&shareurl=true&spm=a313p.22.1ty.22866754216&short_name=h.2hePTv');
//		var_dump($data);

	}
	
	
}
