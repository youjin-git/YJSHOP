<?php
namespace app\admin\Validate;
use think\Validate;
class Menu extends Validate{
		protected $rule = [
	   		['title','require','菜单名称必须|标识只能为字符|标识已经存在'],
	   		['url','require','菜单链接必须'],
   		];
   		
}
