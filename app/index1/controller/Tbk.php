<?php
namespace app\index\controller;
use think\Controller;
//不需要token的类
class Tbk extends controller
{
	public function _initialize(){
		// $a =  \think\Loader::import('Tbk\TopSdk');
//		 $this->c = new \TopClient;
//		 $this->c->appkey = '23539552';
//		 $this->c->secretKey = 'b9c80ac0051d28c9ddabcd6f9d914612';
	}
    public function items_get()
    {
    		$c = $this->c;
			//$c->appkey = $appkey;
			//$c->secretKey = $secret;
			$req = new \Tbk\ItemsGetRequest();
			$req->setFields("num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick");
			$req->setQ("女装");
			$resp = $c->execute($req);		
    }
    public function item_detail_get(){
    		
    }
    //获取用户信息
    public function info($fields='*'){
    	
    }
    public function goods(){
    		$url = 'http://gw.api.taobao.com/router/rest';
    		$data['method'] = 'taobao.atb.items.get';
    		$data['app_key'] = '23539552';
    		$data['timestamp'] = date('Y-m-d H:i:s');
    		$data['format'] = 'json';
    		$data['v'] = '2.0';
    		$data['sign_method'] = 'md5';
    		//curl_post
    }
    
    
}
