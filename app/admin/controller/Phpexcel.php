<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace app\admin\controller;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class Phpexcel extends Think{
	/* 用户中心首页 */
	public function index(){
		set_time_limit(0);
		ini_set("memory_limit", "1024M");
		$filename="./public/2.xls";
		import('PHPExcel/PHPExcel');
	
		$objReader = \PHPExcel_IOFactory::createReader('Excel5');
		$PHPExcel=$objReader->load($filename);
		$sheet_read_arr = array();
		$sheet_read_arr["sheet1"] = array("A","B","C","D","F"); 
		$sheet_read_arr["sheet2"] = array("A","B","C","D","F"); 
		//获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
		$currentSheet=$PHPExcel->getSheet(0);

		$allColumn=$currentSheet->getHighestColumn();
		//获取总行数
	$allRow=$currentSheet->getHighestRow();
	
		for($currentRow=2;$currentRow<=$allRow;$currentRow++){
			//从哪列开始，A表示第一列
			for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
				//数据坐标
				$address=$currentColumn.$currentRow;
				//读取到的数据，保存到数组$arr中
				$arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
			}
		}
		p($arr);
		die;
		foreach($arr as $v){
				$class['name'] = explode('/',$v['E'])[0];
				$class['pid'] = 0;
				$good_class = \think\Db::name('goods_class');
				if(!$id = $good_class->where('name',$class['name'])->value('id')){
						$good_class->insert($class);
						$id = $good_class->getLastInsID();
				}
				$good = \think\Db::name('goods');
				$data['goods_name'] = $v['B'];
				$data['goods_code'] = $v['A'];
				$data['picture'] = $v['C'];
				$data['goods_class_id'] = $id;
				$data['price'] = $v['G'];
				$data['sales'] = $v['H'];
				$data['platform'] = $v['N'];
				$data['coupon_url'] =  $v['V'];
				$data['coupon_price'] = $v['R'];
				$data['date'] = date('ymd');
				$result = $good->insert($data);
				p($result);
		}
		
	}
	public function index1(){
		
		$filename="./Public/file/BBC_store.xls";
		$PHPReader=new \PHPExcel_Reader_Excel5();
		$PHPExcel=$PHPReader->load($filename);
		//获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
		$currentSheet=$PHPExcel->getSheet(0);
			//获取总列数
		$allColumn=$currentSheet->getHighestColumn();
			//获取总行数
		$allRow=$currentSheet->getHighestRow();
		for($currentRow=2;$currentRow<=$allRow;$currentRow++){
			//从哪列开始，A表示第一列
			for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
				//数据坐标
				$address=$currentColumn.$currentRow;
				//读取到的数据，保存到数组$arr中
				$arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
			}
		
		}	
	
		foreach($arr as $kk =>$v){
			 	p(explode($v['E'],'/')[0]);
			 	
				
		}
		die;
		
			foreach($arr as $kk=> $v){
		if($kk>=80){
			$ty['user_name'] = $v['H'];
			$ty['password'] = md5('qrk'.$v['H']);
			M('center_user','sky_','DB_TY')->add($ty);
				$user['nickname'] = $v['F'];
				$user['phone'] = $v['H'];
				$user['status'] = 1;
				$user['stores'] = 1;
				$user['is_seller'] = 1;
			$uid = M('user','pigcms_','DB_BBC')->add($user);
				$data['name'] = $v['D'];
				$data['linkname'] = $v['F'];
				$data['tel'] = $v['H'];
				$data['uid'] = $uid;
				//$data['service_tel'] =$v['H'];
			$id = M('store','pigcms_','DB_BBC')->add($data);
				$dd['address'] = $v['N'];
				$dd['store_id'] = $id;
				$address = $v['K'].'省'.$v['L'].'市'.$v['M'].$v['N'];
				$url = 'http://api.map.baidu.com/geocoder?address='.$address.'&output=json';
				$b = json_decode(file_get_contents($url));
				$dd['long'] = $b->result->location->lng;
				$dd['lat'] = $b->result->location->lat;
			
				$where['name'] = array('like',"%".$v['K']."%");
				$a = M('address')->where($where)->getField('id');
				$dd['province'] = $a;
				$where['name'] = array('like',"%".$v['L']."市%");
				$a = M('address')->where($where)->getField('id');
				$dd['city'] = $a;
				$where['name'] = array('like',"%".$v['M']."%");
				$a = M('address')->where($where)->getField('id');
				$dd['county'] = $a;
				M('store_contact','pigcms_','DB_BBC')->add($dd);
		
			}
			}
		
			
	}
	public function red_info($path=null){
		
		$filename='./'.$path;
		$PHPReader=new \PHPExcel_Reader_Excel5();	
		$PHPExcel=$PHPReader->load($filename);
		//获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
		$currentSheet=$PHPExcel->getSheet(0);
			//获取总列数
		$allColumn=$currentSheet->getHighestColumn();
		$allRow=$currentSheet->getHighestRow();
		for($currentRow=5;$currentRow<=$allRow;$currentRow++){
			//从哪列开始，A表示第一列
			for($currentColumn='B';$currentColumn<=$allColumn;$currentColumn++){
				//数据坐标
				$address=$currentColumn.$currentRow;
				//读取到的数据，保存到数组$arr中
				$arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
			}
		
		}
		return $arr;
		
	}
	
	public function out_data(){
		$this->meta_title = '管理首页';
		$where ='';
		if($_GET['start_time']){
			$start_time = strtotime($_GET['start_time']);

			$where.= " and yy_date >= ".$start_time."";
		}
		if($_GET['end_time']){
				$end_time = strtotime($_GET['end_time']);
			$where.= " and yy_date <= ".$end_time."";
		}
		
	

		$data = M()->query('select count(*) sum,sum(price) price,sum(nums) sums,product_id from onethink_yorder where status= 1 '.$where.'  group by product_id');
	

	
		$count =  count($data);
		$xlsTitle = iconv('utf-8', 'gb2312', "深圳大学");//文件名称
        $fileName = date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
      
       
        $objPHPExcel = new \PHPExcel();
$objPHPExcel->getProperties()->setTitle("材料学院预约统计表");
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(27);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);

$objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('B2')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('C2')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('E2')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
	$objPHPExcel->getActiveSheet()->setCellValue('A1', '材料学院预约统计表');	
		$objPHPExcel->getActiveSheet()->setCellValue('A2', '设备名称');		

		$objPHPExcel->getActiveSheet()->setCellValue('B2', '订单数');		
		$objPHPExcel->getActiveSheet()->setCellValue('C2', '价格');		
		$objPHPExcel->getActiveSheet()->setCellValue('D2', '预约次数');		
		$objPHPExcel->getActiveSheet()->setCellValue('E2', '预约时间段');	
	
	
		foreach($data as $k=>$v){
			
			$name = M('product')->where('id='.$v['product_id'])->getField('name');
			$time = M('product_order')->where(array('product_id'=>$v['product_id']))->count();
			$k +=3;
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$k, $name);	
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$k, $v['sum']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$k, $v['price']);	
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$k, $v['sums']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$k, $time);
		}

       // $OBJphpeXCEL->GETaCTIVEsHEET(0)->MERGEcELLS('a1:'.$CELLnAME[$CELLnUM-1].'1');//合并单元格
        //$OBJphpeXCEL->SETaCTIVEsHEETiNDEX(0)->SETcELLvALUE('a1', $EXPtITLE.'  eXPORT TIME:'.DATE('y-M-D h:I:S'));  
       
        
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
        $objWriter->save('php://output'); 
        exit;   


	}
	public function out($expTitle="w1w1",$expCellName="w1w1",$expTableData=array(1,2)){
	 $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $_SESSION['loginAccount'].date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
       
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));  
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]); 
        } 
          // Miscellaneous glyphs, UTF-8   
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
          }             
        }  
        
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
        $objWriter->save('php://output'); 
        exit;   

	}



	

}
