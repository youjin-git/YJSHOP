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
			  return $this->base_lists($model['name']);
		}
		public function add($nav_id=0){
				$nav = db('nav')->find($nav_id);
				$model_id = $nav['model_id'];
			    $model_id || $this->error('模型名标识必须');
       			$model =  db('model')->find($model_id);
       			return $this->base_add($model['name']);
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
		public function update($nav_id){
				$nav_id || $this->error('模型名标识必须');
       			$model_id =  db('nav')->where('id',$nav_id)->value('model_id');
				$model = db('model')->find($model_id);
       			$model || $this->error('模型不存在');
	  			return parent::base_update($model['name']);
		}
		public function nav_update(){
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