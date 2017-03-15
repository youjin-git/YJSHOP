<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Auth extends Model
{
	    /* 操作的表名 */
    	protected $table_name = null;
		protected $createTime =  '';
		protected $updateTime = '';
		public function update_info($data,$create=true){
				//判断是否存在model
				$model = db('model')->find($data['model_id']);
				if(empty($model)){
					$this->error='不存在model';
					return false;
				}
				//检测数据				
				if(isset($data['id'])&&!empty($data['id'])){
					$result = $this->validate(true)->save($data,['id'=>$data['id']]);
					if(!$result){
						return false;
					}
					if($create){
						$res = $this->updateField($data);
						if(!$res){
							$this->error('编辑属性失败');
							$this->delete($result);
						
						}
					}
				}else{
					$result = $this->validate(true)->save($data);
						if(!$result){
							return false;
						}
					if($create){
						$res = $this->addField($data);
						if(!$res){
							$this->error('新增属性失败');
							$this->delete($result);
						
						}
					}
				}
				//重置缓存
				
				return $data;
				
		}
		protected function addField($field){
		//判断表是否存在
		$table_exist = $this->checkTableExist($field['model_id']);
		
        //获取默认值
        if($field['value'] === ''){
            $default = '';
        }elseif (is_numeric($field['value'])){
            $default = ' DEFAULT '.$field['value'];
        }elseif (is_string($field['value'])){
            $default = ' DEFAULT \''.$field['value'].'\'';
        }else {
            $default = '';
        }

        if($table_exist){
            $sql = <<<sql
                ALTER TABLE `{$this->table_name}`
ADD COLUMN `{$field['name']}`  {$field['field']} {$default} COMMENT '{$field['title']}';
sql;
        }else{
            //新建表时是否默认新增“id主键”字段
            $model_info = db('Model')->field('engine_type,need_pk')->getById($field['model_id']);
            if($model_info['need_pk']){
                $sql = <<<sql
                CREATE TABLE IF NOT EXISTS `{$this->table_name}` (
                `id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
                `{$field['name']}`  {$field['field']} {$default} COMMENT '{$field['title']}' ,
                PRIMARY KEY (`id`)
                )
                ENGINE={$model_info['engine_type']}
                DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
                CHECKSUM=0
                ROW_FORMAT=DYNAMIC
                DELAY_KEY_WRITE=0
                ;
sql;
            }else{
                $sql = <<<sql
                CREATE TABLE IF NOT EXISTS `{$this->table_name}` (
                `{$field['name']}`  {$field['field']} {$default} COMMENT '{$field['title']}'
                )
                ENGINE={$model_info['engine_type']}
                DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
                CHECKSUM=0
                ROW_FORMAT=DYNAMIC
                DELAY_KEY_WRITE=0
                ;
sql;
            }

        }
       
        $res = Db::execute($sql);
        var_dump($res);
        return $res !== false;

		}
		protected function checkTableExist($field_id){
				$model = db('model')->field('name,extend')->find($field_id);
				$table_name = $this->table_name = config('database.prefix').strtolower($model['name']);
				
				  $sql = <<<sql
                SHOW TABLES LIKE '{$table_name}';
sql;

				$res = Db::query($sql);
				
				return $res;
		}
	 protected function updateField($field){
        //检查表是否存在
        $table_exist = $this->checkTableExist($field['model_id']);

        //获取原字段名
        $last_field = $this->getFieldById($field['id'], 'name');

        //获取默认值
        $default = $field['value']!='' ? ' DEFAULT '.$field['value'] : '';

        $sql = <<<sql
            ALTER TABLE `{$this->table_name}`
CHANGE COLUMN `{$last_field}` `{$field['name']}`  {$field['field']} {$default} COMMENT '{$field['title']}' ;
sql;
        $res = Db::execute($sql);
        return $res !== false;
   	 }
   
}