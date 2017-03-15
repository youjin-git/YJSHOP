<?php
namespace app\wap\controller;
use think\Db;

class Order extends Home
{		//http://www.yj251.com/index.php/wap/order/create_info?token=f5cc1b22e2aa77cc263d2176b8790de0&store_id=5054&goods_id=11&nums=1
		public function create_info(){
				input('store_id') ||err('store_id is empty');
				($goods_id = input('goods_id')) ||err('goods_id is empty');
				($nums = input('nums'))  || err('nums is empty');
				($attr = input('attr'))  || err('attr is empty');
				//获取默认地址
				$nums = explode(',',$nums);
				$attr = explode(',',$attr);
				$address = model('address')->_default();
				//获得商品数据
				foreach(explode(',',$goods_id) as $key => $v){
						($goods = get_value('goods',['id'=>$v],'goods_name,picture,id')) || err('goods_id '.$v.' is wrong');
						($price = model('goods')->check_good($v,$attr[$key],$nums[$key])) ||err($goods['goods_name'].' 库存不足！');
						$goods['goods_price'] = $price;
						$goods['nums'] = $nums[$key];
						$goods['attr'] = $attr[$key];
						$goods_info[] = $goods;
				}
				$order['address']=$address;
				$order['goods_info']=$goods_info;
				//获得优惠券数据
				succ($order);
		}
		public function lists(){
				header('Access-Control-Allow-Origin: *');
				($type = input('type'))||err('type is empty');
				$order = model('order');
				if($data = $order->lists(UID,$type)){
						succ($data);
				}else{
						err($order->getError());
				}
		}
		public function create(){
					($store_id = input('store_id')) ||err('store_id is empty');
					($goods_id = input('goods_id')) ||err('goods_id is empty');
					($nums = input('nums'))  || err('nums is empty');
					($attr = input('attr'))  || err('attr is empty');
					($address_id = input('address_id')) || err('请填写收货地址!');
					//检测商品库存
					$attr = explode(',',$attr);
					$nums = explode(',',$nums);
					$goods_price = 0;
					foreach(explode(',',$goods_id) as $key =>$v){
							($goods_name = get_value('goods',['id'=>$v],'goods_name')) || err('goods_id '.$v.' is wrong');
							
							($goods['price'] = model('goods')->check_good($v,$attr[$key],$nums[$key])) || err($goods_name.' 库存不足！');
							$goods_price += $goods['price'];
							$goods['goods_id'] = $v;
							$goods['nums'] = $nums[$key];
							$goods['attr'] = $attr[$key];
							$goods_info[] = $goods;
					}
					$data['order_code'] = date('ymdhis').rand(1000,9999);
					$data['goods_price'] = $goods_price;
					$order_price = $goods_price;
					$data['order_price'] = $order_price;
					$data['pay_price'] = 0;
					$data['member_id'] = UID;
					$data['add_time'] = time();
					$address = get_value('address',['id'=>$address_id],'buy_name,buy_tel,address,area_id');
					$data = array_merge($data,$address);
					Db::startTrans();
					try{
						$order = Db::name('order');
						 $order ->insert($data);
						$order_id = $order->getLastInsID();
						
						foreach($goods_info as $v){
								$v['order_id'] = $order_id;
								Db::name('order_goods')->insert($v);
						}
						
					    Db::commit();    
					} catch (\Exception $e) {
					    // 回滚事务
					    Db::rollback();
					}
					succ(['order_id'=>$order_id,'price'=>$order_price]);
		}
		public function info(){
				($order_id = input('order_id')) || err('order_id is empty');
				
		}
		
}
