<?php
namespace app\wap\model;
use think\Model;
use think\Db;
class Goods extends Model
{
		//商品信息
		public function info($goods_id,$field=true){
				$goods_id || $this->error('goods_id is empty');
				$data = $this->field($field)->find($goods_id);
				preg_match_all('/src="(.*?)"/',$data->data['content'],$b);
				foreach($b[1] as $v){
					$data->data['content'] = str_replace($v,'http://www.yj251.com'.$v,$data->data['content']);	
				}
				$data->data['content'] = $data->data['content'];
				if($goods_attr = $data->data['goods_attr']){
						foreach(explode(',',$goods_attr) as $v){
							 	$attr[explode(':',$v)[0]][] = explode(':',$v)[1];
						}
						foreach($attr as $key=>$v){
								$attr_json[Db::name('attr')->where('id',$key)->value('name')] = Db::name('attr')->where('id','in',implode(',',$v))->select();
						}
						
						$sku = Db::name('goods_sku')->where('goods_id',$data->data['id'])->where('sku','gt',0)->field('goods_id,attr,price,sku')->select();
						foreach($sku as $v){
								$sku_json[$v['attr']] = ['sku'=>$v['sku'],'price'=>$v['price']];
						}
						
					    $data->data['_attr'] = $attr_json;
					    $data->data['_sku'] = $sku_json;
				}else{
						$this->error('商品不存在');
				}
				return  $data;
		}
		//库存是否
		public function check_good($goods_id,$attr,$nums){
				$data = db::name('goods_sku')->where('goods_id',$goods_id)->where('attr',$attr)->field('sku,price')->find();
				if($nums <= $data){
						return $data['price'];
				}else{
						return false;
				}
				
		}  
		
		
		//判断库存
		public function error($msg){
				$this->error = $msg;
				return false;
		}

}