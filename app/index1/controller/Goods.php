<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Goods extends controller
{
    public function lists()
    {
    			
    }
    public function class_lists(){
    		Db::name('goods_class')->where('status',1) = select();
    }
}
