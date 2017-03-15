<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
//不需要token的类
class System extends controller
{
	//订单收益
	public function into_order(){
			$order = db::name('order')->where('order_state',10)->select();
			foreach($order as $v){
					Db::startTrans();
					try{
						//修改订单状态 
						 Db::name('order')->where('order_id',$v['order_id'])->update(['order_state'=>20,'into_time'=>time()]);
						//修改member_info
						 Db::name('member')->where('member_id',$v['member_id'])->update(['order_price'=>['exp','`order_price`+'.$v['order_price']],'order_nums'=>['exp','`order_nums`+1']]);
						 Db::commit();
					} catch (\Exception $e){
    					 // 回滚事务
    					  Db::rollback();
	    				  $this->err($v['order_id'].'is wrong');
					}
			}
			$this->succ('ok');
	}
	//定时器添加所有用户的收益
	public function earning(){
				$time = time();
				$date = date('ymd');
				p($time);
				p($date);
				//计算今日收益
				$a = (int)(time()%(3600*24*9)/(3600*24));
				$member = Db::name('member')->where('order_price','gt',0)->where('member_state',1)->field('member_id,order_price,day_rate,member_all_earnings,member_earnings')->select();
				foreach($member as $v){
						//查询今日是否获得收益
						$count = Db::name('member_earnings')->where('member_id',$v['member_id'])->where('add_date',$date)->count();
						if($count){
							continue;
						}
						//计算今日收益
						$rand = mt_rand(150,300)/100000;
						$rand = $a<5?$rand:-$rand;
						$earning = $v['order_price']*$rand;
						Db::startTrans();
						try{
								
								//修改用户的利率和收益和总收益
								$earnings = $member_info = array();
								$member_info['day_rate'] = $v['day_rate']+$rand;
								$member_info['member_all_earnings'] = $v['member_all_earnings']+$earning;
								$member_info['member_earnings'] = $v['member_earnings']+$earning;
								
								Db::name('member')->where('member_id',$v['member_id'])->update($member_info);
								
							 	$earnings['member_id'] = $v['member_id'];
							 	$earnings['earnings'] = $earning;
							 	$earnings['order_price'] = $v['order_price'];
							 	$earnings['add_time'] = $time;
							 	$earnings['add_date'] = $date;
							 	$earnings['rate'] =  $member_info['day_rate'];
							 	Db::name('member_earnings')->insert($earnings);
								//添加收益记录
								Db::commit();
						}catch(\Exception $e){
								p($e);
								Db::rollback();
		    					 // 回滚事务
			    				$this->err('error');
			   					
						}
					//	$this->succ('ok');
				}
	}
	protected function err($msg='',$code=404){
				  header("Content-type: text/json");
				  $array = array();
				  $array['code'] = $code;
				  $array['msg'] = $msg;
				  echo json_encode($array);
				  exit;
	}
	protected function succ($data=array(),$msg='SUCC',$code=0){
				  //清除null
				  $array = array();
				  $array['code'] = $code;
				  $array['msg'] = $msg;
				  $array['data'] = $data;
				  header("Content-type: text/json");
				  echo json_encode($array);
				  exit;
	}
	
	
}
