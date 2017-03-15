<?php
namespace app\admin\widget;
use think\Controller;
use think\Db;
class Yjshop extends Controller
{
	 	public function  index(){
	 		 	return $this->fetch();
		}
	 	public function three_class($name,$value){
	 			$value = input($name);
	 			$lists = [];
	 			while($value){
	 				$data = db('goods_class')->find($value);
	 				$lists[] = $data;
	 				$value = $data['pid'];
	 			}
	 			$this->assign('lists',array_reverse($lists));
	 			return $this->fetch('Yjshop/three_class');
	 	}
	 	public function attr_lists($name,$value){
	 			$data = db('attr')->where('pid',0)->select();
				$arr=[];
				$goods_attr = isset($value['goods_attr'])?$value['goods_attr']:'';
				$sku = $price =[];
				if($value){
					if($goods_attr){
						foreach(explode(',',$goods_attr) as $v){
							$a = explode(':',$v);
							$arr[$a[0]][] = $a[1];
						}
						//查询所有id
						$attr = db('goods_sku')->where('goods_id',$value['id'])->select();
						foreach($attr as $v){
							$sku[$v['attr']] = $v['sku'];
							$price[$v['attr']] = $v['price'];
						}
						$this->assign('attr',$attr);
						//db('goods_sku')->
					}
				}
				$this->assign('sku',$sku);
				$this->assign('price',$price);
				$this->assign('data',$data);
	 			$this->assign('name',$name);
				$this->assign('value',$goods_attr);
				$this->assign('arr',$arr);
				return $this->fetch('Yjshop/attr');
	 	}
		public function add_attr($id,$name){
			 	 $pid = $id;
			 	 $attr = Db::name('attr');
				 if($id = $attr->where(['name'=>$name,'pid'=>$pid])->value('id')){
				 	return $this->success('添加成功',null,$id);
				 }
				 return  $attr->insert(['pid'=>$pid,'name'=>$name])?$this->success('添加成功',null,$attr->getLastInsID()):$this->error('添加失败');
		}
		public function nav_pid($name,$value){
				$list = db('nav')->where('pid',0)->select();
				$this->assign('list',$list);
				$this->assign('name',$name);
				return $this->fetch('Yjshop/nav_pid');
		}
		public function nav_type($name,$value){
						if(empty($value)){
							$value['type'] = '';
							$value['model_id'] = 0;
							$value['url'] = '';
						}
						$list = db('model')->where('type',2)->select();
						$this->assign('list',$list);
						$this->assign('value',$value);
						return $this->fetch('Yjshop/nav_type');
		}
		public function all_model($name,$value){
				$list = db('model')->select();
				$this->assign('list',$list);
				return  $this->fetch('Yjshop/all_model');
		}
		
}
?>