<?php
namespace com;
use \think\Db;
class Database{
	private $fp;
	
	private $file;
	
	private $size = 0;
	private $config;
	public function __construct($file,$config,$type='export'){
			$this->file = $file;
			$this->config = $config;
	}
	public function create(){
		$sql  = "-- -----------------------------\n";
        $sql .= "-- SentCMS MySQL Data Transfer \n";
        $sql .= "-- \n";
        $sql .= "-- Host     : " . config('database.hostname') . "\n";
        $sql .= "-- Port     : " . config('database.hostport') . "\n";
        $sql .= "-- Database : " . config('database.database') . "\n";
        $sql .= "-- \n";
        $sql .= "-- Part : #{$this->file['part']}\n";
        $sql .= "-- Date : " . date("Y-m-d H:i:s") . "\n";
        $sql .= "-- -----------------------------\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
		return $this->write($sql);
	}
	public function write($sql){
		$size = strlen($sql);
		$size = $this->config['compress']?$size/2:$size;
		$this->open($size);
		return $this->config['compress']?@gzwrite($this->fp,$sql):@fwrite($this->fp,$sql);
	}
	public function open($size){
		if($this->fp){
			$this->size +=$size;
			if($this->size>$this->config['part']){
				 $this->config['compress'] ? @gzclose($this->fp) : @fclose($this->fp);	
				 $this->fp = null;
				 $this->file['part']++;
				 session('backup_file', $this->file);
				 $this->create();
			}
		}else{
			$backuppath = $this->config['path'];
			$filename = "{$backuppath}{$this->file['name']}-{$this->file['part']}.sql";
			if($this->config['compress']){
				$filename = "{$filename}.gz";
				$this->fp = @gzopen($filename,"a{$this->config['level']}");
			}else{
				$this->fp = @fopen($filename,'a');
			}
			$this->size =  filesize($filename)+$size;	
		}
		
	}
	public function backup($table,$start){
		$db = \think\Db::connect();
		if(0 == $start){
			$result = $db->query("SHOW CREATE TABLE `{$table}`");
			$sql = "\n";
			$sql .= "-- -----------------------------\n";
			$sql .= "-- Table structure for `{$table}`\n";
			$sql .= "-- -----------------------------\n";
			$sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
			$sql .= trim($result[0]['Create Table']) . ";\n\n";
			if(false === $this->write($sql)){
                return false;
            }
			$result = $db->query("SELECT COUNT(*) AS count FROM `{$table}`");
			$count  = $result['0']['count'];
				 //备份表数据
			if($count){
				//写入数据注释
				if(0 == $start){
					$sql  = "-- -----------------------------\n";
					$sql .= "-- Records of `{$table}`\n";
					$sql .= "-- -----------------------------\n";
					$this->write($sql);
				}
				//备份数据记录
				$result = $db->query("SELECT * FROM `{$table}` LIMIT {$start}, 1000");
				foreach ($result as $row) {
					$row = array_map('addslashes', $row);
					$sql = "INSERT INTO `{$table}` VALUES ('" . str_replace(array("\r","\n"),array('\r','\n'),implode("', '", $row)) . "');\n";
					if(false === $this->write($sql)){
						return false;
					}
				}
				//还有更多数据
				if($count > $start + 1000){
					return array($start + 1000, $count);
				}
			}
				//备份下一表
				return 0;
			}
	}
	
}
?>