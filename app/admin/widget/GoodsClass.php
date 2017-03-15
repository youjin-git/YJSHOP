<?php
namespace app\admin\widget;
use think\Controller;

class GoodsClass extends Controller
{
	 	public function left($id=0){
			 	$class = db('goods_class')->where('status',1)->select();
                $class = $this->list_to_tree($class,$id);
            	$this->assign('goods_class',$class);
	 			return $this->fetch('goods_class/left');
	 	}
	 	public function list_to_tree($array,$current_id=0,$val=0,$id='id',$pid='pid',$child ='_child'){
		if(empty($array)||!is_array($array)){
			return flase;
		}
		$tree = array();
		foreach($array as $key => $v){
				$tree[$v[$id]] = &$array[$key]; 
		}
	
		isset($tree[$current_id])&&($tree[$tree[$current_id]['pid']]['current'] = 1);
	
		$tree1 = array();
		foreach($array as $key=>$v){
				if($v['pid']==$val){
					$tree1[] = &$array[$key];
				}else{
					if(isset($tree[$v[$pid]])){
							$tree[$v[$pid]][$child][] = &$array[$key]; 
					}
				}
		}
	
		return $tree1;
}
	 	
}
?>