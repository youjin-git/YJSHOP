<?php
namespace app\wap\controller;
use think\Controller;
class Home extends controller
{
	protected function _initialize(){
		header('Access-Control-Allow-Origin: *');
		if(defined('UID')) return;
//		if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE") || strpos($_SERVER["HTTP_USER_AGENT"],"Firefox") || strpos($_SERVER["HTTP_USER_AGENT"],"Chrome") || strpos($_SERVER["HTTP_USER_AGENT"],"Safari"))
//  	{
//  			define('UID',session('member_id'));
//  			if(!UID){
//  				$this->err('去登陆');
//  			}
//  	}else{
    	//解析token
				if($token=input('token')){
							$member_id = db('member_token')->where('token',$token)->value('member_id');
							$member_id||$this->err('token不对');
							session('member_id',$member_id);
				}else{
							session('member_id',null);
				}
				define('UID',session('member_id'));
		    	if(!UID){
		    			$this->err('缺少token');
		    	}
//    	}
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
				   $array = array();
				  $array['code'] = $code;
				  $array['msg'] = $msg;
				  $array['data'] = $data;
				  header("Content-type: text/json");
				  echo json_encode($array);
				  exit;
	}
}
