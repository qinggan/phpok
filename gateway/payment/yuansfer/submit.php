<?php
/**
 * 圆支付
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年10月13日
**/
use Yuansfer\Yuansfer;
use Yuansfer\Exception\YuansferException;

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class yuansfer_submit
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
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/yuansfer/';
		$this->baseurl = $GLOBALS['app']->url;
	
		require_once($this->paydir."vendor/autoload.php");
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
	public function submit()
	{
		global $app;
		$total_fee = price_format_val($this->order['price'],$this->order['currency_id'],$currency_id);
		$currency_id = $this->param['currency'] ? $this->param['currency']['id'] : $this->order['currency_id'];
		$currency_rs = $app->model('currency')->get_one($currency_id);
		$config = array(
			Yuansfer::MERCHANT_NO => $this->param['param']['merchantNo'],
			Yuansfer::STORE_NO => $this->param['param']['storeNo'],
			Yuansfer::API_TOKEN => $this->param['param']['yuansferToken'],
			Yuansfer::TEST_API_TOKEN => $this->param['param']['yuansferToken'],
		);
		
		$yuansfer = new Yuansfer($config);
		if($this->param['param']['ptype'] == 'demo'){
			$yuansfer->setTestMode();
		}else{
			$yuansfer->setProductionMode();
		}
		$api = $yuansfer->createSecurePay();
		$api->setAmount($total_fee);
		$api->setCurrency($currency_rs['code']);
		if($this->param['wap']){
			$api->setTerminal('WAP');
		}else{
			$api->setTerminal('ONLINE');
		}
		if($this->param['param']['paylist']){
			$api->setVendor($this->param['param']['paylist']);
		}
		$api->setReference($this->order['sn']);
		$api->setIpnUrl($this->baseurl."gateway/payment/yuansfer/notify_url.php");
		$return_url = $GLOBALS['app']->url('payment','notice','id='.$this->order['id'].'&yuansferId={yuansferId}&status={status}&amount={amount}&reference={reference}&note={note}','www',true);
		$api->setCallbackUrl($return_url);
		try {
			echo $api->send();
		} catch (YuansferException $e) {
			if ($e instanceof \Yuansfer\Exception\RequiredEmptyException) {
				$message = 'The param: ' . $e->getParam() . ' is empty, in API: ' . $e->getApi();
				$app->error($message);
			}
			// http connect error
			if ($e instanceof \Yuansfer\Exception\HttpClientException) {
				$message = $e->getMessage();
				$app->error($message);
			}
			// http response status code < 200 or >= 300, 301 and 302 will auto redirect
			if ($e instanceof \Yuansfer\Exception\HttpErrorException) {
				/** @var \Httpful\Response http response */
				$response = $e->getResponse();
				$app->error($response);
			}
		}
	}
}