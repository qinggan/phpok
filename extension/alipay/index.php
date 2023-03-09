<?php
/**
 * 支付宝公共类
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年1月30日
**/

class alipay_lib extends _init_lib
{
	private $app_id; // App Id
	private $notify_url; // 异步通知网址
	private $gateway_url = 'https://mapi.alipay.com/gateway.do'; // 网关
	private $private_key; // 私钥信息
	private $public_key; // 公钥信息
	private $quit_url; // 取消支付跳回网页
	private $return_url; // 同步返回跳转通知网址
	private $qr_pay_mode = 2; //支付方式
	private $config = array();
	private $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&'; // HTTPS形式消息验证地址
	private $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?'; // HTTP形式消息验证地址
	public function __construct()
	{
		parent::__construct();
		$pharfile = 'phar://'.$this->dir_extension.'alipay/alipay.phar';
		include_once $pharfile."/AopClient.php";
		include_once $pharfile."/AopCertification.php";
		include_once $pharfile."/request/AlipayTradeAppPayRequest.php"; // App 支付接口
		include_once $pharfile."/request/AlipayTradeCancelRequest.php"; // 交易撤销接口
		include_once $pharfile."/request/AlipayTradeCloseRequest.php"; // 交易关闭接口
		include_once $pharfile."/request/AlipayTradeCreateRequest.php"; // 商户通过该接口进行交易的创建下单
		include_once $pharfile."/request/AlipayTradePagePayRequest.php"; // 即时到账接口
		include_once $pharfile."/request/AlipayTradePageRefundRequest.php"; // 即时到账退款接口
		include_once $pharfile."/request/AlipayTradePayRequest.php"; // 标准支付接口
		include_once $pharfile."/request/AlipayTradeQueryRequest.php"; // 交易查询
		include_once $pharfile."/request/AlipayTradeRefundRequest.php"; // 交易退款接口
		include_once $pharfile."/request/AlipayTradeWapPayRequest.php"; // 手机网站支付接口
	}

	public function aop()
	{
		$aop = new \AopClient;
		if($this->gateway_url){
			$aop->gatewayUrl = $this->gateway_url;
		}
		$aop->appId = $this->app_id;
		$aop->rsaPrivateKey = $this->private_key;
		$aop->format = "json";
		$aop->charset = "UTF-8";
		$aop->signType = "RSA2";
		$aop->alipayrsaPublicKey = $this->public_key;
		return $aop;
	}

	public function aop_verify($post)
	{
		$aop = $this->aop();
		return $aop->rsaCheckV1($post, null, "RSA2");
	}

	public function app_id($appid='')
	{
		if($appid){
			$this->app_id = trim($appid);
		}
		return $this->app_id;
	}

	/**
	 * App 统一下单
	 * @参数 $sn 订单编号
	 * @参数 $price 订单金额
	 * @参数 $title 标题，留空使用订单编号
	 * @参数 $body 说明
	**/
	public function app_create($sn,$price,$title='',$body='')
	{
		$aop = $this->aop();
		//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
		$request = new \AlipayTradeAppPayRequest();
		$request->setNotifyUrl($this->notify_url);
		$tmpdata = array();
		$tmpdata['subject'] = $title ? $title : '订单'.$sn;
		if($body){
			$tmpdata['body'] = $order['body'];
		}
		$tmpdata['out_trade_no'] = $sn;
		$tmpdata['total_amount'] = $price;
		$tmpdata['product_code'] = 'QUICK_MSECURITY_PAY';
		$bizcontent = json_encode($tmpdata);
		$request->setBizContent($bizcontent);
		$response = $aop->sdkExecute($request);
		return $response;
	}

	public function config($key='',$val='')
	{
		if($key && is_array($key)){
			foreach($key as $k=>$v){
				$this->config[$k] = $v;
			}
		}
		if($key && $val !=''){
			$this->config[$k] = $v;
		}
		return $this->config;
	}

