<?php
/* *
 * 类名：ChuanglanSmsApi
 * 功能：创蓝接口请求类
 * 详细：构造创蓝短信接口请求，获取远程HTTP数据
 * 版本：1.3
 * 日期：2014-07-16
 * 说明：
 * 以下代码只是为了方便客户测试而提供的样例代码，客户可以根据自己网站的需要，按照技术文档自行编写,并非一定要使用该代码。
 * 该代码仅供学习和研究创蓝接口使用，只是提供一个参考。
 */

class ChuanglanSmsApi {
	
	public $chuanglan_config;
	
	function __construct()
	{
		global $chuanglan_config;
		require_once(BASE_CORE_PATH."/framework/libraries/ChuanglanSmsHelper/chuanglan_config.php");
		
	}
	/**
	 * 发送短信
	 *
	 * @param string $mobile 手机号码
	 * @param string $msg 短信内容
	 * @param string $needstatus 是否需要状态报告
	 * @param string $extno   扩展码，可选
	 */
	public function sendSMS( $mobile, $msg, $needstatus = 'false', $extno = '') {
		global $chuanglan_config;
		//创蓝接口参数
		$postArr = array (
				          'account' => $chuanglan_config['api_account'],
				          'pswd' => $chuanglan_config['api_password'],
				          'msg' => $msg,
				          'mobile' => $mobile,
				          'needstatus' => $needstatus,
				          'extno' => $extno
                     );
		
		$result = $this->curlPost( $chuanglan_config['api_send_url'] , $postArr);
//		var_dump( $chuanglan_config , $postArr,$result);
		return $result;
	}
	
	function http_request($url,$data = null)
	{	
		if(function_exists('curl_init')){
			$curl = curl_init();
		    curl_setopt($curl, CURLOPT_URL, $url);
		   
		    if (!empty($data)){
		        curl_setopt($curl, CURLOPT_POST, 1);
		        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		    }
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		    $output = curl_exec($curl);
		    curl_close($curl);
			
		
			$result=preg_split("/[,\r\n]/",$output);
	
			if($result[1]==0){
				  return "curl success";
			}else{
				  return "curl error".$result[1];
			}
		}elseif(function_exists('file_get_contents')){
			
			$output=file_get_contents($url.$data);
			$result=preg_split("/[,\r\n]/",$output);
		
			if($result[1]==0){
				  return "success";
			}else{
				  return "error".$result[1];
			}
			
			
		}else{
			return false;
		} 
		
	}

	public function send($mobile, $msg, $needstatus = 'false', $extno = '')
	{
		global $chuanglan_config;
				
		$post_data = array();
		$post_data['account'] = $chuanglan_config['api_account'];//账号
		$post_data['pswd'] = $chuanglan_config['api_password'];//密码
		$post_data['msg']= $msg; 
		$post_data['mobile'] = $mobile;//手机
		$post_data['needstatus']= $needstatus;		
		$res = $this->http_request($chuanglan_config['api_send_url'],http_build_query($post_data));

		return $result;
	}
	
	/**
	 * 查询额度
	 *
	 *  查询地址
	 */
	public function queryBalance() {
		global $chuanglan_config;
		//查询参数
		$postArr = array ( 
		          'account' => $chuanglan_config['api_account'],
		          'pswd' => $chuanglan_config['api_password'],
		);
		$result = $this->curlPost($chuanglan_config['api_balance_query_url'], $postArr);
		return $result;
	}

	/**
	 * 处理返回值
	 * 
	 */
	public function execResult($result){
		$result=preg_split("/[,\r\n]/",$result);
		return $result;
	}

	/**
	 * 通过CURL发送HTTP请求
	 * @param string $url  //请求URL
	 * @param array $postFields //请求参数 
	 * @return mixed
	 */
	private function curlPost($url,$postFields){
		$postFields = http_build_query($postFields);
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postFields );
		$result = curl_exec ( $ch );
		curl_close ( $ch );
		return $result;
	}
	
	//魔术获取
	public function __get($name){
		return $this->$name;
	}
	
	//魔术设置
	public function __set($name,$value){
		$this->$name=$value;
	}
}
?>