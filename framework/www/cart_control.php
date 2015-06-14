<?php
/***********************************************************
	Filename: {phpok}/www/cart_control.php
	Note	: 购物车
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年06月21日 15时21分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cart_control extends phpok_control
{
	public $cart_id = 0;
	function __construct()
	{
		parent::control();
		//取得当前的购物车ID
		$this->cart_id = $this->model('cart')->cart_id($this->session->sessid(),$_SESSION['user_id']);
	}

	public function index_f()
	{
		//取得购物车产品列表
		$rslist = $this->model('cart')->get_all($this->cart_id);
		if(!$rslist){
			error(P_Lang('您的购物车里没有任何产品'),$this->url,"notice",5);
		}
		$this->assign("rslist",$rslist);
		$totalprice = 0;
		foreach($rslist AS $key=>$value){
			$totalprice += price_format_val($value['price'] * $value['qty'],$value['currency_id'],$this->site['currency_id']);
		}
		$price = price_format($totalprice,$this->site['currency_id']);
		$this->assign('price',$price);
		$this->view("cart_index");
	}

	public function checkout_f()
	{
		$rslist = $this->model('cart')->get_all($this->cart_id);
		if(!$rslist){
			error(P_Lang('您的购物车里没有任何产品'),$this->url,"notice",5);
		}
		//生成随机码，以确定客户通过正确途径下单
		$_SESSION['order_spam'] = str_rand(10);
		$totalprice = 0;
		foreach($rslist AS $key=>$value){
			$totalprice += price_format_val($value['price'] * $value['qty'],$value['currency_id'],$this->site['currency_id']);
		}
		$price = price_format($totalprice,$this->site['currency_id']);
		$this->assign('price',$price);
		$this->assign("rslist",$rslist);
		$shipping = $billing = array();
		if($_SESSION['user_id']){
			$shipping_list = $this->model('address')->address_list($_SESSION['user_id'],'shipping');
			if($shipping_list){
				foreach($shipping_list AS $key=>$value){
					if($value['is_default']) $shipping = $value;
				}
				if(!$shipping){
					reset($shipping_list);
					$shipping = current($shipping_list);
				}
			}
			if($this->site['biz_billing']){
				$billing_list = $this->model('address')->address_list($_SESSION['user_id'],'billing');
				if($billing_list){
					foreach($billing_list AS $key=>$value){
						if($value['is_default']) $billing = $value;
					}
					if(!$billing){
						reset($billing_list);
						$billing = current($billing_list);
					}
				}
			}
		}else{
			if($_SESSION['address']['shipping']) $shipping = $_SESSION['address']['shipping'];
			if($_SESSION['address']['billing']) $billing = $_SESSION['address']['billing'];
		}
		$this->assign('shipping',$shipping);
		$this->assign('billing',$billing);
		$this->view("cart_checkout");
	}
}
?>