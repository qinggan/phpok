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
			if($value['sublist']){
				foreach($value['sublist'] as $k=>$v){
					if($v['is_delete']){
						$totalprice += price_format_val($v['price'] * $v['qty'],$this->site['currency_id']);
						$v['_checked'] = ($v['dateline'] && date("Ymd",$v['dateline']) == $_date) ? true : false;
						$v['price_val'] = price_format_val($v['price']);
						$v['price_txt'] = price_format($v['price']);
						$v['price_total'] = price_format($v['price']*$v['qty']);
						$value['sublist'][$k] = $v;
					}
				}
			}
			$value['_checked'] = ($value['dateline'] && date("Ymd",$value['dateline']) == $_date) ? true : false;
			$value['price_val'] = price_format_val($value['price']);
			$value['price_txt'] = price_format($value['price']);
			$value['price_total'] = price_format($value['price']*$value['qty']);
			//比较系统默认价格，看是否有优惠
			if($value['apps']){
				$tmp = explode(",",$value['apps']);
				$tmplist = array();
				foreach($tmp as $k=>$v){
					$t = explode(':',$v);
					if(!$t[0] || !$t[1]){
						continue;
					}
					if(!$this->model($t[0],true)){
						continue;
					}
					$m = $this->model($t[0])->get_one($t[1]);
					$tmplist[] = $m;
				}
				$value['apps'] = $tmplist;
			}
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
		if($this->site['biz_is_user'] && !$this->session->val('user_id')){
			$this->error(P_Lang('您还不是我们的用户，请先登录'));
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
		$arc = phpok("_arc",'title_id='.$id);
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
			$count = $arc['is_virtual'] ? 1 : ($total+$qty);
			$this->model('cart')->update($updateid,$count);
			$insert_id = $updateid;
		}else{
			$count = $arc['is_virtual'] ? 1 : $qty;
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
	 * 一键购买，直接返回 payment_log 里的主键 ID，适用于手机扫码快速支付，仅限用户+虚拟服务
	 * @参数 $id 产品ID
	 * @参数 $payment 付款方式
	**/
	public function buy_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('未登录用户不支持此操作'));
		}
		$me = $this->model('user')->get_one($this->session->val('user_id'),'id',false,false);
		if(!$me){
			$this->error(P_Lang('用户信息不存在'));
		}
		if(!$me['email'] && !$me['mobile']){
			$this->error(P_Lang('用户信息不完整，请补充邮箱和手机号'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = phpok("_arc","title_id=".$id);
		if(!$rs){
			$this->error(P_Lang('产品信息不存在'));
		}
		if(!$rs['is_virtual']){
			$this->error(P_Lang('仅限服务类产品'));
		}
		$payment = $this->get('payment');
		if(!$payment){
			$this->error(P_Lang('未指定付款方式'));
		}

		$sn = $this->model('order')->create_sn();
		$status_list = $this->model('order')->status_list();
		$price = price_format_val($rs['price'],$rs['currency_id'],$this->site['currency_id']);
		$main = array('sn'=>$sn);
		$main['user_id'] = $me['id'];
		$main['addtime'] = $this->time;
		$main['price'] = $price;
		$main['currency_id'] = $this->site['currency_id'];
		$main['currency_rate'] = $this->site['currency']['val'];
		$main['status'] = 'create';
		$main['status_title'] = $status_list['create'];
		$main['passwd'] = md5(str_rand(10));
		$main['email'] = $me['email'];
		$main['mobile'] = $me['mobile'];
		$order_id = $this->model('order')->save($main);
		if(!$order_id){
			$this->error(P_Lang('订单创建失败，请联系管理员'));
		}
		$tmp = array('order_id'=>$order_id,'tid'=>$rs['id']);
		$tmp['title'] = $rs['title'];
		$tmp['price'] = $price;
		$tmp['qty'] = 1;
		$tmp['is_virtual'] = 1;
		$this->model('order')->save_product($tmp);
		
		$pricelist = $this->model('site')->price_status_all(true);
		if($pricelist){
			foreach($pricelist as $key=>$value){
				if(!$value['status']){
					unset($pricelist[$key]);
					continue;
				}
				if($value['default'] && $value['currency_id']){
					$value['price_val'] = price_format_val($value['default'],$value['currency_id'],$this->site['currency_id']);
				}
				if($value['identifier'] == 'product'){
					$value['price_val'] = price_format_val($rs['price'],$rs['currency_id'],$this->site['currency_id']);
				}
				if($value['hidden'] && (!$value['price_val'] || $value['price_val'] == '0.00')){
					unset($pricelist[$key]);
					continue;
				}
				$tmp = array('order_id'=>$order_id,'code'=>$value['identifier'],'price'=>$value['price_val']);
				$this->model('order')->save_order_price($tmp);
			}
		}

		$note = P_Lang('订单创建成功，订单编号：{sn}',array('sn'=>$sn));
		$log = array('order_id'=>$order_id,'addtime'=>$this->time,'who'=>$me['user'],'note'=>$note);
		$this->model('order')->log_save($log);

		$param = 'id='.$order_id."&status=create";
		$this->model('task')->add_once('order',$param);

		$order = $main;
		$order['id'] = $order_id;
		//基于财富付款
		if(!is_numeric($payment)){
			$this->payment_wealth($payment,$order,$me);
		}
		$payment_rs = $this->model('payment')->get_one($payment);
		if(!$payment_rs){
			$this->error(P_Lang('支付方式不存在'));
		}
		if(!$payment_rs['status']){
			$this->error(P_Lang('支付方式未启用'));
		}
		//更新支付状态
		$this->model('order')->update_order_status($order['id'],'unpaid',P_Lang('订单等待支付'));
		$title = P_Lang('订单：{sn}',array('sn'=>$order['sn']));
		$array = array('sn'=>$order['sn'],'type'=>'order','payment_id'=>$payment,'title'=>$title,'content'=>$title);
		$array['dateline'] = $this->time;
		$array['user_id'] = $me['id'];
		$array['price'] = $order['price'];
		$array['currency_id'] = $order['currency_id'];
		$array['currency_rate'] = $order['currency_rate'];
		$insert_id = $this->model('payment')->log_create($array);
		if(!$insert_id){
			$this->error(P_Lang('支付创建失败'));
		}
		$this->success($insert_id);
	}

	/**
	 * 基于财富的付款
	 * @参数 $payment 付款标识
	 * @参数 $order 订单信息
	 * @参数 $me 用户
	**/
	private function payment_wealth($payment,$order,$user)
	{
		$wealth = $this->model('wealth')->get_one($payment,'identifier');
		if(!$wealth){
			$this->error(P_Lang('支付方式无效，请检查'));
		}
		$me_val = $this->model('wealth')->get_val($user['id'],$wealth['id']);
		if(!$me_val){
			$this->error(P_Lang('{title}余额不足，请先充值',array('title'=>$wealth['title'])));
		}
		$myprice = round($me_val*$wealth['cash_ratio']/100,$wealth['dnum']);
		$unpaid_price = $this->model('order')->unpaid_price($order['id']);
		if(!$unpaid_price){
			$this->error(P_Lang('订单没有存在未付订单'));
		}
		if($unpaid_price > $myprice){
			$this->error(P_Lang('{title}余额不足，请先充值',array('title'=>$wealth['title'])));
		}
		$surplus = floatval($myprice - $unpaid_price);

		//扣除用户积分
		$savelogs = array('wid'=>$wealth['id'],'goal_id'=>$user['id'],'mid'=>0,'val'=>'-'.$unpaid_price);
		$savelogs['appid'] = $this->app_id;
		$savelogs['dateline'] = $this->time;
		$savelogs['user_id'] = $user['id'];
		$savelogs['ctrlid'] = 'payment';
		$savelogs['funcid'] = 'create';
		$savelogs['url'] = 'index.php';
		$savelogs['note'] = P_Lang('{title}支付',array('title'=>$wealth['title']));
		$savelogs['status'] = 1;
		$savelogs['val'] = -$unpaid_price;
		$this->model('wealth')->save_log($savelogs);
		$data = array('wid'=>$wealth['id'],'uid'=>$user['id'],'lasttime'=>$this->time,'val'=>$surplus);
		$this->model('wealth')->save_info($data);
		//创建订单日志，记录支付信息
		$tmparray = array('price'=>$unpaid_price,'payment'=>$wealth['title'],'integral'=>$unpaid_price,'unit'=>$wealth['unit']);
		$note = P_Lang('使用{payment}支付{price}，共消耗{payment}{integral}{unit}',$tmparray);
		$log = array('order_id'=>$order['id'],'addtime'=>$this->time,'who'=>$user['user'],'note'=>$note);
		$this->model('order')->log_save($log);

		$array = array('order_id'=>$order['id'],'payment_id'=>$payment);
		$array['title'] = P_Lang('余额支付');
		$array['price'] = $unpaid_price;
		$array['currency_id'] = $order['currency_id'];
		$array['currency_rate'] = $order['currency_rate'];
		$array['startdate'] = $this->time;
		$array['dateline'] = $this->time;
		$array['ext'] = serialize(array('备注'=>'余额支付'));
		$this->model('order')->save_payment($array);
		//登记支付记录
		$array = array('type'=>'order','price'=>$unpaid_price,'currency_id'=>$order['currency_id'],'sn'=>$order['sn']);
		$array['currency_rate'] = $order['currency_rate'];
		$array['content'] = $array['title'] = P_Lang('订单：{sn}',array('sn'=>$rs['sn']));
		$array['payment_id'] = $payment;
		$array['dateline'] = $this->time;
		$array['user_id'] = $user['id'];
		$array['status'] = 1;
		$this->model('payment')->log_create($array);	
		$this->model('order')->update_order_status($rs['id'],'paid',P_Lang('订单已付款'));
		$this->success();
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
		$gd_id = $this->config['cart']['gd_id'];
		$tmp = explode(",",$thumb_id);
		foreach($tmp as $key=>$value){
			$value = trim($value);
			if(!$value || !$rs[$value]){
				continue;
			}
			if(is_string($rs[$value])){
				$array['thumb'] = $rs[$value];
				break;
			}
			if(is_array($rs[$value])){
				if($gd_id && $rs[$value]['gd'] && $rs[$value]['gd'][$gd_id]){
					$array['thumb'] = $rs[$value]['gd'][$gd_id];
				}else{
					$array['thumb'] = $rs[$value]['filename'];
				}
				break;
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
		$this->data('product_rs',$rs);
		$this->data('cart_rs',$array);
		$t = $this->node('system_cart_product_id');
		if($t){
			$array = $this->data('cart_rs');
			$this->undata('cart_rs');
			$this->undata('product_rs');
		}
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
		$id = $this->get('id','int');
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
				$id[$key] = intval($value);
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
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要计算的产品ID'));
		}
		if(is_string($id)){
			$id = explode(",",$id);
		}
		if($id && is_array($id)){
			foreach($id as $key=>$value){
				$id[$key] = intval($value);
				if(!$value){
					unset($id[$key]);
					continue;
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
		$id = $this->get('id','int');
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
				$id[$key] = intval($value);
				if(!$value){
					unset($id[$key]);
					continue;
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
				$this->error(P_Lang('非用户没有地址库功能'));
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
				$this->data('cart_ids',$id);
				$this->node('PHPOK_cart_coupon');
				$tmp = $this->data('cart_coupon');
				if(!$tmp){
					unset($pricelist[$key]);
					continue;
				}
				$value['price'] = price_format($tmp_price,$this->site['currency_id']);
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
		$id = $this->get('id','int');
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
			if($user_rs){
				unset($user_rs['pass'],$user_rs['mobile'],$user_rs['email']);
				$r['user'] = $user_rs;
			}
		}
		$totalprice = 0;
		foreach($rslist as $key=>$value){
			//比较系统默认价格，看是否有优惠
			if($value['apps']){
				$tmp = explode(",",$value['apps']);
				$tmplist = array();
				foreach($tmp as $k=>$v){
					$t = explode(':',$v);
					if(!$t[0] || !$t[1]){
						continue;
					}
					if(!$this->model($t[0],true)){
						continue;
					}
					$m = $this->model($t[0])->get_one($t[1]);
					$tmplist[] = $m;
				}
				$value['apps'] = $tmplist;
			}
			$rslist[$key] = $value;
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
			$address_id = $this->get("address_id","int");
			$tmp = $this->_address($address_id);
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
				if($value['identifier'] == 'shipping'){
					if($is_virtual){
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
					$this->data('cart_ids',$id);
					$this->node('PHPOK_cart_coupon');
					$tmp = $this->data('cart_coupon');
					$tmp_price = $tmp['price'];
					$value['price'] = price_format($tmp['price'],$this->site['currency_id']);
					$value['price_val'] = -$tmp['price'];
					$discount = -$tmp['price'];
					$pricelist[$key] = $value;
					$tmp['price'] = price_format($tmp_price,$this->site['currency_id']);
					$tmp['price_val'] = $tmp_price;
					$r['discount'] = $tmp;
					unset($tmp);
				}
				$pricelist[$key] = $value;
			}
		}
		foreach($pricelist as $key=>$value){
			if($value['hidden'] && (!$value['price_val'] || $value['price_val'] == '0.00')){
				unset($pricelist[$key]);
			}
		}
		$r['pricelist'] = $pricelist;
		$this->assign('pricelist',$pricelist);
		if($freight_price){
			$price = price_format(($totalprice+$freight_price+$discount+$tax),$this->site['currency_id']);
			$price_val = price_format_val(($totalprice+$freight_price+$discount+$tax),$this->site['currency_id']);
		}else{
			$price = price_format($totalprice+$discount+$tax,$this->site['currency_id']);
			$price_val = price_format_val($totalprice+$discount+$tax,$this->site['currency_id']);
		}
		$r['price'] = $price;
		$r['price_val'] = $price_val;
		$this->assign('price',$price);
		$this->assign('price_val',$price_val);
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
		$id = $this->get('id','int');
		if($id){
			if(!is_array($id)){
				$id = explode(",",$id);
			}
			foreach($id as $key=>$value){
				$id[$key] = intval($value);
				if(!$value || !trim($value) || !intval($value)){
					unset($id[$key]);
					continue;
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
				$this->error(P_Lang('非用户没有地址库功能'));
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
	

	private function _address($address_id=0)
	{
		$condition = "a.user_id='".$this->session->val('user_id')."'";
		$addresslist = $this->model('address')->get_list($condition,0,30);
		if(!$addresslist){
			return false;
		}

		$first = 0;
		$first_address = $address = array();
		foreach($addresslist as $key=>$value){
			if($key<1){
				$first = $value['id'];
				$first_address = $value;
			}
			if($address_id){
				if($value['id'] == $address_id){
					$address = $value;
				}
			}else{
				if($value['is_default']){
					$address_id = $value['id'];
					$address = $value;
				}
			}
		}
		if(!$address_id && $first){
			$address_id = $first;
			$address = $first_address;
		}
		return array('address_id'=>$address_id,'address_list'=>$addresslist,'address'=>$address);
	}
}

