<?php
namespace app\admin\controller;
class Index extends Admin
{
	
    public function index()
    {
    	$model['title'] = 'xxx';
    	$this->assign('model',$model);
   		return $this->fetch();
    }
}
