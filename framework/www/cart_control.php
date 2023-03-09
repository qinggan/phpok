<?php
/**
 * 购物车
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年08月17日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cart_control extends phpok_control
{
	/**
	 * 购物车ID，该ID将贯穿整个购物过程
	**/
	private $cart_id = 0;

	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
		$this->cart_id = $this->model('cart')->cart_id($this->session->sessid(),$this->session->val('user_id'));
	}

	/**
	 * 购物车内容，留空读取cart_tip模板信息提示
	**/
	public function index_f()
	{
		//取得购物车产品列表
		$rslist = $this->model('cart')->get_all($this->cart_id);
		if(!$rslist){
			$this->model('site')->site_id($this->site['id']);
			$tplfile = $this->model('site')->tpl_file($this->ctrl,'tip');
			if(!$tplfile){
				$tplfile = 'cart_tip';
			}
	    	$this->view($tplfile);
		}
		$this->assign("rslist",$rslist);
		$totalprice = 0;
		$_date = date("Ymd",$this->time);
		foreach($rslist as $key=>$value){
			$totalprice += $value['price_total'];
			$value['_checked'] = ($value['dateline'] && date("Ymd",$value['dateline']) == $_date) ? true : false;
			$rslist[$key] = $value;
		}
		$price = price_format($totalprice,$this->site['currency_id']);
		$this->assign('price',$price);
		$this->model('site')->site_id($this->site['id']);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'cart_index';
		}
		$this->view($tplfile);
	}

	/**
	 * 快速添加订单
	**/
	public function quick_f()
	{
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'cart_quick';
		}
		$this->view($tplfile);
	}

	/**
	 * 购物车产品加入成功后，跳转的页面
	 * @参数 $id 加入成功后返回的 qinggan_cart_product 表里的主键ID
	 * @参数 $product_id 产品，即 qinggan_list 表中的ID，在购物车里，统一叫产品ID
	**/
	public function success_f()
	{
		$product_id = $this->get('product_id','int');
		$id = $this->get('id','int');
		$this->assign('product_id',$product_id);
		$this->assign('id',$id);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'cart_success';
		}
		$this->view($tplfile);
	}

	/**
	 * 购物车结算页，生成订单并进行支付
	**/
	public function checkout_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!is_array($id)){
				$id = explode(",",$id);
			}
			foreach($id as $key=>$value){
				if(!$value || !trim($value) || !intval($value)){
					unset($id[$key]);
					continue;
				}
				$id[$key] = intval($value);
			}
			$this->assign('id',implode(",",$id));
		}
		//定义要结算的产品ID
		$rslist = $this->model('cart')->get_all($this->cart_id,$id);
		if(!$rslist){
			$this->error(P_Lang('您的购物车里没有任何产品'),$this->url);
		}
		if($this->session->val('user_id')){
			$user_rs = $this->model('user')->get_one($this->session->val('user_id'));
			$this->assign('user',$user_rs);
		}
		$totalprice = 0;
		foreach($rslist as $key=>$value){
			$totalprice += $value['price_total'];
		}
		$this->assign('product_price',price_format($totalprice,$this->site['currency_id']));
		$this->assign("rslist",$rslist);
		//检测购物车是否需要使用地址
		$is_virtual = true;
		foreach($rslist as $key=>$value){
			if(!$value['is_virtual']){
				$is_virtual = false;
			}
		}
		$this->assign('is_virtual',$is_virtual);

		if($is_virtual && $user_rs){
			$address = array('mobile'=>$user_rs['mobile'],'email'=>$user_rs['email']);
			$this->assign('address',$address);
		}
		if(!$is_virtual){
			$this->_address();
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
					$value['price'] = price_format($totalprice,$this->site['currency_id']);
					$value['price_val'] = $totalprice;
				}
				if($value['identifier'] == 'shipping'){
					if($is_virtual){
						unset($pricelist[$key]);
						continue;
					}
					if($this->tpl->val('address')){
						$freight_price = $this->_freight();
						if($freight_price){
							$value['price'] = price_format($freight_price,$this->site['currency_id']);
							$value['price_val'] = $freight_price;
						}
					}
				}
				if($value['identifier'] == 'discount'){
					$this->data("cart_id",$this->cart_id);
					$this->data('cart_ids',$id);
					$this->node('PHPOK_cart_coupon');
					$tmp = $this->data('cart_coupon');
					if($tmp){
						$value['price'] = price_format(-$tmp['price'],$this->site['currency_id']);
						$value['price_val'] = -$tmp['price'];
					}else{
						$value['price'] = price_format('0',$this->site['currency_id']);
						$vlaue['price_val'] = '0.00';
					}
					$discount = -$tmp['price'];
					$this->assign('coupon_code',$tmp['code']);
					$this->assign('coupon_title',$tmp['title']);
				}
				$pricelist[$key] = $value;
			}
		}
		foreach($pricelist as $key=>$value){
			if($value['hidden'] && (!$value['price_val'] || $value['price_val'] == '0.00')){
				unset($pricelist[$key]);
			}
		}
		$this->assign('pricelist',$pricelist);
		if($freight_price){
			$price = price_format(($totalprice+$freight_price+$discount),$this->site['currency_id']);
			$price_val = price_format_val(($totalprice+$freight_price+$discount),$this->site['currency_id']);
		}else{
			$price = price_format($totalprice+$discount,$this->site['currency_id']);
			$price_val = price_format_val($totalprice+$discount,$this->site['currency_id']);
		}
		$this->assign('price',$price);
		$this->assign('price_val',$price_val);
		//支付方式
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$weixin_client = false;
		$miniprogram_client = false;
		if($user_agent && strpos(strtolower($user_agent),'micromessenger') !== false){
			$weixin_client = true;
		}
		if($user_agent && strpos(strtolower($user_agent),'miniprogram') !== false){
			$miniprogram_client = true;
		}
		$is_mobile = ($this->is_mobile() || $weixin_client || $miniprogram_client) ? true : false;
		$paylist = $this->model('payment')->get_all($this->site['id'],1,$is_mobile);
		if($paylist){
			foreach($paylist as $key=>$value){
				if(!$value['paylist']){
					unset($paylist[$key]);
					continue;
				}
				if($weixin_client || $miniprogram_client){
					foreach($value['paylist'] as $k=>$v){
						if($v['code'] != 'wxpay'){
							unset($value['paylist'][$k]);
							continue;
						}
						$t = array();
						if($v['param'] && is_string($v['param'])){
							$t = unserialize($v['param']);
						}
						if($miniprogram_client && $t['trade_type'] != 'miniprogram'){
							unset($value['paylist'][$k]);
							continue;
						}
						if(!$miniprogram_client && $t['trade_type'] == 'miniprogram'){
							unset($value['paylist'][$k]);
							continue;
						}
					}
					$paylist[$key] = $value;
				}
			}
			$this->assign('paylist',$paylist);
		}
		if($this->session->val('user_id')){
			$wlist = $this->model('order')->balance($this->session->val('user_id'));
			if($wlist){
				if($wlist['balance']){
					$this->assign('balance',$wlist['balance']);
				}
				if($wlist['integral']){
					$this->assign('integral',$wlist['integral']);
				}
			}
		}
		$this->model('site')->site_id($this->site['id']);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'cart_checkout';
		}
		$this->view($tplfile);
	}

	public function review_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!is_array($id)){
				$id = explode(",",$id);
			}
			foreach($id as $key=>$value){
				if(!$value || !trim($value) || !intval($value)){
					unset($id[$key]);
				}
				$id[$key] = intval($value);
			}
			$this->assign('id',implode(",",$id));
		}
		//定义要结算的产品ID
		$rslist = $this->model('cart')->get_all($this->cart_id,$id);
		if(!$rslist){
			$this->error(P_Lang('您的购物车里没有任何产品'),$this->url);
		}
		if($this->session->val('user_id')){
			$user_rs = $this->model('user')->get_one($this->session->val('user_id'));
			$this->assign('user',$user_rs);
		}
		$totalprice = 0;
		$qty = 0;
		foreach($rslist as $key=>$value){
			$totalprice += $value['price_total'];
			$qty += $value['qty'];
		}
		$this->assign('product_price',price_format($totalprice,$this->site['currency_id']));
		$this->assign("rslist",$rslist);
		$this->assign('qty',$qty);
		//检测购物车是否需要使用地址
		$is_virtual = true;
		foreach($rslist as $key=>$value){
			if(!$value['is_virtual']){
				$is_virtual = false;
			}
		}
		$this->assign('is_virtual',$is_virtual);

		if($is_virtual && $user_rs){
			$address = array('mobile'=>$user_rs['mobile'],'email'=>$user_rs['email']);
			$this->assign('address',$address);
		}
		if(!$is_virtual){
			$this->_address();
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
					$value['price'] = price_format($totalprice,$this->site['currency_id']);
					$value['price_val'] = $totalprice;
					$pricelist[$key] = $value;
				}
				if($value['identifier'] == 'shipping'){
					if($is_virtual){
						unset($pricelist[$key]);
						continue;
					}
					if($this->tpl->val('address')){
						$freight_price = $this->_freight();
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
		}
		foreach($pricelist as $key=>$value){
			if($value['hidden'] && (!$value['price_val'] || $value['price_val'] == '0.00')){
				unset($pricelist[$key]);
			}
		}
		$this->assign('pricelist',$pricelist);
		if($freight_price){
			$price = price_format(($totalprice+$freight_price+$discount),$this->site['currency_id']);
			$price_val = price_format_val(($totalprice+$freight_price+$discount),$this->site['currency_id']);
		}else{
			$price = price_format($totalprice+$discount,$this->site['currency_id']);
			$price_val = price_format_val($totalprice+$discount,$this->site['currency_id']);
		}
		$this->assign('price',$price);
		$this->assign('price_val',$price_val);
		//支付方式
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$weixin_client = false;
		$miniprogram_client = false;
		if($user_agent && strpos(strtolower($user_agent),'micromessenger') !== false){
			$weixin_client = true;
		}
		if($user_agent && strpos(strtolower($user_agent),'miniprogram') !== false){
			$miniprogram_client = true;
		}
		$is_mobile = ($this->is_mobile() || $weixin_client || $miniprogram_client) ? true : false;
		$paylist = $this->model('payment')->get_all($this->site['id'],1,$is_mobile);
		if($paylist){
			foreach($paylist as $key=>$value){
				if(!$value['paylist']){
					unset($paylist[$key]);
					continue;
				}
				if($weixin_client || $miniprogram_client){
					foreach($value['paylist'] as $k=>$v){
						if($v['code'] != 'wxpay'){
							unset($value['paylist'][$k]);
							continue;
						}
						$t = array();
						if($v['param'] && is_string($v['param'])){
							$t = unserialize($v['param']);
						}
						if($miniprogram_client && $t['trade_type'] != 'miniprogram'){
							unset($value['paylist'][$k]);
							continue;
						}
						if(!$miniprogram_client && $t['trade_type'] == 'miniprogram'){
							unset($value['paylist'][$k]);
							continue;
						}
					}
					$paylist[$key] = $value;
				}
			}
			$this->assign('paylist',$paylist);
		}
		if($this->session->val('user_id')){
			$wlist = $this->model('order')->balance($this->session->val('user_id'));
			if($wlist){
				if($wlist['balance']){
					$this->assign('balance',$wlist['balance']);
				}
				if($wlist['integral']){
					$this->assign('integral',$wlist['integral']);
				}
			}
		}

		$email = $this->get('email');
		if($email){
			$this->session->assign('cart_email',$email);
			$this->assign('email',$email);
		}
		$address = $this->addr_info();
		if($address){
			$this->session->assign('cart_address',$address);
		}
		if(count($address)>1){
			$same_as_shipping = $this->get('same_as_shipping','int');
			$this->session->assign('cart_same_as_shipping',$same_as_shipping);
			$this->assign('cart_same_as_shipping',$same_as_shipping);
		}
		$this->assign('address',$address);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'cart_review';
		}
		$this->view($tplfile);
	}

	public function confirm_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!is_array($id)){
				$id = explode(",",$id);
			}
			foreach($id as $key=>$value){
				if(!$value || !trim($value) || !intval($value)){
					unset($id[$key]);
				}
				$id[$key] = intval($value);
			}
			$this->assign('id',implode(",",$id));
		}
		//定义要结算的产品ID
		$rslist = $this->model('cart')->get_all($this->cart_id,$id);
		if(!$rslist){
			$this->error(P_Lang('您的购物车里没有任何产品'),$this->url);
		}
		if($this->session->val('user_id')){
			$user_rs = $this->model('user')->get_one($this->session->val('user_id'));
			$this->assign('user',$user_rs);
		}
		$totalprice = 0;
		$qty = 0;
		foreach($rslist as $key=>$value){
			$totalprice += price_format_val($value['price'] * $value['qty']);
			$qty += $value['qty'];
		}
		$this->assign('product_price',price_format($totalprice,$this->site['currency_id']));
		$this->assign("rslist",$rslist);
		$this->assign('qty',$qty);
		//检测购物车是否需要使用地址
		$is_virtual = true;
		foreach($rslist as $key=>$value){
			if(!$value['is_virtual']){
				$is_virtual = false;
			}
		}
		$this->assign('is_virtual',$is_virtual);

		if($is_virtual && $user_rs){
			$address = array('mobile'=>$user_rs['mobile'],'email'=>$user_rs['email']);
			$this->assign('address',$address);
		}
		if(!$is_virtual){
			$this->_address();
		}
		$pricelist = $this->model('site')->price_status_all(true);
		$discount = 0;
		if($pricelist){
			foreach($pricelist as $key=>$value){
				if(!$value['status']){
					unset($pricelist[$key]);
					continue;
				}
				if($value['default']){
					$value['price'] = price_format($value['default'],$this->site['currency_id']);
					$value['price_val'] = $value['default'];
				}
				if($value['identifier'] == 'product'){
					$value['price'] = price_format($totalprice,$this->site['currency_id']);
					$value['price_val'] = $totalprice;
				}
				if($value['identifier'] == 'shipping'){
					if($is_virtual){
						unset($pricelist[$key]);
						continue;
					}
					if($this->tpl->val('address')){
						$freight_price = $this->_freight();
						if(!$freight_price && !$value['default']){
							unset($pricelist[$key]);
							continue;
						}
						if($freight_price){
							$value['price'] = price_format($freight_price,$this->site['currency_id']);
							$value['price_val'] = $freight_price;
						}
					}else{
						if(!$value['default']){
							unset($pricelist[$key]);
							continue;
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
					if($tmp['min_price'] > $totalprice){
						unset($pricelist[$key]);
						continue;
					}
					if(!$tmp['discount_type']){
						$tmp_price = round($totalprice * $tmp['discount_val'] / 100,2);
					}else{
						$tmp_price = $tmp['discount_val'];
					}
					$value['price'] = price_format(-$tmp_price,$this->site['currency_id']);
					$value['price_val'] = -$tmp_price;
					$discount = -$tmp_price;
					$this->assign('coupon_code',$tmp['code']);
				}
				$pricelist[$key] = $value;
			}
		}
		foreach($pricelist as $key=>$value){
			if($value['hidden'] && (!$value['price_val'] || $value['price_val'] == '0.00')){
				unset($pricelist[$key]);
			}
		}
		$this->assign('pricelist',$pricelist);
		if($freight_price){
			$price = price_format(($totalprice+$freight_price+$discount),$this->site['currency_id']);
			$price_val = price_format_val(($totalprice+$freight_price+$discount),$this->site['currency_id']);
		}else{
			$price = price_format($totalprice+$discount,$this->site['currency_id']);
			$price_val = price_format_val($totalprice+$discount,$this->site['currency_id']);
		}
		$this->assign('price',$price);
		$this->assign('price_val',$price_val);
		//支付方式
		//支付方式
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$weixin_client = false;
		$miniprogram_client = false;
		if($user_agent && strpos(strtolower($user_agent),'micromessenger') !== false){
			$weixin_client = true;
		}
		if($user_agent && strpos(strtolower($user_agent),'miniprogram') !== false){
			$miniprogram_client = true;
		}
		$is_mobile = ($this->is_mobile() || $weixin_client || $miniprogram_client) ? true : false;
		$paylist = $this->model('payment')->get_all($this->site['id'],1,$is_mobile);
		$array = array('type'=>'order','price'=>price_format_val($price_unpaid,$rs['currency_id'],$rs['currency_id']),'currency_id'=>$rs['currency_id'],'sn'=>$rs['sn']);
		$array['content'] = $array['title'] = P_Lang('订单：{sn}',array('sn'=>$rs['sn']));
		$array['dateline'] = $this->time;
		$array['user_id'] = $this->session->val('user_id');
		$this->model('payment')->log_delete_notstatus($rs['sn'],'order');
		$insert_id = $this->model('payment')->log_create($array);
		$log = $array;
		$log['id'] = $insert_id;
		$this->assign('log',$log);
		foreach($paylist as $key=>$value){
			if(!$value['paylist']){
				unset($paylist[$key]);
				continue;
			}
			foreach($value['paylist'] as $k=>$v){
				if($v['param'] && is_string($v['param'])){
					$v['param'] = unserialize($v['param']);
				}
				if(!file_exists($this->dir_gateway.'payment/'.$v['code'].'/submit.php')){
					unset($value['paylist'][$k]);
					continue;
				}
				$this->assign('payment',$v);
				include_once($this->dir_gateway.'payment/'.$v['code'].'/submit.php');
				$name = $v['code'].'_submit';
				$obj = new $name($log,$v);
				$tmp = $obj->select();
				$v['html'] = $tmp;
				$value['paylist'][$k] = $v;
			}
			$paylist[$key] = $value;
		}
		$this->assign("paylist",$paylist);
		if($this->session->val('user_id')){
			$wlist = $this->model('order')->balance($this->session->val('user_id'));
			if($wlist){
				if($wlist['balance']){
					$this->assign('balance',$wlist['balance']);
				}
				if($wlist['integral']){
					$this->assign('integral',$wlist['integral']);
				}
			}
		}

		$email = $this->get('email');
		if($email){
			$this->session->assign('cart_email',$email);
			$this->assign('email',$email);
		}
		$address = $this->addr_info();
		if($address){
			$this->session->assign('cart_address',$address);
		}
		if(count($address)>1){
			$same_as_shipping = $this->get('same_as_shipping','int');
			$this->session->assign('cart_same_as_shipping',$same_as_shipping);
			$this->assign('cart_same_as_shipping',$same_as_shipping);
		}
		$this->assign('address',$address);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'cart_confirm';
		}
		$this->view($tplfile);
	}

	private function addr_info()
	{
		$addressconfig = $this->config['order']['address'] ? explode(",",$this->config['order']['address']) : array('shipping');
		$address = array();
		foreach($addressconfig as $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			$array = array();
			$array['type'] = $value;
			$array['country'] = $this->get($value."-country");
			$array['province'] = $this->get($value."-province");
			$array['city'] = $this->get($value."-city");
			$array['county'] = $this->get($value."-county");
			$array['address'] = $this->get($value."-address");
			$array['address2'] = $this->get($value."-address2");
			$array['mobile'] = $this->get($value."-mobile");
			$array['tel'] = $this->get($value."-tel");
			$array['email'] = $this->get($value."-email");
			$array['fullname'] = $this->get($value."-fullname");
			$array['firstname'] = $this->get($value."-firstname");
			$array['lastname'] = $this->get($value."-lastname");
			$array['zipcode'] = $this->get($value."-zipcode");
			$array['order_id'] = $order_id;
			if($array['fullname'] || $array['firstname']){
				$address[$value] = $array;
			}
		}
		return $address;
	}

	/**
	 * 用户购买商品最后填写的地址
	**/
	private function _address()
	{
		if(!$this->session->val('user_id')){
			$this->assign('pca_rs',form_edit('pca','','pca'));
			return true;
		}
		$condition = "a.user_id='".$this->session->val('user_id')."'";
		$addresslist = $this->model('address')->get_list($condition,0,30);
		if($addresslist){
			$first = $address_id = 0;
			foreach($addresslist as $key=>$value){
				if($key<1){
					$first = $value['id'];
				}
				if($value['is_default']){
					$address_id = $value['id'];
					break;
				}
			}
			if(!$address_id && $first){
				$address_id = $first;
			}
			$this->assign('address_id',$address_id);
			$this->assign('address_list',$addresslist);
		}
	}

	/**
	 * 计算运费
	 * @参数 $rslist 购物车里的产品列表
	 * @参数 $address 数组，地址
	 * @返回 false 或 运费，未格式化
	**/
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
		return $this->model('cart')->freight_price($data,$address['province'],$address['city']);
	}

	public function price_f()
	{
		$is_virtual = true;
		$rslist = $this->model('cart')->get_all($this->cart_id);
		if(!$rslist){
			$this->error(P_Lang('购物车是空的'));
		}
		$province = $this->get('province');
		$city = $this->get('city');
		$freight_price = $product_price = 0;
		foreach($rslist AS $key=>$value){
			if(!$value['is_virtual']){
				$is_virtual = false;
			}
			$product_price += price_format_val($value['price'] * $value['qty']);
		}
		if($province && $city && !$is_virtual){
			$address = array('province'=>$province,'city'=>$city);
			$freight_price = $this->_freight($rslist,$address);
		}
		$pricelist = $this->model('site')->price_status_all();
		$price = $product_price;
		if($pricelist){
			foreach($pricelist as $key=>$value){
				if(!$value['status'] || $key == 'discount'){
					unset($pricelist[$key]);
					continue;
				}
				if($value['default']){
					$value['price'] = price_format($value['default'],$this->site['currency_id']);
					$value['price_val'] = $value['default'];
				}
				if($key == 'product'){
					$value['price'] = price_format($product_price);
					$value['price_val'] = $product_price;
				}
				if($key == 'shipping'){
					if($is_virtual){
						unset($pricelist[$key]);
						continue;
					}
					if($freight_price){
						$value['price'] = price_format($freight_price,$this->site['currency_id']);
						$value['price_val'] = $freight_price;
						$price += $freight_price;
					}else{
						if($value['default']){
							$price += $value['default'];
						}
					}
				}
				$pricelist[$key] = $value;
			}
		}
		foreach($pricelist as $key=>$value){
			if($value['hidden'] && (!$value['price_val'] || $value['price_val'] == '0.00')){
				unset($pricelist[$key]);
			}
		}
		$data = array('pricelist'=>$pricelist,'price'=>price_format($price));
		$this->success($data);
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

	public function address_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此功能'));
		}
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('user')->address_one($id);
			if(!$rs || $rs['user_id'] != $_SESSION['user_id']){
				$this->error(P_Lang('地址信息不存在或您没有权限修改此地址'));
			}
			$this->assign('id',$id);
			$this->assign('rs',$rs);
		}else{
			$rs = array();
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'cart_address';
		}
		$this->view($tplfile);
	}
}