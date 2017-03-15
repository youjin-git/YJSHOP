<?php
namespace app\index\controller;
use think\Controller;
//不需要token的类
class H5 extends controller
{
	//1.分享超级淘-送红包  2.详细教程 3.搜索教程 4.快捷入口教程 5.新手教程 6.常见问题
	public function share(){
		 return $this->fetch();
	}
	public function course(){
			return $this->fetch();
	}
    public function search(){
    		return $this->fetch();
    }
	public function entrance(){
				return $this->fetch();
	}
	public function novice(){
				return $this->fetch('entrance');
	}
	public function problems(){
			return $this->fetch();
	}
	public function boss(){
			return $this->fetch();
	}
	public function reg(){
			echo '等待开通';
	}
}
