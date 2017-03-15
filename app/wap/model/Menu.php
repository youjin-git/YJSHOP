<?php
namespace app\wap\model;
use think\Model;
class Menu extends Model
{	
		protected $createTime = '';
		protected $updateTime = '';
		protected $insert  = ['pid'=>0,'hide'=>0];
	 	public function del($id){
	 		$this->error = '删除失败';
	 		if($this->get_son($id)){
	 				$this->error='请先删除子类';
	 				return false;
	 		}	
	 		return $this->where('id',$id)->delete();
			
		}
		public function lists($name='menu',$val=0,$id='id',$pid='pid',$child ='_child'){
				 $data = db($name)->where($pid,$val)->order('sort')->select();
				 $data = list_to_tree($data,$val,$id,$pid,$child);
			     return  $data;
		}
		public function get_son($id){
			return $this->where('pid',$id)->select();				
		}
		public function update_info($data,$id){
				$id>0||$this->error('id不存在');
				return $this->save($data,['id'=>$id]);
		}
		public function error($msg){
				$this->error=$msg;
				return false;
		}
		
}