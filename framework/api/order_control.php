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
		$psize = 100;
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
		if($total){
			$rslist = $this->model('order')->get_list($condition,$offset,$psize);
			foreach ($rslist as $key => $value){
			    $product = $this->model('order')->product_list($value['id']);
				$qty = 0;
				if($product){
					foreach($product as $k=>$v){
						$v['price_val'] = price_format_val($v['price'],$value['currency_id'],$value['currency_id']);
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
				$rslist[$key]['price_val'] = price_format_val($value['price'],$value['currency_id'],$value['currency_id']);
            }
			$data['total'] = $total;
			$data['rslist'] = $rslist;
			$data['pageid'] = $pageid;
			$data['psize'] = $psize;
		}
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
		}
		if(!$id || !is_array($id)){
			$this->error(P_Lang('没有要结算的产品-1'));
		}
		foreach($id as $key=>$value){
			$value = intval($value);
			if(!$value){
				unset($id[$key]);
				continue;
			}
		}
		$rslist = $this->model('cart')->get_all($this->cart_id,$id);
		if(!$rslist){
			$this->json(P_Lang("没有要结算的产品-2"));
		}
		$is_virtual = true;
		foreach($rslist as $key=>$value){
			if(!$value['is_virtual']){
				$is_virtual = false;
				break;
			}
		}
		if($is_virtual){
			$mobile = $this->get('mobile');
			$email = $this->get('email');
			if(!$mobile){
				$this->error(P_Lang('请填写手机号'));
			}
			if(!$this->lib('common')->tel_check($mobile,'mobile')){
				$this->error(P_Lang('手机号不合法'));
			}
		}else{
			$address_id = $this->get('address_id','int');
			if($this->session->val('user_id') && $address_id){
				$address = $this->model('address')->get_one($address_id);
				if(!$address){
					$this->error(P_Lang('收件人信息不存在，请检查'));
				}
				if($address['user_id'] != $this->session->val('user_id')){
					$this->error(P_Lang('收件人信息与账号不匹配，请检查'));
				}
			}
			if(!isset($address) || !$address){
				$tmp = $this->form_address();
				if(!$tmp['status']){
					$this->error($tmp['info']);
				}
				$address = $tmp['info'];
			}
			if(!$address){
				$this->error(P_Lang('地址信息不完整'));
			}
			$mobile = $address['mobile'];
			$email = $address['email'];
		}
		//运费
		$shipping = 0;
		//产品价格
		$price = 0;
		
		$tmp = array('number'=>0,'weight'=>0,'volume'=>0,'price'=>0);
		$tmp_is_virtual = true;
		
		foreach($rslist as $key=>$value){
			$price += floatval($value['price']) * intval($value['qty']);
			if(!$value['is_virtual'] && $address && $address['province'] && $address['city']){
				$tmp_is_virtual = false;
				$tmp['number'] += intval($value['qty']);
				$tmp['weight'] += floatval($value['weight']) * intval($value['qty']);
				$tmp['volume'] += floatval($value['volume']) * intval($value['qty']);
			}
		}
		
		//计算运费
		if(!$tmp_is_virtual && $address && $address['province'] && $address['city']){
			$tmp['price'] = $price;
			$shipping = $this->model('cart')->freight_price($tmp,$address['province'],$address['city']);
		}
		$allprice = floatval($price) + floatval($shipping);
		$coupon = $this->_coupon($price);
		if($coupon){
			$allprice = floatval($price) + floatval($shipping) - floatval($coupon);
		}

		$sn = $this->model('order')->create_sn();
		$main = array('sn'=>$sn);
		$main['user_id'] = $user ? $user['id'] : 0;
		$main['addtime'] = $this->time;
		$main['price'] = $allprice;
		$main['currency_id'] = $this->site['currency_id'];
		$main['status'] = 'create';
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
			$tmp = array('order_id'=>$order_id);
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
		$pricelist = $this->model('site')->price_status_all();
		if($pricelist){
			foreach($pricelist as $key=>$value){
				$tmp_price = '0.00';
				if($key == 'product'){
					$tmp_price = $price;
				}elseif($key == 'shipping'){
					$tmp_price = $shipping;
				}elseif($key == 'discount' && $coupon){
					$tmp_price = -$coupon;
				}
				$tmp = array('order_id'=>$order_id,'code'=>$key,'price'=>$tmp_price);
				$this->model('order')->save_order_price($tmp);
			}
		}
		//删除购物车信息
		$this->data("cart_id",$this->cart_id);
		$this->nodes("PHPOK_cart_coupon");
		$coupon_rs = $this->data("cart_coupon");
		$this->data("order_id",$order_id);
		$this->data("price",$coupon);
		$this->nodes('PHPOK_coupon_to_history');

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
		//增加订单通知
		$param = 'id='.$order_id."&status=create";
		$this->model('task')->add_once('order',$param);
		$rs = array('sn'=>$sn,'passwd'=>$main['passwd'],'id'=>$order_id);
		$this->success($rs);
	}

	/**
	 * 优惠码功能
	**/
	private function _coupon($totalprice)
	{
		if(!$totalprice){
			return false;
		}
		$this->data("cart_id",$this->cart_id);
		$this->node('PHPOK_cart_coupon');
		$tmp = $this->data('cart_coupon');
		if(!$tmp){
			return false;
		}
		if($tmp['min_price'] > $totalprice){
			return false;
		}
		if(!$tmp['discount_type']){
			$tmp_price = round($totalprice * $tmp['discount_val'] / 100,2);
		}else{
			$tmp_price = $tmp['discount_val'];
		}
		return $tmp_price;
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
		$rs['price_paid'] = price_format_val($paid_price);
		$rs['price_unpaid'] = price_format_val($unpaid_price);
		$data = array('rs'=>$rs);
		$address = $this->model('order')->address($rs['id']);
		if($address){
			$data['address'] = $address;
		}
		$rslist = $this->model('order')->product_list($rs['id']);
		if($rslist){
			$data['rslist'] = $rslist;
			$qty = 0;
			foreach($rslist as $key=>$value){
				$qty += intval($value['qty']);
			}
			$rs['qty'] = $qty;
		}
		$rs['price_val'] = price_format($rs['price'],$rs['currency_id'],$rs['currency_id']);
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
		$this->success();
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