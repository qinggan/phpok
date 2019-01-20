<?php
/**
 * 订单信息管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
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
			$condition .= " AND status IN('".implode("','",$tmp)."')";
			$pageurl = $this->url('order','','status='.rawurlencode($status));
			$this->assign('status',$status);
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
		$this->view($tplfile);
	}

	/**
	 * 查看订单信息
	 * @参数 back 返回上一级，未指定时，会员返回HTTP_REFERER或订单列表，游客返回HTTP_REFERER或首页
	 * @参数 id 订单ID号，仅限已登录会员使用
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
				$rs['pay_info'] = '部分支付';
			}else{
				$rs['pay_info'] = '未支付';
			}
		}else{
			$rs['pay_info'] = '已支付';
		}
		$rs['status_info'] = ($status_list && $status_list[$rs['status']]) ? $status_list[$rs['status']] : $rs['status'];
		$this->assign('rs',$rs);
		$address = $this->model('order')->address($rs['id']);
		$this->assign('address',$address);
		$rslist = $this->model('order')->product_list($rs['id']);
		$this->assign('rslist',$rslist);
		//获取发票信息
		$invoice = $this->model('order')->invoice($rs['id']);
		$this->assign('invoice',$invoice);
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
			$this->assign('pricelist',$pricelist);
		}
		if($this->model('order')->check_payment_is_end($rs['id'])){
			$this->assign('pay_end',true);
		}
		$loglist = $this->model('order')->log_list($rs['id']);
		$this->assign('loglist',$loglist);
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
		$this->assign('rs',$rs);
		unset($order);
		if($this->model('order')->check_payment_is_end($rs['id'])){
			$url = $this->session->val('user_id') ? $this->url('order','info','id='.$rs['id']) : $this->url('order','info','sn='.$rs['sn'].'&passwd='.$rs['passwd']);
			$this->success(P_Lang('您的订单 {sn} 已经支付完成，无需再支付',array('sn'=>$rs['sn'])),$url);
		}
		$mobile = $this->is_mobile ? 1 : 0;
		$paylist = $this->model('payment')->get_all($this->site['id'],1,$mobile);
		$this->assign("paylist",$paylist);
		$this->balance();
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_payment';
		}
		$price_paid = $this->model('order')->paid_price($rs['id']);
		$this->assign('price_paid',$price_paid);
		$price_unpaid = $this->model('order')->unpaid_price($rs['id']);
		$this->assign('price_unpaid',$price_unpaid);
		$this->assign('rs',$rs);
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
			$this->error(P_Lang('非会员账号不能执行此操作'),$this->url('login','index','_back='.rawurlencode($this->url('order','comment','id='.$id))));
		}
		$backurl = $this->lib('server')->referer();
		if(!$backurl){
			$backurl = $this->url('order');
		}
		$rs = $this->model('order')->get_one($id);
		if($rs['user_id'] != $userid){
			$this->error(P_Lang('您没有权限评论此订单信息'),$backurl);
		}
		if(!$rs['endtime']){
			$this->error(P_Lang('订单未结束，暂不支持评论'),$backurl);
		}
		if($rs['status'] == 'cancel'){
			$this->error(P_Lang('订单已取消，不支持评论'),$backurl);
		}
		$plist = $this->model('order')->product_list($id);
		if(!$plist){
			$this->error(P_Lang('订单中无法找到相关产品信息'),$backurl);
		}
		$rslist = false;
		foreach($plist as $key=>$value){
			if(!$value['tid']){
				continue;
			}
			if(!$rslist){
				$rslist = array();
			}
			$condition = "tid='".$value['tid']."' AND uid='".$userid."' AND order_id='".$id."'";
			$commentlist = $this->model('reply')->get_list($condition,0,100,"","addtime ASC,id ASC");
			if($commentlist){
				$value['comment'] = $commentlist;
			}
			$rslist[] = $value;
		}
		if(!$rslist){
			$this->error(P_Lang('订单中没有找到可以关联的产品信息，所以不支持评论'),$backurl);
		}
		$this->assign('rslist',$rslist);
		$this->assign('rs',$rs);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_comment';
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
		$this->view('order_logistics');
	}
}