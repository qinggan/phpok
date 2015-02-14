<?php
/*****************************************************************************************
	文件： payment/tenpay/submit.php
	备注： 财付通确认支付
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月3日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tenpay_submit
{
	//支付接口初始化
	var $param;
	var $order;
	var $paydir;
	var $baseurl;
	function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'payment/tenpay/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once($this->paydir."tenpay.php");
	}

	function submit()
	{
		$tenpay = new tenpay_lib();
		$tenpay->set_key($this->param['param']['key']);
		$tenpay->set_biz($this->param['param']['pid']);
		$tenpay->set_email($this->param['param']['email']);
		$tenpay->set_url('https://gw.tenpay.com/gateway/pay.htm');
		$notify_url = $this->baseurl."payment/tenpay/notify_url.php";
        //同步通知网址
        $return_url = api_url('payment','notice','id='.$this->order['id'],true);
        //付款金额
		$currency_id = $this->param['currency'] ? $this->param['currency']['id'] : $this->order['currency_id'];
		$total_fee = price_format_val($this->order['price'],$this->order['currency_id'],$currency_id);
        //商品展示地址
        $show_url = $GLOBALS['app']->url('order','info','id='.$this->order['id']);
		/************************************************************/
		//商户编号
		$desc = 'SN:'.$this->order['sn'];
		$tenpay->param('partner',$this->param['param']['pid']);
		$tenpay->param("out_trade_no", $this->order['sn']);
		$tenpay->param("total_fee", $total_fee * 100);  //总金额
		$tenpay->param("return_url", $return_url);
		$tenpay->param("notify_url", $notify_url);
		$tenpay->param("body",$desc);
		//银行类型
		if($this->param['param']['bank'])
		{
			$tenpay->param("bank_type",trim(strtoupper($this->param['param']['bank'])));
		}
		else
		{
			$tenpay->param("bank_type", "DEFAULT");
		}
		//用户ip
		$tenpay->param("spbill_create_ip",$GLOBALS['app']->lib('common')->ip());//客户端IP
		$tenpay->param("fee_type", "1");               //币种
		$tenpay->param("subject",$desc);          //商品名称，（中介交易时必填）

		//系统可选参数
		$tenpay->param("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
		$tenpay->param("service_version", "1.0"); 	  //接口版本号
		$tenpay->param("input_charset", "utf-8");   	  //字符集
		$tenpay->param("sign_key_index", "1");    	  //密钥序号

		//业务可选参数
		$ptype = $this->param['param']['ptype'] == 'create_direct_pay_by_user' ? 1 : 2;
		$tenpay->param("attach", $this->order['passwd']);      //附件数据，原样返回就可以了
		$tenpay->param("product_fee", "");        	  //商品手续费用
		$tenpay->param("transport_fee", "0");      	  //物流费用
		$tenpay->param("time_start", date("YmdHis",$this->time));  //订单生成时间
		$tenpay->param("time_expire", "");             //订单失效时间
		$tenpay->param("buyer_id", "");                //买方财付通帐号
		$tenpay->param("goods_tag", "");               //商品标记
		$tenpay->param("trade_mode",$ptype);      //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
		$tenpay->param("transport_desc","");              //物流说明
		$tenpay->param("trans_type","1");              //交易类型
		$tenpay->param("agentid","");                  //平台ID
		$tenpay->param("agent_type",0);               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
		$tenpay->param("seller_id","");                //卖家的商户号

		$url = $tenpay->url();
		header("Location:".$url);
		exit;
	}
}
?>