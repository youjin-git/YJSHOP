<?php
namespace app\wap\validate;
use think\Validate;
class Address extends Validate{
		protected $rule = [
	   		['buy_name','require','姓名为空'],
	   		['buy_tel','require|tel','手机号码为空'],
	   		['address','require|min:6','收货地址为空']
   		];
   	 	protected $scene = [
        	'update'  =>  ['member_mobile'],
        	'forget_password' =>['member_passwd'],
    	];

}
