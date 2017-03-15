<?php
namespace app\admin\widget;
use think\Controller;

class Cms extends Controller
{
	 	public function left($id=0){
			 	$class = db('nav')->where('is_show',1)->select();
                $class = $this->list_to_tree($id);
            	$this->assign('goods_class',$class);
	 			return $this->fetch('cms/left');
	 	}
	 	public function list_to_tree($id){
				$groups = db('nav')->order('sort')->select();
				$current= '';
				$list = [];
				foreach($groups as $v){
					$key = $v['pname']?$v['pname']:$v['name'];
					($v['id']==$id)&&$current = $key;
					$list[$key][] = $v;
				}
				$this->assign('id',$id);
				$this->assign('current',$current);
				return $list;
		}
	 	
}
?>