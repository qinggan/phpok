<?php
/**
 * 提交支付
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年04月07日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class paypalcc_submit
{
	private $payment_config;
	private $order;
	private $paydir;
	private $baseurl;
	
	public function __construct($order,$param)
	{
		$this->payment_config = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/paypalcc/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once($this->paydir."paypalcc.php");
	}

	public function submit()
	{
		global $app;
		$id = $app->get('id','int');
		$paypal = new paypalcc_payment($this->payment_config['param']['act_type']);
		$price = $this->order['price'];
		if($this->order['currency_id'] != $this->payment_config['currency']['id']){
			$price = price_format_val($this->order['price'],$this->order['currency_id'],$this->payment_config['currency']['id']);
		}
		$paypal->price($price);
		$paypal->currency($this->payment_config['currency']['code']);
		$paypal->api_username($this->payment_config['param']['api_username']);
		$paypal->api_password($this->payment_config['param']['api_password']);
		$paypal->api_signature($this->payment_config['param']['api_signature']);
		$cc_data = $app->get('cc_data');
		if(!$cc_data){
			$app->view("payment/paypalcc/submit");
			exit;
		}
		$paypal->cc($cc_data);
		//如果订单付款成功
		$rs = $paypal->submit();
		if(!$rs || !is_array($rs)){
			$app->error('付款失败，请联系管理员');
		}
		if(!$rs['ACK']){
			$app->error('请求失败，请检查');
		}
		$state = strtolower($rs['ACK']);
		if($state != 'success' && $state != 'successwithwarning'){
			$error = array();
			foreach($rs as $key=>$value){
				if(strpos($key,'L_ERRORCOD') !== false){
					$tmpid = str_replace('L_ERRORCOD','',$key);
					$error[$tmpid] = '错误代码：'.$value.'，错误信息：'.$rs['L_LONGMESSAG'.$tmpid];
				}
			}
			$app->error(implode('<br/>',$error));
		}
		//更新订单信息
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$payinfo = $app->model('order')->order_payment_notend($order['id']);
				if($payinfo){
					$payment_data = array('dateline'=>$app->time);
					$app->model('order')->save_payment($payment_data,$payinfo['id']);
					//更新订单日志
					$app->model('order')->update_order_status($order['id'],'paid');
					$note = P_Lang('订单支付完成，编号：{sn}',array('sn'=>$order['sn']));
					$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
					$app->model('order')->log_save($log);
				}
			}
		}
		if($this->order['type'] == 'recharge'){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
		$app->success();
	}
}