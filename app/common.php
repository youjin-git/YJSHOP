<?php

function sendSMS($phone,$content)
{
	
  // include('/extend/ChuanglanSmsHelper/ChuanglanSmsApi.php');
    $clapi  = new \ChuanglanSmsHelper\ChuanglanSmsApi();
    
    $result = $clapi->sendSMS($phone, $content.'【便利淘】','true');
    $result = $clapi->execResult($result);
    if(isset($result[1]) && $result[1]==0)
    {
        	return 1;
    }else
    {
        return $result[1];
    }

}
function is_phone($phone){
	 return 1 ===  preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $phone);
}
function err($msg='',$code=404){
				  header("Content-type: text/json");
				  $array = array();
				  $array['code'] = $code;
				  $array['msg'] = $msg;
				  echo json_encode($array);
				  exit;
}
function succ($data=array(),$msg='SUCC',$code=0){
			
				  $array = array();
				  $array['code'] = $code;
				  $array['msg'] = $msg;
				  $array['data'] = $data;
				  header("Content-type: text/json");
				  echo json_encode($array);
				  exit;
}
function createFolder($path)
{
    if (!file_exists($path))
    {
       	 createFolder(dirname($path));
       	 if(!mkdir($path, 0777)){//0777可以不写  
       	 	return false;
       	 }
    }
}
function get_value($table,$where,$field){
		return strpos($field, ',')?db($table)->where($where)->field($field)->find():db($table)->where($where)->value($field);
}
include ('common/common/function.php');
