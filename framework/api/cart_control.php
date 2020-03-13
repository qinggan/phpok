<?php
/**
 * 购物车接口请求相关
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年08月19日
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
		$this->config('is_ajax',true);
	}

	/**
	 * 取得购物车列表
	**/
	public function index_f()
	{
		$rslist = $this->model('cart')->get_all($this->cart_id);
		if(!$rslist){
			$this->success('empty');
		}
		$totalprice = 0;
		$_date = date("Ymd",$this->time);
		foreach($rslist as $key=>$value){
			$totalprice += price_format_val($value['price'] * $value['qty'],$this->site['currency_id']);
			$value['_checked'] = ($value['dateline'] && date("Ymd",$value['dateline']) == $_date) ? true : false;
			$value['price_val'] = price_format_val($value['price']);
			$value['price_txt'] = price_format($value['price']);
			$value['price_total'] = price_format($value['price']*$value['qty']);
			$rslist[$key] = $value;
		}
		$price = price_format_val($totalprice,$this->site['currency_id']);
		$price_txt = price_format($totalprice,$this->site['currency_id']);
		$data = array('rslist'=>$rslist,'price'=>$price,'price_txt'=>$price_txt);
		$this->success($data);
	}


	/**
	 * 加入购物车
	 * @参数 id 产品ID
	 * @参数 title 产品名称（当产品ID不存在时）
	 * @参数 qty 产品数量，仅支持数字
	 * @参数 ext 产品性属，仅有id时有效，只支持数字
	 * @参数 price 产品价格，仅当id为空时有效
	 * @参数 thumb 产品缩略图，仅当id为空时有效
	 * @参数 _clear 设为1表示立即订购
	 * @返回 JSON数据
	 * @更新时间 2018年04月22日
	**/
	public function add_f()
	{
		if(!$this->cart_id){
			$this->error(P_Lang('购物车编号异常'));
		}
		$clear = $this->get('_clear','int');
		if($clear){
			$this->model('cart')->clear_cart($this->cart_id);
		}
		$id = $this->get('id','int');
		$qty = $this->get('qty','int');
		if(!$qty || $qty<0){
			$qty = 1;
		}
		$array = $id ? $this->product_from_tid($id) : $this->product_from_title();
		$rslist = $this->model('cart')->get_all($this->cart_id);
		$updateid = $total = 0;
		if(!$rslist){
			$rslist = array();
		}
		foreach($rslist as $key=>$value){
			if($id){
				if($value['tid'] == $id && $this->model('cart')->product_ext_compare($array['ext'],$value['ext'])){
					$updateid = $value['id'];
					$total = $value['qty'];
				}
			}else{
				if($value['title'] == $array['title'] && $array['price'] == $value['price'] && $this->model('cart')->product_ext_compare($array['ext'],$value['ext'])){
					$updateid = $value['id'];
					$total = $value['qty'];
				}
			}
		}
		if($updateid){
			$this->model('cart')->update($updateid,($total+$qty));
			$insert_id = $updateid;
		}else{
			$array['qty'] = $qty;
			$insert_id = $this->model('cart')->add($array);
		}
		//判断是否有捆绑销售的产品ID
		$bundle = $this->get('bundle');
		if($bundle){
			$this->bundle_save($id,$bundle,$qty);
		}
		$this->success($insert_id);
	}

	/**
	 * 保存捆绑销售
	**/
	private function bundle_save($id,$bundle,$qty=0)
	{
		$sublist = $this->model('cart')->sub_all($id);
		$list = explode(",",$bundle);
		foreach($list as $key=>$value){
			if(!$value || !trim($value) || !intval($value)){
				continue;
			}
			$bundle_qty = $this->get('qty'.$value);
			if(!$bundle_qty){
				$bundle_qty = $qty;
			}
			$tmparray = $this->product_from_tid($value,$bundle_qty,true);
			if(!$tmparray){
				continue;
			}
			$tmparray['parent_id'] = $id;
			$tmparray['qty'] = $bundle_qty;
			if($sublist){
				foreach($sublist as $k=>$v){
					if($v['tid'] == $value){
						$this->model('cart')->update($v['id'],($v['qty']+$bundle_qty));
					}else{
						$this->model('cart')->add($tmparray);
					}
				}
			}else{
				$this->model('cart')->add($tmparray);
			}
		}
	}

	private function product_from_title()
	{
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('产品名称不能为空'));
		}
		$array = array('cart_id'=>$this->cart_id);
		$array['title'] = $title;
		$array['price'] = $this->get('price','float');
		$array['tid'] = 0;
		$array['weight'] = $this->get('weight','float');
		$array['volume'] = $this->get('volume','float');
		$ext = $this->get('ext');
		if($ext && is_array($ext)){
			sort($ext);
		}
		$array['ext'] = $ext;
		$array['thumb'] = $this->get('thumb');
		$array['is_virtual'] = $this->get('is_virtual','int');
		return $array;
	}

	/**
	 * 产品数据从id传过来的值生成
	 * @参数 $id 产品ID
	 * @参数 $qty 数量
	 * @参数 $bundle 捆绑商品ID
	**/
	private function product_from_tid($id,$qty=0,$bundle=false)
	{
		$array = array('tid'=>$id,'cart_id'=>$this->cart_id);
		$rs = $this->call->phpok('_arc',array('site'=>$this->site['id'],'title_id'=>$id));		
		if(!$rs){
			$this->error(P_Lang('产品信息不存在或未启用'));
		}
		$thumb_id = $this->config['cart']['thumb_id'] ? $this->config['cart']['thumb_id'] : 'thumb';
		if($rs[$thumb_id]){
			if(is_string($rs[$thumb_id])){
				$array['thumb'] = $rs[$thumb_id];
			}
			if(is_array($rs[$thumb_id])){
				$array['thumb'] = $rs[$thumb_id]['filename'];
			}
		}
		$array['title'] = $rs['title'];
		$array['is_virtual'] = $rs['is_virtual'];
		$array['unit'] = $rs['unit'];
		$ext = $this->get('ext');
		if($bundle){
			$ext = $this->get('ext'.$id);
		}
		$int = false;
		if($ext && (is_string($ext) || is_int($ext))){
			$int = true;
			$ext = explode(",",$ext);
			foreach($ext as $key=>$value){
				$value = intval($value);
				if(!$value){
					unset($ext[$key]);
					continue;
				}
				$ext[$key] = $value;
			}
		}
		if($ext && is_array($ext)){
			sort($ext);
		}
		$price = $rs['price'];
		//基于Apps扩展的
		if($rs['apps']){
			$tmp_apps = array();
			foreach($rs['apps'] as $key=>$value){
				if(!$value['list']){
					continue;
				}
				$tmp_id = $this->get($key.'_id','int');
				if(!$tmp_id){
					$tmp_id = $value['rs']['id'];
				}
				foreach($value['list'] as $k=>$v){
					if($tmp_id != $v['id'] || !$v['price']){
						continue;
					}
					if($v['price']){
						$price = $v['price'];
						$tmp_apps[$key] = $tmp_id;
						break;
					}
				}
			}
			if($tmp_apps){
				$tmp = array();
				foreach($tmp_apps as $key=>$value){
					$tmp[] = $key.':'.$value;
				}
				$array['apps'] = implode(",",$tmp);
				unset($tmp_apps,$tmp);
			}
		}
		$price = price_format_val($price,$rs['currency_id'],$this->site['currency_id']);
		$weight = $rs['weight'];
		$volume = $rs['volume'];
		if($ext && $rs['attrlist'] && $int){
			foreach($rs['attrlist'] as $key=>$value){
				foreach($value['rslist'] as $k=>$v){
					if(in_array($v['id'],$ext)){
						$price = floatval($price) + price_format_val($v['price'],$rs['currency_id'],$this->site['currency_id']);
						$weight = floatval($weight) + floatval($v['weight']);
						$volume = floatval($volume) + floatval($v['volume']);
					}
				}
			}
		}
		$array['ext'] = $ext;
		$array['price'] = $price;
		$array['weight'] = $weight;
		$array['volume'] = $volume;
		return $array;
	}

	/**
	 * 获取购物车里的产品总数
	**/
	public function total_f()
	{
		$total = $this->model('cart')->total($this->cart_id);
		$this->success($total);
	}

	public function clear_f()
	{
		$this->model('cart')->clear_cart($this->cart_id);
		$this->success();
	}

	/**
	 * 更新购物车里的产品数量
	 * @参数 id 购物车里的产品ID，即表（cart_product）的主键id，不是产品ID
	 * @参数 qty 更新购物车数量，不能小于1
	 * @返回 
	 * @更新时间 
	**/
	public function qty_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定产品ID'));
		}
		$rs = $this->model('cart')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('产品不存在'));
		}
		if($rs['cart_id'] != $this->cart_id){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$qty = $this->get('qty');
		if(!$qty || $qty<0){
			$qty = $rs['qty'];
		}
		$this->model('cart')->update($id,$qty);
		$rs["qty"] = $qty;
		$this->success($rs);
	}

	/**
	 * 删除购物车产品数据
	 * @参数 id 购物车里的产品ID，即表（cart_product）的主键id，不是产品ID
	 * @返回 JSON数据
	**/
	public function delete_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定产品ID'));
		}
		if(is_string($id)){
			$list = explode(",",$id);
			unset($id);
			$id = array();
			foreach($list as $key=>$value){
				if($value && intval($value)){
					$id[] = intval($value);
				}
			}
		}
		if($id && is_array($id)){
			foreach($id as $key=>$value){
				if(!$value || !intval($value)){
					unset($id[$key]);
				}
			}
		}
		if(!$id || count($id)<1){
			$this->error(P_Lang('没有有效的产品ID'));
		}
		foreach($id as $key=>$value){
			$rs = $this->model('cart')->get_one($value);
			if(!$rs){
				$this->error(P_Lang('产品不存在'));
			}
			if($rs['cart_id'] != $this->cart_id){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$this->model('cart')->delete_product($value);
		}
		$this->success();
	}

	/**
	 * 格式化价格，带货币符号
	 * @参数 price 单个价格或多个价格
	 * @返回 返回字符串（单个价格）或数组（多个价格）
	 * @更新时间 2016年09月01日
	**/
	public function price_format_f()
	{
		$price = $this->get('price','float');
		if(is_array($price)){
			$list = array();
			foreach($price as $key=>$value){
				$list[$key] = price_format($value,$this->site['currency_id']);
			}
			$this->success($list);
		}
		$this->success(price_format($price,$this->site['currency_id']));
	}

	/**
	 * 格式化价格，无货币符号
	 * @参数 price 单个价格或多个价格
	 * @返回 返回字符串（单个价格）或数组（多个价格）
	 * @更新时间 2016年09月01日
	**/
	public function price_format_val_f()
	{
		$price = $this->get('price','float');
		if(is_array($price)){
			$list = array();
			foreach($price as $key=>$value){
				$list[$key] = price_format_val($value,$this->site['currency_id']);
			}
			$this->success($list);
		}
		$this->success(price_format_val($price,$this->site['currency_id']));
	}

	public function price_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定要计算的产品ID'));
		}
		if(is_string($id)){
			$id = explode(",",$id);
		}
		if($id && is_array($id)){
			foreach($id as $key=>$value){
				if(!$value || !intval($value)){
					unset($id[$key]);
				}
			}
		}
		if(!$id || count($id)<1){
			$this->error(P_Lang('没有有效的产品ID'));
		}
		$price = 0;
		$rslist = $this->model('cart')->get_all($this->cart_id);
		if(!$rslist){
			$this->error(P_Lang('购物车信息为空'));
		}
		foreach($rslist as $key=>$value){
			if(in_array($value['id'],$id)){
				$price += price_format_val($value['price'] * $value['qty'],$this->site['currency_id']);
			}
		}
		$show = price_format($price,$this->site['currency_id']);
		$val = price_format_val($price,$this->site['currency_id']);
		$this->success(array('val'=>$val,'price'=>$show));
	}

	public function pricelist_f()
	{
		$id = $this->get('id');
		if(!$id){
			$id = $this->get('ids');
			if($id && !is_array($id)){
				$id = explode(",",$id);
			}
		}
		if($id && !is_array($id)){
			$id = explode(",",$id);
		}
		if($id){
			foreach($id as $key=>$value){
				if(!$value || !trim($value) || !intval($value)){
					unset($id[$key]);
				}
			}
		}
		$rslist = $this->model('cart')->get_all($this->cart_id,$id);	
		if(!$rslist){
			$this->error(P_Lang('未找到产品信息'));
		}
		$address_id = $this->get('address_id','int');
		if(!$address_id){
			$province = $this->get('province');
			$city = $this->get('city');
		}else{
			$address = $this->model('address')->get_one($address_id);
			if(!$address){
				$this->error(P_Lang('地址信息不存在'));
			}
			if(!$this->session->val('user_id')){
				$this->error(P_Lang('非会员没有地址库功能'));
			}
			if($address['user_id'] != $this->session->val('user_id')){
				$this->error(P_Lang('这不是您的地址，没有权限查阅及调用'));
			}
			$province =  $address['province'];
			$city = $address['city'];
		}
		$freight_price = 0;
		$discount = 0;
		$is_virtual = true;
		$totalprice = 0;
		$tmp = array('number'=>0,'weight'=>0,'volume'=>0,'price'=>0);
		foreach($rslist as $key=>$value){
			$totalprice += price_format_val($value['price'] * $value['qty']);
			if(!$value['is_virtual']){
				$is_virtual = false;
				$tmp['number'] += intval($value['qty']);
				$tmp['weight'] += floatval($value['weight']) * intval($value['qty']);
				$tmp['volume'] += floatval($value['volume']) * intval($value['qty']);
			}
		}
		if($province && $city){
			$tmp['price'] = $totalprice;
			$freight_price = $this->model('cart')->freight_price($tmp,$province,$city);
		}
		$pricelist = $this->model('site')->price_status_all(true);
		if(!$pricelist){
			$this->error(P_Lang('未实现价格组功能'));
		}
		foreach($pricelist as $key=>$value){
			if(!$value['status']){
				unset($pricelist[$key]);
				continue;
			}
			$value['price_val'] = '0.00';
			if($value['default']){
				$value['price'] = price_format($value['default'],$this->site['currency_id']);
				$value['price_val'] = $value['default'];
			}
			if($value['identifier'] == 'product'){
				$value['price'] = price_format($totalprice,$this->site['currency_id']);
				$value['price_val'] = $totalprice;
				$pricelist[$key] = $value;
				continue;
			}
			if($value['identifier'] == 'shipping'){
				if($is_virtual || (!$freight_price && !$value['default'])){
					unset($pricelist[$key]);
					continue;
				}
				if($freight_price){
					$value['price'] = price_format($freight_price,$this->site['currency_id']);
					$value['price_val'] = $freight_price;
				}
				$pricelist[$key] = $value;
				continue;
			}
			if($value['identifier'] == 'discount'){
				//增加优惠码的节点
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
				$pricelist[$key] = $value;
				continue;
			}
			$pricelist[$key] = $value;
		}
		$this->success($pricelist);
	}
	
	public function checkout_f()
	{
		$r = array();
		$id = $this->get('id');
		if($id){
			if($id && !is_array($id)){
				$id = explode(",",$id);
			}
			foreach($id as $key=>$value){
				if(!$value || !trim($value) || !intval($value)){
					unset($id[$key]);
					continue;
				}
				$id[$key] = intval($value);
			}
		}
		//定义要结算的产品ID
		$r['id'] = implode(",",$id);
		$rslist = $this->model('cart')->get_all($this->cart_id,$id);
		if(!$rslist){
			$this->error(P_Lang('您的购物车里没有任何产品'));
		}
		if($this->session->val('user_id')){
			$user_rs = $this->model('user')->get_one($this->session->val('user_id'));
			$r['user'] = $user_rs;
		}
		$totalprice = 0;
		foreach($rslist as $key=>$value){
			$totalprice += price_format_val($value['price'] * $value['qty']);
		}
		
		$r['product_price'] = price_format_val($totalprice,$this->site['currency_id']);
		$r['rslist'] = $rslist;
	
		//检测购物车是否需要使用地址，及计算运费
		$is_virtual = true;
		foreach($rslist as $key=>$value){
			if(!$value['is_virtual']){
				$is_virtual = false;
				break;
			}
		}
		$r['is_virtual'] = $is_virtual;

		
		if($is_virtual && $user_rs){
			$address = array('mobile'=>$user_rs['mobile'],'email'=>$user_rs['email']);
			$r['address'] = $address;
		}
		if(!$is_virtual){
			$tmp = $this->_address();
			if($tmp){
				$r['address'] = $tmp['address'];
			}
		}
		$freight_price = 0;
		if(!$is_virtual && $r['address']){
			$tmp = array('number'=>0,'weight'=>0,'volume'=>0,'price'=>0);
			foreach($rslist as $key=>$value){
				if(!$value['is_virtual'] && $r['address']['province'] && $r['address']['city']){
					$tmp['number'] += intval($value['qty']);
					$tmp['weight'] += floatval($value['weight']) * intval($value['qty']);
					$tmp['volume'] += floatval($value['volume']) * intval($value['qty']);
				}
			}
			//计算运费
			if($r['address']['province'] && $r['address']['city']){
				$tmp['price'] = $totalprice;
				$freight_price = $this->model('cart')->freight_price($tmp,$r['address']['province'],$r['address']['city']);
			}
		}
		$r['shipping'] = $freight_price;
		
		$pricelist = $this->model('site')->price_status_all(true);
		$discount = $tax = 0;
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
				//税收计算
				if($value['identifier'] == 'tax' && $this->session->val('tax')){
					$value['price'] = price_format($this->session->val('tax'),$this->site['currency_id']);
					$value['price_val'] = $this->session->val('tax');
					$tax = $this->session->val('tax');
				}
				if($value['identifier'] == 'shipping'){
					if($is_virtual || (!$freight_price && !$value['default'])){
						unset($pricelist[$key]);
						continue;
					}
					if($freight_price){
						$value['price'] = price_format($freight_price,$this->site['currency_id']);
						$value['price_val'] = $freight_price;
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
					$pricelist[$key] = $value;
					$tmp['price'] = price_format($tmp_price,$this->site['currency_id']);
					$tmp['price_val'] = $tmp_price;
					$r['discount'] = $tmp;
					unset($tmp);
				}
				$pricelist[$key] = $value;
			}
		}
		$r['pricelist'] = $pricelist;
		if($freight_price){
			$price = price_format(($totalprice+$freight_price+$discount+$tax),$this->site['currency_id']);
			$price_val = price_format_val(($totalprice+$freight_price+$discount+$tax),$this->site['currency_id']);
		}else{
			$price = price_format($totalprice+$discount+$tax,$this->site['currency_id']);
			$price_val = price_format_val($totalprice+$discount+$tax,$this->site['currency_id']);
		}
		$r['price'] = $price;
		$r['price_val'] = $price_val;
		//支付方式
		$paylist = $this->model('payment')->get_all($this->site['id'],1,($this->is_mobile ? 1 : 0));
		$this->assign("paylist",$paylist);
		if($this->session->val('user_id')){
			$wlist = $this->model('order')->balance($this->session->val('user_id'));
			if($wlist){
				if($wlist['balance']){
					$r['balance'] = $wlist['balance'];
				}
				if($wlist['integral']){
					$r['integral'] = $wlist['integral'];
				}
			}
		}
		$this->success($r);
	}

	/**
	 * 运费计算
	 * @参数 id，购物车里要结算的产品ID，留空读取购物车全部产品
	 * @参数 address_id，地址ID，留空单独读取省，市信息
	 * @参数 province 省份名称
	 * @参数 city 城市名称
	**/
	public function freight_f()
	{
		$id = $this->get('id');
		if($id){
			if(!is_array($id)){
				$id = explode(",",$id);
			}
			foreach($id as $key=>$value){
				if(!$value || !trim($value) || !intval($value)){
					unset($id[$key]);
				}
			}
			$rslist = $this->model('cart')->get_all($this->cart_id,$id);
		}else{
			$rslist = $this->model('cart')->get_all($this->cart_id);
		}
		if(!$rslist){
			$this->error(P_Lang('无结算产品信息'));
		}
		$address_id = $this->get('address_id','int');
		if(!$address_id){
			$province = $this->get('province');
			$city = $this->get('city');
		}else{
			$address = $this->model('address')->get_one($address_id);
			if(!$address){
				$this->error(P_Lang('地址信息不存在'));
			}
			if(!$this->session->val('user_id')){
				$this->error(P_Lang('非会员没有地址库功能'));
			}
			if($address['user_id'] != $this->session->val('user_id')){
				$this->error(P_Lang('这不是您的地址，没有权限查阅及调用'));
			}
			$province =  $address['province'];
			$city = $address['city'];
		}
		if(!$province || !$city){
			$this->error(P_Lang('参数不完整，未指定省市信息，无法计算运费'));
		}
		$is_virtual = true;
		$tmp = array('number'=>0,'weight'=>0,'volume'=>0,'price'=>0);
		$totalprice = 0;
		foreach($rslist as $key=>$value){
			$totalprice += price_format_val($value['price'] * $value['qty']);
			if(!$value['is_virtual']){
				$tmp['number'] += intval($value['qty']);
				$tmp['weight'] += floatval($value['weight']) * intval($value['qty']);
				$tmp['volume'] += floatval($value['volume']) * intval($value['qty']);
			}
		}
		$tmp['price'] = $totalprice;
		$price = $this->model('cart')->freight_price($tmp,$province,$city);
		$this->success($price);
	}
	

	private function _address()
	{
		$condition = "a.user_id='".$this->session->val('user_id')."'";
		$addresslist = $this->model('address')->get_list($condition,0,30);
		if(!$addresslist){
			return false;
		}

		$first = $address_id = 0;
		$first_address = $address = array();
		foreach($addresslist as $key=>$value){
			if($key<1){
				$first = $value['id'];
				$first_address = $value;
			}
			if($value['is_default']){
				$address_id = $value['id'];
				$address = $value;
			}
		}
		if(!$address_id && $first){
			$address_id = $first;
			$address = $first_address;
		}
		return array('address_id'=>$address_id,'address_list'=>$addresslist,'address'=>$address);
	}
}

