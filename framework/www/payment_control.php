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
	}

	/**
	 * 创建支付链接，仅限订单有效
	 * @参数 id 订单ID，仅限会员登录时有效
	 * @参数 sn 订单编号，游客购买有效
	 * @参数 passwd 订单密码，游客购买有效
	 * @参数 balance 财富支付，仅限会员登录时有效
	 * @参数 payment 支付方式 仅限财富支付不能完全抵消或是游客购买时有效
	 * @更新时间 2016年08月16日
	**/
	public function create_f()
	{
		if($this->session->val('user_id')){
			$id = $this->get('id','int');
			if(!$id){
				$this->error(P_Lang('未指定订单号ID'));
			}
			$rs = $this->model('order')->get_one($id);
			$order_url = $this->url('order','info','id='.$id);
			$error_url = $this->url('order');
		}else{
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
		}
		if($this->model('order')->check_payment_is_end($rs['id'])){
			$this->error(P_Lang('订单已支付过，不能重复操作'),$order_url);
		}
		if(!$this->balance_minus($rs,true)){
			$payment = $this->get('payment','int');
			if(!$payment){
				$this->error(P_Lang('未指定付款方式'),$order_url);
			}
			$payment_rs = $this->model('payment')->get_one($payment);
			if(!$payment_rs){
				$this->error(P_Lang('支付方式不存在'),$order_url);
			}
			if(!$payment_rs['status']){
				$this->error(P_Lang('支付方式未启用'),$order_url);
			}
			$chk = $this->model('payment')->log_check($rs['sn']);
			if($chk && $chk['status']){
				$this->error(P_Lang('订单{sn}已支付完成，不能重复执行',array('sn'=>$info['sn'])),$order_url);
			}
		}
		$this->balance_minus($rs);
		//检测是否已支付完成
		if($this->model('order')->check_payment_is_end($rs['id'])){
			$this->model('order')->update_order_status($rs['id'],'paid');
			//订单返财富
			$this->model('wealth')->order($rs['id'],$this->session->val('user_id'));
			$this->success(P_Lang('订单已支付完成'),$order_url);
		}
		//订单未支付完成创建生成链接
		$price_paid = $this->model('order')->paid_price($rs['id']);
		$price = $rs['price'] - $price_paid;
		$array = array('type'=>'order','price'=>$price,'currency_id'=>$rs['currency_id'],'sn'=>$rs['sn']);
		$array['content'] = $array['title'] = P_Lang('订单：{sn}',array('sn'=>$rs['sn']));
		$array['payment_id'] = $payment;
		$array['dateline'] = $this->time;
		$array['user_id'] = $this->session->val('user_id');
		$chk = $this->model('payment')->log_check($rs['sn']);
		if($chk){
			$insert_id = $chk['id'];
			$this->model('payment')->log_update($array,$chk['id']);
		}else{
			$insert_id = $this->model('payment')->log_create($array);
			if(!$insert_id){
				$this->error(P_Lang('支付记录创建失败，请联系管理员'),$order_url);
			}
		}
		$this->model('order')->update_order_status($rs['id'],'unpaid');
		//增加order_payment
		$array = array('order_id'=>$rs['id'],'payment_id'=>$payment);
		$array['title'] = $payment_rs['title'];
		$array['price'] = $price;
		$array['startdate'] = $this->time;
		$order_payment = $this->model('order')->order_payment($rs['id'],$payment);
		if(!$order_payment){
			$this->model('order')->save_payment($array);
		}else{
			$this->model('order')->save_payment($array,$order_payment['id']);
		}
		$this->success(P_Lang('成功创建支付链，请稍候，即将为您跳转支付页面…'),$this->url('payment','submit','id='.$insert_id));
	}

	/**
	 * 从余额中扣除费用，如果余额已超出订单金额，则无需再跳转到支付链
	 * @参数 $order 订单信息
	 * @参数 $check 是否仅用于检测，为true时不扣除费用
	 * @返回 false 或 剩余订单信息
	 * @更新时间 2016年08月04日
	**/
	private function balance_minus($order,$check=false)
	{
		if(!$this->session->val('user_id')){
			return false;
		}
		$balance = $this->get('balance','int');
		if(!$balance){
			return false;
		}
		$wlist = $this->model('order')->balance($this->session->val('user_id'));
		if(!$wlist){
			return false;
		}
		$rslist = array();
		foreach($wlist as $key=>$value){
			$rslist[$value['id']] = $value;
		}
		$totalprice = price_format_val($order['price'],$order['currency_id'],$order['currency_id']);
		$tmpprice = 0;
		foreach($balance as $key=>$value){
			if(!$value || !$rslist[$value]){
				continue;
			}
			if($check){
				$balance = price_format_val($rslist[$value]['price'],$order['currency_id'],$order['currency_id']);
				$tmpprice += $balance;
			}else{
				$tmporder = array('id'=>$order['id'],'sn'=>$order['sn'],'price'=>$totalprice,'currency_id'=>$order['currency_id']);
				$tmp = $this->balance_order_payment($tmporder,$rslist[$value]);
				if(!$tmp){
					continue;
				}
				if(!$tmp['price']){
					return true;
				}
				$totalprice = $tmp['price'];
			}
		}
		if($check){
			if($tmpprice >= $totalprice){
				return true;
			}
			return false;
		}
		return true;
	}

	/**
	 * 创建订单及扣除订单费用
	 * @参数 $order 订单信息，数组
	 * @参数 $info 用户积分信息
	 * @返回 数组 或 false
	 * @更新时间 2016年08月16日
	**/
	private function balance_order_payment($order,$info)
	{
		$totalprice = price_format_val($order['price'],$order['currency_id'],$order['currency_id']);
		$balance = price_format_val($info['price'],$order['currency_id'],$order['currency_id']);
		if(!$balance){
			return false;
		}
		$surplus = $balance >= $totalprice ? 0 : ($totalprice - $balance);
		$array = array('order_id'=>$order['id'],'payment_id'=>0);
		$array['title'] = $info['title'];
		$array['price'] = $balance;
		$array['startdate'] = $this->time;
		$array['dateline'] = $this->time;
		$array['ext'] = serialize(array('wealth'=>$info['id'],'wealth_val'=>$info['val']));
		$this->model('order')->save_payment($array);
		//扣除会员积分
		$savelogs = array('wid'=>$info['id'],'goal_id'=>$this->session->val('user_id'),'mid'=>0,'val'=>'-'.$info['val']);
		$savelogs['appid'] = $this->app_id;
		$savelogs['dateline'] = $this->time;
		$savelogs['user_id'] = $this->session->val('user_id');
		$savelogs['ctrlid'] = 'payment';
		$savelogs['funcid'] = 'create';
		$savelogs['url'] = 'index.php';
		$savelogs['note'] = $note ? P_Lang('财富抵现：').$note : P_Lang('财富抵现');
		$savelogs['status'] = 1;
		$data = array('wid'=>$info['id'],'uid'=>$this->session->val('user_id'),'lasttime'=>$this->time);
		//剩余积分
		if($surplus){
			$data['val'] = 0;
			$savelogs['val'] = -$info['val'];
			$paid_price = price_format($info['price'],$order['currency_id'],$order['currency_id']);
		}else{
			$savelogs['val'] = -($order['price'] * $info['val'] / $info['price']);
			$data['val'] = round(($info['val']+$savelogs['val']),$info['dnum']);
			$paid_price = price_format($order['price'],$order['currency_id'],$order['currency_id']);
		}
		$this->model('wealth')->save_log($savelogs);
		$this->model('wealth')->save_info($data);
		//创建订单日志，记录支付信息
		$tmparray = array('sn'=>$order['sn'],'price'=>$paid_price,'payment'=>$info['title']);
		$note = P_Lang('订单{sn}支付{price}，支付方法：{payment}',$tmparray);
		$who = $this->session->val('user_name');
		$log = array('order_id'=>$order['id'],'addtime'=>$this->time,'who'=>$who,'note'=>$note);
		$this->model('order')->log_save($log);
		
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
			error(P_Lang('订单已支付过了，不能再次执行'),'','error');
		}
		$payment_rs = $this->model('payment')->get_one($log['payment_id']);
		if(!$payment_rs){
			error(P_Lang('支付方式不存在'),'','error');
		}
		if(!$payment_rs['status']){
			error(P_Lang('支付方式未启用'),'','error');
		}
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/submit.php';
		if(!file_exists($file)){
			$tmpfile = str_replace($this->dir_root,'',$file);
			error(P_Lang('支付接口异常，文件{file}不存在',array('file'=>$tmpfile)),'','error');
		}
		include($file);
		$name = $payment_rs['code']."_submit";
		$payment = new $name($log,$payment_rs);
		$payment->submit();
	}

	public function notice_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('执行异常，请检查，缺少参数ID'),'','error');
		}
		$rs = $this->model('payment')->log_one($id);
		if(!$rs){
			error(P_Lang('订单信息不存在'),$this->url('index'),'error');
		}
		if($rs['type'] == 'order'){
			//$order = $this->model('order')->get_one_from_sn($rs['sn']);
			$url = $this->url('order','info','sn='.$rs['sn']);
		}elseif($rs['type'] == 'recharge'){
			$url = $this->url('usercp','wealth','sn='.$rs['sn']);
		}else{
			$url = $this->url('payment','show','id='.$id);
		}
		//同步通知
		if($rs['status']){
			error(P_Lang('您的订单付款成功，请稍候…'),$url,'ok');
		}
		$payment_rs = $this->model('payment')->get_one($rs['payment_id']);
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/notice.php';
		if(!file_exists($file)){
			$tmpfile = str_replace($this->dir_root,'',$file);
			error(P_Lang('支付接口异常，文件{file}不存在',array('file'=>$tmpfile)),'','error');
		}
		include($file);
		$name = $payment_rs['code'].'_notice';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
		error(P_Lang('您的订单付款成功，请稍候…'),$url,'ok');
	}

	//异步通知方案
	//考虑到异步通知存在读不到$_SESSION问题，使用sn和pass组合
	public function notify_f()
	{
		$sn = $this->get('sn');
		if(!$sn){
			exit('error');
		}
		$rs = $this->model('payment')->log_check($sn);
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
			error(P_Lang('未指定ID'),'','error');
		}
		$rs = $this->model('payment')->log_one($id);
		if(!$rs){
			error(P_Lang('数据不存在，请检查'),'','error');
		}
		if($rs['type'] == 'order'){
			//$order = $this->model('order')->get_one_from_sn($rs['sn']);
			//if(!$order){
			//	error(P_Lang('订单信息不存在'),'','error');
			//}
			$url = $this->url('order','info','sn='.$rs['sn']);
			$this->_location($url);
		}
		$this->view('payment_show');
	}
}

?>