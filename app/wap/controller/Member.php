<?php
namespace app\wap\controller;
use think\Db;
class Member extends Home
{
    public function index()
    {
    		$this->info(UID,'member_id,member_name');
    }
    //获取用户信息
    public function info($fields='*',$json=0){
    		$result = model('Member')->info(UID,$fields);
    		if($json==1){
    			return $result;
    		}
    		$result?$this->succ($result):$this->err('获取失败');
    }
    public function updateInfo(){
    			($fields = input('fields')) || err('修改失败，缺少fields');
    			$field = explode(',',$fields);
    			foreach($field as $v){
    				switch($v){
    					case 'member_tbopenid':	//保存淘宝openid
	    					if(input('member_tbopenid')){
	    							$data['member_tbopenid'] = input('member_tbopenid');
	    							$data['is_tb_bind'] = 1;
	    					}else{
	    						 $this->err('修改失败，缺少member_tbopenid值');
	    					}
    					break;
    					case 'is_tb_bind':
    					case 'member_sex':
    					case 'member_nickname':
    					case 'member_truename':
    					case 'member_email':
    					case 'member_areaid':
    					case 'member_cityid':
    					case 'member_provinceid':
    					case 'member_avatar':
    					case 'zfb':
    					input($v)&&$data[$v] = input($v);
    					break;
    					case 'member_mobile':
    					$validate = 'Member.update';
    					$base = new \app\index\controller\Base();
    					$base->checkCode(input($v),input('code'),1);
    					$data['member_mobile_bind'] = 1;
    					input($v)&&$data[$v] = input($v);
    					break;
    					default:
    					$this->err('fields错误');
    					break;
    				}
    			}
    			$Member = model('Member');
    			$Member->validate(isset($validate)?$validate:false)->save($data,['member_id'=>UID])!==false?$this->succ([],'修改成功'):$this->err($Member->getError());
    }
    //所有收益
   	public function earning(){
   			 ($type = input('type'))||$this->err('缺少参数type');
   			 ($date = input('date'))||$this->err('缺少日期类型');
   			 $where['member_id'] = UID;
   			 $now_time = time();
   			 $lists = array();
   			 $max = 0;
   			 $i=0;
   			 for(--$type;$type>=0;$type--){
   			 		$time = strtotime("-$type day");
   			 		$where['add_date'] = date('ymd',$time);
   			 		$date1 = date($date,$time);
				    $earnings = db('member_earnings')->where($where)->value('earnings');
					$earnings||$earnings=0;
					$lists[$i]['earnings'] = $earnings;
					$lists[$i]['date'] = $date1;
					($type==1)&&$data['yertoday'] = $earnings;
					($type==0)&&$data['today'] = $earnings;
					$max>$earnings||$max=$earnings;
					$i++;
   			 }
   			 $data['max'] = $max;
   			 $data['before'] = $lists;
   			  //预测明日收益
   			 $data['tomorrow'] = '0.01';
   			 //用户信息
   			 $list = db('member')->where('member_id',UID)->field('order_price,order_nums,member_earnings,member_tx_earnings,member_all_earnings,year_rate')->find();
   			 $data = array_merge($data,$list);
   			 $this->succ($data);
   	}
   	//历史记录
   	public function search_record(){
   			$limit = input('limit')?input('limit'):5;
			$data = db('member_search_record')->where('member_id',UID)->where('is_show',1)->limit($limit)->order('update_time desc')->column('keyword');
			$search['history'] = $data;
			$search['hot'] =  ['辣条','牛奶'];
			$data!==false?$this->succ($search):$this->err('查询失败');
   	}
   	public function delete_search_record(){
			$map['member_id'] = UID;
			$this->request->has('keyword')&&$map['keyword'] = $keyword;
			db('member_search_record')->where($map)->update(['is_show'=>0])!==false?$this->succ('删除成功'):$this->err('不存在该keyword');
   	}
   	//添加记录
   	public function add_search_record(){
			($key =input('keyword')) || $this->err('keyword不存在');
			$info = db('member_search_record')->where(['keyword'=>$key,'member_id'=>UID])->field('id,search_nums')->find();
			if(empty($info)){
					$data['member_id'] = UID;
					$data['keyword'] = $key;
					$data['search_nums'] = 1;
					$data['is_show'] = 1;
					$data['add_time'] = $data['update_time'] = time();
					$result = db('member_search_record')->insert($data);
			}else{
					$data['update_time'] = time();
					$data['search_nums'] = ++$info['search_nums'];
					$data['is_show'] = 1;
					$result = db('member_search_record')->where('id',$info['id'])->update($data);
			}
			$result?$this->succ('记录成功'):$this->err('记录失败');
   	}
   	//添加提现
   	public function add_tx(){
   			($earnings = input('earnings'))||$this->err('缺少earings');
   			//查询是最小值
   			$earnings<1&&$this->err('最小金额为1');
   			$member_info = $this->info('member_earnings,member_tx_earnings',1);
   			$earnings>$member_info['member_earnings']&&$this->err('你的余额不足');
   			//记录提现记录
   			Db::startTrans(); 
			try{
				
   					$data['member_earnings'] = $member_info['member_earnings'] - $earnings;
   					$data['member_tx_earnings'] = $member_info['member_tx_earnings'] + $earnings;
   					Db::name('member')->where('member_id',UID)->update($data);
   					//添加记录
   					$tx['member_id']=UID;
   					$tx['tx_earnings'] = $earnings; 
   					$tx['add_time'] = time();
   					$tx['state'] = 0;
   					Db::name('member_tx_record')->insert($tx);
   					Db::commit();
   					$this->succ($data['member_earnings']);
			}catch (\Exception  $e) {
					// 回滚事务 
					Db::rollback();
					$this->err('未知错误');
			}
			
   	}
   	//添加提现记录lv1
   	public function add_tx_1(){
   			($earnings = input('earnings'))||$this->err('缺少earings');
   			$member_info = $this->info('member_earnings,member_tx_earnings',1);
   			$Balance = model('Balance');
   			if($Balance->save_log($earnings,2,$member_info)){
   				$this->succ('添加成功');
   			}else{
   				$this->err($Balance->getError());
   			}
   	}
   	//table--表名字  
   	//limit--条数
   	//member_fields = 用户信息
   	public function table_lists($table='',$limit=10,$fields='*',$where=[]){
   			$where['member_id'] = UID;
   			switch($table){
   				case 'member_earnings':
   				break;
   				case 'member_tx_record':
   				break;
   				case 'member_balance':
   				break;
   				case 'order':
   				break;
   				case 'address':
   				$order = 'id desc';
   				break;
   				default:
   				$this->err('table is Error');
   				break;
   			}
   			$result = Db::name($table)->field($fields)->where($where)->order($order)->paginate($limit);
   			$this->succ($result->toArray());
   	}
   	//存入
   	public function  order($type=0,$limit=10,$fields=""){
   			$where = [];
   			switch($type){
   				case 1:
   				$where['order_state'] = ['in','5,10'];
   				break;
   				case 2:
   				$where['order_state'] = 20;
   				break;
   				case 3:
   				$where['order_state'] = 0;
   				break;
   				default:
   				//$this->err('type is wrong');
   				break;
   			}
   			$json = $this->table_lists('order',$limit,'*','',1,$where);
   			succ($json['lists']);
   	}
   	public function tongyong_lists(){
   		$data = $this->table_lists(input('table'),input('limit'),input('fields'),input('member_fields'),1);
   		switch(input('table')){
   				case 'order':
   				foreach($data['lists']['data'] as &$v){
   						$info = $v;
   						$v = null;
   						$v['name'] = '购买 '.$info['order_code'];
   						$v['add_time'] =$info['into_time'];
   						$v['price'] = $info['order_price'];
   				}
   				break;
   				case 'member_balance':
   					foreach($data['lists']['data'] as &$v){
   						$info = $v;
   						$v = null;
   						$v['name'] = $info['type']==1?'超级钱包利息':'申请提现(支出)';
   						$v['add_time'] = $info['add_time'];
   						$v['price'] = $info['balance'];
   					}
   				break;
   				case 'member_tx_record':
   					foreach($data['lists']['data'] as &$v){
   						$info = $v;
   						$v = null;
   						$v['name'] = '提现成功';
   						$v['add_time'] = $info['add_time'];
   						$v['price'] = $info['tx_earnings'];
   					}
   				break;
   				case 'member_earnings':
   					foreach($data['lists']['data'] as &$v){
   						$info = $v;
   						$v = null;
   						$v['name'] = '超级钱包利息';
   						$v['add_time'] = $info['add_time'];
   						$v['price'] = $info['earnings'];
   					}
   				break;
   		}
   		$this->succ($data);
   	}
   	public function update_address(){
   			$address = model('address');
   			$data['address']=input('address');
   			$data['buy_tel'] = input('buy_tel');
   			$data['buy_name'] = input('buy_name');
   			$data['member_id'] = UID;
   			input('id')&&$data['id'] = input('id');
   			if($address->update_info($data)){
   				succ($data);
   			}else{
   				err($address->getError());
   			}
   			
   	}
}
