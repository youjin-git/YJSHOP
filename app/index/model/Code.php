<?php
namespace app\index\model;
use think\Model;
class Code extends Model
{
		protected $createTime = 'addtime';
		protected $updateTime = '';
		protected $auto  = ['status'=>1];
	    
}