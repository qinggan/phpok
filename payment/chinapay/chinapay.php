<?php
/*****************************************************************************************
	文件： payment/chinapay/chinapay.php
	备注： Chinapay处理类
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月4日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class chinapay_lib
{
	public $pri_key;
	public $pub_key;
	public $debug;
	public $dir_root;
	public $url_pay; //支付请求地址
	public $url_qry; //查询请求地址
	public $url_ref; //退款请求地址
	public $pid;//商户号
	public $orderid;
	public $option; //扩展属性
	
	function __construct($root_dir='',$pid='',$debug=false)
	{
		$this->dir_root = $root_dir;
		$this->debug = $debug;
		$this->set_url();
		$this->set_pid($pid);
		include_once('netpayclient.php');
	}

	//初始化请求信息
	function set_url()
	{
		if($this->debug)
		{
			$this->url_pay = 'http://payment-test.ChinaPay.com/pay/TransGet';
			$this->url_qry = 'http://payment-test.chinapay.com/QueryWeb/processQuery.jsp';
			$this->url_ref = 'http://payment-test.chinapay.com/refund/SingleRefund.jsp';
		}
		else
		{
			$this->url_pay = 'https://payment.ChinaPay.com/pay/TransGet';
			$this->url_qry = 'http://console.chinapay.com/QueryWeb/processQuery.jsp';
			$this->url_ref = 'https://bak.chinapay.com/refund/SingleRefund.jsp';
		}
	}

	function set_pid($pid='')
	{
		$this->pid = $pid;
	}

	function set_debug($debug=false)
	{
		$this->debug = $debug;
		$this->set_url();
	}

	function set_pri_key($prikey='')
	{
		if(!$prikey)
		{
			$prikey = 'payment/chinapay/merprk_'.$this->pid.'.key';
		}
		if(is_file($this->dir_root.$prikey))
		{
			$this->pri_key = $this->dir_root.$prikey;
		}
		else
		{
			$this->pri_key = '';
		}
	}

	function set_pub_key($pubkey)
	{
		if(!$pubkey)
		{
			$pubkey = 'payment/chinapay/pgpubk.key';
		}
		if(is_file($this->dir_root.$pubkey))
		{
			$this->pub_key = $this->dir_root.$pubkey;
		}
		else
		{
			$this->pub_key = '';
		}
	}


	function set_orderid($id,$time='')
	{
		//当订单ID超过10位数后，不再使用规则
		if(strlen($id)>8)
		{
			$this->orderid = str_pad($id,16,'0',STR_PAD_LEFT);
			return $this->orderid;
		}
		if(!$time)
		{
			$time = time();
		}
		$id = str_pad($id,8,'0',STR_PAD_LEFT);
		$this->orderid = date("Ymd",$time).$id;
		return $this->orderid;
	}

	function set_options($array)
	{
		$array['price'] = padstr($array['price'] * 100,12);
		$this->option = $array;
	}

	function curyid($code='CNY')
	{
		if($code != 'CNY')
		{
			return $code;
		}
		else
		{
			return '156';
		}
	}

	//创建支付按钮
	function action_form($formid='phpok')
	{
		if(!$this->pri_key || !$this->pub_key)
		{
			return false;
		}
		$merid = buildKey($this->pri_key);
		if(!$merid)
		{
			return false;
		}
		//按次序组合订单信息为待签名串
		$curyid = $this->curyid($this->option['currency']);
		$plain = $merid.$this->orderid.$this->option['price'].$curyid.$this->option['date'].'0001'.$this->option['passwd'];
		//生成签名值，必填
		$chkvalue = sign($plain);
		if (!$chkvalue) {
			return false;
		}
		$html = '<form action="'.$this->url_pay.'" method="post" id="'.$formid.'">';
		$html.= '<input type="hidden" name="MerId" value="'.$merid.'" />';
		$html.= '<input type="hidden" name="Version" value="20070129" />';
		$html.= '<input type="hidden" name="OrdId" value="'.$this->orderid.'" />';
		$html.= '<input type="hidden" name="TransAmt" value="'.$this->option['price'].'" />';
		$html.= '<input type="hidden" name="CuryId" value="'.$curyid.'" />';
		$html.= '<input type="hidden" name="TransDate" value="'.$this->option['date'].'" />';
		$html.= '<input type="hidden" name="TransType" value="0001" />';
		$html.= '<input type="hidden" name="BgRetUrl" value="'.$this->option['notify_url'].'" />';
		$html.= '<input type="hidden" name="PageRetUrl" value="'.$this->option['return_url'].'" />';
		$html.= '<input type="hidden" name="GateId" value="" />';
		$html.= '<input type="hidden" name="Priv1" value="'.$this->option['passwd'].'" />';
		$html.= '<input type="hidden" name="ChkValue" value="'.$chkvalue.'" />';
		$html.= '<input type="submit" value=" 提交 " />';
		$html.= '</form>';
		return $html;
	}

	function verify($opts)
	{
		if(!$this->pri_key || !$this->pub_key)
		{
			return false;
		}
		$chk = buildKey($this->pub_key);
		if(!$chk)
		{
			return false;
		}
		$plain = $opts['merid'].$opts['orderno'].$opts['amount'].$opts['currencycode'].$opts['transdate'].$opts['transtype'];
		$plain.= $opts['status'];
		//$flag = verifyTransResponse($opts['merid'],$opts['orderno'],$opts['amount'], $opts['currencycode'], $opts['transdate'], $transtype, $status, $checkvalue);
		$flag = verify($plain,$opts['checkvalue']);
		if(!$flag)
		{
			return false;
		}
		return true;
	}
}
?>