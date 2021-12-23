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
				$is_virtual = 1;
				foreach($product as $k=>$v){
					$v['price_val'] = price_format_val($v['price'],$value['currency_id'],$value['currency_id'],$value['currency_rate'],$value['currency_rate']);
					$v['price_show'] = price_format($v['price'],$value['currency_id'],$value['currency_id'],$value['currency_rate'],$value['currency_rate']);
					if(!$v['is_virtual']){
						$is_virtual = 0;
					}
					$product[$k] = $v;
					$qty += intval($v['qty']);
				}
				$rslist[$key]['is_virtual'] = $is_virtual; //判断是否是虚拟产品
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
			//付款记录
			$paylist = $this->model('order')->payment_all($value['id']);
			if($paylist){
				$payinfo = end($paylist);
				$rslist[$key]['payment'] = $payinfo;
				$rslist[$key]['paylist'] = $paylist;
			}
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
		$id = $this->get('id','int');
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
		$pricelist = $this->model('site')->price_status_all(true);
		$discount = 0;
		if($pricelist){
			foreach($pricelist as $key=>$value){
				if(!$value['status']){
					unset($pricelist[$key]);
					continue;
				}
				if($value['default'] && $value['currency_id']){
					$value['price'] = price_format($value['default'],$value['currency_id'],$this->site['currency_id']);
					$value['price_val'] = price_format_val($value['default'],$value['currency_id'],$this->site['currency_id']);
				}
				if($value['identifier'] == 'product'){
					$value['price'] = price_format($price,$this->site['currency_id']);
					$value['price_val'] = $price;
					$pricelist[$key] = $value;
				}
				if($value['identifier'] == 'shipping'){
					if($address){
						$freight_price = $this->_freight($rslist,$address);
						if($freight_price){
							$value['price'] = price_format($freight_price,$this->site['currency_id']);
							$value['price_val'] = $freight_price;
						}
					}
				}
				if($value['identifier'] == 'discount'){
					$this->data("cart_id",$this->cart_id);
					$this->node('PHPOK_cart_coupon');
					$tmp = $this->data('cart_coupon');
					if(!$tmp){
						unset($pricelist[$key]);
						continue;
					}
					$value['price'] = price_format(-$tmp['price'],$this->site['currency_id']);
					$value['price_val'] = -$tmp['price'];
					$discount = -$tmp['price'];
					$pricelist[$key] = $value;
					$this->assign('coupon_code',$tmp['code']);
				}
				$pricelist[$key] = $value;
			}
			foreach($pricelist as $key=>$value){
				if($value['hidden'] && (!$value['price_val'] || $value['price_val'] == '0.00')){
					unset($pricelist[$key]);
				}
			}
			$this->data('pricelist',$pricelist);
			$this->data('rslist',$rslist);
			$this->data('address',$address);
			$this->node("system_pricelist");
			$pricelist = $this->data('pricelist');
			$this->undata('pricelist');
			$allprice = 0;
			foreach($pricelist as $key=>$value){
				if($value['action'] == 'add'){
					$allprice += $value['price_val'];
				}else{
					$allprice -= abs($value['price_val']);
				}
			}
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
			$tmp['note'] = $value['note'];
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
		if($pricelist){
			foreach($pricelist as $key=>$value){
				$tmp = array('order_id'=>$order_id,'code'=>$value['identifier'],'price'=>$value['price_val']);
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
		//用户注册，如果手机号或是邮箱在系统中找不到
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
			//当手机及邮箱都不存在时，自动注册用户
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
				//订单改成用户
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
				$this->error(P_Lang('非用户不能通过ID获取订单信息'));
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
		//付款记录
		$paylist = $this->model('order')->payment_all($rs['id']);
		if($paylist){
			$data['payinfo'] = end($paylist);
			$data['paylist'] = $paylist;
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
		$this->model('order')->update_order_status($rs['id'],'cancel',P_Lang('用户取消订单'));		
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
		$this->model('order')->update_order_status($rs['id'],'end',P_Lang('订单完成'));
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
		$this->model('order')->update_order_status($rs['id'],'received',P_Lang('用户确认订单已收'));
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
		$sort = $this->get('sort');
		if(!$sort){
			$sort = 'asc';
		}
		$sort = strtoupper($sort);
		$loglist = $this->model('order')->log_list($rs['id'],$sort);
		if(!$loglist){
			$this->error(P_Lang('订单中找不到相关物流信息，请联系客服'));
		}
		$rslist = array();
		foreach($express_list as $key=>$value){
			$tmp = $value;
			$tmplist = array();
			foreach($loglist as $k=>$v){
				if(!$v['order_express_id'] || $v['order_express_id'] != $value['id']){
					continue;
				}
				$tmplist[] = $v;
			}
			$tmp['rslist'] = $tmplist;
			$rslist[] = $tmp;
		}
		$this->success($rslist);
	}

	/**
	 * 异步退款通知
	 * @参数 sn 退单号
	 * @参数 其他参数（POST或是GET进来，原生读取执行传给支付通知文件）
	**/
	public function refund_f()
	{
		$sn = $this->get('sn');
		if(!$sn){
			$this->error(P_Lang('未指定退单号'));
		}
		$rs = $this->model('order')->refund_one($sn,'sn');
		if(!$rs){
			$this->error(P_Lang('退单号不存在'));
		}
		if($rs['backtype'] != '_default'){
			$this->error(P_Lang('非原路返回退单，不支持此操作'));
		}
		$pinfo = $this->model('order')->order_payment_info($rs['order_payment_id']);
		if(!$pinfo){
			$this->error(P_Lang('付款信息不存在'));
		}
		if(!is_numeric($pinfo['payment_id'])){
			$this->error(P_Lang('内置财富，不支持此操作'));
		}
		$payment_rs = $this->model('payment')->get_one($pinfo['payment_id']);
		if(!$payment_rs){
			$this->error(P_Lang('未指定付款方式'));
		}
		if($payment_rs && $payment_rs['param'] && is_string($payment_rs['param'])){
			$payment_rs['param'] = unserialize($payment_rs['param']);
		}
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/refund.php';
		if(!file_exists($file)){
			$this->error(P_Lang('当前支付未开发退款接口，请联系开发人员定制或扩展'));
		}
		include_once($file);
		$name = $payment_rs['code']."_refund";
		$payment = new $name($pinfo,$payment_rs,$rs);
		$payment->notify();
		exit;
	}

	public function status_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$uid = $this->session->val('user_id');
		$sql  = "SELECT count(id) as total,status FROM ".$this->db->prefix."order ";
		$sql .= "WHERE user_id='".$uid."' GROUP BY status";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$this->error(P_Lang('没有找到订单信息'));
		}
		$rs = array();
		foreach($rslist as $key=>$value){
			$rs[$value['status']] = $value['total'];
		}
		$this->success($rs);
	}

	private function _get_order()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->session->val('user_id')){
				$this->error(P_Lang('非用户不能执行此操作'));
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

	private function _freight($rslist='',$address='')
	{
		if(!$rslist){
			$rslist = $this->tpl->val('rslist');
			if(!$rslist){
				return false;
			}
		}
		if(!$rslist || !is_array($rslist)){
			return false;
		}
		if(!$address){
			$address = $this->tpl->val('address');
			if(!$address){
				return false;
			}
		}
		if(!$address || !is_array($address)){
			return false;
		}
		$weight = $volume = $total = 0;
		foreach($rslist as $key=>$value){
			$weight += floatval($value['weight'] * $value['qty']);
			$volume += floatval($value['volume'] * $value['qty']);
			$total += $value['qty'];
		}
		$data = array('weight'=>$weight,'number'=>$total,'volume'=>$volume);
		return $this->model('cart')->freight_price($data,$address['province'],$address['city'],$address['country']);
	}
}