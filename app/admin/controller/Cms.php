<?php 
namespace app\admin\controller;
use \think\Db;
class Cms extends Think{
		public function lists($nav_id=0){
			  $nav = db('nav')->find($nav_id);
			  $this->assign('data',$nav);
			  switch($nav['type']){
			  		case 1:
					return $this->fetch('content_edit');
					break;
					case 2:
					$model_id = $nav['model_id'];
					break;
					case 3:
					return $this->fetch('url_edit');
					break;
					default:
					return $this->fetch('index');
					break;
			  }
			  //分类
			  $model =  db('model')->find($model_id);
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
                $map['nav_id']=$nav_id;
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
                	  	 		if(isset($get[$value['key']])&&$get[$value['key']]!==''){
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
		public function add($nav_id=0){
				$nav = db('nav')->find($nav_id);
				$model_id = $nav['model_id'];
			    $model_id || $this->error('模型名标识必须');
       			$model =  db('model')->find($model_id);
       			$model || $this->error('模型不存在');
       			if($this->request->isPost()){
       					//验证字段
       					$post = input('post.');
       					$this->toValidate($model['id'],$post,'add');
						if(isset($post['price'])&&($price = $post['price']))unset($post['price']);
						if(isset($post['sku'])&&($sku = $post['sku']))unset($post['sku']);
						$a = \think\Db::name($model['name']);
						$a->insert($post);
						
						$goods_id = $a->getLastInsID();
						if(isset($price)){
							foreach($price as $key => $v){
										$data['attr'] = $key;
										$data['price'] = $v;
										$data['sku'] = $sku[$key];
										$data['goods_id'] = $goods_id;
										db('goods_sku')->insert($data);
							}
						}
       				 	$goods_id?$this->success('添加成功'):$this->error('未知错误');
       			}else{
       					$fields = get_model_attribute($model['id']);
       					$this->assign('model',$model);
       					$this->assign('fields',$fields);
       					return $this->fetch('add');
       			}
		}
		public function edit(){
				$model  = $this->model_name;
				$model || $this->error('模型名标识必须');
       			$model =  db('model')->getByName($model);
       			$model || $this->error('模型不存在');
       			if($this->request->isPost()){
       				
       						($id=input($model['need_pk']))||$this->error('id不存在');
							$goods =  Db::name($model['name']);
							$data = $goods->find($id);
							$post = input('post.');
							
							if(isset($post['price'])&&($price = $post['price']))unset($post['price']);
							if(isset($post['sku'])&&($sku = $post['sku']))unset($post['sku']);
							
							$this->toValidate($model['id'],$post,'edit');
							Db::startTrans();		
							try{
								Db::name($model['name'])->where($model['need_pk'],$id)->update($post);
							if(isset($price)){
								if($post['goods_attr']==$data['goods_attr']){
										foreach($price as $key => $v){
												if(db('goods_sku')->where(['goods_id'=>$id,'attr'=>$key])->count()){
													db('goods_sku')->where(['goods_id'=>$id,'attr'=>$key])->update(['price'=>$v,'sku'=>$sku[$key]]);
												}else{
													$attr['attr'] = $key;
													$attr['price'] = $v;
													$attr['sku'] = $sku[$key];
													$attr['goods_id'] = $id;
													db('goods_sku')->insert($attr);
												}
										}
								}else{
									Db::name('goods_sku')->where('goods_id',$id)->delete();
									foreach($price as $key => $v){
												$attr['attr'] = $key;
												$attr['price'] = $v;
												$attr['sku'] = $sku[$key];
												$attr['goods_id'] = $id;
												db('goods_sku')->insert($attr);
									}
								}}
									
								Db::commit();
							} catch (\Exception $e) {
								Db::rollback();
								$this->error('未知错误');
							}
							$this->success('修改成功');
						
							//检测是否
//	       					$post = input('post.');
//							$price = $post['price'];
//							unset($post['price']);
//							$sku = $post['sku'];
//							unset($post['sku']);
//							foreach($price as $key => $v){
//									$data['attr'] = $key;
//									$data['price'] = $v;
//									$data['sku'] = $sku[$key];
//									$data['goods_id'] = ;
//									db('goods_sku')->where('goods_id',$post[$model['need_pk']])->update($data);
//							}
	       				
//	       					!==false?$this->success('修改成功'):$this->error('未知错误');
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
		public function delete(){
			return parent::base_del($this->model_name);
		}
	    public function base_update($nav_id=0){
	  			$nav_id || $this->error('模型名标识必须');
       			$model_id =  db('nav')->where('id',$nav_id)->value('model_id');
				$model = db('model')->find($model_id);
				
       			$model || $this->error('模型不存在');
	  			if($this->request->isPost()){
	       			$pk=$model['need_pk'];
	       			$post = input('post.');
					if(isset($post[$pk]))
					{
							$this->toValidate($model['id'],$post,'edit');
	       					db($model['name'])->where($model['need_pk'],$post[$pk])->update($post)!==false?$this->success('修改成功'):$this->error('未知错误');
					}else{
							$this->toValidate($model['id'],$post,'add');
       				 		db($model['name'])->insert($post)?$this->success('添加成功'):$this->error('未知错误');
					}
				}else{
					$this->err('未知错误');
				}
	  }
		public function update(){
					$post = input('post.');
					$id = $post['id'];
					if(db('nav')->where('id',$post['id'])->update($post)!==false){
							$this->success('更新成功');	
					}else{
							$this->error('更新失败');
					}
		}
}

?>