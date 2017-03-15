<?php
namespace app\index\controller;
use think\Controller;
class Index extends Controller
{
   	public function push($os='iOS',$registrationId=0,$msg = '便利淘欢迎您'){
   			$app_key = '7487c0b8080843d9a092a693';
        	$master_secret = 'd2f62c2b059aabe80cc5cf71';
		    $client = new \JPush\Client($app_key, $master_secret);
			$ios_notification = array(
	            'sound' => 'hello jpush',
	            'badge' => 2,
	            'content-available' => true,
	            'category' => 'jiguang',
	            'extras' => array(
	              	    "url"=>'',
	                    "desURL"=>"http://www.sina.com.cn",
	                    "text"=>'',
	                    "title"=>"猜你喜欢"
	            ),
	        );
			if(stristr($os,'iOS')){
						$a = $client->push()->setPlatform('ios');
						$b = $registrationId ==0?$a->addAllAudience():$a->addRegistrationId($registrationId);
						$result = $b->iosNotification($msg)->send();
			}else{
				
			}
			if($result['http_code']==200){
				succ();	
			}
   	}
			
}
