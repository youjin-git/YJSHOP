<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Balance extends Model
{
		protected $name="member_balance";
		protected $createTime = 'addtime';
		protected $updateTime = '';
		
		public function save_log($earnings,$type,$member_info){
			//开启事物
			Db::startTrans();
			try{
				switch($type){
					case 1://收益记录
					
					break;
					case 2://提现记录
					$earnings<1&&$this->error('最小金额为1');
					$earnings>$member_info['member_earnings']&&$this->error('你的余额不足');
					$data['member_earnings'] = $member_info['member_earnings'] - $earnings;
   					$data['member_tx_earnings'] = $member_info['member_tx_earnings'] + $earnings;
   					if(Db::name('member')->where('member_id',UID)->update($data)==false){
   						$this->error('修改用户失败');
   					}
   					//添加记录
   					$tx['member_id']=UID;
   					$tx['tx_earnings'] = $earnings; 
   					$tx['add_time'] = time();
   					$tx['state'] = 0;
   					Db::name('member_tx_record')->insert($tx)==false&&$this->error('添加记录失败');
					break;
					default:
					$this->error('type is Error');
					break;
					
			}
				$balance_data['member_id'] = UID;
				$balance_data['balance'] = $earnings;
				$balance_data['type'] = $type;
				$balance_data['add_time'] = time();
				Db::name('member_balance')->insert($balance_data)==false&&$this->error('添加balance失败');
				Db::commit();  
				return true;
			} catch (\Exception $e){
				p($e);
 			   // 回滚事务
	  			Db::rollback();
	  			return false;
			}
			
		}
		public function error($msg){
				$this->error = $msg;
				return false;
		}
	
}