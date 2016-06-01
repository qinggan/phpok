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
	    	$this->view("cart_tip");
		}
		foreach($rslist as $key=>$value){
			if($value['ext'] && $value['attrlist']){
				$ext = explode(",",$value['ext']);
				foreach($value['attrlist'] as $k=>$v){
					foreach($v['rslist'] as $kk=>$vv){
						if(in_array($vv['id'],$ext)){
							$value['_attrlist'][] = array('title'=>$v['title'],'content'=>$vv['title']);
						}
					}
				}
			}
			$rslist[$key] = $value;
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
		if(!$_SESSION['user_id']){
			error(P_Lang('请先注册或登录'),$this->url('login','','_back='.rawurlencode($this->url('cart','checkout'))));
		}
		$user = $this->model('user')->get_one($_SESSION['user_id']);
		$rslist = $this->model('cart')->get_all($this->cart_id);
		if(!$rslist){
			error(P_Lang('您的购物车里没有任何产品'),$this->url,"notice",5);
		}
		foreach($rslist as $key=>$value){
			if($value['ext'] && $value['attrlist']){
				$ext = explode(",",$value['ext']);
				foreach($value['attrlist'] as $k=>$v){
					foreach($v['rslist'] as $kk=>$vv){
						if(in_array($vv['id'],$ext)){
							$value['_attrlist'][] = array('title'=>$v['title'],'content'=>$vv['title']);
						}
					}
				}
			}
			$rslist[$key] = $value;
		}
		$totalprice = 0;
		foreach($rslist AS $key=>$value){
			$totalprice += price_format_val($value['price'] * $value['qty'],$value['currency_id'],$this->site['currency_id']);
		}
		$price = price_format($totalprice,$this->site['currency_id']);
		$_SESSION['cart']['totalprice'] = $totalprice;
		$this->assign('price',$price);
		$this->assign("rslist",$rslist);
		//收件人信息处理模块
		$this->_address();
		
		//发票管理模块
		$this->_invoice();
		$pricelist = $this->model('site')->price_status_all(true);
		if($pricelist){
			foreach($pricelist as $key=>$value){
				if(!$value['status']){
					unset($pricelist[$key]);
				}
			}
		}
		$this->assign('pricelist',$pricelist);
		$this->view("cart_checkout");
	}

	public function address_f()
	{
		$this->_address();
		$this->view("cart_address");
	}

	public function invoice_f()
	{
		$this->_invoice();
		$this->view("cart_invoice");
	}

	private function _invoice()
	{
		$ilist = $this->model('user')->invoice($_SESSION['user_id']);
		$invoice = array('id'=>'no','title'=>P_Lang('不开发票'),'content'=>'');
		$ilist[] = $invoice;
		$default_invoice = $first = $selected = false;
		if(is_array($ilist)){
			foreach($ilist as $key=>$value){
				if($key<1){
					$first = $value;
				}
				if($value['is_default']){
					$default_invoice = $value;
				}
				if($_SESSION['cart'] && $_SESSION['cart']['invoice_id'] && $_SESSION['cart']['invoice_id'] == $value['id']){
					$selected = $value;
				}
			}
		}
		if(!$default_invoice){
			$default_invoice = $first;
		}
		if($selected){
			$default_invoice = $selected;
		}
		unset($first,$selected);
		$_SESSION['cart']['invoice_id'] = $default_invoice['id'];
		$this->assign('invoice',$default_invoice);
		$this->assign('invoicelist',$ilist);
		$this->assign('totalinvoice',count($ilist));
	}

	private function _address()
	{
		$addresslist = $this->model('user')->address($_SESSION['user_id']);
		$v_address = array('id'=>'email','fullname'=>P_Lang('邮箱'),'address'=>'','email'=>$user['email']);
		if($_SESSION['cart']['address_email']){
			$v_address['email'] = $_SESSION['cart']['address_email'];
		}
		$addresslist[] = $v_address;
		$default_address = $first = $selected = false;
		if(is_array($addresslist)){
			foreach($addresslist as $key=>$value){
				if($key<1){
					$first = $value;
				}
				if($value['is_default']){
					$default_address = $value;
				}
				if($_SESSION['cart'] && $_SESSION['cart']['address_id'] && $_SESSION['cart']['address_id'] == $value['id']){
					$selected = $value;
				}
			}
		}
		if(!$default_address){
			$default_address = $first;
		}
		if($selected){
			$default_address = $selected;
		}
		unset($first,$selected);
		$_SESSION['cart']['address_id'] = $default_address['id'];
		$this->assign('address',$default_address);
		$this->assign('addresslist',$addresslist);
	}

	//计算运费
	public function freight_f()
	{
		if(!$_SESSION['cart']){
			$this->json(P_Lang('您的购物车里没有任何产品'));
		}
		unset($_SESSION['cart']['freight_price']);
		$price_zero = price_format('0.00',$this->site['currency_id']);
		if($_SESSION['cart']['address_id'] == 'email'){
			$this->json($price_zero,true);
		}
		$rslist = $this->model('cart')->get_all($this->cart_id);
		if(!$rslist){
			$this->json(P_Lang('您的购物车里没有任何产品'));
		}
		$weight = $volume = $pid = $total = 0;
		foreach($rslist as $key=>$value){
			$pid = $value['project_id'];
			$weight += floatval($value['weight'] * $value['qty']);
			$volume += floatval($value['volume'] * $value['qty']);
			$total += $value['qty'];
			if($value['ext'] && $value['attrlist']){
				$ext = explode(",",$value['ext']);
				foreach($value['attrlist'] as $k=>$v){
					foreach($v['rslist'] as $kk=>$vv){
						if(in_array($vv['id'],$ext)){
							$weight += floatval($vv['weight']);
							$volume += floatval($vv['volume']);
						}
					}
				}
			}
		}
		//读取项目信息
		$project = $this->model('project')->get_one($pid,false);
		if(!$project || !$project['freight']){
			$this->json($price_zero,true);
		}
		$freight = $this->model('freight')->get_one($project['freight']);
		if(!$freight){
			$this->json($price_zero,true);
		}
		$param_val = false;
		if($freight['type'] == 'weight'){
			$param_val = $weight;
		}elseif($freight['type'] == 'volume'){
			$param_val = $volume;
		}elseif($freight['type'] == 'number'){
			$param_val = $total;
		}elseif($freight['type'] == 'fixed'){
			$param_val = 'fixed';
		}
		$address = $this->model('user')->address_one($_SESSION['cart']['address_id']);
		if(!$address || $address['user_id'] != $_SESSION['user_id']){
			$this->json($price_zero,true);
		}
		$zone_id = $this->model('freight')->zone_id($freight['id'],$address['province'],$address['city']);
		if(!$zone_id){
			$this->json($price_zero,true);
		}
		$val = $this->model('freight')->price_one($zone_id,$param_val);
		if($val){
			if(strpos($val,'N') !== false){
				$val = str_replace("N",$param_val,$val);
				eval("\$val = $val;");
			}
			$_SESSION['cart']['freight_price'] = $val;
			$this->json(price_format($val,$this->site['currency_id']),true);
		}
		$this->json($price_zero,true);
	}

	public function all_price_f()
	{
		$price = $_SESSION['cart']['totalprice'];
		if($_SESSION['cart']['freight_price']){
			$price += $_SESSION['cart']['freight_price'];
		}
		$this->json(price_format($price,$this->site['currency_id']),true);
	}

	public function address_selected_f()
	{
		if(!$_SESSION['user_id']){
			$this->json(P_Lang('非会员没有此功能权限'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->json(P_Lang('未指定地址ID'));
		}
		if($id != 'email'){
			$rs = $this->model('user')->address_one($id);
			if($rs['user_id'] != $_SESSION['user_id']){
				$this->json(P_Lang('地址信息与会员不匹配'));
			}
		}else{
			//如果有传Email地址过来，则保存相应的email信息
			$email = $this->get('email');
			if($email && $this->lib('common')->email_check($email)){
				$_SESSION['cart']['address_email'] = $email;
			}
		}
		$_SESSION['cart']['address_id'] = $id;
		$this->json(true);
	}

	public function invoice_selected_f()
	{
		if(!$_SESSION['user_id']){
			$this->json(P_Lang('非会员没有此功能权限'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->json(P_Lang('未指定地址ID'));
		}
		if($id != 'no'){
			$rs = $this->model('user')->invoice_one($id);
			if($rs['user_id'] != $_SESSION['user_id']){
				$this->json(P_Lang('信息与会员不匹配'));
			}
		}
		$_SESSION['cart']['invoice_id'] = $id;
		$this->json(true);
	}
}
?>