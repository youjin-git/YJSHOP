<?php
// +----------------------------------------------------------------------
// | TwoThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.twothink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

namespace addons\Datetime;
use addons\Addons;
/**
 * 编辑器插件
 * @author yangweijie <yangweijiester@gmail.com>
 */
class Datetime extends Addons{

		public $info = array(
				'name'=>'editor',
				'title'=>'前台编辑器',
				'description'=>'用于增强整站长文本的输入和显示',
				'status'=>1,
				'author'=>'thinkphp',
				'version'=>'0.1'
			);
		public function install(){
			return true;
		}
		public function uninstall(){
			return true;
		}
		/**
		 * 编辑器挂载的文章内容钩子
		 * @param array('name'=>'表单name','value'=>'表单对应的值')
		 */
		public function Datetime($data){
			$this->assign('addons_data', $data);
			$this->assign('addons_config', $this->getConfig());
			$this->display('content');
		}

	}
