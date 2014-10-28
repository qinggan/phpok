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
	function __construct()
	{
		parent::control();
		//取得当前的购物车ID
		$this->cart_id = $this->model('cart')->cart_id($this->session->sessid(),$_SESSION['user_id']);
	}
	
	function create_f()
	{
		$rslist = $this->model('cart')->get_all($this->cart_id);
		if(!$rslist)
		{
			$this->json(P_Lang("您的购物车里没有任何产品！"));
		}
		$totalprice = 0;
		$qty = 0;
		foreach($rslist AS $key=>$value)
		{
			$totalprice += price_format_val($value['price'] * $value['qty'],$value['currency_id'],$this->site['currency_id']);
			$qty += $value['qty'];
		}
		//订单总价格
		//$price = price_format_val($totalprice,$this->site['currency_id'],$this->site['currency_id']);
		//填写送货地址，本系统判断：姓名，国家，省份，地址信息，电话或手机，Email等填写是否完整
		$shipping = $this->shipping();
		//判断是否有账单地址
		$billing = $this->billing();
		//if(!$payment) $this->json('请选择一种付款方式');
		//创建订单编号
		$sn = $this->create_sn();
		//存储订单信息
		$array['sn'] = $sn;
		$array['user_id'] = $_SESSION['user_id'];
		$array['addtime'] = $this->time;
		$array['qty'] = $qty;
		$array['price'] = $totalprice;
		$array['currency_id'] = $this->site['currency_id'];
		$array['status'] = '审核中';
		$array['passwd'] = md5(str_rand(10));
		$oid = $this->model('order')->save($array);
		if(!$oid)
		{
			$this->json('订单创建失败，请检查');
		}
		//存储订单产品信息
		foreach($rslist AS $key=>$value)
		{
			$tmp = array('order_id'=>$oid,'tid'=>$value['tid']);
			$tmp['title'] = $value['title'];
			$tmp['price'] = price_format_val($value['price'],$value['currency_id'],$this->site['currency_id']);
			$tmp['qty'] = $value['qty'];
			$tmp['thumb'] = $value['thumb'] ? $value['thumb']['id'] : 0;
			//产品扩展属性
			$tmp['ext'] = $value['ext'] ? serialize(unserialize($value['ext'])) : '';
			//存储产品信息
			$this->model('order')->save_product($tmp);
		}
		//存储送货地址信息
		if($shipping)
		{
			$tmp = array('order_id'=>$oid,'type_id'=>'shipping');
			$tmp['country'] = $shipping['country'];
			$tmp['province'] = $shipping['province'];
			$tmp['city'] = $shipping['city'];
			$tmp['county'] = $shipping['county'];
			$tmp['address'] = $shipping['address'];
			$tmp['zipcode'] = $shipping['zipcode'];
			$tmp['mobile'] = $shipping['mobile'];
			$tmp['tel'] = $shipping['tel'];
			$tmp['email'] = $shipping['email'];
			$tmp['fullname'] = $shipping['fullname'];
			$tmp['gender'] = $shipping['gender'];
			$this->model('order')->save_address($tmp);
		}
		if($billing)
		{
			$tmp = array('order_id'=>$oid,'type_id'=>'billing');
			$tmp['country'] = $billing['country'];
			$tmp['province'] = $billing['province'];
			$tmp['city'] = $billing['city'];
			$tmp['county'] = $billing['county'];
			$tmp['address'] = $billing['address'];
			$tmp['zipcode'] = $billing['zipcode'];
			$tmp['mobile'] = $billing['mobile'];
			$tmp['tel'] = $billing['tel'];
			$tmp['email'] = $billing['email'];
			$tmp['fullname'] = $billing['fullname'];
			$tmp['gender'] = $billing['gender'];
			$this->model('order')->save_address($tmp);
		}
		//清除购物车信息
		$this->model('cart')->delete($this->cart_id);
		//存储Shipping地址和Billing地址
		$this->save_shipping($shipping);
		$this->save_billing($billing);
		//判断是否有邮件通知
		$this->email_notice($array);
		//返回订单信息
		$rs = array('sn'=>$sn,'passwd'=>$array['passwd']);
		$this->json($rs,true);
	}

	function email_notice($order)
	{
		if(!$order || !is_array($order) || !$this->site['biz_etpl'])
		{
			return false;
		}
		$tpl_rs = $this->model('email')->get_identifier($this->site['biz_etpl'],$this->site['id']);
		if(!$tpl_rs)
		{
			phpok_log('未配置邮件模板，订单：'.$order['sn'].' 不能通知管理员');
			return false;
		}
		//未配置好邮件通知
		if(!$this->site['email_server'] || !$this->site['email_account'] || !$this->site['email_pass'])
		{
			return false;
		}
		//检测是否有系统管理员
		$email = $this->model('admin')->get_mail(true);
		if(!$email)
		{
			phpok_log('无系统管理员邮箱，订单：'.$order['sn'].' 不能通知管理员');
			return false;
		}
		//发送邮件
		$this->assign('order',$order);
		$title = $this->fetch($tpl_rs["title"],"content");
		$content = $this->fetch($tpl_rs["content"],"content");
		$this->lib('email')->send_admin($title,$content,$email);
		return true;
	}

	//送货地址
	function shipping()
	{
		//姓名不能为空
		$shipping['fullname'] = $this->get('s-fullname');
		if(!$shipping['fullname']) $this->json('姓名不能为空');
		//取得性别
		$shipping['gender'] = $this->get('s-gender','int');
		//国家不能为空
		$shipping['country'] = $this->get('s-country');
		if(!$shipping['country']) $this->json('国家不能为空');
		//省份信息不能为空
		$shipping['province'] = $this->get('s-province');
		if(!$shipping['province']) $this->json('请选择您所在省份信息');
		//检测市，县，区
		$shipping['city'] = $this->get('s-city');
		if($shipping['city'] == '市辖区' || $shipping['city'] == '市辖县') $shipping['city'] = '('.$shipping['city'].')';
		$shipping['county'] = $this->get('s-county');
		if($shipping['county'] == '市辖区' || $shipping['county'] == '市辖县') $shipping['county'] = '';
		//检测地址是否有填写
		$shipping['address'] = $this->get('s-address');
		if(!$shipping['address']) $this->json('请填写送货地址信息，要求尽可能详实');
		//邮编号
		$shipping['zipcode'] = $this->get('s-zipcode');
		if(!$shipping['zipcode']) $this->json('邮编不能为空');
		//检测电话或手机
		$shipping['tel'] = $this->get('s-tel');
		$shipping['mobile'] = $this->get('s-mobile');
		if(!$shipping['tel'] && !$shipping['mobile']) $this->json('至少要求填写一个联系方式：电话或手机');
		if($shipping['tel'])
		{
			if(!$this->isTel($shipping['tel'],'tel')) $this->json('电话填写不正确，请填写规范，如：0755-123456789');
		}
		if($shipping['mobile'])
		{
			if(!$this->isTel($shipping['mobile'],'mobile')) $this->json('手机填写不正确，请填写规范，如：158185xxxxx');
		}
		//验证邮箱
		$shipping['email'] = $this->get('s-email');
		if(!$shipping['email']) $this->json('Email不能为空，系统会发送订单状态到这个邮箱上');
		if(!preg_match('/^[A-Za-z0-9-_.+%]+@[A-Za-z0-9-.]+\.[A-Za-z]{2,4}$/',$shipping['email']))
		{
			$this->json('Email不符合要求，请填写正确的Email');
		}
		return $shipping;
	}

	//存储Shipping信息
	function save_shipping($shipping)
	{
		if(!$shipping) return false;
		//存储到地址库中
		if($_SESSION['user_id'])
		{
			$id = $this->get('s-id','int');
			if($id)
			{
				$rs = $this->model('address')->get_one($id);
				if(!$rs) $this->json('登记地址信息异常，错误编号：ADDRESS1002');
				if($rs['user_id'] != $_SESSION['user_id']) $this->json('登记地址信息异常，错误编号：ADDRESS1001');
				if($rs['type_id'] != 'shipping') $this->json('登记地址信息异常，错误编号：ADDRESS1003');
				$this->model('address')->save($shipping,$id);
			}
			else
			{
				$shipping['type_id'] = 'shipping';
				$shipping['user_id'] = $_SESSION['user_id'];
				$id = $this->model('address')->save($shipping);
				if(!$id) $this->json('登记地址信息异常，错误编号：ADDRESS1004');
			}
		}
		else
		{
			$_SESSION['address']['shipping'] = $shipping;
		}
		return $shipping;
	}

	//账单地址
	function billing()
	{
		//判断系统是否启用了账单地址
		if(!$this->site['biz_billing']) return false;
		//姓名不能为空
		$billing['fullname'] = $this->get('b-fullname');
		if(!$billing['fullname']) $this->json('账单姓名不能为空');
		//取得性别
		$billing['gender'] = $this->get('b-gender','int');
		//国家不能为空
		$billing['country'] = $this->get('b-country');
		if(!$billing['country']) $this->json('国家不能为空');
		//省份信息不能为空
		$billing['province'] = $this->get('b-province');
		if(!$billing['province']) $this->json('请选择您所在省份信息');
		//检测市，县，区
		$billing['city'] = $this->get('b-city');
		if($billing['city'] == '市辖区' || $billing['city'] == '市辖县') $billing['city'] = '('.$billing['city'].')';
		$billing['county'] = $this->get('b-county');
		if($billing['county'] == '市辖区' || $billing['county'] == '市辖县') $billing['county'] = '';
		//检测地址是否有填写
		$billing['address'] = $this->get('b-address');
		if(!$billing['address']) $this->json('请填写账单地址信息，要求尽可能详实');
		//邮编号
		$billing['zipcode'] = $this->get('b-zipcode');
		if(!$billing['zipcode']) $this->json('邮编不能为空');
		//检测电话或手机
		$billing['tel'] = $this->get('b-tel');
		$billing['mobile'] = $this->get('b-mobile');
		if(!$billing['tel'] && !$billing['mobile']) $this->json('至少要求填写一个联系方式：电话或手机');
		if($billing['tel'])
		{
			if(!$this->isTel($billing['tel'],'tel')) $this->json('电话填写不正确，请填写规范，如：0755-123456789');
		}
		if($billing['mobile'])
		{
			if(!$this->isTel($billing['mobile'],'mobile')) $this->json('手机填写不正确，请填写规范，如：158185xxxxx');
		}
		return $billing;
	}

	function save_billing($billing)
	{
		if(!$billing) return false;
		//存储到地址库中
		if($_SESSION['user_id'])
		{
			$id = $this->get('b-id','int');
			if($id)
			{
				$rs = $this->model('address')->get_one($id);
				if(!$rs) $this->json('登记地址信息异常，错误编号：ADDRESS1005');
				if($rs['user_id'] != $_SESSION['user_id']) $this->json('登记地址信息异常，错误编号：ADDRESS1006');
				if($rs['type_id'] != 'billing') $this->json('登记地址信息异常，错误编号：ADDRESS1007');
				$this->model('address')->save($billing,$id);
			}
			else
			{
				$billing['type_id'] = 'billing';
				$billing['user_id'] = $_SESSION['user_id'];
				$id = $this->model('address')->save($billing);
				if(!$id) $this->json('登记地址信息异常，错误编号：ADDRESS1008');
			}
		}
		else
		{
			$_SESSION['address']['billing'] = $billing;
		}
		return $billing;
	}

	//是否电话判断
	function isTel($tel,$type='')
	{
		$regxArr = array(
			'mobile'  =>  '/^(\+?86-?)?(18|15|13)[0-9]{9}$/',
			'tel' =>  '/^(\+?86-?)?(010|02\d{1}|0[3-9]\d{2})-\d{7,9}(-\d+)?$/',
			'400' =>  '/^400(-\d{3,4}){2}$/',
		);
		if($type && isset($regxArr[$type]))
		{
			return preg_match($regxArr[$type], $tel) ? true:false;
		}
		foreach($regxArr as $regx)
		{
			if(preg_match($regx, $tel ))
			{
				return true;
			}
		}
		return false;
	}

	//创建订单编号
	function create_sn()
	{
		$sntype = $this->site['biz_sn'];
		if(!$sntype) $sntype = 'year-month-date-number';
		$sn = '';
		$list = explode('-',$sntype);
		foreach($list AS $key=>$value)
		{
			if($value == 'year') $sn.= date("Y",$this->time);
			if($value == 'month') $sn.= date("m",$this->time);
			if($value == 'date') $sn.= date("d",$this->time);
			if($value == 'hour') $sn.= date('H',$this->time);
			if($value == 'minute' || $value == 'minutes') $sn.= date("i",$this->time);
			if($value == 'second' || $value == 'seconds') $sn.= date("s",$this->time);
			//随机数，这里仅提供两位随机数
			if($value == 'rand' || $value == 'rands') $sn .= rand(10,99);
			if($value == 'time' || $value == 'times') $sn .= $this->time;
			//序号
			if($value == 'number')
			{
				$condition = "FROM_UNIXTIME(addtime,'%Y-%m-%d')='".date("Y-m-d",$this->time)."'";
				$total = $this->model('order')->get_count($condition);
				if(!$total) $total = '0';
				$total++;
				$sn .= str_pad($total,3,'0',STR_PAD_LEFT);
			}
			//自增ID号
			if($value == 'id')
			{
				$maxid = $this->model('order')->maxid();
				$sn .= str_pad($maxid,5,'0',STR_PAD_LEFT);
			}
			//包含会员信息
			if($value == 'user')
			{
				$sn .= $_SESSION['user_id'] ? 'U'.str_pad($_SESSION['user_id'],5,'0',STR_PAD_LEFT) : 'G';
			}
			if(substr($value,0,6) == 'prefix')
			{
				$sn .= str_replace(array('prefix','[',']'),'',$value);
			}
		}
		return $sn;
	}

}

?>