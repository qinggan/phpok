<?php
/**
 * 异步通知
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年01月26日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class alipay_notify
{
	private $paydir;
	private $order;
	private $payment;
	private $alipay;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/alipay/';
		$this->alipay = $GLOBALS['app']->lib('alipay');
		$this->baseurl = $GLOBALS['app']->url;
	}

	public function submit()
	{
		global $app;
		unset($_GET[$app->config['ctrl_id']],$_GET[$app->config['func_id']],$_GET['sn'],$_GET['_noCache']);
		//基于APP的异步通知处理
		if($this->param['param']['ptype'] == 'app'){
			$this->alipay->app_id($this->param['param']['pid']);
			$this->alipay->private_key($this->param['param']['prikey']);
			$this->alipay->public_key($this->param['param']['pubkey']);
			$flag = $this->alipay->aop_verify($_GET);
			if(!$flag){
				exit('fail');
			}
			$price = $app->get('total_amount','float');
		}else{
			$alipay_config = array('partner'=>$this->param['param']['pid'],'key'=>$this->param['param']['key']);
			$alipay_config['sign_type'] ='MD5';
			$alipay_config['input_charset']= 'utf-8';
			$alipay_config['cacert']    = $this->paydir.'cacert.pem';
			$alipay_config['transport']    = 'http';
			$this->alipay->config($alipay_config);
			$flag = $this->alipay->verify($_GET);
			if(!$flag){
				exit('fail');
			}
			$price = $app->get('total_fee','float');
		}
		//付款金额，支付宝接口仅支持人民币
		$trade_status = $app->get('trade_status');
		$tmp = array('WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_FINISHED','TRADE_SUCCESS');
		if(!$trade_status || !in_array($trade_status,$tmp)){
			exit('fail');
		}
		$alipay = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		//$alipay = array();
		//更新扩展数据
		$alipay['log_id'] = $this->order['id'];
		$alipay['buyer_email'] = $app->get('buyer_email') ? $app->get('buyer_email') : $app->get('buyer_logon_id');
		$alipay['buyer_id'] = $app->get('buyer_id');
		$alipay['out_trade _no'] = $app->get('out_trade _no');
		$alipay['seller_email'] = $app->get('seller_email');
		$alipay['seller_id'] = $app->get('seller_id');
		$alipay['trade_no'] = $app->get('trade_no');
		$alipay['trade_status'] = $app->get('trade_status');
		$alipay['notify_id'] = $app->get('notify_id');
		$alipay['notify_time'] = $app->get('notify_time');
		$alipay['notify_type'] = $app->get('notify_type');
		$alipay['total_fee'] = $price;
		$alipay['body'] = $app->get('body');
		$alipay['agent_user_id'] = $app->get('agent_user_id');
		$alipay['extra_common_param'] = $app->get('extra_common_param');
		$alipay['subject'] = $app->get('subject');
		$array = array('status'=>1,'ext'=>serialize($alipay));
		$app->db->update_array($array,'payment_log',array('id'=>$this->order['id']));
		//如果当前支付操作是订单
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$payinfo = $app->model('order')->order_payment_notend($order['id']);
				if($payinfo){
					$payment_data = array('dateline'=>$app->time,'ext'=>serialize($alipay));
					$payment_data['price'] = $price; //登记实付金额
					$payment_data['currency_id'] = $this->param['currency']['id']; //登记实付货币
					$payment_data['currency_rate'] = $this->param['currency']['val']; //登记实付汇率
					$app->model('order')->save_payment($payment_data,$payinfo['id']);
					//更新订单日志
					$app->model('order')->update_order_status($order['id'],'paid');
					$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
					$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
					$app->model('order')->log_save($log);
				}
			}
		}
		//充值操作
		if($this->order['type'] == 'recharge' && $alipay['goal']){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notify',$this->order['id']);
		exit('success');
	}
}