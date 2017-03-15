<?php
namespace app\admin\controller;
use think\Validate;
class Config extends Admin
{
	public function index(){
			
			return $this->fetch();
	}
		
	 protected function parseDocumentList(&$list,$model_id=null){
        $model_id = $model_id ? $model_id : 1;
        $attrList = $this->get_model_attribute($model_id,false,'id,name,type,extra');
        // 对列表数据进行显示处理
        if(is_array($list)){
            foreach ($list as $k=>$data){
                foreach($data as $key=>$val){
                    if(isset($attrList[$key])){
                        $extra      =   $attrList[$key]['extra'];
                        $type       =   $attrList[$key]['type'];
                        if('select'== $type || 'checkbox' == $type || 'radio' == $type || 'bool' == $type) {
                            // 枚举/多选/单选/布尔型
                            $options    =   parse_field_attr($extra);
                            if($options && array_key_exists($val,$options)) {
                                $data[$key]    =   $options[$val];
                            }
                        }elseif('date'==$type){ // 日期型
                            $data[$key]    =   date('Y-m-d',$val);
                        }elseif('datetime' == $type){ // 时间型
                            $data[$key]    =   date('Y-m-d H:i',$val);
                        }
                    }
                }
                $data['model_id'] = $model_id;
                $list[$k]   =   $data;
            }
        }
        //return $list;
    }
	public function get_model_attribute($model_id = 0,$group,$field){
			static $list;
			if(empty($model_id)||!is_numeric($model_id)){
					return false;
			}
			
			//获取属性
			if(!isset($list[$model_id])){
					$data = db('attribute')->where('model_id',$model_id)->field($field)->select();
					$list[$model_id] = $data;
			}
			
			if($group){
				
			
			}else{
					foreach($list[$model_id] as $v){
							$attr[$v['name']] =$v;
					}
				
			}
				return $attr;			
			
	}
	public function add($model=0){
		 $model = db('Model')->where(array('status' => 1))->find($model);
	
		 $model || $this->error('模型不存在！');
		 $Model = db($model['name']);
		 if(Request::instance()->isPOST()){
		 	
		 	
		 }else{
		 	  $fields   =  get_model_attribute($model['id']);
		 	  var_dump($fields);
		 	
		 }
		 
		 //} 
		$this->display();
	}
	public function edit(){
			
		
	}
	 protected function checkAttr($Model,$model_id){
        $fields     =   get_model_attribute($model_id,false);
        $validate   =   $auto   =   array();
        foreach($fields as $key=>$attr){
            if($attr['is_must']){// 必填字段
                $validate[]  =  array($attr['name'],'require',$attr['title'].'必须!');
            }
            // 自动验证规则
            if(!empty($attr['validate_rule'])) {
                $validate[]  =  array($attr['name'],$attr['validate_rule'],$attr['error_info']?$attr['error_info']:$attr['title'].'验证错误',0,$attr['validate_type'],$attr['validate_time']);
            }
            // 自动完成规则
            if(!empty($attr['auto_rule'])) {
                $auto[]  =  array($attr['name'],$attr['auto_rule'],$attr['auto_time'],$attr['auto_type']);
            }elseif('checkbox'==$attr['type']){ // 多选型
                $auto[] =   array($attr['name'],'arr2str',3,'function');
            }elseif('date' == $attr['type']){ // 日期型
                $auto[] =   array($attr['name'],'strtotime',3,'function');
            }elseif('datetime' == $attr['type']){ // 时间型
                $auto[] =   array($attr['name'],'strtotime',3,'function');
            }
        }
        return $Model->validate($validate)->auto($auto);
    }
}
