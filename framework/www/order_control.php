<?php
/**
 * 订单信息管理
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2016年08月01日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_control extends phpok_control
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
	 * 取得订单列表
	 * @参数 pageid 页码ID
	**/
	public function index_f()
	{
		$backurl = $this->url('order');
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您还未登录，请先登录'),$this->url('login','','_back='.rawurlencode($backurl)));
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$condition = "user_id='".$this->session->val('user_id')."'";
		$pageurl = $this->url('order');
		$status = $this->get('status');
		if($status){
			$tmp = explode(",",$status);
			foreach($tmp as $key=>$value){
				$value = $this->format($value,'id');
				if(!$value){
					unset($tmp[$key]);
					continue;
				}
			}
			$condition .= " AND status IN('".implode("','",$tmp)."')";
			$pageurl = $this->url('order','','status='.rawurlencode($status));
			$this->assign('status',$status);
		}
		$keywords = $this->get('keywords');
		if($keywords){
			$tmp = str_replace(' ','%',$keywords);
			$sql = "SELECT DISTINCT op.order_id FROM ".$this->db->prefix."order_product op LEFT JOIN ".$this->db->prefix."order o ON(op.order_id=o.id) WHERE o.user_id='".$this->session->val('user_id')."' AND op.title LIKE '%".$tmp."%' ORDER BY o.id DESC LIMIT 500";
			$tmplist = $this->db->get_all($sql);
			if($tmplist){
				$o_ids = array();
				foreach($tmplist as $key=>$value){
					$o_ids[] = $value['order_id'];
				}
				$condition .= " AND (id IN(".implode(",",$o_ids).") OR sn LIKE '%".$keywords."%')";
			}else{
				$condition .= " AND sn LIKE '%".$keywords."%'";
			}
			$this->assign('keywords',$keywords);
			$pageurl .= "&keywords=".rawurlencode($keywords);
		}
		$days = $this->get('days');
		if($days){
			$this->assign('days',$days);
			$pageurl .= "&days=".rawurlencode($days);
			$tmp = explode(":",$days);
			if($tmp[1] && is_numeric($tmp[1])){
				if($tmp[0] == 'day'){
					$condition .= " AND addtime>=".($this->time-($tmp[1]*24*3600));
				}
				if($tmp[0] == 'month'){
					$tmptime = mktime(0, 0, 0, date("m",$this->time)-$tmp[1], 1, date("Y",$this->time));
					$condition .= " AND addtime>=".$tmptime;
				}
				if($tmp[0] == 'year'){
					$condition .= " AND FROM_UNIXTIME(addtime,'%Y')=".$tmp[1];
				}
			}
		}
		$total = $this->model('order')->get_count($condition);
		if($total){
			$rslist = $this->model('order')->get_list($condition,$offset,$psize);
			foreach ($rslist as $key => $value){
			    $product = $this->model('order')->product_list($value['id']);
			    $rslist[$key]['product'] = $product;
			    $unpaid_price = $this->model('order')->unpaid_price($value['id']);
		        $paid_price = $this->model('order')->paid_price($value['id']);
		        if($unpaid_price > 0){
			        if($paid_price>0){
				        $rslist[$key]['pay_info'] = '部分支付';
			        }else{
				        $rslist[$key]['pay_info'] = '未支付';
			        }
		        }else{
			        $rslist[$key]['pay_info'] = '已支付';
		        }
            }
			$this->assign('rslist',$rslist);
			$this->assign('pageid',$pageid);
			$this->assign('pageurl',$pageurl);
			$this->assign('total',$total);
			$this->assign('psize',$psize);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_list';
		}
		$yearlist = array();
		
		
		$sql = "SELECT MIN(addtime) FROM ".$this->db->prefix."order";
		$tmp = $this->db->count($sql);
		if($tmp){
			$yearlist[] = array('content'=>"day:30",'title'=>P_Lang('30天内订单'));
			$tmptime = mktime(0, 0, 0, date("m",$this->time)-3, 1, date("Y",$this->time));
			if($tmp && $tmp<$tmptime){
				$yearlist[] = array('content'=>"month:3",'title'=>P_Lang('三个月内订单'));
			}
			$year = date("Y",$this->time);
			for($i=$year;$i>=$tmp;$i--){
				$yearlist[] = array('content'=>"year:".$i,'title'=>P_Lang('{year}年订单',array('year'=>$i)));
			}
		}else{
			$year = date("Y",$this->time);
			$yearlist[] = array('content'=>"year:".$year,'title'=>P_Lang('{year}年订单',array('year'=>$year)));
		}
		$this->assign('yearlist',$yearlist);
		$this->view($tplfile);
	}

	/**
	 * 查看订单信息
	 * @参数 back 返回上一级，未指定时，用户返回HTTP_REFERER或订单列表，游客返回HTTP_REFERER或首页
	 * @参数 id 订单ID号，仅限已登录用户使用
	 * @参数 sn 订单编号，如果订单ID为空时，使用SN来查询
	 * @参数 passwd 订单密码，仅限游客查阅时需要使用
	**/
	public function info_f()
	{
		$back = $this->get('back');
		if(!$back){
			$back = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : ($this->session->val('user_id') ? $this->url('order') : $this->url);
		}
		$order = $this->_order();
		if(!$order['status']){
			$this->error($order['error'],$back);
		}
		$rs = $order['info'];
		unset($order);
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
		$this->assign('paid_price',$paid_price);
		$this->assign('unpaid_price',$unpaid_price);
		$rs['status_info'] = ($status_list && $status_list[$rs['status']]) ? $status_list[$rs['status']] : $rs['status'];
		$this->assign('rs',$rs);
		$addressconfig = $this->config['order']['address'] ? explode(",",strtolower($this->config['order']['address'])) : array('shipping');
		if($addressconfig){
			$address = array();
			foreach($addressconfig as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$address[trim($value)] = $this->model('order')->address($rs['id'],trim($value));
			}
			if(!$address['shipping'] && $address['billing']){
				$address['shipping'] = $address['billing'];
			}
			$this->assign('address',$address['shipping']);
			if($address['billing'] && $address['shipping']){
				$is_same = true;
				foreach($address['shipping'] as $key=>$value){
					if($key == 'type' || $key == 'id'){
						continue;
					}
					if($value && $address['billing'][$key] && $value != $address['billing'][$key]){
						$is_same = false;
						break;
					}
					if(!$value && $address['billing'][$key]){
						$is_same = false;
						break;
					}
					if($value && !$address['billing'][$key]){
						$is_same = false;
						break;
					}
				}
				if(!$is_same){
					$this->assign('billing',$address['billing']);
				}
			}
		}
		$rslist = $this->model('order')->product_list($rs['id']);
		$this->assign('rslist',$rslist);
		//获取价格
		$price_tpl_list = $this->model('site')->price_status_all();
		$order_price = $this->model('order')->order_price($rs['id']);
		if($price_tpl_list && $order_price){
			$pricelist = array();
			foreach($price_tpl_list as $key=>$value){
				$tmpval = floatval($order_price[$key]);
				if(!$value['status']){
					continue;
				}
				if($value['hidden'] && (!$order_price[$key] || $order_price[$key] == '0.00')){
					continue;
				}
				$tmp = array('val'=>$tmpval);
				$tmp['price'] = price_format($order_price[$key],$rs['currency_id']);
				$tmp['title'] = $value['title'];
				$pricelist[$key] = $tmp;
			}
			$this->assign('pricelist',$pricelist);
		}
		if($this->model('order')->check_payment_is_end($rs['id'])){
			$this->assign('pay_end',true);
		}
		$loglist = $this->model('order')->log_list($rs['id']);
		$this->assign('loglist',$loglist);

		//查找物流
		$express_all = $this->model('order')->express_all($rs['id']);
		if($express_all){
			$shipping = current($express_all);
			$this->assign('shipping',$shipping);
		}

		//付款记录
		$paylist = $this->model('order')->payment_all($rs['id']);
		if($paylist){
			$payinfo = end($paylist);
			$this->assign('payinfo',$payinfo);
			$this->assign('paylist',$paylist);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_info';
		}
		$this->view($tplfile);
	}

	/**
	 * 订单支付页
	 * @参数 
	 * @返回 
	 * @更新时间 
	**/
	public function payment_f()
	{
		$back = $this->get('back');
		if(!$back){
			$back = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : ($this->session->val('user_id') ? $this->url('order') : $this->url);
		}
		$order = $this->_order();
		if(!$order['status']){
			$this->error($order['error'],$back);
		}
		$rs = $order['info'];
		$status_list = $this->model('order')->status_list();
		$price_unpaid = $unpaid_price = $this->model('order')->unpaid_price($rs['id']);
		$price_paid = $paid_price = $this->model('order')->paid_price($rs['id']);
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
		$this->assign('price_paid',$price_paid);
		$this->assign('price_unpaid',$price_unpaid);
		$this->assign('price_val',($price_unpaid ? $price_unpaid : $rs['price']));
		$this->assign('rs',$rs);
		unset($order);
		if($price_unpaid && $price_unpaid<0.01){
			$url = $this->session->val('user_id') ? $this->url('order','info','id='.$rs['id']) : $this->url('order','info','sn='.$rs['sn'].'&passwd='.$rs['passwd']);
			$this->success(P_Lang('您的订单 {sn} 已经支付完成',array('sn'=>$rs['sn'])),$url);
		}
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
		if(!$paylist){
			$this->error(P_Lang('系统未配置支付方式，请检查'));
		}	
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
		$addressconfig = $this->config['order']['address'] ? explode(",",$this->config['order']['address']) : array('shipping');
		if($addressconfig){
			$address = array();
			foreach($addressconfig as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$address[trim($value)] = $this->model('order')->address($rs['id'],trim($value));
			}
			$this->assign('address',$address);
		}
		$rslist = $this->model('order')->product_list($rs['id']);
		$this->assign('rslist',$rslist);
		$price_tpl_list = $this->model('site')->price_status_all();
		$order_price = $this->model('order')->order_price($rs['id']);
		if($price_tpl_list && $order_price){
			$pricelist = array();
			foreach($price_tpl_list as $key=>$value){
				$tmpval = floatval($order_price[$key]);
				if(!$value['status']){
					continue;
				}
				$tmp = array('val'=>$tmpval);
				$tmp['price'] = price_format($order_price[$key],$rs['currency_id']);
				$tmp['price_val'] = price_format_val($order_price[$key],$rs['currency_id']);
				$tmp['title'] = $value['title'];
				$pricelist[$key] = $tmp;
			}
			foreach($pricelist as $key=>$value){
				if($value['hidden'] && (!$value['price_val'] || $value['price_val'] == '0.00')){
					unset($pricelist[$key]);
				}
			}
			$this->assign('pricelist',$pricelist);
		}
		$this->balance();
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_payment';
		}
		$this->view($tplfile);
	}

	/**
	 * 获取订单信息，无论成功或是失败均返回数据或布尔值
	 * @参数 id 订单ID号
	 * @参数 sn 订单编号
	 * @参数 passwd 订单密码
	**/
	private function _order()
	{
		$userid = $this->session->val('user_id');
		if($userid){
			$id = $this->get('id','int');
			if(!$id){
				$sn = $this->get('sn');
				if(!$sn){
					return array('status'=>false,'error'=>P_Lang('未指定订单ID或订单号'));
				}
				$rs = $this->model('order')->get_one_from_sn($sn);
			}else{
				$rs = $this->model('order')->get_one($id);
			}
			if(!$rs){
				return array('status'=>false,'error'=>P_Lang('订单信息不存在'));
			}
			if($rs['user_id'] != $userid){
				$passwd = $this->get('passwd');
				if(!$passwd || ($passwd && $passwd != $rs['passwd'])){
					return array('status'=>false,'error'=>P_Lang('您没有权限查看此订单'));
				}
			}
		}else{
			$sn = $this->get('sn');
			$passwd = $this->get('passwd');
			if(!$sn || !$passwd){
				return array('status'=>false,'error'=>P_Lang('参数不完整'));
			}
			$rs = $this->model('order')->get_one_from_sn($sn);
			if(!$rs){
				return array('status'=>false,'error'=>P_Lang('订单信息不存在'));
			}
			if($passwd != $rs['passwd']){
				return array('status'=>false,'error'=>P_Lang('您没有权限查看此订单'));
			}
		}
		return array('status'=>true,'info'=>$rs);
	}

	/**
	 * 余额支付，无余额不使用
	**/
	private function balance()
	{
		if(!$this->session->val('user_id')){
			return false;
		}
		$wlist = $this->model('order')->balance($this->session->val('user_id'));
		
		if(!$wlist){
			return false;
		}
		if($wlist['balance']){
			$this->assign('balance',$wlist['balance']);
		}
		if($wlist['integral']){
			$this->assign('integral',$wlist['integral']);
		}
		return true;
	}

	/**
	 * 订单评论
	 * @参数 $id 订单ID
	 * @更新时间 
	**/
	public function comment_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('订单ID不能为空'),$this->url('order'));
		}
		$userid = $this->session->val('user_id');
		if(!$userid){
			$this->error(P_Lang('非用户账号不能执行此操作'),$this->url('login','index','_back='.rawurlencode($this->url('order','comment','id='.$id))));
		}
		$backurl = $this->lib('server')->referer();
		if(!$backurl){
			$backurl = $this->url('order');
		}
		$rs = $this->model('order')->get_one($id);
		if($rs['user_id'] != $userid){
			$this->error(P_Lang('您没有权限评论此订单信息'),$backurl);
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
		$plist = $this->model('order')->product_list($id);
		$is_comment = true;
		$tip_info = array();
		if($rs['endtime'] && $rs['status'] != 'cancel' && $plist){
			$rslist = array();
			foreach($plist as $key=>$value){
				if(!$value['tid']){
					continue;
				}
				$condition = "tid='".$value['tid']."' AND uid='".$userid."' AND order_id='".$id."'";
				$commentlist = $this->model('reply')->get_list($condition,0,100,"","addtime ASC,id ASC");
				if($commentlist){
					$value['comment'] = $commentlist;
				}
				$rslist[] = $value;
			}
			if(!$rslist || count($rslist)<1){
				$tip_info[] = P_Lang('订单中没有找到可以关联的产品信息，不支持评论');
				$is_comment = false;
			}
		}
		if(!$rs['endtime'] || !in_array($rs['status'],array('received','stop','end'))){
			$tip_info[] = P_Lang('订单未结束，暂不支持评论');
			$is_comment = false;
		}
		if($rs['status'] == 'cancel'){
			$tip_info[] = P_Lang('订单已取消，不支持评论');
			$is_comment = false;
		}
		if(!$plist){
			$tip_info[] = P_Lang('订单中无法找到相关产品信息');
			$is_comment = false;
		}
		
		if($rslist && count($rslist)>0){
			$this->assign('rslist',$rslist);
		}
		$this->assign('rs',$rs);
		$this->assign('is_comment',$is_comment);
		$this->assign('tip_info',$tip_info);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_comment';
		}
		$this->view($tplfile);
	}

	public function log_f()
	{
		$back = $this->get('back');
		if(!$back){
			$back = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : ($this->session->val('user_id') ? $this->url('order') : $this->url);
		}
		$order = $this->_order();
		if(!$order['status']){
			$this->error($order['error'],$back);
		}
		$rs = $order['info'];
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
		$this->assign('rs',$rs);
		$loglist = $this->model('order')->log_list($rs['id']);
		$this->assign('loglist',$loglist);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_log';
		}
		$this->view($tplfile);
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
		if($this->session->val('user_id')){
			$error_url = $this->url('order','info','id='.$rs['id']);
		}else{
			$error_url = $this->url('order','info','sn='.$rs['sn'].'&passwd='.$rs['passwd']);
		}
		
		if(!$rs['status']){
			$this->error(P_Lang('订单状态异常，请联系客服'),$error_url);
		}
		$array = array('create','unpaid');
		if(in_array($rs['status'],$array)){
			$this->error(P_Lang('仅限已支付的订单才能查看物流'),$error_url);
		}
		$is_virtual = true;
		$plist = $this->model('order')->product_list($rs['id']);
		if(!$plist){
			$this->error(P_Lang('这是一张空白订单，没有产品，无法获得物流信息'),$error_url);
		}
		foreach($plist as $key=>$value){
			if(!$value['is_virtual']){
				$is_virtual = false;
				break;
			}
		}
		if($is_virtual){
			$this->error(P_Lang('服务类订单没有物流信息'),$error_url);
		}
		$express_list = $this->model('order')->express_all($rs['id']);
		if(!$express_list){
			$this->error(P_Lang('订单还未录入物流信息'),$error_url);
		}
		//更新远程链接
		$rslist = array();
		foreach($express_list as $key=>$value){
			$value['express_info'] = $this->model('express')->get_one($value['express_id']);
			$url = $this->url('express','remote','id='.$value['id'],'api',true);
			if($this->config['self_connect_ip']){
				$this->lib('curl')->host_ip($this->config['self_connect_ip']);
			}
			$this->lib('curl')->connect_timeout(5);
			$this->lib('curl')->get_content($url);
			$rslist[$value['id']] = $value;
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
		$this->assign('rslist',$rslist);
		$this->assign('rs',$rs);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_logistics';
		}
		$this->view($tplfile);
	}
}