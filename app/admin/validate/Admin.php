<?php
namespace app\admin\Validate;
use think\Validate;
class Admin extends Validate{
		protected $rule = [
	   		['username','require|unique:admin','用户名为空|用户名已注册'],
	   		['password','require|min:6','密码为空|手机号码最少6位字符']
   		];
}