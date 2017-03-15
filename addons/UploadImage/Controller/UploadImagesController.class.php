<?php

namespace Addons\UploadImages\Controller;
use Home\Controller\AddonsController;

class UploadImagesController extends AddonsController{
		public function photo(){
				echo M('picture')->where(array('id'=>$_POST['id']))->getField('link');
				
		}
		public function save_photo(){
			$_POST['val']&&$a = M('picture')->where(array('id'=>$_POST['id']))->setField('link',$_POST['val']);
		
		}
}
