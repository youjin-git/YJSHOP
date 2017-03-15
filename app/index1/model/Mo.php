<?php
namespace app\index\model;
use think\Model;
class Mo extends Model
{
		protected $name  = 'model';
		protected $createTime =  'create_time';
		protected $updateTime = 'update_time';
		protected $auto  = ['extend'=>0,'need_pk'=>1,'field_group'=>'1:基础','status' => 1];
	 	
}