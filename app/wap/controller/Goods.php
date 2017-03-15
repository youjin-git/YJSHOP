<?php
namespace app\wap\controller;
use think\Controller;
use think\Db;

class Goods extends controller
{
	//商品列表
    public function lists()
    {
    	input('class_id_2')||$this->err('分类id不存在');
    }
	//商品分类
    public function class_lists($id='2'){
    		$Menus = Model('Menu')->lists('goods_class',$id);
    		succ($Menus);
    }
	public function info(){
			($goods_id = input('goods_id'))||err('goods_id is empty');
			$GoodModel = Model('goods');
			$data = $GoodModel->info($goods_id);
			if($data){
			   	succ($data);
			}else{
				err($data->getError());
			}
	}
}
