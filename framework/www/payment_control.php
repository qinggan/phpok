<?php
/**
 * 支付相关操作
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年08月02日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class payment_control extends phpok_control
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
		$this->model('url')->nocache(true);
	}

	/**
	 * 在线充值
	 * @参数 id 充值的目标标识，如果存在必须是字符串或整型数字
	 * @参数 val 充值金额
	**/
	public function index_f()
	{
		$back = $this->get('back');
		if(!$back){
			$back = $this->lib('server')->referer();
		}
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('请先登录，非会员没有此权限'),$this->url('login','','_back='.rawurlencode($this->url('payment'))));
		}
		$id = $this->get('id');
		if($id){
			$typeid = intval($id) > 0 ? 'id' : 'identifier';
			$rs = $this->model('wealth')->get_one($id,$typeid);
			if(!$rs){
				$this->error(P_Lang('要支付的目标不存在，请检查'),$back);
			}
			if(!$rs['status']){
				$this->error(P_Lang('财富：{title} 未启用',array('title'=>$rs['title'])),$back);
			}
			if(!$rs['ifpay']){
				$this->error(P_Lang('{title}不支持在线充值',array('title'=>$rs['title'])),$back);
			}
			$this->assign('rs',$rs);
			$this->assign('id',$rs['id']);
		}else{
			$wealthlist = $this->model('wealth')->get_all(1);
			if(!$wealthlist){
				$this->error(P_Lang('没有可以充值的财富方案'),$back);
			}
			$wlist = false;
			foreach($wealthlist as $key=>$value){
				if(!$value['ifpay']){
					continue;
				}
				if(!$wlist){
					$wlist = array();
				}
				$wlist[$value['identifier']] = $value;
			}
			$this->assign('rslist',$wlist);
		}
		$paylist = $this->model('payment')->get_all($this->site['id'],1,($this->is_mobile ? 1 : 0));
		$this->assign("paylist",$paylist);
		$price = $this->get('price','float');
		if($price){
			$this->assign('price',$price);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'payment_index';
		}
		$this->view($tplfile);
	}

	/**
	 * 财富明细信息
	 * @参数 id 给哪个财富ID充值
	 * @参数 val 充值的金额
	**/
	public function wealth_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('请先登录，非会员没有此权限'),$this->url('login','','_back='.rawurlencode($this->url('payment'))));
		}
		//创建充值页面
		$id = $this->get('id','int');
		if(!id){
			$this->error(P_Lang('未指定要充值的财富ID'));
		}
		$rs = $this->model('wealth')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('财富方案不存在'));
		}
		if(!$rs['status']){
			$this->error(P_Lang('财富方案未启用'));
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'payment_wealth';
		}
		$this->view($tplfile);
	}

	/**
	 * 创建支付链接
	 * @参数 id 订单ID，仅限会员登录时有效
	 * @参数 sn 订单编号，游客购买有效
	 * @参数 type 支付链类型
	 * @参数 passwd 订单密码，游客购买有效
	 * @参数 balance 财富支付，仅限会员登录时有效
	 * @参数 payment 支付方式 仅限财富支付不能完全抵消或是游客购买时有效
	 * @更新时间 2016年08月16日
	**/
	public function create_f()
	{
		$userid = $this->session->val('user_id');
		$type = $this->get('type');
		if(!$type){
			$type = 'order';
		}
		if($type == 'order'){
			$this->_create_order();
		}
		$backurl = $this->lib('server')->referer();
		$wealth = $this->get('wealth','int');
		if(!$wealth){
			$this->error(P_Lang('未指定充值目标'),$backurl);
		}
		$price = $this->get('price','float');
		if(!$price){
			$this->error(P_Lang('未指定充值金额'),$backurl);
		}
		if($price < 1){
			$this->error(P_Lang('充值金额不能少于1元'),$backurl);
		}
		$payment = $this->get('payment','int');
		if(!$payment){
			$this->error(P_Lang('未指定支付方式'),$backurl);
		}
		$sn = uniqid('CZ');
		$array = array('type'=>$type,'price'=>$price,'currency_id'=>$this->site['currency_id'],'sn'=>$sn);
		$array['title'] = P_Lang('在线充值');
		$array['content'] = P_Lang('充值编号：{sn}',array('sn'=>$sn));
		$array['payment_id'] = $payment;
		$array['dateline'] = $this->time;
		$array['user_id'] = $userid;
		$array['status'] = 0;
		$tmp = array('goal'=>$wealth);
		$array['ext'] = serialize($tmp);
		$insert_id = $this->model('payment')->log_create($array);
		if(!$insert_id){
			$this->error(P_Lang('支付记录创建失败，请联系管理员'),$backurl);
		}
		$this->success(P_Lang('成功创建支付链，请稍候，即将为您跳转支付页面…'),$this->url('payment','submit','id='.$insert_id));
	}

	/**
	 * 更新付款方式
	**/
	public function update_f()
	{
		$id = $this->get('id','int');
		$rs = $this->model('payment')->log_one($id);
		if(!$rs){
			$this->error(P_Lang('没有找到支付记录'));
		}
		$payment = $this->get('payment');
		if(!$payment){
			$this->error(P_Lang('未指定支付方式'));
		}
		$array = array('payment_id'=>$payment);
		$this->model('payment')->log_update($array,$id);
		$url = $this->url('payment','submit','id='.$id);
		if($rs['type'] == 'order'){
			$order = $this->model('order')->get_one_from_sn($rs['sn']);
			$payment_info = $this->model('payment')->get_one($payment);
			$payinfo = $this->model('order')->order_payment_notend($order['id']);
			if($payinfo){
				$data = array('payment_id'=>$payment,'title'=>$payment_info['title']);
				$this->model('order')->save_payment($data,$payinfo['id']);
			}
		}
		$this->_location($url);
	}

	/**
	 * 创建订单的支付链
	 * @参数 id 订单ID，仅限会员登录时有效
	 * @参数 sn 订单编号，游客购买有效
	 * @参数 passwd 订单密码，游客购买有效
	 * @参数 payment 支付方式 仅限财富支付不能完全抵消或是游客购买时有效
	 * @返回 
	 * @更新时间 
	**/
	private function _create_order()
	{
		$userid = $this->session->val('user_id');
		$id = $this->get('id','int');
		if(!$id){
			$sn = $this->get('sn');
			if(!$sn){
				$this->error(P_Lang('未指定定单编号'));
			}
			$passwd = $this->get('passwd');
			if(!$passwd){
				$this->error(P_Lang('订单密码不能为空'));
			}
			$rs = $this->model('order')->get_one_from_sn($sn);
			if(!$rs){
				$this->error(P_Lang('订单信息不存在'));
			}
			if($rs['passwd'] != $passwd){
				$this->error(P_Lang('订单权限验证不通过'));
			}
			$order_url = $this->url('order','info','sn='.$sn.'&passwd='.$passwd);
			$error_url = $this->config['url'];
		}else{
			$rs = $this->model('order')->get_one($id);
			$order_url = $this->url('order','info','id='.$id);
			$error_url = $this->url('order');
		}
		if($this->model('order')->check_payment_is_end($rs['id'])){
			$this->error(P_Lang('订单已支付过，不能重复操作'),$order_url);
		}
		//若积分抵扣完全能满足支付，则跳过支付
		if($userid && $this->integral_minus($rs,true)){
			//如果积分超出
			$this->integral_minus($rs);
			$array = array('order_id'=>$rs['id'],'payment_id'=>0);
			$array['title'] = P_Lang('积分抵扣支付');
			$array['price'] = 0;
			$array['startdate'] = $this->time;
			$array['dateline'] = $this->time;
			$array['ext'] = serialize(array('备注'=>'积分抵现完全支付'));
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
				$this->success(P_Lang('订单{sn}支付成功',array('sn'=>$rs['sn'])),$order_url);
			}
			$this->model('payment')->log_create($array);
			$this->model('order')->update_order_status($rs['id'],'paid');
			$this->success(P_Lang('订单{rs}支付成功',array('sn'=>$rs['sn'])),$order_url);
		}
		if($userid){
			$this->integral_minus($rs);
		}
		$payment = $this->get('payment');
		if(!$payment){
			$this->error(P_Lang('未指定支付方式'),$error_url);
		}
		if(is_numeric($payment)){
			$payment = intval($payment);
			$payment_rs = $this->model('payment')->get_one($payment);
			$currency_id = $payment_rs['currency'] ? $payment_rs['currency']['id'] : $rs['currency_id'];
		}else{
			if(!$userid){
				$this->error(P_Lang('非会员不支持余额支付功能'));
			}
			$payment_rs = $this->model('wealth')->get_one($payment,'identifier');
		}
		//订单未支付完成创建生成链接
		$price_paid = $this->model('order')->paid_price($rs['id']);
		$price = $rs['price'] - $price_paid;
		if(!is_numeric($payment)){
			//检测余额是否充足
			$my_integral = $this->model('wealth')->get_val($userid,$payment_rs['id']);
			$my_price = round($my_integral*$payment_rs['cash_ratio']/100,$payment_rs['dnum']);
			if(floatval($my_price) < floatval($price)){
				$this->error(P_Lang('您的余额不足，请先充值或使用其他方式支付'),$error_url);
			}
		}
		
		$array = array('type'=>'order','price'=>price_format_val($price,$rs['currency_id'],$currency_id),'currency_id'=>$currency_id,'sn'=>$rs['sn']);
		$array['content'] = $array['title'] = P_Lang('订单：{sn}',array('sn'=>$rs['sn']));
		$array['payment_id'] = $payment;
		$array['dateline'] = $this->time;
		$array['user_id'] = $this->session->val('user_id');
		if(!is_numeric($payment)){
			$array['ext'] = serialize(array('wealth'=>$payment_rs['id']));
		}
		//删除未完成的支付日志
		$this->model('payment')->log_delete_notstatus($rs['sn'],'order');
		$insert_id = $this->model('payment')->log_create($array);
		if(!$insert_id){
			$this->error(P_Lang('支付记录创建失败，请联系管理员'),$order_url);
		}
		$this->model('order')->update_order_status($rs['id'],'unpaid');
		//增加order_payment
		$array = array('order_id'=>$rs['id'],'payment_id'=>$payment);
		$array['title'] = $payment_rs['title'];
		$array['price'] = price_format_val($price,$rs['currency_id'],$currency_id);
		$array['currency_id'] = $currency_id;
		$array['startdate'] = $this->time;
		$this->model('order')->delete_not_end_order($rs['id']);
		$this->model('order')->save_payment($array);
		$this->success(P_Lang('成功创建支付链，请稍候，即将为您跳转支付页面…'),$this->url('payment','submit','id='.$insert_id));
	}

	/**
	 * 积分抵扣
	 * @参数 integral 积分ID，数组，可叠加使用
	 * @参数 integral_val 要处理的积分数值
	 * @参数 $order 订单信息，数组
	 * @参数 $check 是否检测，为true时仅作检测，为false时表示直接扣除
	 * @返回 true/false
	 * @更新时间 
	**/
	private function integral_minus($order,$check=false)
	{
		if(!$this->session->val('user_id')){
			return false;
		}
		$integral_val = $this->get('integral_val','int');
		if(!$integral_val){
			return false;
		}
		$wlist = $this->model('order')->balance($this->session->val('user_id'));
		if(!$wlist){
			return false;
		}
		if(!$wlist['integral']){
			return false;
		}
		$wlist = $wlist['integral'];
		$totalprice = price_format_val($order['price'],$order['currency_id'],$order['currency_id']);
		$tmpprice = 0;
		foreach($integral_val as $key=>$value){
			if(!$value || !intval($value) || !$key || !intval($key) || !$wlist[$key]){
				continue;
			}
			if($value > $wlist[$key]['val']){
				continue;
			}
			$useprice = round($value*$wlist[$key]['cash_ratio']/100,$wlist[$key]['dnum']);
			if($check){
				$tmpprice += price_format_val($useprice,$order['currency_id'],$order['currency_id']);
			}else{
				//$tmporder = array('id'=>$order['id'],'sn'=>$order['sn'],'price'=>$totalprice,'currency_id'=>$order['currency_id']);
				$tmp = $this->integral_order_payment($order,$wlist[$key],$value);
				if($tmp && $tmp['status'] && $tmp['price']){
					$totalprice = $tmp['price'];
					//更新订单总额
					$data = array('price'=>$tmp['price']);
					$this->model('order')->save($data,$order['id']);
				}
			}
		}
		if($check){
			if($tmpprice >= $totalprice){
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * 积分抵扣
	 * @参数 $order 订单信息，数组
	 * @参数 $info 用户积分信息
	 * @参数 $integral 积分
	 * @返回 数组 或 false
	 * @更新时间 2016年11月27日
	**/
	private function integral_order_payment($order,$info,$integral=0)
	{
		if(!$this->session->val('user_id')){
			return false;
		}
		if(!$order || !$info || !$integral){
			return false;
		}
		$totalprice = price_format_val($order['price'],$order['currency_id'],$order['currency_id']);
		$price = round($integral*$info['cash_ratio']/100,$info['dnum']);
		$balance = price_format_val($price,$order['currency_id'],$order['currency_id']);
		$surplus = $balance >= $totalprice ? 0 : floatval($totalprice - $balance);
		//扣除会员积分
		$savelogs = array('wid'=>$info['id'],'goal_id'=>$this->session->val('user_id'),'mid'=>0,'val'=>'-'.$integral);
		$savelogs['appid'] = $this->app_id;
		$savelogs['dateline'] = $this->time;
		$savelogs['user_id'] = $this->session->val('user_id');
		$savelogs['ctrlid'] = 'payment';
		$savelogs['funcid'] = 'create';
		$savelogs['url'] = 'index.php';
		$savelogs['note'] = P_Lang('财富（{title}）抵现',array('title'=>$info['title']));
		$savelogs['status'] = 1;
		$savelogs['val'] = -$integral;
		$data = array('wid'=>$info['id'],'uid'=>$this->session->val('user_id'),'lasttime'=>$this->time);
		$data['val'] = intval($info['val'] - $integral);
		//剩余积分
		if($surplus){
			$paid_price = price_format($price,$order['currency_id'],$order['currency_id']);
		}else{
			$paid_price = price_format($order['price'],$order['currency_id'],$order['currency_id']);
		}
		$this->model('wealth')->save_log($savelogs);
		$this->model('wealth')->save_info($data);
		//创建订单日志，记录支付信息
		$tmparray = array('price'=>$paid_price,'payment'=>$info['title'],'integral'=>$integral,'unit'=>$info['unit']);
		$note = P_Lang('使用{payment}抵扣{price}，共消耗{payment}{integral}{unit}',$tmparray);
		$who = $this->session->val('user_name');
		$log = array('order_id'=>$order['id'],'addtime'=>$this->time,'who'=>$who,'note'=>$note);
		$this->model('order')->log_save($log);
		$this->model('order')->integral_discount($order['id'],$balance);
		return array('price'=>$surplus,'status'=>true);
	}

	/**
	 * 提交支付
	 * @参数 id 支付ID号
	**/
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
			$payment->submit();
			exit;
		}
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不支持余额支付，请登录再执行此操作'));
		}
		if(!$log['payment_id']){
			$ext = $log['ext'] ? unserialize($log['ext']) : false;
			if(!$ext || !$ext['wealth'] || !is_numeric($ext['wealth'])){
				$this->error(P_Lang('支付信息异常，无法找到支付方法，请联系管理员'));
			}
			$wealth = $this->model('wealth')->get_one($ext['wealth']);
			$log['payment_id'] = $wealth['identifier'];
			if(!$log['payment_id']){
				$this->error(P_Lang('支付信息异常，无法找到支付方法，请联系管理员'));
			}
		}
		$wealth = $this->model('wealth')->get_one($log['payment_id'],'identifier');
		//取得要扣除的积分
		$integral = round(($log['price'] * 100)/$wealth['cash_ratio'],$wealth['dnum']);
		$my_integral = $this->model('wealth')->get_val($this->session->val('user_id'),$wealth['id']);
		if(!$my_integral){
			$this->error(P_Lang('您的余额为空，请先充值'),$this->url('payment','index','id='.$wealth['id']));
		}
		if(floatval($my_integral) < floatval($integral)){
			$this->error(P_Lang('您的余额不足，请先充值'),$this->url('payment','index','id='.$wealth['id']));
		}
		//扣除会员积分
		$savelogs = array('wid'=>$wealth['id'],'goal_id'=>$this->session->val('user_id'),'mid'=>0,'val'=>'-'.$integral);
		$savelogs['appid'] = $this->app_id;
		$savelogs['dateline'] = $this->time;
		$savelogs['user_id'] = $this->session->val('user_id');
		$savelogs['ctrlid'] = 'payment';
		$savelogs['funcid'] = 'create';
		$savelogs['url'] = 'index.php';
		$savelogs['note'] = P_Lang('支付订单：{sn}',array('sn'=>$log['sn']));
		$savelogs['status'] = 1;
		$savelogs['val'] = -$integral;
		$data = array('wid'=>$wealth['id'],'uid'=>$this->session->val('user_id'),'lasttime'=>$this->time);
		$data['val'] = intval($my_integral - $integral);
		$this->model('wealth')->save_log($savelogs);
		$this->model('wealth')->save_info($data);
		//更新payment_log日志
		$array = array('status'=>1);
		$array['ext'] = serialize(array($wealth['title']=>$integral));
		$this->model('payment')->log_update($array,$log['id']);
		//更新订单状态
		if($log['type'] == 'order'){
			$order = $this->model('order')->get_one_from_sn($log['sn']);
			$this->model('order')->update_order_status($order['id'],'paid',P_Lang('支付完成'));
			$order_payment = $this->model('order')->order_payment($order['id'],$log['payment_id']);
			if($order_payment){
				$array = array('dateline'=>$this->time);
				$array['ext'] = serialize(array($wealth['title']=>$integral));
				$this->model('order')->save_payment($array,$order_payment['id']);
			}
		}
		$price = price_format($log['price'],$this->site['currency_id']);
		$this->success(P_Lang('支付操作完成，您共支付：{price}',array('price'=>$price)),$this->url('payment','show','id='.$id));
	}

	public function notice_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('执行异常，请检查，缺少参数ID'));
		}
		$rs = $this->model('payment')->log_one($id);
		if(!$rs){
			$this->error(P_Lang('订单信息不存在'),$this->url('index'));
		}
		if($rs['type'] == 'order'){
			$order = $this->model('order')->get_one_from_sn($rs['sn']);
			$url = $this->url('order','info','sn='.$rs['sn'].'&passwd='.$order['passwd']);
			$addressconfig = $this->config['order']['address'] ? explode(",",$this->config['order']['address']) : array('shipping');
			if($addressconfig){
				$address = array();
				foreach($addressconfig as $key=>$value){
					if(!$value || !trim($value)){
						continue;
					}
					$address[trim($value)] = $this->model('order')->address($order['id'],trim($value));
				}
				$this->assign('address',$address);
			}
		}elseif($rs['type'] == 'recharge'){
			$url = $this->url('usercp','wealth','sn='.$rs['sn']);
		}else{
			$url = $this->url('payment','show','id='.$id);
		}
		$this->assign('payinfo',$rs);
		$this->assign('order',$order);
		//同步通知
		if($rs['status']){
			$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func.'-success');
			if($this->tpl->check_exists($tplfile)){
				$this->view($tplfile);
				exit;
			}
			$this->success(P_Lang('您的订单付款成功，请稍候…'),$url);
		}
		$payment_rs = $this->model('payment')->get_one($rs['payment_id']);
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/notice.php';
		if(!file_exists($file)){
			$tmpfile = str_replace($this->dir_root,'',$file);
			$this->error(P_Lang('支付接口异常，文件{file}不存在',array('file'=>$tmpfile)));
		}
		include($file);
		$name = $payment_rs['code'].'_notice';
		$cls = new $name($rs,$payment_rs);
		$obj = $cls->submit();
		if(!$obj){
			$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func.'-error');
			if($this->tpl->check_exists($tplfile)){
				$this->view($tplfile);
				exit;
			}
			$this->error(P_Lang('付款失败，请检查…'));
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func.'-success');
		if($this->tpl->check_exists($tplfile)){
			$this->view($tplfile);
			exit;
		}
		$this->success(P_Lang('您的订单付款成功，请稍候…'),$url);
	}

	//异步通知方案
	//考虑到异步通知存在读不到$_SESSION问题，使用sn和pass组合
	public function notify_f()
	{
		$sn = $this->get('sn');
		if(!$sn){
			exit('error');
		}
		if(strpos($sn,'-') !== false){
			$tmp = explode("-",$sn);
			$sn = $tmp[0];
			$rs = $this->model('payment')->log_one($tmp[1]);
		}else{
			$rs = $this->model('payment')->log_check_notstatus($sn);
		}
		if(!$rs){
			exit('error');
		}
		$payment_rs = $this->model('payment')->get_one($rs['payment_id']);
		if(!$payment_rs){
			exit('error');
		}
		if(!$payment_rs['status']){
			exit('error');
		}
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/notify.php';
		if(!file_exists($file)){
			exit('error');
		}
		include($file);
		$name = $payment_rs['code'].'_notify';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
		exit('success');
	}

	public function show_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('payment')->log_one($id);
		if(!$rs){
			$this->error(P_Lang('数据不存在，请检查'));
		}
		if($rs['type'] == 'order'){
			if($this->session->val('user_id')){
				$order = $this->model('order')->get_one_from_sn($rs['sn']);
				$url = $this->url('order','info','id='.$order['id']);
				$this->_location($url);
			}else{
				$this->success(P_Lang('订单{sn}支付完成',array('sn'=>$rs['sn'])),$this->url);
			}
		}
		if($this->session->val('user_id')){
			$this->_location($this->url('usercp','wealth'));
		}else{
			$this->_location($this->url);
		}
	}
}
