<?php
/**
 * 汉特支付
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年2月10日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class hantepay_submit
{
	//支付接口初始化
	public $param;
	public $order;
	public $paydir;
	public $baseurl;
	private $alipay;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/hantepay/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once $this->paydir.'hantepay.class.php';
	}

	public function param($param)
	{
		$this->param = $param;
	}

	public function order($order)
	{
		$this->order = $order;
	}

	//创建订单
	public function submit($json=false)
	{
		global $app;
		$pay = new hantepay();
		$pay->merchant_no($this->param['param']['merchant_no']);
		$pay->store_no($this->param['param']['store_no']);
		$pay->apikey($this->param['param']['key_secret']);
		if($this->param['param']['ptype'] != 'qrpay'){
			$method = $this->param['param']['payment_method'] == 'fixed' ? 'alipay' : $this->param['param']['payment_method'];
			$pay->payment_method($method);
		}
        $notify_url = $this->baseurl."gateway/payment/hantepay/notify_url.php";
        $return_url = $this->baseurl."gateway/payment/hantepay/notice_url.php";
		$pay->notify_url($notify_url);
		$pay->notice_url($return_url);
		$pay->paytype($this->param['param']['ptype']);
		$currency_id = $this->param['currency'] ? $this->param['currency']['id'] : $this->order['currency_id'];
		$currency = $app->model('currency')->get_one($currency_id);
		$CNY_data = array('securepay','micropay');
		if($this->param['param']['auto_rmb'] == 'auto' && $currency['code'] == 'CNY' && in_array($this->param['param']['ptype'],$CNY_data)){
			$total_fee = price_format_val($this->order['price'],$this->order['currency_id'],$currency['id']);
			$pay->price_CNY(intval($total_fee*100));
		}else{
			$currency = $app->model('currency')->get_one('USD','code');
			if(!$currency){
				$this->error(P_Lang('货币不存在'));
			}
			$total_fee = price_format_val($this->order['price'],$this->order['currency_id'],$currency['id']);
			$pay->price(intval($total_fee*100));
		}
		$pay->sn($this->order['sn'].'-'.$this->order['id']);
		$pay->body($this->order['sn']);
		$pay->is_mobile($app->is_mobile());
		$rs = $pay->submit();
		//安全支付，直接跳转
		if($this->param['param']['ptype'] == 'securepay'){
			$app->_location($rs['pay_url']);
		}
		if($this->param['param']['ptype'] == 'qrcode' || $this->param['param']['ptype'] == 'qrpay'){
			$tplist = array();
			$tplist[0] = 'payment/hantepay/qrcode';
			$tplist[1] = 'payment/hantepay_qrcode';
			$tplist[2] = 'payment_hantepay_qrcode';
			$tplist[3] = 'hantepay_qrcode';
			$tplist[4] = 'payment_qrcode';
			$tpl = '';
			foreach($tplist as $key=>$value){
				if($app->tpl->check_exists($value)){
					$tpl = $value;
					break;
				}
			}
			$app->assign('qrcode_url',$rs['code_url']);
			$app->assign('trade_no',$rs['trade_no']);
			$app->assign('sn',$pay->sn());
			$app->assign('payment_method',$this->param['param']['payment_method']);
			$app->assign('order',$this->order);
			if($tpl){
				$app->view($tpl);
			}
			$app->view($this->paydir.'qrcode.html','abs-file');
		}
	}
}