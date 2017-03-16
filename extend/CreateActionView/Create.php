<?php 
//
namespace CreateActionView;
Class Create{
	protected $error = '';
	public function action($model_name){
		
			$name = \think\Loader::parseName($model_name,1);
			$baseUrl = \think\App::$modulePath;
			$filename = $baseUrl .'controller'.DS.$name . EXT;
			 if(is_file($filename)){
					$this->error = '已经存在了控制器,请先删除在生成';
					return false;
			 }else{
				 //读取文件
				 $string = file_get_contents(EXTEND_PATH.'CreateActionView'.DS.'Action.php');
				 if(empty($string)){
						$this->error = EXTEND_PATH.'CreateActionView'.DS.'Action.php不存在';
						return false;
				 }
				 $string = str_replace(['YJSHOP','yjshop'],[$name,$model_name],$string);
				 if(file_put_contents($filename,$string)){
						return true;
				 }else{
						$this->error = '写入失败';
						return false;
				 }
			 }
			
	}
	public function view($model_name){
			$baseUrl = \think\App::$modulePath;
			$filename = $baseUrl .'view'.DS.$model_name;
			if(is_dir($filename)){
				$this->error = '已经存在了view，请先删除在生存';
				return false;
			}else{
				createFolder($filename);
					
			}
			$view = ['add.html','edit.html','lists.html'];
			foreach($view as $v){
					$path = $filename.DS.$v;
					$string = file_get_contents(EXTEND_PATH.'CreateActionView'.DS.'View'.DS.$v);
					if(!file_put_contents($path,$string)){
							$this->error = '写入失败';
							return false;
					}
			}
			return true;
			//读取文件
			
			
	}
	public function getError(){
			return $this->error;
	}
	
	
}

?>