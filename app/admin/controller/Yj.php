<?php
namespace app\admin\controller;
use think\Controller;
use app\Auth;
class Yj extends controller
{
	protected function _initialize(){
		if(defined('UID')) return;
		define('UID',is_login());
		if(!UID){//判断是否登录
			 $this->redirect('Base/login');
		}
		//判断是否是超级管理员
		define('IS_ROOT',is_administrator(UID));
		define('CONTROLLER_NAME',$this->request->controller());
		define('ACTION_NAME',$this->request->action());
		/**
		if(!IS_ROOT&&C('ADMIN_ALLOW_IP')){//开启IP检测
				if(!in_array(get_client_ip(),explode(',',C('ADMIN_ALLOW_IP')))){
					$this->error('403:禁止访问');
				}
		}**/
		//检测权限
		if(!IS_ROOT){
			 //检测访问权限
			//$module = $this->request->module();	
			$rule = strtolower(CONTROLLER_NAME.'/'.ACTION_NAME);
			if(!$this->checkRule($rule)){
				 	$this->error('未授权访问!');
			}
		}
		$this->assign('action',CONTROLLER_NAME);
		$this->assign('__MENU__',$this->getMenus());
	}
	public function checkRule($rule,$uid=UID,$type=1){
		$auth = new Auth();
		if(!$auth->check($rule,$uid,$type)){
			return false;
		}
		return true;
	}
	public function getMenus(){
		$menus = session('admin_menus'.CONTROLLER_NAME);
		if(empty($menus)){
				$where['pid'] = 0;
				$where['hide'] = 0;
				$menus['main'] = db('Menu')->where($where)->order('sort asc')->field('id,title,url')->select();
				$menus['child'] = array();
				foreach($menus['main'] as $key => $item){
						if(!IS_ROOT&&!$this->checkRule($item['url'])){
							unset($menus['main'][$key]);
							continue;
						}
						if(strtolower(CONTROLLER_NAME.'/'.ACTION_NAME)  == strtolower($item['url'])){
                   			 $menus['main'][$key]['class']='current';
                		}
               }
                $pid = \think\Db::name('Menu')->where('pid!=0 AND url like "%'.CONTROLLER_NAME.'/'.ACTION_NAME.'%"')->value('pid');
           		
                if($pid){
                		$nav = db('Menu')->find($pid);
                	
                		if($nav['pid']){
                			$nav = db('Menu')->find($nav['pid']);
                		}
                		
                		foreach($menus['main'] as $key=>$item){
                		
                			if($item['id']==$nav['id']){
                					$menus['main'][$key]['class'] = 'current';
                					$groups = db('Menu')->where(array('group'=>array('neq',''),'pid' =>$item['id']))->group('`group`')->order('id')->column('group');
									
                					 $where          =   array();
                      				 $where['pid']   =   $item['id'];
                       				 $where['hide']  =   0;
                       				 $second_urls = db('Menu')->where($where)->value('id,url');
                       				
                       				 foreach($groups as $v){
	                       				 	 $map['group'] = $v;
		                       				 $map['pid']     =   $item['id'];
		                          			 $map['hide']    =   0;
		                       				 $menuList = db('Menu')->where($map)->field('id,pid,title,url,tip')->order('sort asc')->select();
		                  					 $menus['child'][$v] = list_to_tree($menuList,$item['id']);
                       				 }
                			}
                		}
                }       	
		}
		
		 return $menus;
	}
}
