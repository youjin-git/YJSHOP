<?php
namespace app\admin\Validate;
use think\Validate;
class Attribute extends Validate{
		
		protected $rule = [
			['title','require|max:100','字段名称必须|字段名称不能超过100个字符'],
	   		['name','require|alphaDash|unique:model','字段标识必须|字段标识只能为字符|字段标识已经存在'],
	  		['type','require','字段类型必须'],
	  		['field','require','字段定义必须'],
	  		['remark','max:100','备注不能超过100个字符'],
	  		['model_id','require','未选择操作的模型'],
   		];
		
}
