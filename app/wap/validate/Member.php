<?php
namespace app\Wap\Validate;
use think\Validate;
class Member extends Validate{
		protected $rule = [
	   		['member_mobile','require|unique:member','手机号码为空|手机号码已注册'],
	   		['member_passwd','require|min:6','密码为空|手机密码最少6位字符']
   		];
   	 	protected $scene = [
        	'update'  =>  ['member_mobile'],
        	'forget_password' =>['member_passwd'],
    	];

}
