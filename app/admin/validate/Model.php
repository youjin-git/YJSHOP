<?php
namespace app\admin\Validate;
use think\Validate;
class Model extends Validate{
		protected $rule = [
	   		['name','require|alphaDash|unique:model','标识必须|标识只能为字符|标识已经存在'],
	   		['title','require','标题必须'],
   		];
   		
}
