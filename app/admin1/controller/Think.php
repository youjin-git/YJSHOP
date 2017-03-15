<?php 
namespace app\admin\controller;
 
class Think extends Admin{
        public function base_lists($model=0){ 
        		
                $model || $this->error('模型名标识必须');
                $model =  db('model')->getByName($model);
                
                $model || $this->error('模型不存在');
                $fields = array();
                $grids = preg_split('/[;\r\n]+/s',trim($model['list_grid']));
                foreach($grids as &$value){
                    if(empty($value)){
                        continue;
                    }
                    //字段：标题：连接
                    $val = explode(':',$value);
                    //支持多个字段显示
                    $field = explode(',',$val[0]);
                    $value = array('field'=>$field,'title'=>$val[1]);
                    if(isset($val[2])){
                            $value['href'] = $val[2];
                            preg_replace_callback('/\[([a-z_]+)+\]/',function($match)use(&$fields){
                                            $fields[] = $match[1];
                            }, $value['href']);
                            
                    }
                    if(strpos($val[1],'|')){
                            list($value['title'],$value['format']) = explode('|',$val[1]);
                    }
                    foreach($field as $val){
                        $fields[] = explode('|',$val)[0];
                    }   
                }
                
                $fields = array_unique($fields);
                //关键词搜索
                $search = $map = array();
               // $key = $model['search_key']?$model['search_key']:'title';
               // if($this->request->has($key)){
                  //      $map[$key] = array('like','%'.input($key).'%');
                //}
                $search_key = empty($model['search_key'])?[]:preg_split('/[;\r\n]+/s',trim($model['search_key']));
                $get = $this->request->get();
                
                foreach($search_key as &$value){
                		if(empty($value))continue;
                		 $val = explode(':',$value);
                		 $value = array();
                		 list($value['key'],$value['name']) = $val;
                		 list($value['type'],$value['extra']) = db('attribute')->where('name',$value['key'])->field(['type'=>0,'extra'=>1])->find();
                	  	 if($value['type']=='string'){
                	  	 		if(isset($get[$value['key']])&&!empty($get[$value['key']])){
                	  	 			$map[$value['key']] = ['like','%'.$get[$value['key']].'%'];
                	  	 		}
                	  	 }elseif($value['type']=='datetime'||$value['type']=='time'){
                	  	 		 $a = $b = [];
                	  	 		if(isset($get['start_'.$value['key']])&&!empty($get['start_'.$value['key']])){
                	  	 			 $start_time = strtotime($get['start_'.$value['key']]);
                	  	 			 $a = ['>',$start_time];
                	  	 		}
                	  	 		if(isset($get['end_'.$value['key']])&&!empty($get['end_'.$value['key']])){
                	  	 			 $end_time = strtotime($get['end_'.$value['key']]);
                	  	 			 $b = ['<',$end_time];
                	  	 		}	
                	  	 			array_merge($a,$b)&&$map[$value['key']] = array_merge($a,$b);
                	  	 }else{
                	  	 		if(isset($get[$value['key']])&&!empty($get[$value['key']])){
                	  	 			$map[$value['key']] = $get[$value['key']];
                	  	 		}
                	  	 }
                }
                $attrList = get_model_attribute($model['id'],false,'id,name,type,extra');
                
                $row = 10;
                if($model['need_pk']){
                    in_array($model['need_pk'],$fields) || array_push($fields,$model['need_pk']);
                }
                $name = $model['name'];
                $data = db($name)->field($fields)->where($map)->order($model['need_pk']?$model['need_pk'].' DESC':'')
                        ->paginate($row,false,['query'=>request()->get()]);
              	$this->parseDocumentList($data,$model['id'],$attrList);
                $this->assign('model_id',$model['id']);
                $this->assign('list_grids', $grids);
                $this->assign('lists',$data);
                $this->assign('model',$model);
                $this->assign('search_key',$search_key);
                return $this->fetch('lists');
}
		protected function parseDocumentList(&$list,$model_id=null,$attrList){
       		$model_id = $model_id?$model_id:1;
       		$attrList = $attrList?$attrList:get_model_attribute($model_id,false,'id,name,type,extra');
       		if(is_object($list)){
       				foreach($list as $k=>$data){
       					foreach($data as $key=>$val){
       							if(isset($attrList[$key])){
       									$extra = $attrList[$key]['extra'];
       									$type  = $attrList[$key]['type'];
       									if($type=='select'||$type=='checkbox'||$type=="radio"||$type=="bool"){
       											    $options  = parse_field_attr($extra);
       												if($options&&array_key_exists($val,$options)){
       													 $data[$key] = $options[$val];
       												}
       									}elseif('date'==$type){ // 日期型
					                            	$data[$key]    =   date('Y-m-d',$val);
					                    }elseif('datetime' == $type){ // 时间型
					                          	  	$data[$key]    =   date('Y-m-d H:i',$val);
					                    }elseif('widget' == $type){
					                    			
					                    }
       							}
       					}
       					$data['model_id'] = $model_id;
       					$list[$k] =$data;
       				}
       				
       		}
       		  	
       }
       public function base_add($model=0){
       		    $model || $this->error('模型名标识必须');
       			$model =  db('model')->getByName($model);
       			$model || $this->error('模型不存在');
       			if($this->request->isPost()){
       					//验证字段
       					$post = input('post.');
       					$this->toValidate($model['id'],$post,'add');
       				 	db($model['name'])->insert($post)?$this->success('添加成功'):$this->error('未知错误');
       				 	
       			}else{
       					$fields = get_model_attribute($model['id']);
       				
       					$this->assign('model',$model);
       					$this->assign('fields',$fields);
       					return $this->fetch('add');
       			}
       }
       public function base_edit($model=0){
       			$model || $this->error('模型名标识必须');
       			$model =  db('model')->getByName($model);
       			$model || $this->error('模型不存在');
       			
       			if($this->request->isPost()){
	       					($id=input($model['need_pk']))||$this->error('id不存在');
	       					$post = input('post.');
	       					$this->toValidate($model['id'],$post,'edit');
	       					db($model['name'])->where($model['need_pk'],$id)->update($post)!==false?$this->success('修改成功'):$this->error('未知错误');
       			}else{
       					($id=input($model['need_pk']))||$this->error('不存在id');
       					$data = db($model['name'])->find($id);
       					$fields = get_model_attribute($model['id']);
       					$this->assign('data',$data);
       					$this->assign('model',$model);
       					$this->assign('fields',$fields);
       					return $this->fetch('edit');
       			}
       }
      public function toValidate($model_id,&$post,$method){
      	
      			$fields = get_model_attribute($model_id,false);
      			 $validate   =   $auto   =   array();
      			 foreach($fields as $key=>$attr){
      			 	   if($attr['is_must']){// 必填字段
            			  	 $validate[]  =  array($attr['name'],'require',$attr['title'].'必须!');
            			}
      			 		if($attr['validate_rule']){
      			 			$validate[] = array($attr['name'],$attr['validate_rule'],$attr['error_info']);
							
      			 		}
      			 		if(!empty($attr['auto_rule'])) {
      			 			switch($attr['auto_type']){
								default:
								(($attr['auto_time']==1&&$method=='add')||($attr['auto_time']==2&&$method=='edit')||($attr['auto_time']==3))&&($post[$attr['name']] = $attr['auto_rule']());
								break;
							}
      			 		}else{
      			 			switch($attr['type']){
      			 				case 'datetime':
      			 				case 'date':
      			 				isset($post[$attr['name']])&&$post[$attr['name']] = strtotime($post[$attr['name']]);
      			 				break;
      			 				case 'checkbox':
      			 				isset($post[$attr['name']])&&$post[$attr['name']] = implode(',',$post[$attr['name']]);
      			 				break;
      			 			}
							
      			 		}
      			}
      			$result = $this->validate($post,$validate);
      			$result===true||$this->error($result);
      			//自动运行
      		
      			
      }
      public function base_del($model=0){
    		$ids = input('post.ids/a');
    		empty($ids) && $this->error('参数不能为空');
    		foreach($ids as $v){
    				$res = db($model)->delete($v);
    				if(!$res){
    					break;
    				}
    			}
    			if(!$res){
    				$this->error('删除失败');
    			}else{
    				$this->success('删除成功!');
    			}
	  }
}
 
 
?>