<?php 
namespace addons;
class Init{
	public function run(){
				//获取系统配置
				$data = \think\Config::get('app_debug')?[]:cache('hooks');
				if(empty($data)){
						$hooks = \think\Db::name('Hooks')->column('name,addons');
						foreach($hooks as $key =>$value){
								if($value){
									 $map['status']  =   1;
									 $names          =   explode(',',$value);
									 $map['name']    =   array('IN',$names);
									 $data = db('Addons')->where($map)->column('id,name');
									if($data){
										$addons1 = array_intersect($names, $data);
										$addons[$key] = array_map('get_addon_class',$addons1);
										\think\Hook::add($key,$addons[$key]);
									}
								}
						}
						cache('hooks',$addons);
				}else{
					 \think\Hook::import($data, false);
				}
	}
}


?>