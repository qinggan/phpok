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
		$this->cart_id = $this->model('cart')->cart_id($this->session->sessid(),$_SESSION['user_id'],$_SESSION['user_rs']['invoice_type'],$_SESSION['user_rs']['invoice_title']);
	}
	
	public function create_f()
	{
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
		$sn = $this->create_sn();
		$allprice = round(($_SESSION['cart']['totalprice']+$_SESSION['cart']['freight_price']),2);
		$array['sn'] = $sn;
		$array['user_id'] = $_SESSION['user_id'];
		$array['addtime'] = $this->time;
		$array['qty'] = $qty;
		$array['price'] = $allprice;
		$array['currency_id'] = $this->site['currency_id'];
		$array['status'] = P_Lang('审核中');
		$array['passwd'] = md5(str_rand(10));
		$array['product_price'] = $_SESSION['cart']['totalprice'];
		$array['freight_price'] = $_SESSION['cart']['freight_price'];
		$array['pay_price'] = $allprice;
		$array['pay_currency'] = $this->site['currency_id'];
		if($_SESSION['cart']['address_id'] == 'email'){
			$array['email'] = $this->get('email');
			if(!$array['email']){
				$this->json(P_Lang('Email地址不能为空'));
			}
			if(!$this->lib('common')->email_check($email)){
				$this->json(P_Lang('Email地址不合法'));
			}
		}else{
			$address = $this->model('user')->address_one($_SESSION['cart']['address_id']);
			if(!$address || $address['user_id'] != $_SESSION['user_id']){
				$this->json(P_Lang('地址信息异常，与用户不匹配'));
			}
			$array['email'] = $address['email'];
			if(!$array['email']){
				$array['email'] = $this->user['email'];
			}
		}
		$array['note'] = $this->get('note');
		$oid = $this->model('order')->save($array);
		if(!$oid){
			$this->json(P_Lang('订单创建失败'));
		}
		foreach($rslist AS $key=>$value){
			$tmp = array('order_id'=>$oid,'tid'=>$value['tid']);
			$tmp['title'] = $value['title'];
			$tmp['price'] = price_format_val($value['price'],$value['currency_id'],$this->site['currency_id']);
			$tmp['qty'] = $value['qty'];
			$tmp['weight'] = $value['weight'];
			$tmp['volume'] = $value['volume'];
			$tmp['thumb'] = $value['thumb'] ? $value['thumb']['filename'] : 0;
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
			$this->model('order')->save_invoice($tmp);
		}
		$this->model('cart')->delete($this->cart_id);
		unset($_SESSION['cart']);
		$this->email_notice($array);
		$rs = array('sn'=>$sn,'passwd'=>$array['passwd'],'id'=>$oid);
		$this->json($rs,true);
	}

	private function email_notice($order)
	{
		if(!$order || !is_array($order) || !$this->site['biz_etpl']){
			return false;
		}
		$tpl_rs = $this->model('email')->get_identifier($this->site['biz_etpl'],$this->site['id']);
		if(!$tpl_rs){
			return false;
		}
		if(!$this->site['email_server'] || !$this->site['email_account'] || !$this->site['email_pass']){
			return false;
		}
		$email = $this->model('admin')->get_mail(true);
		if(!$email){
			return false;
		}
		$this->assign('order',$order);
		$title = $this->fetch($tpl_rs["title"],"content");
		$content = $this->fetch($tpl_rs["content"],"content");
		$this->lib('email')->send_admin($title,$content,$email);
		return true;
	}

	//送货地址
	private function shipping()
	{
		$shipping['fullname'] = $this->get('s-fullname');
		if(!$shipping['fullname']){
			$this->json(P_Lang('姓名不能为空'));
		}
		$shipping['gender'] = $this->get('s-gender','int');
		$shipping['country'] = $this->get('s-country');
		if(!$shipping['country']){
			$this->json(P_Lang('国家不能为空'));
		}
		$shipping['province'] = $this->get('s-province');
		if(!$shipping['province']){
			$this->json(P_Lang('请选择您所在省份信息'));
		}
		$shipping['city'] = $this->get('s-city');
		$shipping['county'] = $this->get('s-county');
		$shipping['address'] = $this->get('s-address');
		if(!$shipping['address']){
			$this->json(P_Lang('请填写送货地址信息，要求尽可能详实'));
		}
		$shipping['zipcode'] = $this->get('s-zipcode');
		if(!$shipping['zipcode']){
			$this->json(P_Lang('邮编不能为空'));
		}
		$shipping['tel'] = $this->get('s-tel');
		$shipping['mobile'] = $this->get('s-mobile');
		if(!$shipping['tel'] && !$shipping['mobile']){
			$this->json(P_Lang('至少要求填写一个联系方式：电话或手机'));
		}
		if($shipping['tel'] && !$this->isTel($shipping['tel'],'tel')){
			$this->json(P_Lang('电话填写不正确，请填写规范，如：0755-123456789'));
		}
		if($shipping['mobile'] && !$this->isTel($shipping['mobile'],'mobile')){
			$this->json(P_Lang('手机填写不正确，请填写规范，如：158185xxxxx'));
		}
		$shipping['email'] = $this->get('s-email');
		if(!$shipping['email']){
			$this->json(P_Lang('Email不能为空，系统会发送订单状态到这个邮箱上'));
		}
		if(!preg_match('/^[A-Za-z0-9-_.+%]+@[A-Za-z0-9-.]+\.[A-Za-z]{2,4}$/',$shipping['email'])){
			$this->json(P_Lang('Email不符合要求，请正确填写'));
		}
		return $shipping;
	}

	//存储Shipping信息
	private function save_shipping($shipping)
	{
		if(!$shipping){
			return false;
		}
		//存储到地址库中
		if($_SESSION['user_id']){
			$id = $this->get('s-id','int');
			if($id){
				$rs = $this->model('address')->get_one($id);
				if(!$rs || ($rs && $rs['user_id'] != $_SESSION['user_id']) || ($rs && $rs['type_id'] != 'shipping')){
					$this->json(P_Lang('登记地址信息出错'));
				}
				$this->model('address')->save($shipping,$id);
			}else{
				$shipping['type_id'] = 'shipping';
				$shipping['user_id'] = $_SESSION['user_id'];
				$id = $this->model('address')->save($shipping);
				if(!$id){
					$this->json('登记地址信息出错');
				}
			}
		}else{
			$_SESSION['address']['shipping'] = $shipping;
		}
		return $shipping;
	}

	//账单地址
	private function billing()
	{
		if(!$this->site['biz_billing']){
			return false;
		}
		//姓名不能为空
		$billing['fullname'] = $this->get('b-fullname');
		if(!$billing['fullname']){
			$this->json(P_Lang('姓名不能为空'));
		}
		$billing['gender'] = $this->get('b-gender','int');
		$billing['country'] = $this->get('b-country');
		if(!$billing['country']){
			$this->json(P_Lang('国家不能为空'));
		}
		$billing['province'] = $this->get('b-province');
		if(!$billing['province']){
			$this->json(P_Lang('请选择您所在省份信息'));
		}
		$billing['city'] = $this->get('b-city');
		$billing['county'] = $this->get('b-county');
		$billing['address'] = $this->get('b-address');
		if(!$billing['address']){
			$this->json('请填写账单地址信息，要求尽可能详实');
		}
		$billing['zipcode'] = $this->get('b-zipcode');
		if(!$billing['zipcode']){
			$this->json(P_Lang('邮编不能为空'));
		}
		$billing['tel'] = $this->get('b-tel');
		$billing['mobile'] = $this->get('b-mobile');
		if(!$billing['tel'] && !$billing['mobile']){
			$this->json(P_Lang('至少要求填写一个联系方式：电话或手机'));
		}
		if($billing['tel'] && !$this->isTel($billing['tel'],'tel')){
			$this->json(P_Lang('电话填写不正确，请填写规范，如：0755-123456789'));
		}
		if($billing['mobile'] && !$this->isTel($billing['mobile'],'mobile')){
			$this->json(P_Lang('手机填写不正确，请填写规范，如：158185xxxxx'));
		}
		$billing['email'] = $this->get('b-email');
		if(!$billing['email']){
			$this->json(P_Lang('Email不能为空，系统会发送订单状态到这个邮箱上'));
		}
		if(!preg_match('/^[A-Za-z0-9-_.+%]+@[A-Za-z0-9-.]+\.[A-Za-z]{2,4}$/',$billing['email'])){
			$this->json(P_Lang('Email不符合要求，请正确填写'));
		}
		return $billing;
	}

	private function save_billing($billing)
	{
		if(!$billing){
			return false;
		}
		if($_SESSION['user_id']){
			$id = $this->get('b-id','int');
			if($id){
				$rs = $this->model('address')->get_one($id);
				if(!$rs || ($rs && $rs['user_id'] != $_SESSION['user_id']) || ($rs && $rs['type_id'] != 'billing')){
					$this->json('登记地址信息出错');
				}
				$this->model('address')->save($billing,$id);
			}else{
				$billing['type_id'] = 'billing';
				$billing['user_id'] = $_SESSION['user_id'];
				$id = $this->model('address')->save($billing);
				if(!$id){
					$this->json('登记地址信息出错');
				}
			}
		}else{
			$_SESSION['address']['billing'] = $billing;
		}
		return $billing;
	}

	//是否电话判断
	private function isTel($tel,$type='')
	{
		$regxArr = array(
			'mobile'  =>  '/^(\+?86-?)?(18|15|13)[0-9]{9}$/',
			'tel' =>  '/^(\+?86-?)?(010|02\d{1}|0[3-9]\d{2})-\d{7,9}(-\d+)?$/',
			'400' =>  '/^400(-\d{3,4}){2}$/',
		);
		if($type && isset($regxArr[$type])){
			return preg_match($regxArr[$type], $tel) ? true:false;
		}
		foreach($regxArr as $regx){
			if(preg_match($regx, $tel )){
				return true;
			}
		}
		return false;
	}

	private function create_sn()
	{
		$sntype = $this->site['biz_sn'];
		if(!$sntype) $sntype = 'year-month-date-number';
		$sn = '';
		$list = explode('-',$sntype);
		foreach($list AS $key=>$value){
			if($value == 'year') $sn.= date("Y",$this->time);
			if($value == 'month') $sn.= date("m",$this->time);
			if($value == 'date') $sn.= date("d",$this->time);
			if($value == 'hour') $sn.= date('H',$this->time);
			if($value == 'minute' || $value == 'minutes') $sn.= date("i",$this->time);
			if($value == 'second' || $value == 'seconds') $sn.= date("s",$this->time);
			if($value == 'rand' || $value == 'rands') $sn .= rand(10,99);
			if($value == 'time' || $value == 'times') $sn .= $this->time;
			if($value == 'number'){
				$condition = "FROM_UNIXTIME(addtime,'%Y-%m-%d')='".date("Y-m-d",$this->time)."'";
				$total = $this->model('order')->get_count($condition);
				if(!$total) $total = '0';
				$total++;
				$sn .= str_pad($total,3,'0',STR_PAD_LEFT);
			}
			if($value == 'id'){
				$maxid = $this->model('order')->maxid();
				$sn .= str_pad($maxid,5,'0',STR_PAD_LEFT);
			}
			//包含会员信息
			if($value == 'user'){
				$sn .= $_SESSION['user_id'] ? 'U'.str_pad($_SESSION['user_id'],5,'0',STR_PAD_LEFT) : 'G';
			}
			if(substr($value,0,6) == 'prefix'){
				$sn .= str_replace(array('prefix','[',']'),'',$value);
			}
		}
		return $sn;
	}

}

?>