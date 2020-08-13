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

	/**
	 * 取得订单列表
	 * @参数 pageid 页码ID
	**/
	public function index_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您还未登录，请先登录'));
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$condition = "user_id='".$this->session->val('user_id')."'";
		$status = $this->get('status');
		$data = array();
		if($status){
			$tmp = explode(",",$status);
			$condition .= " AND status IN('".implode("','",$tmp)."')";
			$data['status'] = $status;
		}
		$total = $this->model('order')->get_count($condition);
		if(!$total){
			$this->error(P_Lang('暂无订单信息'));
		}
		$status_list = $this->model('order')->status_list();
		$rslist = $this->model('order')->get_list($condition,$offset,$psize);
		foreach ($rslist as $key => $value){
		    $product = $this->model('order')->product_list($value['id']);
			$qty = 0;
			if($product){
				foreach($product as $k=>$v){
					$v['price_val'] = price_format_val($v['price'],$value['currency_id'],$value['currency_id'],$value['currency_rate'],$value['currency_rate']);
					$v['price_show'] = price_format($v['price'],$value['currency_id'],$value['currency_id'],$value['currency_rate'],$value['currency_rate']);
					$product[$k] = $v;
					$qty += intval($v['qty']);
				}
			}
		    $rslist[$key]['product'] = $product;
			$rslist[$key]['qty'] = $qty;
		    $unpaid_price = $this->model('order')->unpaid_price($value['id']);
	        $paid_price = $this->model('order')->paid_price($value['id']);
	        if($unpaid_price > 0){
		        if($paid_price>0){
			        $rslist[$key]['pay_info'] = P_Lang('部分支付');
		        }else{
			        $rslist[$key]['pay_info'] = P_Lang('未支付');
		        }
	        }else{
		        $rslist[$key]['pay_info'] = P_Lang('已支付');
	        }
	        if(!$value['status_title'] && $status_list && $status_list[$value['status']]){
		        $rslist[$key]['status_title'] = $status_list[$value['status']];
	        }
			$rslist[$key]['price_val'] = price_format_val($value['price'],$value['currency_id'],$value['currency_id'],$value['currency_rate'],$value['currency_rate']);
			$rslist[$key]['price_show'] = price_format($value['price'],$value['currency_id'],$value['currency_id'],$value['currency_rate'],$value['currency_rate']);
        }
		$data['total'] = $total;
		$data['rslist'] = $rslist;
		$data['pageid'] = $pageid;
		$data['psize'] = $psize;
		$this->success($data);
	}

	/**
	 * 创建订单
	**/
	public function create_f()
	{
		$user = array();
		if($this->session->val('user_id')){
			$user = $this->model('user')->get_one($this->session->val('user_id'));
		}
		$id = $this->get('id');
		if($id && is_string($id)){
			$id = explode(",",$id);
			foreach($id as $key=>$value){
				$value = intval($value);
				if(!$value){
					unset($id[$key]);
					continue;
				}
				$id[$key] = $value;
			}
		}
		$rslist = $this->model('cart')->get_all($this->cart_id,$id);
		if(!$rslist){
			$this->error(P_Lang("没有要结算的产品"));
		}
		$is_virtual = true;
		foreach($rslist as $key=>$value){
			if(!$value['is_virtual']){
				$is_virtual = false;
				break;
			}
		}
		$email = $this->get('email');
		$mobile = $this->get('mobile');
		if($email && !$this->lib('common')->email_check($email)){
			$this->error(P_Lang('邮箱不合法'));
		}
		if($mobile && !$this->lib('common')->tel_check($mobile)){
			$this->error(P_Lang('手机号不合法'));
		}
		$address = array();
		$address_id = 0;
		if(!$is_virtual){
			$address_id = $this->get('shipping_address_id','int');
			if(!$address_id){
				$address_id = $this->get('address_id','int');
			}
			if($address_id){
				$tmp = $this->model('address')->get_one($address_id);
				if(!$tmp){
					$this->error(P_Lang('地址信息不存在'));
				}
				if(!$this->session->val('user_id') || $tmp['user_id'] != $this->session->val("user_id")){
					$this->error(P_Lang('您没有当前地址权限'));
				}
				$address['shipping'] = $tmp;
			}else{
				$tmp = $this->form_address('shipping');
				if(!$tmp['status']){
					$this->error($tmp['info']);
					exit;
				}
				$address['shipping'] = $tmp['info'];
			}
			$billing_address_id = $this->get("billing_address_id","int");
			if($billing_address_id){
				$tmp = $this->model('address')->get_one($billing_address_id);
				if(!$tmp){
					$this->error(P_Lang('账单地址信息不存在'));
				}
				if(!$this->session->val('user_id') || $tmp['user_id'] != $this->session->val("user_id")){
					$this->error(P_Lang('您没有当前地址权限'));
				}
				$address['billing'] = $tmp;
			}else{
				if(strpos($this->config['order']['address'],'billing') !== false){
					$tmp = $this->form_address('billing');
					if(!$tmp['status']){
						$this->error($tmp['info']);
						exit;
					}
					$address['billing'] = $tmp['info'];
				}
			}
			//检测
			if($address['shipping']){
				if(!$email && $address['shipping']['email']){
					$email = $address['shipping']['email'];
				}
				if(!$mobile && $address['shipping']['mobile']){
					$mobile = $address['shipping']['mobile'];
				}
			}
			if($address['billing']){
				if(!$email && $address['billing']['email']){
					$email = $address['billing']['email'];
				}
				if(!$mobile && $address['billing']['mobile']){
					$mobile = $address['billing']['mobile'];
				}
			}
			//检测Billing地址齐全，不齐全则合并shipping的
			if(strpos($this->config['order']['address'],'billing') !== false){
				if(!$address['billing']){
					$address['billing'] = $address['shipping'];
				}
				foreach($address['billing'] as $key=>$value){
					if(!$value && $key != 'address2'){
						$address['billing'][$key] = $address['shipping'][$key];
					}
				}
			}
		}
		if(!$mobile && !$email){
			$this->error(P_Lang('手机号或邮箱必须有一个不为空'));
		}
		$shipping = 0;
		$price = 0;
		$tax = 0;
		if($this->session->val('tax')){
			$tax = floatval($this->session->val('tax'));
		}
		$tmp = array('number'=>0,'weight'=>0,'volume'=>0,'price'=>0);
		$tmp_is_virtual = true;
		foreach($rslist as $key=>$value){
			$price += floatval($value['price']) * intval($value['qty']);
			if(!$value['is_virtual'] && $address && $address['shipping']['province'] && $address['shipping']['city']){
				$tmp_is_virtual = false;
				$tmp['number'] += intval($value['qty']);
				$tmp['weight'] += floatval($value['weight']) * intval($value['qty']);
				$tmp['volume'] += floatval($value['volume']) * intval($value['qty']);
			}
		}
		
		//计算运费
		if(!$tmp_is_virtual && $address && $address['shipping']['province'] && $address['shipping']['city']){
			$tmp['price'] = $price;
			$shipping = $this->model('cart')->freight_price($tmp,$address['shipping']['province'],$address['shipping']['city']);
		}
		$allprice = floatval($price) + floatval($shipping) + $tax;
		$coupon = $this->_coupon($price,$id);
		if($coupon){
			$allprice = floatval($price) + floatval($shipping) + $tax - floatval($coupon);
		}

		$sn = $this->model('order')->create_sn();
		$status_list = $this->model('order')->status_list();
		$main = array('sn'=>$sn);
		$main['user_id'] = $user ? $user['id'] : 0;
		$main['addtime'] = $this->time;
		$main['price'] = $allprice;
		$main['currency_id'] = $this->site['currency_id'];
		$main['currency_rate'] = $this->site['currency']['val'];
		$main['status'] = 'create';
		$main['status_title'] = $status_list['create'];
		$main['passwd'] = md5(str_rand(10));
		$main['email'] = $email;
		$main['mobile'] = $mobile;
		$main['note'] = $this->get('note');
		//存储扩展字段信息
		$tmpext = $this->get('ext');
		if($tmpext){
			foreach($tmpext as $key=>$value){
				$key = $this->format($key);
				if(!$key || !$value){
					unset($tmpext[$key]);
					continue;
				}
			}
			if($tmpext){
				$main['ext'] = serialize($tmpext);
			}
		}
		$order_id = $this->model('order')->save($main);
		if(!$order_id){
			$this->error(P_Lang('订单创建失败'));
		}
		foreach($rslist as $key=>$value){
			$tmp = array('order_id'=>$order_id,'tid'=>$value['tid']);
			$tmp['title'] = $value['title'];
			$tmp['price'] = price_format_val($value['price'],$this->site['currency_id']);
			$tmp['qty'] = $value['qty'];
			$tmp['weight'] = $value['weight'];
			$tmp['volume'] = $value['volume'];
			$tmp['unit'] = $value['unit'];
			$tmp['thumb'] = $value['thumb'] ? $value['thumb'] : '';
			$tmp['ext'] = $value['_attrlist'] ? serialize($value['_attrlist']) : '';
			$tmp['is_virtual'] = $value['is_virtual'];
			$this->model('order')->save_product($tmp);
		}
		if($address){
			foreach($address as $key=>$value){
				$tmp = array('order_id'=>$order_id);
				$tmp['country'] = $value['country'];
				$tmp['province'] = $value['province'];
				$tmp['city'] = $value['city'];
				$tmp['county'] = $value['county'];
				$tmp['address'] = $value['address'];
				$tmp['address2'] = $value['address2'];
				$tmp['mobile'] = $value['mobile'];
				$tmp['zipcode'] = $value['zipcode'];
				$tmp['tel'] = $value['tel'];
				$tmp['email'] = $value['email'];
				$tmp['fullname'] = $value['fullname'];
				$tmp['firstname'] = $value['firstname'];
				$tmp['lastname'] = $value['lastname'];
				$tmp['type'] = $key;
				$this->model('order')->save_address($tmp);
				if(!$value['id'] && !$address_id && $this->session->val('user_id')){
					unset($tmp['type'],$tmp['order_id']);
					$tmp['user_id'] = $this->session->val('user_id');
					$this->model('address')->save($tmp);
				}
			}
		}
		$pricelist = $this->model('site')->price_status_all();
		if($pricelist){
			foreach($pricelist as $key=>$value){
				$tmp_price = $value['default'] ? $value['default'] : '0.00';
				if($key == 'product'){
					$tmp_price = $price;
				}elseif($key == 'shipping' && $shipping){
					$tmp_price = $shipping;
				}elseif($key == 'discount' && $coupon){
					$tmp_price = -$coupon;
				}elseif($key == 'tax' && $this->session->val('tax')){
					$tmp_price = $this->session->val('tax');
				}
				$tmp = array('order_id'=>$order_id,'code'=>$key,'price'=>$tmp_price);
				$this->model('order')->save_order_price($tmp);
			}
		}
		//删除购物车信息
		$this->data("cart_id",$this->cart_id);
		$this->node("PHPOK_cart_coupon");
		$coupon_rs = $this->data("cart_coupon");
		$this->data("order_id",$order_id);
		$this->data("price",$coupon);
		$this->node('PHPOK_coupon_to_history');

		//删除购物车信息
		$this->model('cart')->delete($this->cart_id,$id);
		
		//填写订单日志
		$note = P_Lang('订单创建成功，订单编号：{sn}',array('sn'=>$sn));
		$log = array('order_id'=>$order_id,'addtime'=>$this->time,'who'=>$user['user'],'note'=>$note);
		$this->model('order')->log_save($log);
		//如果使用了优惠码
		if($coupon_rs){
			$note = P_Lang('订单优惠码{code}',array('code'=>$coupon_rs['code']));
			$log = array('order_id'=>$order_id,'addtime'=>$this->time,'who'=>$user['user'],'note'=>$note);
			$this->model('order')->log_save($log);
		}
		//会员注册，如果手机号或是邮箱在系统中找不到
		if(!$this->session->val('user_id')){
			$mobile_exit = $email_exit = false;
			if($mobile){
				$chk = $chk2 = false;
				$chk = $this->model('user')->user_mobile($mobile);
				$chk2 = $this->model('user')->get_one($mobile,'user',false,false);
				if($chk || $chk2){
					$mobile_exit = true;
				}
			}
			if($email){
				$chk = $chk2 = false;
				$chk = $this->model('user')->user_email($email);
				$chk2 = $this->model('user')->get_one($email,'user',false,false);
				if($chk || $chk2){
					$email_exit = true;
				}
			}
			//当手机及邮箱都不存在时，自动注册会员
			if(!$email_exit && !$mobile_exit && ($mobile || $email)){
				$username = $mobile ? $mobile : $email;
				$passwd = $this->lib('common')->str_rand(10);
				$code = $this->lib('common')->str_rand(6,'number');
				$usergroup = $this->model('usergroup')->get_default(true);
				$data = array('user'=>$username,'email'=>$email,'mobile'=>$mobile,'pass'=>password_create($passwd));
				$data['status'] = 0;
				$data['group_id'] = $usergroup['id'];
				$data['regtime'] = $this->time;
				$data['code'] = $code;
				$user_id = $this->model('user')->save($data);
				$ext = array('id'=>$user_id);
				$this->model('user')->save_ext($ext);
				//发送激活邮件
				$param = 'id='.$user_id.'&act=active';
				$this->model('task')->add_once('register',$param);
				//订单改成会员
				$tmparray = array('user_id'=>$user_id);
				$this->model('order')->save($tmparray,$order_id);
			}
		}
		//增加订单通知
		$param = 'id='.$order_id."&status=create";
		$this->model('task')->add_once('order',$param);
		$rs = array('sn'=>$sn,'passwd'=>$main['passwd'],'id'=>$order_id);
		$this->success($rs);
	}

	/**
	 * 优惠码功能
	**/
	private function _coupon($totalprice,$id)
	{
		if(!$totalprice){
			return false;
		}
		$this->data("cart_id",$this->cart_id);
		$this->data('cart_ids',$id);
		$this->node('PHPOK_cart_coupon');
		$tmp = $this->data('cart_coupon');
		if(!$tmp){
			return false;
		}
		return $tmp['price'];
	}

	/**
	 * 获取表单地址
	 * @返回 数组
	**/
	private function form_address($type='shipping')
	{
		$array = array('type'=>$type);
		$country = $this->get($type.'-country');
		if(!$country){
			$country = '中国';
		}
		$array['country'] = $country;
		$array['province'] = $this->get($type.'-province');
		$array['city'] = $this->get($type.'-city');
		$array['county'] = $this->get($type.'-county');
		$array['fullname'] = $this->get($type.'-fullname');
		if(!$array['fullname']){
			$array['firstname'] = $this->get($type.'-firstname');
			$array['lastname'] = $this->get($type.'-lastname');
		}
		$array['address'] = $this->get($type.'-address');
		$array['address2'] = $this->get($type.'-address2');
		$array['mobile'] = $this->get($type.'-mobile');
		$array['tel'] = $this->get($type.'-tel');
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
		$array['email'] = $this->get($type.'-email');
		if($array['email']){
			if(!$this->lib('common')->email_check($array['email'])){
				return array('status'=>false,'info'=>P_Lang('邮箱格式不对'));
			}
		}
		$array['zipcode'] = $this->get($type.'-zipcode');
		return array('status'=>true,'info'=>$array);
	}

	public function info_f()
	{
		$user = array();
		if($this->session->val('user_id')){
			$user = $this->model('user')->get_one($this->session->val('user_id'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$sn = $this->get('sn');
			$passwd = $this->get('passwd');
			if(!$sn || !$passwd){
				$this->error(P_Lang('未指定订单编码或密串'));
			}
			$rs = $this->model('order')->get_one($sn,'sn');
			if(!$rs){
				$this->error(P_Lang('订单信息不存在'));
			}
			if($rs['passwd']  != $passwd){
				$this->error(P_Lang('订单密串不符合要求'));
			}
		}else{
			if(!$user || !$user['id']){
				$this->error(P_Lang('非会员不能通过ID获取订单信息'));
			}
			$rs = $this->model('order')->get_one($id);
			if(!$rs){
				$this->error(P_Lang('订单信息不存在'));
			}
			if(!$rs['user_id'] || $rs['user_id'] != $user['id']){
				$this->error(P_Lang('您没有权限查看此订单'));
			}
		}
		$status_list = $this->model('order')->status_list();
		$unpaid_price = $this->model('order')->unpaid_price($rs['id']);
		$paid_price = $this->model('order')->paid_price($rs['id']);
		if($unpaid_price > 0){
			if($paid_price>0){
				$rs['pay_info'] = P_Lang('部分支付');
			}else{
				$rs['pay_info'] = P_Lang('未支付');
			}
		}else{
			$rs['pay_info'] = P_Lang('已支付');
		}
		$rs['status_info'] = ($status_list && $status_list[$rs['status']]) ? $status_list[$rs['status']] : $rs['status'];
		$rs['price_paid'] = price_format_val($paid_price,$rs['currency_id'],$rs['currency_id'],$rs['currency_rate'],$rs['currency_rate']);
		$rs['price_paid_show'] = price_format($paid_price,$rs['currency_id'],$rs['currency_id'],$rs['currency_rate'],$rs['currency_rate']);
		$rs['price_unpaid'] = price_format_val($unpaid_price,$rs['currency_id'],$rs['currency_id'],$rs['currency_rate'],$rs['currency_rate']);
		$rs['price_unpaid_show'] = price_format($unpaid_price,$rs['currency_id'],$rs['currency_id'],$rs['currency_rate'],$rs['currency_rate']);
		$data = array('rs'=>$rs);
		$address = $this->model('order')->address($rs['id'],'shipping');
		if($address){
			$data['address'] = $address;
		}
		$billing = $this->model('order')->address($rs['id'],'billing');
		if($billing){
			$data['billing'] = $billing;
		}
		$rslist = $this->model('order')->product_list($rs['id']);
		if($rslist){
			$qty = 0;
			foreach($rslist as $key=>$value){
				$qty += intval($value['qty']);
				$value['price_show'] = price_format($value['price'],$rs['currency_id'],$rs['currency_id'],$rs['currency_rate'],$rs['currency_rate']);
				$rslist[$key] = $value;
			}
			$data['rslist'] = $rslist;
			$rs['qty'] = $qty;
		}
		$rs['price_val'] = price_format_val($rs['price'],$rs['currency_id'],$rs['currency_id'],$rs['currency_rate'],$rs['currency_rate']);
		$rs['price_show'] = price_format($rs['price'],$rs['currency_id'],$rs['currency_id'],$rs['currency_rate'],$rs['currency_rate']);
		$rs['addtime_format'] = date("Y-m-d H:i:s",$rs['addtime']);
		$data['rs'] = $rs;
		//获取价格
		$price_tpl_list = $this->model('site')->price_status_all();
		$order_price = $this->model('order')->order_price($rs['id']);
		if($price_tpl_list && $order_price){
			$pricelist = array();
			foreach($price_tpl_list as $key=>$value){
				$tmpval = floatval($order_price[$key]);
				if(!$value['status'] || !$tmpval){
					continue;
				}
				$tmp = array('val'=>$tmpval);
				$tmp['price'] = price_format($order_price[$key],$rs['currency_id']);
				$tmp['title'] = $value['title'];
				$pricelist[$key] = $tmp;
			}
			if($pricelist){
				$data['pricelist'] = $pricelist;
			}
		}
		$data['pay_end'] = false;
		if($this->model('order')->check_payment_is_end($rs['id'])){
			$data['pay_end'] = true;
		}
		$loglist = $this->model('order')->log_list($rs['id']);
		if($loglist){
			$data['loglist'] = $loglist;
		}
		$this->success($data);
	}

	/**
	 * 订单取消
	 * @参数 $id 订单ID号
	 * @参数 $sn 订单SN码
	 * @参数 $passwd 订单密码
	**/
	public function cancel_f()
	{
		$rs = $this->_get_order();
		if(!$rs['status']){
			$this->error(P_Lang('订单状态异常，请联系客服'));
		}
		$array = array('create','unpaid');
		if(!in_array($rs['status'],$array)){
			$this->error(P_Lang('仅限订单未支付才能取消订单'));
		}
		//更新订单日志
		$this->model('order')->update_order_status($rs['id'],'cancel');
		
		$who = '';
		if($rs['user_id']){
			$user = $this->model('user')->get_one($rs['user_id']);
			$who = $user['user'];
		}else{
			$address = $this->model('order')->address($rs['id']);
			if($address){
				$who = $address['fullname'];
			}
		}
		$log = array('order_id'=>$rs['id']);
		$log['addtime'] = $this->time;
		if($who){
			$log['who'] = $who;
		}
		$log['note'] = P_Lang('会员取消订单');
		$log['user_id'] = $rs['user_id'];
		$this->model('order')->log_save($log);
		$this->plugin('plugin-order-status',$id,'cancel');
		$this->success();
	}
	
	public function end_f()
	{
		$rs = $this->_get_order();
		if(!$rs['status']){
			$this->error(P_Lang('订单状态异常，请联系客服'));
		}
		//更新订单日志
		$this->model('order')->update_order_status($rs['id'],'end');
		
		$who = '';
		if($rs['user_id']){
			$user = $this->model('user')->get_one($rs['user_id']);
			$who = $user['user'];
		}else{
			$address = $this->model('order')->address($rs['id']);
			if($address){
				$who = $address['fullname'];
			}
		}
		$log = array('order_id'=>$rs['id']);
		$log['addtime'] = $this->time;
		if($who){
			$log['who'] = $who;
		}
		$log['note'] = P_Lang('订单完成');
		$log['user_id'] = $rs['user_id'];
		$this->model('order')->log_save($log);
		$this->model('wealth')->order($rs['id'],P_Lang('订单完成赚送积分'));
		$this->plugin('plugin-order-status',$id,'end');
		$this->success();
	}

	/**
	 * 确认收货
	 * @参数 $id 订单ID号
	 * @参数 $sn 订单SN码
	 * @参数 $passwd 订单密码
	**/
	public function received_f()
	{
		$rs = $this->_get_order();
		if(!$rs['status']){
			$this->error(P_Lang('订单状态异常，请联系客服'));
		}
		$array = array('shipping','paid');
		if(!in_array($rs['status'],$array)){
			$this->error(P_Lang('订单仅限已付款或已发货状态下能确认收货'));
		}
		$this->model('order')->update_order_status($rs['id'],'received');
		$who = '';
		if($rs['user_id']){
			$user = $this->model('user')->get_one($rs['user_id']);
			$who = $user['user'];
		}else{
			$address = $this->model('order')->address($rs['id']);
			if($address){
				$who = $address['fullname'];
			}
		}
		$log = array('order_id'=>$rs['id']);
		$log['addtime'] = $this->time;
		if($who){
			$log['who'] = $who;
		}
		$log['note'] = P_Lang('会员确认订单已收');
		$log['user_id'] = $rs['user_id'];
		$this->model('order')->log_save($log);
		$this->plugin('plugin-order-status',$id,'received');
		$this->success();
	}

	public function log_f()
	{
		$rs = $this->_get_order();
		if(!$rs['status']){
			$this->error(P_Lang('订单状态异常，请联系客服'));
		}
		$loglist = $this->model('order')->log_list($rs['id']);
		if(!$loglist){
			$this->error(P_Lang('日志信息不存在'));
		}
		foreach($loglist as $key=>$value){
			$value['addtime_format'] = date("Y-m-d H:i",$value['addtime']);
			$loglist[$key] = $value;
		}
		$this->success($loglist);
	}

	/**
	 * 获取物流信息
	 * @参数 $id 订单ID号
	 * @参数 $sn 订单SN码
	 * @参数 $passwd 订单密码
	 * @参数 $sort 值为ASC或DESC
	**/
	public function logistics_f()
	{
		$rs = $this->_get_order();
		if(!$rs['status']){
			$this->error(P_Lang('订单状态异常，请联系客服'));
		}
		$array = array('create','unpaid');
		if(in_array($rs['status'],$array)){
			$this->error(P_Lang('仅限已支付的订单才能查看物流'));
		}
		$is_virtual = true;
		$plist = $this->model('order')->product_list($rs['id']);
		if(!$plist){
			$this->error(P_Lang('这是一张空白订单，没有产品，无法获得物流信息'));
		}
		foreach($plist as $key=>$value){
			if(!$value['is_virtual']){
				$is_virtual = false;
				break;
			}
		}
		if($is_virtual){
			$this->error(P_Lang('服务类订单没有物流信息'));
		}
		$express_list = $this->model('order')->express_all($rs['id']);
		if(!$express_list){
			$this->error(P_Lang('订单还未录入物流信息'));
		}
		foreach($express_list as $key=>$value){
			$url = $this->url('express','remote','id='.$value['id'],'api',true);
			if($this->config['self_connect_ip']){
				$this->lib('curl')->host_ip($this->config['self_connect_ip']);
			}
			$this->lib('curl')->connect_timeout(5);
			$this->lib('curl')->get_content($url);
		}
		$loglist = $this->model('order')->log_list($rs['id']);
		if(!$loglist){
			$this->error(P_Lang('订单中找不到相关物流信息，请联系客服'),$error_url);
		}
		foreach($loglist as $key=>$value){
			if(!$value['order_express_id']){
				continue;
			}
			$rslist[$value['order_express_id']]['rslist'][] = $value;
		}
		$sort = $this->get('sort');
		if($sort && strtoupper($sort) == 'DESC'){
			foreach($rslist as $key=>$value){
				krsort($value['rslist']);
				$rslist[$key] = $value;
			}
		}
		$this->success($rslist);
	}

	private function _get_order()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->session->val('user_id')){
				$this->error(P_Lang('非会员不能执行此操作'));
			}
			$rs = $this->model('order')->get_one($id);
			if(!$rs){
				$this->error(P_Lang('订单不存在'));
			}
			if($rs['user_id'] != $this->session->val('user_id')){
				$this->error(P_Lang('您没有权限操作此订单'));
			}
		}else{
			$sn = $this->get('sn');
			$passwd = $this->get('passwd');
			if(!$sn || !$passwd){
				$this->error(P_Lang('参数不完整，不能执行此操作'));
			}
			$rs = $this->model('order')->get_one($sn,'sn');
			if(!$rs){
				$this->error(P_Lang('订单不存在'));
			}
			if($rs['passwd'] != $passwd){
				$this->error(P_Lang('订单密码不正确'));
			}
		}
		return $rs;
	}
}