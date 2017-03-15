<?php
namespace Addons\UploadFile\Controller;
use app\admin\Controller\Addons;


class Yjshop extends Addons{
		public function photo(){
				echo M('picture')->where(array('id'=>$_POST['id']))->getField('link');
		}
		public function save_photo(){
			$_POST['val']&&$a = M('picture')->where(array('id'=>$_POST['id']))->setField('link',$_POST['val']);
		}
		public function uploadfile(){
					$return  = array('status' => 1, 'info' => '上传成功', 'data' => ''); 
					//TODO: 用户登录检测
					/* 调用文件上传组件上传文件 */
					$file = request()->file('file');
					if (empty($file)) {
						$this->error('请选择上传文件');
					}
					$info = $file->move(config('file_path').'admin');
					if($info){
						$save_name = config('file_path').'admin/'.$info->getSaveName();
						$data['path'] = strchr($save_name,'/public');
						$data['create_time'] = time();
						$data['status'] = 1;
						$id = db('picture')->insert($data);
						//存入数据库
						 
						$return =  ['id'=>$id,'path'=>$data['path'],'filename'=>$info->getFilename()];
					}else{
						// 上传失败获取错误信息
						return false;
					}
					/* 返回JSON数据 */
					return json($return);
		}
}
