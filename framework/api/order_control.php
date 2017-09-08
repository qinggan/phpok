<?php
/***********************************************************
	Filename: {phpok}/api/order_control.php
	Note	: 创建订单操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月8日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->cart_id = $this->model('cart')->cart_id($this->session->sessid(),$_SESSION['user_id']);
	}
	
	public function create_f()
	{
		$user = array();
		if($this->session->val('user_id')){
			$user = $this->model('user')->get_one($this->session->val('user_id'));
		}
		$rslist = $this->model('cart')->get_all($this->cart_id);
		if(!$rslist){
			$this->json(P_Lang("您的购物车里没有产品"));
		}
		$totalprice = $qty = $weight = $volume = 0;
		foreach($rslist AS $key=>$value){
			$weight += $value['weight'] * $value['qty'];
			$totalprice += price_format_val($value['price'] * $value['qty'],$value['currency_id'],$this->site['currency_id']);
			$qty += $value['qty'];
		}
		$sn = $this->model('order')->create_sn();
		$allprice = round(($this->session->val('cart.totalprice')+$this->session->val('cart.freight_price')),2);
		$main = array('sn'=>$sn);
		$main['user_id'] = $user['id'];
		$main['addtime'] = $this->time;
		$main['price'] = $allprice;
		$main['currency_id'] = $this->site['currency_id'];
		$main['status'] = 'create';
		$main['passwd'] = md5(str_rand(10));
		if($_SESSION['user_id']){
			if($_SESSION['cart']['address_id'] == 'email'){
				$main['email'] = $this->get('email');
				if(!$main['email']){
					$this->json(P_Lang('Email地址不能为空'));
				}
				if(!$this->lib('common')->email_check($main['email'])){
					$this->json(P_Lang('Email地址不合法'));
				}
			}else{
				$address = $this->model('user')->address_one($_SESSION['cart']['address_id']);
				if(!$address || $address['user_id'] != $user['id']){
					$this->json(P_Lang('请完善您的收货地址信息'));
				}
				$main['email'] = $address['email'];
				if(!$main['email']){
					$main['email'] = $user['email'];
				}
			}
		}else{
			$tmp_address = $this->form_address();
			if(!$tmp_address['status']){
				$this->json($tmp_address['info']);
			}
			$address = $tmp_address['info'];
			$main['email'] = $address['email'];
		}
		$main['note'] = $this->get('note');
		$oid = $this->model('order')->save($main);
		if(!$oid){
			$this->json(P_Lang('订单创建失败'));
		}
		foreach($rslist AS $key=>$value){
			$tmp = array('order_id'=>$oid,'tid'=>$value['tid']);
			$tmp['title'] = $value['title'];
			$tmp['price'] = price_format_val($value['price'],$this->site['currency_id']);
			$tmp['qty'] = $value['qty'];
			$tmp['weight'] = $value['weight'];
			$tmp['volume'] = $value['volume'];
			$tmp['unit'] = $value['unit'];
			$tmp['thumb'] = $value['thumb'] ? $value['thumb'] : '';
			if($value['ext'] && $value['attrlist']){
				$tmpext = array();
				$ext = explode(",",$value['ext']);
				foreach($value['attrlist'] as $k=>$v){
					foreach($v['rslist'] as $kk=>$vv){
						if(in_array($vv['id'],$ext)){
							$value['_attrlist'][] = array();
							$tmp1 = array('title'=>$v['title'],'content'=>$vv['title'],'price'=>$vv['price'],'weight'=>$vv['weight']);
							$tmp1['volume'] = $vv['volume'];
							$tmpext[] = $tmp1;
						}
					}
				}
				if($tmpext && count($tmpext)>0){
					$tmp['ext'] = serialize($tmpext);
				}
			}
			$this->model('order')->save_product($tmp);
		}
		if($address){
			$tmp = array('order_id'=>$oid);
			$tmp['country'] = $address['country'];
			$tmp['province'] = $address['province'];
			$tmp['city'] = $address['city'];
			$tmp['county'] = $address['county'];
			$tmp['address'] = $address['address'];
			$tmp['mobile'] = $address['mobile'];
			$tmp['tel'] = $address['tel'];
			$tmp['email'] = $address['email'];
			$tmp['fullname'] = $address['fullname'];
			$this->model('order')->save_address($tmp);
		}
		if($_SESSION['cart']['invoice_id'] != 'no'){
			$invoice = $this->model('user')->invoice_one($_SESSION['cart']['invoice_id']);
			$tmp = array('order_id'=>$oid,'type'=>$invoice['type'],'title'=>$invoice['title'],'content'=>$invoice['content']);
			$tmp['note'] = $invoice['note'];
			$this->model('order')->save_invoice($tmp);
		}
		$pricelist = $this->model('site')->price_status_all();
		if($pricelist){
			foreach($pricelist as $key=>$value){
				$tmp_price = '0.00';
				if($key == 'product'){
					$tmp_price = $_SESSION['cart']['totalprice'];
				}elseif($key == 'shipping'){
					$tmp_price = $_SESSION['cart']['freight_price'];
				}
				$tmp = array('order_id'=>$oid,'code'=>$key,'price'=>$tmp_price);
				$this->model('order')->save_order_price($tmp);
			}
		}
		//删除购物车信息
		$this->model('cart')->delete($this->cart_id);
		unset($_SESSION['cart']);
		//填写订单日志
		$note = P_Lang('订单创建成功，订单编号：{sn}',array('sn'=>$sn));
		$log = array('order_id'=>$oid,'addtime'=>$this->time,'who'=>$user['user'],'note'=>$note);
		$this->model('order')->log_save($log);
		//增加订单通知
		$param = 'id='.$oid."&status=create";
		$this->model('task')->add_once('order',$param);
		$rs = array('sn'=>$sn,'passwd'=>$main['passwd'],'id'=>$oid);
		$this->json($rs,true);
	}

	/**
	 * 获取表单地址
	 * @返回 数组
	**/
	private function form_address()
	{
		$array = array();
		$country = $this->get('country');
		if(!$country){
			$country = '中国';
		}
		$array['country'] = $country;
		$array['province'] = $this->get('pca_p');
		$array['city'] = $this->get('pca_c');
		$array['county'] = $this->get('pca_a');
		$array['fullname'] = $this->get('fullname');
		if(!$array['fullname']){
			return array('status'=>false,'info'=>P_Lang('收件人姓名不能为空'));
		}
		$array['address'] = $this->get('address');
		$array['mobile'] = $this->get('mobile');
		$array['tel'] = $this->get('tel');
		if(!$array['mobile'] && !$array['tel']){
			return array('status'=>false,'info'=>P_Lang('手机或固定电话必须有填写一项'));
		}
		if($array['mobile']){
			if(!$this->lib('common')->tel_check($array['mobile'],'mobile')){
				return array('status'=>false,'info'=>P_Lang('手机号格式不对'));
			}
		}
		if($array['tel']){
			if(!$this->lib('common')->tel_check($array['tel'],'tel')){
				return array('status'=>false,'info'=>P_Lang('电话格式不对'));
			}
		}
		$array['email'] = $this->get('email');
		if($array['email']){
			if(!$this->lib('common')->email_check($array['email'])){
				return array('status'=>false,'info'=>P_Lang('邮箱格式不对'));
			}
		}
		return array('status'=>true,'info'=>$array);
	}

	public function info_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$sn = $this->get('sn');
			if(!$sn){
				$this->json(P_Lang('未指定订单ID或SN号'));
			}
			$rs = $this->model('order')->get_one_from_sn($sn);
		}else{
			$rs = $this->model('order')->get_one($id);
		}
		if(!$rs){
			$this->json(P_Lang('订单信息不存在'));
		}
		if($_SESSION['user_id']){
			if($rs['user_id'] != $_SESSION['user_id']){
				$this->json(P_Lang('您没有权限获取此订单信息'));
			}
		}else{
			$passwd = $this->get('passwd');
			if(!$passwd){
				$this->json(P_Lang('查询密码不能留空'));
			}
			if($passwd != $rs['passwd']){
				$this->json(P_Lang('密码不正确'));
			}
		}
		$paycheck = $this->model('order')->check_payment_is_end($rs['id']);
		if($paycheck){
			$rs['pay_end'] = true;
		}else{
			$rs['pay_end'] = false;
		}
		$this->json($rs,true);
	}
}