<?php
/*****************************************************************************************
	文件： payment/alipay/submit.php
	备注： 支付接口提交页
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月2日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class alipay_submit
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
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/alipay/';
		$this->alipay = $GLOBALS['app']->lib('alipay');
		$this->baseurl = $GLOBALS['app']->url;
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
        $notify_url = $this->baseurl."gateway/payment/alipay/notify_url.php";
        $return_url = $app->url('payment','notice','id='.$this->order['id'],'www',true);
        $show_url = $app->url('payment','show','id='.$this->order['id'],'www',true);
        $currency_id = $this->param['currency'] ? $this->param['currency']['id'] : $this->order['currency_id'];
        $total_fee = price_format_val($this->order['price'],$this->order['currency_id'],$currency_id);
        $form_url = 'https://mapi.alipay.com/gateway.do';
		if($this->param['param']['envtype'] == 'demo'){
			$form_url = 'https://mapi.alipaydev.com/gateway.do';
		}
		if($this->param['param']['envtype'] == 'product_n'){
			$form_url = 'https://intlmapi.alipay.com/gateway.do';
		}
        // APP支付
        if($this->param['param']['ptype'] == 'app' && $json){
			$this->alipay->app_id($this->param['param']['pid']);
			$this->alipay->private_key($this->param['param']['prikey']);
			$this->alipay->public_key($this->param['param']['pubkey']);
			$this->alipay->notify_url($notify_url);
			$this->alipay->gateway_url($form_url);
			$info = $this->alipay->app_create($this->order['sn'].'-'.$this->order['id'],floatval($total_fee));
			$app->success(array('orderInfo'=>$info,'provider'=>'alipay'));
        }

        //新版PC即时到账支付 - 基于公钥+私钥
        if($this->param['param']['ptype'] == 'pagepay'){
	        $this->alipay->gateway_url($form_url);
	        $this->alipay->app_id($this->param['param']['pid']);
			$this->alipay->private_key($this->param['param']['prikey']);
			$this->alipay->public_key($this->param['param']['pubkey']);
			$this->alipay->notify_url($notify_url);
			$this->alipay->return_url($cancel_url);
			$info = $this->alipay->pagepay_create($this->order['sn'].'-'.$this->order['id'],floatval($total_fee));
			exit;
        }

        //新版手机支付
        if($this->param['param']['ptype'] == 'wappay'){
	        $this->alipay->gateway_url($form_url);
	        $this->alipay->app_id($this->param['param']['pid']);
			$this->alipay->private_key($this->param['param']['prikey']);
			$this->alipay->public_key($this->param['param']['pubkey']);
			$this->alipay->notify_url($notify_url);
			$this->alipay->return_url($return_url);
			$this->alipay->quit_url($return_url);
			$info = $this->alipay->mobile_create($this->order['sn'].'-'.$this->order['id'],floatval($total_fee));
			exit;
        }

        //其他支付（包括海外）
        $alipay_config = array('partner'=>$this->param['param']['pid'],'key'=>$this->param['param']['key']);
		$alipay_config['sign_type'] ='MD5';
		$alipay_config['input_charset']= 'utf-8';
		$alipay_config['cacert']    = $this->paydir.'cacert.pem';
		$alipay_config['transport']    = 'http';
		$this->alipay->config($alipay_config);
        
		$parameter = array(
			"service" => $this->param['param']['ptype'],
			"partner" => trim($this->param['param']['pid']),
			"payment_type"	=> 1,
			"notify_url"	=> $notify_url,
			"return_url"	=> $return_url,
			"seller_email"	=> $this->param['param']['email'],
			"out_trade_no"	=> $this->order['sn'].'-'.$this->order['id'],
			"subject"	=> $this->order['title'],
			"body"	=> $this->order['content'],
			"show_url"	=> $show_url,
			"_input_charset"	=> 'utf-8'
		);
		if($this->param['param']['ptype'] == 'create_forex_trade' || $this->param['param']['ptype'] == 'create_forex_trade_wap'){
			$currency_rs = $app->model('currency')->get_one($currency_id);
			if($currency_rs['code'] == 'CNY'){
				$parameter['rmb_fee'] = $total_fee;
			}else{
				$parameter['total_fee'] = $total_fee;
			}
			$parameter['currency'] = $currency_rs['code'];
			if($this->param['param']['envtype'] == 'product_n'){
				$parameter['product_code'] = 'NEW_OVERSEAS_SELLER';
				if($this->param['param']['ptype'] == 'create_forex_trade_wap'){
					$parameter['product_code'] = 'NEW_WAP_OVERSEAS_SELLER';
				}
			}
			$form_url = 'https://intlmapi.alipay.com/gateway.do';
		}else{
			$parameter['total_fee'] = $total_fee;
			$parameter['anti_phishing_key'] = '';
			$parameter['exter_invoke_ip'] = phpok_ip();
		}

		$params = $this->alipay->params($parameter);
		$this->alipay->submit($params,$form_url);
	}
}