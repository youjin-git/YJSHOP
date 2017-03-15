<?php 
namespace addons\EditorForAdmin;
use addons\addons;
/**
 * 编辑器插件 
 */

class EditorForAdmin extends addons{

		public $info = array(
			'name'=>'editorforadmin',
			'title'=>'后台编辑器',
			'description'=>'用于增强整站长文本的输入和显示',
			'status'=>1,
			'author'=>'thinkphp',
			'version'=>'0.2'
		);
		public function install(){
			return true;
		}
		public function uninstall(){
			return true;
		}
		/**
		 * 编辑器挂载的后台文档模型文章内容钩子
		 * @param array('name'=>'表单name','value'=>'表单对应的值')
		 */
		public function adminArticleEdit($data){
			
			$this->assign('addons_data', $data); 
			$this->assign('addons_config', $this->getConfig());
			return $this->fetch('content');
		}
	}
