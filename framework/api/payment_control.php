<?php
/***********************************************************
	Filename: {phpok}/api/payment_control.php
	Note	: 付款操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月14日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class payment_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function create_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$token = $this->get('token');
			if(!$token){
				$this->json(P_Lang('数据传参不完整，请检查'));
			}
			if(!$this->site){
				$this->json(P_Lang('数据异常，无法获取站点信息'));
			}
			$info = $this->lib('token')->decode($token);
			if(!$info || !$info['price']){
				$this->json(P_Lang('数据不完整，请检查'));
			}
		}else{
			$rs = $this->model('order')->get_one($id);
			if(!$rs){
				$this->json(P_Lang('订单信息不存在'));
			}
			$info = $rs;
			$info['type'] = 'order';
		}

		if(!$info['sn']){
			$info['sn'] = $this->_create_sn();
		}
		if(!$info['type']){
			$info['type'] = 'order';
		}
		if(!$info['currency_id']){
			$info['currency_id'] = $this->site['currency_id'];
		}
		if($info['type'] == 'order'){
			$title = P_Lang('订单：{sn}',array('sn'=>$sn));
		}elseif($info['type'] == 'recharge'){
			$title = P_Lang('充值：{sn}',array('sn'=>$sn));
		}else{
			$title = $this->get('title');
			if(!$title){
				$title = P_Lang('其他：{sn}',array('sn'=>$sn));
			}
		}
		$payment = $this->get('payment');
		if(!$payment){
			$this->json(P_Lang('未指定付款方式'));
		}
		if(!is_numeric($payment) && $this->session->val('user_id')){
			//如果积分超出
			$wealth = $this->model('wealth')->get_one($payment,'identifier');
			if(!$rs){
				$this->error(P_Lang('支付方式无效，请检查'));
			}
			$me_val = $this->model('wealth')->get_val($this->session->val('user_id'),$wealth['id']);
			if(!$me_val){
				$this->error(P_Lang('{title}余额不足，请先充值',array('title'=>$wealth['title'])));
			}
			$myprice = round($me_val*$wealth['cash_ratio']/100,$wealth['dnum']);
			$unpaid_price = $this->model('order')->unpaid_price($rs['id']);
			if(!$unpaid_price){
				$this->error(P_Lang('订单没有存在未付订单'));
			}
			if($unpaid_price > $myprice){
				$this->error(P_Lang('{title}余额不足，请先充值',array('title'=>$wealth['title'])));
			}
			$surplus = floatval($myprice - $unpaid_price);

			//扣除会员积分
			$savelogs = array('wid'=>$wealth['id'],'goal_id'=>$this->session->val('user_id'),'mid'=>0,'val'=>'-'.$unpaid_price);
			$savelogs['appid'] = $this->app_id;
			$savelogs['dateline'] = $this->time;
			$savelogs['user_id'] = $this->session->val('user_id');
			$savelogs['ctrlid'] = 'payment';
			$savelogs['funcid'] = 'create';
			$savelogs['url'] = 'index.php';
			$savelogs['note'] = P_Lang('财富（{title}）抵现',array('title'=>$wealth['title']));
			$savelogs['status'] = 1;
			$savelogs['val'] = -$unpaid_price;
			$data = array('wid'=>$wealth['id'],'uid'=>$this->session->val('user_id'),'lasttime'=>$this->time);
			$data['val'] = $surplus;
			$this->model('wealth')->save_log($savelogs);
			$this->model('wealth')->save_info($data);
			//创建订单日志，记录支付信息
			$tmparray = array('price'=>$unpaid_price,'payment'=>$wealth['title'],'integral'=>$unpaid_price,'unit'=>$wealth['unit']);
			$note = P_Lang('使用{payment}抵扣{price}，共消耗{payment}{integral}{unit}',$tmparray);
			$who = $this->session->val('user_name');
			$log = array('order_id'=>$rs['id'],'addtime'=>$this->time,'who'=>$who,'note'=>$note);
			$this->model('order')->log_save($log);
			$this->model('order')->integral_discount($order['id'],$unpaid_price);

			$array = array('order_id'=>$rs['id'],'payment_id'=>0);
			$array['title'] = P_Lang('余额支付');
			$array['price'] = 0;
			$array['startdate'] = $this->time;
			$array['dateline'] = $this->time;
			$array['ext'] = serialize(array('备注'=>'余额支付'));
			$this->model('order')->save_payment($array);
			//登记支付链
			$array = array('type'=>'order','price'=>'0.00','currency_id'=>$rs['currency_id'],'sn'=>$rs['sn']);
			$array['content'] = $array['title'] = P_Lang('订单：{sn}',array('sn'=>$rs['sn']));
			$array['payment_id'] = 0;
			$array['dateline'] = $this->time;
			$array['user_id'] = $this->session->val('user_id');
			$array['status'] = 1;
			$chk = $this->model('payment')->log_check($rs['sn'],'order');
			if($chk){
				if(!$chk['status']){
					$this->model('payment')->log_update($array,$chk['id']);
				}
				$this->model('order')->update_order_status($rs['id'],'paid');
				$this->success();
			}
			$this->model('payment')->log_create($array);
			$this->model('order')->update_order_status($rs['id'],'paid');
			$this->success();
		}
		$payment_rs = $this->model('payment')->get_one($payment);
		if(!$payment_rs){
			$this->json(P_Lang('支付方式不存在'));
		}
		if(!$payment_rs['status']){
			$this->json(P_Lang('支付方式未启用'));
		}
		$chk = $this->model('payment')->log_check($info['sn']);
		if($chk){
			if($chk['status']){
				$this->json(P_Lang('订单{sn}已支付完成，不能重复执行',array('sn'=>$info['sn'])));
			}
			$array = array('type'=>$info['type'],'payment_id'=>$payment,'title'=>$title,'content'=>$title);
			$array['dateline'] = $this->time;
			$array['price'] = $info['price'];
			$array['currency_id'] = $info['currency_id'];
			$this->model('payment')->log_update($array,$chk['id']);
			$this->json($chk['id'],true);
		}
		$array = array('sn'=>$info['sn'],'type'=>$info['type'],'payment_id'=>$payment,'title'=>$title,'content'=>$title);
		$array['dateline'] = $this->time;
		$array['user_id'] = $info['user_id'] ? $info['user_id'] : $this->user['id'];
		$array['price'] = $info['price'];
		$array['currency_id'] = $info['currency_id'];
		$insert_id = $this->model('payment')->log_create($array);
		if(!$insert_id){
			$this->json(P_Lang('支付记录创建失败'));
		}
		//更新订单状态
		if($info['type'] == 'order'){
			$order = $this->model('order')->get_one_from_sn($info['sn']);
			if(!$order){
				$this->model('payment')->log_delete($insert_id);
				$this->json(P_Lang('订单信息不存在'));
			}
			//更新支付状态
			$this->model('order')->update_order_status($order['id'],'unpaid');
			//写入日志
			$note = P_Lang('订单进入等待支付状态，编号：{sn}',array('sn'=>$sn));
			$log = array('order_id'=>$order['id'],'addtime'=>$this->time,'who'=>$this->user['user'],'note'=>$note);
			$this->model('order')->log_save($log);
			//增加order_payment
			$array = array('order_id'=>$order['id'],'payment_id'=>$payment_rs['id']);
			$array['title'] = $payment_rs['title'];
			$array['price'] = $info['price'];
			$array['startdate'] = $this->time;
			$order_payment = $this->model('order')->order_payment($order['id']);
			if(!$order_payment){
				$this->model('order')->save_payment($array);
			}else{
				$this->model('order')->save_payment($array,$order_payment['id']);
			}
		}
		$this->json($insert_id,true);
	}

	private function _create_sn()
	{
		$a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$rand_str = '';
		for($i=0;$i<3;$i++){
			$rand_str .= $a[rand(0,25)];
		}
		$rand_str .= rand(1000,9999);
		$rand_str .= date("YmdHis",$this->time);
		return $rand_str;
	}

	//异步通知
	public function notify_f()
	{
		$sn = $this->get('sn');
		if(!$sn){
			exit('fail');
		}
		$rs = $this->model('order')->get_one_from_sn($sn);
		if(!$rs){
			exit('fail');
		}
		$payment_rs = $this->model('payment')->get_one($rs['pay_id']);
		if(!$payment_rs){
			exit('fail');
		}
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/notify.php';
		if(!file_exists($file)){
			exit('fail');
		}
		include_once($file);
		$name = $payment_rs['code'].'_notify';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
	}


	public function status_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('payment')->log_one($id);
		if(!$rs){
			$this->json(P_Lang('支付信息不存在'));
		}
		if($rs['status']){
			$this->json(true);
		}else{
			$this->json(P_Lang('等待支付完成'));
		}
	}

	//查询订单接口
	public function query_f()
	{
		$sn = $this->get('sn');
		if(!$sn){
			$this->json(P_Lang('未指定订单编号'));
		}
		if(strpos($sn,'-') !== false){
			$tmp = explode("-",$sn);
			$sn = $tmp[0];
			$rs = $this->model('payment')->log_one($tmp[1]);
		}else{
			$rs = $this->model('payment')->log_check_notstatus($sn);
		}
		if(!$rs){
			$this->json(P_Lang('订单不存在'));
		}
		$payment_rs = $this->model('payment')->get_one($rs['payment_id']);
		if(!$payment_rs){
			$this->json(P_Lang('支付方式不存在'));
		}
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/query.php';
		if(!file_exists($file)){
			$this->json(P_Lang('查询接口不存在'));
		}
		include_once($file);
		$name = $payment_rs['code'].'_query';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
	}

	
	//权限验证
	private function auth_check()
	{
		$sn = $this->get('sn');
		$back = $this->get('back');
		if(!$back) $back = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $this->url;
		//判断订单是否存在
		if($sn) $rs = $this->model('order')->get_one_from_sn($sn,$_SESSION['user_id']);
		if(!$rs)
		{
			$id = $this->get('id','int');
			if(!$id) error("无法获取订单信息，请检查！",$back,'error');
			$rs = $this->model('order')->get_one($id);
			if(!$rs) error("订单信息不存在，请检查！",$back,'error');
		}
		//判断是否有维护订单权限
		if($_SESSION['user_id'])
		{
			if($rs['user_id'] != $_SESSION['user_id']) error('您没有权限维护此订单：'.$rs['sn'],$back,'error');
		}
		else
		{
			$passwd = $this->get('passwd');
			if($passwd != $rs['passwd']) error('您没有权限维护此订单：'.$rs['sn'],$back,'error');
		}
		return $rs;
	}
	
	public function submit_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定支付订单ID'));
		}
		$log = $this->model('payment')->log_one($id);
		if(!$log){
			$this->error(P_Lang('订单信息不存在'));
		}
		if($log['status']){
			$this->error(P_Lang('订单已支付过了，不能再次执行'));
		}
		if($log['type'] == 'order'){
			$orderinfo = $this->model('order')->get_one($log['sn'],'sn');
			$paid_price = $this->model('order')->paid_price($orderinfo['id']);
			$unpaid_price = $this->model('order')->unpaid_price($orderinfo['id']);
			$this->assign('paid_price',$paid_price);
			$this->assign('unpaid_price',$unpaid_price);
			$this->assign('orderinfo',$orderinfo);
		}
		
		if($log['payment_id'] && is_numeric($log['payment_id'])){
			$payment_rs = $this->model('payment')->get_one($log['payment_id']);
			if(!$payment_rs){
				$this->error(P_Lang('支付方式不存在'));
			}
			if(!$payment_rs['status']){
				$this->error(P_Lang('支付方式未启用'));
			}
			$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/submit.php';
			if(!file_exists($file)){
				$tmpfile = str_replace($this->dir_root,'',$file);
				$this->error(P_Lang('支付接口异常，文件{file}不存在',array('file'=>$tmpfile)));
			}
			include($file);
			$name = $payment_rs['code']."_submit";
			$payment = new $name($log,$payment_rs);
			$payment->submit(true);
			exit;
		}
	}


}