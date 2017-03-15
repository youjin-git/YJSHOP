<?php
namespace app\wap\model;
use think\Model;
use think\Db;
class Address extends Model
{
		protected $createTime =  '';
		protected $updateTime = '';
	
		//地址列表
		public function lists(){
				 return  $this->where('member_id',UID)->select();
		}
		//默认地址
		public function _default(){
				$address =  $this->where('member_id',UID)->where('is_default',1)->find();
				return  $address?$address->toArray():'';
		}
		//更新地址
		public function update_info($data){
				return $this->validate(true)->save($data,isset($data['id'])?['id'=>$data['id']]:[]);
		}
		//删除地址
		public function delete(){
			
		}
		public function error($msg){
				$this->error = $msg;
				return false;
		}
	
}