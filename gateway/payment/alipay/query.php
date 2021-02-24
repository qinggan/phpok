<?php
/**
 * 订单接口查询
 * @作者 苏相锟 <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2020年12月16日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class alipay_query
{
	//支付接口初始化
	public $param;
	public $order;
	public $paydir;
	public $baseurl;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/alipay/';
		$this->alipay_dir = $GLOBALS['app']->extension_dir.'alipay/alipay.phar/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once $this->paydir."lib/alipay_submit.class.php";
        include_once $this->alipay_dir."AopClient.php";
		include_once $this->alipay_dir."AopCertification.php";
		include_once $this->alipay_dir."request/AlipayTradeQueryRequest.php";
		include_once $this->alipay_dir."request/AlipayTradeWapPayRequest.php";
		include_once $this->alipay_dir."request/AlipayTradeAppPayRequest.php";
	}

	public function param($param)
	{
		$this->param = $param;
	}

	public function order($order)
	{
		$this->order = $order;
	}

	public function submit()
	{
		global $app;
		//检查订单信息
		if($this->order['status']){
			$app->success();
		}
		$data = array();
		if($this->param['param']['ptype'] == 'app'){
			$aop = new \AopClient ();
			$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
			$aop->appId = trim($this->param['param']['pid']);
			$aop->rsaPrivateKey = trim($this->param['param']['key']);
			$aop->format = "json";
			$aop->signType = "RSA2";
			$aop->alipayrsaPublicKey = trim($this->param['param']['pubkey']);
			$aop->apiVersion = '1.0';
			$aop->postCharset='UTF-8';
			$aop->charset='UTF-8';
			$request = new \AlipayTradeQueryRequest ();
			$tmpdata = array();
			$tmpdata['out_trade_no'] = $this->order['sn'].'-'.$this->order['id'];
			$request->setBizContent($app->lib('json')->encode($tmpdata));
			$result = $aop->execute($request); 
			$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
			$resultCode = $result->$responseNode->code;
			if(!$resultCode || $resultCode != 10000){
				$msg = $result->$responseNode->msg;
				if($result->$responseNode->sub_msg){
					$msg .= ' '.$result->$responseNode->sub_msg;
				}
				$app->error($msg);
			}
			$data = (array) $result->$responseNode;
		}
		$tmp = array('WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_FINISHED','TRADE_SUCCESS');
		if($data && in_array($data['trade_status'],$tmp)){
			$alipay = $this->order['ext'] ? unserialize($this->order['ext']) : array();
			$alipay['log_id'] = $this->order['id'];
			foreach($data as $key=>$value){
				$alipay[$key] = $value;
			}
			$array = array('status'=>1,'ext'=>serialize($alipay));
			if(!$this->order['status']){
				$array = array('status'=>1,'ext'=>serialize($alipay));
				$app->model('payment')->log_update($array,$this->order['id']);
			}
			if($this->order['type'] == 'order'){
				$order = $app->model('order')->get_one_from_sn($this->order['sn']);
				if($order){
					$payinfo = $app->model('order')->order_payment_notend($order['id']);
					if($payinfo){
						$payment_data = array('dateline'=>$this->time,'ext'=>serialize($alipay));
						$payment_data['price'] = $price; //登记实付金额
						$payment_data['currency_id'] = $this->param['currency']['id']; //登记实付货币
						$payment_data['currency_rate'] = $this->param['currency']['val']; //登记实付汇率
						$app->model('order')->save_payment($payment_data,$payinfo['id']);
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
		$app->error('暂无查到订单');
	}
}