	/**
	 * 网关地址
	 * @参数 $val 支付宝网关地址
	**/
	public function gateway_url($val='')
	{
		if($val){
			$this->gateway_url = trim($val);
		}
		return $this->gateway_url;
	}


	/**
	 * 手机网页支付
	 * @参数 $sn 订单编号
	 * @参数 $price 订单金额
	 * @参数 $title 标题，留空使用订单编号
	 * @参数 $body 说明
	**/
	public function mobile_create($sn,$price,$title='',$body='')
	{
		$aop = $this->aop();
		$request = new \AlipayTradeWapPayRequest ();
		$request->setNotifyUrl($this->notify_url);
		$request->setReturnUrl($this->return_url);
		$request->setQuitUrl($this->quit_url);
		$tmpdata = array();
		$tmpdata['subject'] = $title ? $title : '订单'.$sn;
		if($body){
			$tmpdata['body'] = $order['body'];
		}
		$tmpdata['out_trade_no'] = $sn;
		$tmpdata['total_amount'] = $price;
		$tmpdata['product_code'] = 'QUICK_WAP_PAY';
		$tmpdata['timeout_express'] = '1h';
		$bizcontent = json_encode($tmpdata);
		$request->setBizContent($bizcontent);
		$form = $aop->pageExecute($request);
		echo $form;
		exit;
	}


	/**
	 * 签名字符串
	 * @参数 $prestr 需要签名的字符串
	 * @参数 $key 私钥
	 * return 签名结果
	 */
	public function md5_sign($prestr, $key='')
	{
		if(!$key){
			$key = $this->config['key'];
		}
		$prestr = $prestr . $key;
		return md5($prestr);
	}

	/**
	 * 验证签名
	 * @参数 $prestr 需要签名的字符串
	 * @参数 $sign 签名结果
	 * @参数 $key 私钥
	 * return 签名结果
	 */
	public function md5_verify($prestr, $sign, $key='')
	{
		if(!$key){
			$key = $this->config['key'];
		}
		$prestr = $prestr . $key;
		$mysgin = md5($prestr);

		if($mysgin == $sign) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * 异步通知网址
	 * @参数 $val 自定义的异步通知地址
	**/
	public function notify_url($val='')
	{
		if($val){
			$this->notify_url = trim($val);
		}
		return $this->notify_url;
	}

	public function qr_pay_mode($val='')
	{
		if($val){
			$this->qr_pay_mode = $val;
		}
		return $this->qr_pay_mode;
	}

	/**
	 * 即时到账支付接口
	 * @参数 $sn 订单编号
	 * @参数 $price 订单金额
	 * @参数 $title 标题，留空使用订单编号
	 * @参数 $body 说明
	**/
	public function pagepay_create($sn,$price,$title='',$body='')
	{
		$aop = $this->aop();
		$request = new \AlipayTradePagePayRequest();
		$request->setNotifyUrl($this->notify_url);
		$request->setReturnUrl($this->return_url);
		$tmpdata = array();
		$tmpdata['subject'] = $title ? $title : '订单'.$sn;
		if($body){
			$tmpdata['body'] = $order['body'];
		}
		$tmpdata['out_trade_no'] = $sn;
		$tmpdata['total_amount'] = $price;
		$tmpdata['product_code'] = 'FAST_INSTANT_TRADE_PAY';
		$tmpdata['timeout_express'] = '1h';
		if($this->qr_pay_mode){
			$tmpdata['qr_pay_mode'] = $this->qr_pay_mode;
		}
		$bizcontent = json_encode($tmpdata);
		$request->setBizContent($bizcontent);
		$form = $aop->pageExecute($request);
		echo $form;
		exit;
	}

	/**
	 * 生成要提交的数组
	 * @参数 $para_temp
	**/
	public function params($para_temp)
	{
		//除去待签名参数数组中的空值和签名参数
		$para_filter = array();
		foreach($para_temp as $key=>$val){
			if($key == "sign" || $key == "sign_type" || $val == ""){
				continue;
			}
			$para_filter[$key] = $val;
		}
		ksort($para_filter);
		reset($para_filter);
		//生成签名结果
		$mysign = $this->params2sign($para_filter);
		//签名结果与签名方式加入请求提交参数组中
		$para_filter['sign'] = $mysign;
		$para_filter['sign_type'] = 'MD5';
		return $para_filter;
	}

	
	public function params2sign($para)
	{
		$arg  = "";
		foreach($para as $key=>$val){
			$arg.=$key."=".$val."&";
		}
		$arg = substr($arg,0,-1);
		
		//如果存在转义字符，那么去掉转义
		if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()){
			$arg = stripslashes($arg);
		}
		return $this->md5_sign($arg);
	}
	
