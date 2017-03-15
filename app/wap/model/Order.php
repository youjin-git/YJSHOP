<?php
namespace app\wap\model;
use think\Model;
use think\Db;
class Order extends Model
{
//		0:取消
//		10:默认
//		20:已经付款
//		30:已经发货了
//		40:交易完成
// 		50:退货
		public function lists($uid,$type){
						$uid||$this->error('用户id不存在');
						switch($type){	
							case 1:
							$where = '1=1';
							break;
							case 2:
							$where['order_state'] = ['in','20,30'];
							break;
							case 3:
							$where['order_state'] = 40;
							break;
							default:
							$where['order_state'] = $type;
							break;
						}
						$data = $this->where($where)->paginate(10)->toArray();
						foreach($data['data'] as &$v){
								$v['_lists'] = db('order_goods')->where('order_id',$v['order_id'])->select();
						}
						return $data;
		}
		//商品信息
		public function info($order_Id,$member_id){
				
		}
		//判断库存
		public function error($msg){
				$this->error = $msg;
				return false;
		}
		
}