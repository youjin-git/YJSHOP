<?php
namespace app\wap\controller;
use think\Db;

class Cart extends Home
{
		
		public function lists(){
					$cart = Db::name('cart')->where('member_id',UID)->field('store_id,goods_id,nums,attr')->select();
					$data = [];
					foreach($cart as &$v){
							 
							 $v['goods_price'] = get_value('goods_sku',['goods_id'=>$v['goods_id'],'attr'=>$v['attr']],'price');
							 $v = array_merge(get_value('goods',['id'=>$v['goods_id']],'goods_name,picture'),$v);
							 $data[$v['store_id']]['_lists'][] = $v;
					}
//					foreach($goods_info as $key=>$v){
//							
//					}
					succ($data);
		}
		public function check_good($goods_id,$attr,$nums){
				
				return	$nums<=db::name('goods_sku')->where('goods_id',$goods_id)->where('attr',$attr)->value('sku');		
		}
		//http://127.0.0.1/BLT/wap/cart/update?token=75e1e3f2378bcbdb89cd6389dc5e8410&&attr=76-71
		public function update($store_id=0,$goods_id=0,$attr,$nums=1){	
					$cart = db::name('cart');
					$result = $cart->where('store_id',$store_id)->where('goods_id',$goods_id)->where('member_id',UID)->find();
					var_dump($result);
					if($result){
						if($nums>0){
							$nums += $result['nums'];	
							$nums>0&&$this->check_good($goods_id,$attr,$nums)||err('库存不足');
						}else{
							$nums += $result['nums'];
							$nums<=0&&db::name('cart')->where('id',$result['id'])->delete()&&succ();
						}
						$result = db::name('cart')->where('id',$result['id'])->update(['nums'=>$nums]);
					//添加	
					}else{
						$this->check_good($goods_id,$attr,$nums)||err('库存不足');
						$data['store_id'] = $store_id;
						$data['goods_id'] = $goods_id;
						$data['attr'] = $attr;
						$data['member_id'] = UID;
						$data['nums'] = $nums;
						$result = db::name('cart')->insert($data);
					}
					$result?succ():err('操作失败');
		}
		public function  listsOp(){
   			($buyer_id  =$_SESSION['member_id'])||err('member_id is empty');
   		 	$param['table'] = 'cart';
   		 	$param['where'] = 'buyer_id='.$buyer_id;
   		 	$data = db::select($param);
   			$data1 = [];
   		 	foreach ($data as $key => $value){
   		 			$goods_info  = db::getRow(['table'=>'goods','field'=>'goods_id','value'=>$value['goods_id']]);
   		 			$value['goods_name'] = $goods_info['goods_name'];
   		 			$value['goods_image'] =  $goods_info['goods_image'];
   		 			$value['goods_price'] = $goods_info['goods_price'];
   		 			$data1[$value['store_id']]['store_id'] = $value['store_id'];
   		 			$data1[$value['store_id']]['store_name'] = $value['store_name'];
   		 			$data1[$value['store_id']]['goods_info'][] = $value;
   		 	}
   		 	sort($data1);
   		 	$lists['lists'] = empty($data1)?[]:$data1;
   		 	list($lists['nums'],$lists['price']) = $this->cart_count($buyer_id);
   		 	succ($lists);
   		}
   	//8b91297ba4efe2a8c0a02cc1cd0e0bbf
  	 	public function updateOp(){
   			($store_id = $_REQUEST['store_id'])||err('store_id is empty');
   			($buyer_id  =$_SESSION['member_id'])||err('member_id is empty');
   			($goods_id = $_REQUEST['goods_id'])||err('goods_id is empty');
   			$nums = $_REQUEST['nums']?$_REQUEST['nums']:1;
   			//获取goods库存
   			$goods_info = $this->goods_info($goods_id);
   			//获取store_id信息
   			$store_info = $this->store_info($store_id);
   			$param['table'] = 'cart';
   			$param['field'] = ['buyer_id','store_id','goods_id'];
   			$param['value'] = [$buyer_id,$store_id,$goods_id];
   			$cart_info = db::getRow($param,'cart_id,goods_num');
   			if(empty($cart_info)){
   				//添加一条购物车记录
   				$data['buyer_id'] = $buyer_id;
   				$data['store_id'] = $store_id;
   				$data['store_name'] = $store_info['store_name'];
   				$data['goods_id'] = $goods_id;
   				$data['goods_num'] = $nums;
   				$nums<1&&err('nums必须大于1');
   				if(db::insert('cart',$data)){
   					succ($this->cart_count($buyer_id),'添加成功');
   				}else{
   					err('添加失败');
   				}
   			}else{
   				$data['goods_num'] = $nums+$cart_info['goods_num'];
   				if($data['goods_num']<=0){
   						$this->delete($store_id,$buyer_id,$goods_id);
   				}
   			  	if(db::update('cart',$data,'cart_id='.$cart_info['cart_id'])){
   			  		succ($this->cart_count($buyer_id),'添加成功');
   			  	}else{
   			  		err('添加失败');
   			  	}
   			}	
  	}
   	public function deleteOp(){
   			$store_id = $_REQUEST['store_id']?$_REQUEST['store_id']:0;
   			($buyer_id  =$_SESSION['member_id'])||err('member_id is empty');
   			$goods_id = $_REQUEST['goods_id']?$_REQUEST['goods_id']:0;
   			$this->delete($store_id,$buyer_id,$goods_id);
   			
   	}
   	//删除
   	public function delete($store_id,$buyer_id,$goods_id=0){
   			$where[] = '1=1';
   			$where[] = 'buyer_id ='.$buyer_id;
   			$store_id&&($where[] = "store_id in (".$store_id.")");
   			$goods_id&&($where[] = "goods_id in (".$goods_id.")");
   			if(db::delete('cart',implode(' and ',$where))){
   					succ($this->cart_count($buyer_id));
   			}else{
   					err('删除失败');
   			}
   	}
   	public function cart_count1($buyer_id,$store_id=0){
   		$param['table'] = 'cart';
   		//$param['where'] = 'store_id = '.$store_id.' and buyer_id = '.$buyer_id;
   		$param['where'] = 'buyer_id = '.$buyer_id;
   		$param['field'] = 'sum(goods_num) as nums';
		
   		$data = db::select($param);
   		return  empty($data[0]['nums'])?0:$data[0]['nums'];
   	}
	public function cart_count($buyer_id,$store_id=0){
   		$param['table'] = 'cart';
   		//$param['where'] = 'store_id = '.$store_id.' and buyer_id = '.$buyer_id;
   		$param['where'] = 'buyer_id = '.$buyer_id;
   		$data = db::select($param);
		$price = $nums = 0;
		foreach($data as $v){
				$nums += $v['goods_num'];
				$goods_info = db::getRow(['table'=>'goods','field'=>'goods_id','value'=>$v['goods_id']]);
				$price += $v['goods_num']*$goods_info['goods_price'];
		}
   		return  [$nums,(string)$price];
   	}
   	
   	public function goods_info($id,$nums=1){
   		$data = db::getRow(['table'=>'goods','field'=>'goods_id','value'=>$id]);
   		if(empty($data)){
   			err('商品不存在');
   		}
   		if($data['is_delete']==1||$data['goods_state']!=1){
   			err('商品已经下架');
   		}
   		if($data['goods_storage']<$nums){
   			err('商品库存不足');
   		}
   		return $data;
   	}
   	public function store_info($store_id){
			$data = db::getRow(['table'=>'store_info','field'=>'store_id','value'=>$store_id]);
   			if(empty($data)){
   				err('有这个店铺？');
   			}
   			return $data;
   	}
}