	public function private_key($val='')
	{
		if($val){
			$this->private_key = trim($val);
		}
		return $this->private_key;
	}

	public function public_key($val='')
	{
		if($val){
			$this->public_key = trim($val);
		}
		return $this->public_key;
	}

	/**
	 * 订单查询
	**/
	public function query($sn='')
	{
		global $app;
		$aop = $this->aop();
		$request = new \AlipayTradeQueryRequest();
		$tmpdata = array();
		$tmpdata['out_trade_no'] = $sn;
		$request->setBizContent($app->lib('json')->encode($tmpdata));
		$rs = $aop->execute($request);
		return $rs;
	}

	public function quit_url($val='')
	{
		if($val){
			$this->quit_url = trim($val);
		}
		return $this->quit_url;
	}

	public function refund($params)
	{
		$aop = $this->aop();
		$request = new \AlipayTradeRefundRequest();
		$tmpdata = array();
		$tmpdata['trade_no'] = $params['trade_no'];
		$tmpdata['out_request_no'] = $params['sn'];
		$tmpdata['refund_amount'] = $params['price'];
		$tmpdata['refund_currency'] = $params['currency'];
		$tmpdata['refund_reason'] = $params['note'];
		$bizcontent = json_encode($tmpdata);
		$request->setBizContent($bizcontent);
		$rs = $aop->execute($request);
		return $rs;
	}

	public function refund_page($params)
	{
		$aop = $this->aop();
		$request = new \AlipayTradePageRefundRequest();
		$request->setReturnUrl($this->return_url);
		$tmpdata = array();
		$tmpdata['trade_no'] = $params['trade_no'];
		$tmpdata['out_request_no'] = $params['sn'];
		$tmpdata['refund_amount'] = $params['price'];
		$tmpdata['refund_reason'] = $params['note'];
		$bizcontent = json_encode($tmpdata);
		$request->setBizContent($bizcontent);
		$form = $aop->pageExecute($request);
		echo 'demo';
		//echo $form;
		exit;
	}

	public function return_url($val='')
	{
		if($val){
			$this->return_url = trim($val);
		}
		return $this->return_url;
	}


	public function submit($params,$form_url='')
	{
		if(!$form_url){
			$form_url = $this->gateway_url;
		}
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$form_url."?_input_charset=utf-8' method='post'>";
		foreach($params as $key=>$val){
			$sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
		}
        $sHtml .= "<input type='submit' value='Loading...'>";
        $sHtml .= "</form>";
		$sHtml .= "<script>document.forms['alipaysubmit'].submit();</script>";
		echo $sHtml;
		exit;
	}

	public function verify($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$params = $this->params($data);
		if($params['sign'] != $data['sign']){
			return false;
		}
		if(!$data['notify_id']){
			return true;
		}
		$transport = strtolower(trim($this->config['transport']));
		$partner = trim($this->config['partner']);
		$verify_url  = $transport == 'https' ? $this->https_verify_url : $this->http_verify_url;
		$verify_url .= "partner=" . $partner . "&notify_id=" . $data["notify_id"];
		$info = $this->curl($verify_url);
		if(preg_match("/true$/i",$info)){
			return true;
		}
		return false;
	}

	private function curl($url,$cacert_url='')
	{
		if(!$cacert_url){
			$cacert_url = $this->config['cacert'];
		}
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
		curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($curl);
		return $responseText;
	}
}
