<?php
namespace app\index\controller;
use think\Controller;
//不需要token的类
class H5 extends controller
{
	//1.分享超级淘-送红包  2.详细教程 3.搜索教程 4.快捷入口教程 5.新手教程 6.常见问题
	public function share(){
		 	echo '分享超级淘-送红包 ';
			
	}
	public function course(){
			echo '详细教程';
	}
    public function search(){
    		echo '搜索教程';
    }
	public function entrance(){
			echo '入口';
	}
	public function novice(){
			 echo '新手';
	}
	public function problems(){
			echo '常见问题';
	}
}
