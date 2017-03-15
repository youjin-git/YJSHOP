<?php
namespace app\admin\model;
use think\Model;
class Mo extends Model
{
		protected $name  = 'model';
		protected $createTime =  'create_time';
		protected $updateTime = 'update_time';
		protected $auto  = ['extend'=>0,'field_group'=>'1:基础','status' => 1];
	 	public function del($id){
	 		//获取表明
	 		$model = $this->where('id',$id)->value('name');
	 		
	 		if(empty($model)){
	 				return false;
	 		}
	 		
	 		$table_name = config('database.prefix').strtolower($model);		
	 		//删除属性数据
	 		db('Attribute')->where('model_id',$id)->delete();
	 		$this->where('id',$id)->delete();
	 		     //检查数据表是否存在
        $sql = <<<sql
                SHOW TABLES LIKE '{$table_name}';
sql;
		$res = \think\Db::execute($sql);
		if($res){
				 //删除该表
        $sql = <<<sql
                DROP TABLE {$table_name};
sql;
		$res = \think\Db::execute($sql);
		
		return $res!==false;
		}
		return true;
		}
}