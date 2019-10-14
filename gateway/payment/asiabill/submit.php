<?php
/*****************************************************************************************
	文件： payment/submit.php
	备注： asiabill支付提交页
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月24日 09时56分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class asiabill_submit
{
	public $param;
	public $order;
	public $baseurl;
	public $paydir;
	private $config;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/asiabill/';
		$this->baseurl = $GLOBALS['app']->url;
		if($this->param['param']){
			$this->config = array('mer_no'=>$this->param['param']["mer_no"]);
			$this->config['gateway_no'] = $this->param['param']["gateway_no"];
			$this->config['sign_key'] = $this->param['param']["sign_key"];
			$this->config['action_url'] = $this->param['param']['action'];
			$this->config['utype'] = $this->param['param']['utype'];
		}
		include_once($this->paydir."lib/asiabill.php");
	}

	/**
	 * 执行提交按钮
	**/
	public function submit()
	{
		global $app;
		$htmlbutton  = $this->get_html();
		$htmlbutton .= "\n";
		$htmlbutton .= '<script type="text/javascript">'."\n";
		$htmlbutton .= 'document.getElementById("asiabill_payment").submit();'."\n";
		$htmlbutton .= '</script>'."\n";
		$app->assign('htmlinfo',$htmlbutton);
		$app->view('payment/to_payment');
	}

	private function get_html()
	{
		global $app;
		$obj = new asiabill_payment($this->config);
		$return_url = $app->url('payment','notice','id='.$this->order['id'],'www',true);
		$notify_url = $this->baseurl."gateway/payment/asiabill/notify_url.php";
		$currency = $app->model('currency')->get_one($this->param['currency'],'code');
		$price = price_format_val($this->order['price'],$this->order['currency_id'],$currency['id']);
		$obj->params('sn',$this->order['sn'].'-'.$this->order['id']);
		$obj->params('currency',$currency['code']);
		$obj->params('price',$price);
		$obj->params('return_url',$return_url);
		$obj->params('notify_url',$notify_url);
		$orderinfo = $app->model('order')->get_one($this->order['sn'],'sn');
		if(!$orderinfo){
			$app->error(P_Lang('订单信息不存在'));
		}
		$address = $app->model('order')->address($orderinfo['id'],'billing');
		if(!$address){
			$app->error(P_Lang('无账单地址，不支持此支付方式'));
		}
		$obj->params('firstname',$address['firstname']);
		$obj->params('lastname',$address['lastname']);
		$email = $address['email'] ? $address['email'] : $orderinfo['email'];
		$obj->params('email',$email);
		$mobile = $address['mobile'] ? $address['mobile'] : $orderinfo['mobile'];
		$obj->params('mobile',$mobile);
		$sql = "SELECT * FROM ".$app->db->prefix."world_location WHERE level=2 AND (name_en='".$address['country']."' OR name='".$address['country']."')";
		$tmp = $app->db->get_one($sql);
		if(!$tmp){
			$tmp = array('code'=>'CN');
		}
		$obj->params('country',$tmp['code']);
		$obj->params('city',$address['city']);
		if($address['province']){
			$obj->params('state',$address['province']);
		}
		$obj->params('address',$address['address']);
		$obj->params('zipcode',$address['zipcode']);
		$obj->params('is_mobile',$app->is_mobile());
		$html = $obj->create_html();
		$app->assign('htmlinfo',$html);
		return $html;
	}

	public function select()
	{
		global $app;
		$html = $this->get_html();
		if($app->tpl->check_exists('payment/asiabill/html')){
			return $app->tpl->fetch('payment/asiabill/html');
		}
		if(file_exists($app->dir_gateway.'payment/asiabill/html.html')){
			return $app->tpl->fetch($app->dir_gateway.'payment/asiabill/html.html','abs-file');
		}
		return $html;
	}
}