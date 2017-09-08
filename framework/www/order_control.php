<?php
/**
 * 订单信息管理
 * @package phpok\www
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
			$this->error(P_Lang('您还不是会员，请先登录'),$this->url('login','','_back='.rawurlencode($backurl)));
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$condition = "user_id='".$this->session->val('user_id')."'";
		$pageurl = $this->url('order');
		$total = $this->model('order')->get_count($condition);
		$this->assign('pageid',$pageid);
		$this->assign('pageurl',$pageurl);
		$this->assign('total',$total);
		$this->assign('psize',$psize);
		if($total){
			$rslist = $this->model('order')->get_list($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_list';
		}
		$this->view($tplfile);
	}

	/**
	 * 创建订单
	 * @参数 
	 * @参数 
	 * @参数 
	 * @返回 
	 * @更新时间 
	**/
	public function create_f()
	{
		$user = array();
		if($this->session->val('user_id')){
			$user = $this->model('user')->get_one($this->session->val('user_id'));
		}
		$rslist = $this->model('cart')->get_all($this->cart_id);
		if(!$rslist){
			$this->error(P_Lang("您的购物车里没有产品"),$this->url);
		}
		$freight_price = $product_price = $total_price = $qty = $weight = $volume = 0;
		$is_virtual = true;
		foreach($rslist AS $key=>$value){
			$weight += $value['weight'] * $value['qty'];
			$volume += $value['volume'] * $value['qty'];
			$product_price += $value['price'] * $value['qty'];
			$total_price += $value['price'] * $value['qty'];
			$qty += $value['qty'];
			if(!$value['is_virtual']){
				$is_virtual = false;
			}
		}
		$address = array();
		if(!$is_virtual){
			$address['fullname'] = $this->get('fullname');
			if(!$address['fullname']){
				$this->error(P_Lang('姓名不能为空'),$this->url('cart','check'));
			}
			$address['country'] = $this->get('country');
			if(!$address['country']){
				$address['country'] = '中国';
			}
			$address['province'] = $this->get('pca_p');
			if(!$address['province']){
				$this->error(P_Lang('请选择省份'),$this->url('cart','check'));
			}
			$address['city'] = $this->get('pca_c');
			if(!$address['city']){
				$this->error(P_Lang('请选择城市'),$this->url('cart','check'));
			}
			$address['county'] = $this->get('pca_a');
			$address['address'] = $this->get('address');
			if(!$address['address']){
				$this->error(P_Lang('地址不能为空'),$this->url('cart','check'));
			}
			$address['mobile'] = $this->get('mobile');
			$address['tel'] = $this->get('tel');
			if(!$address['mobile'] && !$address['tel']){
				$this->error(P_Lang('手机号或联系电话至少要有一项不能为空'),$this->url('cart','check'));
			}
			if($address['mobile'] && !$this->lib('common')->tel_check($address['mobile'],'mobile')){
				$this->error(P_Lang('手机号格式不正确'),$this->url('cart','check'));
			}
			if($address['tel'] && !$this->lib('common')->tel_check($address['tel'])){
				$this->error(P_Lang('联系电话格式不正确'),$this->url('cart','check'));
			}
			$address['zipcode'] = $this->get('zipcode');
			$freight_price = $this->model('cart')->freight_price(array('weight'=>$weight,'number'=>$qty,'volume'=>$volume),$address['province'],$address['city']);
			if($freight_price){
				$total_price += $freight_price;
			}
		}else{
			$address['mobile'] = $this->get('mobile');
			if(!$address['mobile']){
				$this->error(P_Lang('手机号不能为空'),$this->url('cart','check'));
			}
			if(!$this->lib('common')->tel_check($address['mobile'],'mobile')){
				$this->error(P_Lang('手机号格式不正确'),$this->url('cart','check'));
			}
		}
		$address['email'] = $this->get('email');
		if(!$address['email']){
			$this->error(P_Lang('邮箱不能为空'),$this->url('cart','check'));
		}
		if(!$this->lib('common')->email_check($address['email'])){
			$this->error(P_Lang('邮箱格式不正确'),$this->url('cart','check'));
		}
		$sn = $this->model('order')->create_sn();
		$main = array('sn'=>$sn);
		$main['user_id'] = $user['id'];
		$main['addtime'] = $this->time;
		$main['price'] = $total_price;
		$main['currency_id'] = $this->site['currency_id'];
		$main['status'] = 'create';
		$main['passwd'] = md5(str_rand(10));
		$main['mobile'] = $address['mobile'];
		$main['email'] = $address['email'];
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
		$id = $this->model('order')->save($main);
		if(!$id){
			$this->error(P_Lang('订单创建失败'),$this->url('cart','check'));
		}
		foreach($rslist as $key=>$value){
			$tmp = array('order_id'=>$id,'tid'=>$value['tid']);
			$tmp['title'] = $value['title'];
			$tmp['price'] = $value['price'];
			$tmp['qty'] = $value['qty'];
			$tmp['weight'] = $value['weight'];
			$tmp['volume'] = $value['volume'];
			$tmp['unit'] = $value['unit'];
			$tmp['thumb'] = $value['thumb'] ? $value['thumb'] : '';
			$tmp['ext'] = $value['_attrlist'] ? serialize($value['_attrlist']) : '';
			$tmp['is_virtual'] = $value['is_virtual'];
			$this->model('order')->save_product($tmp);
		}
		if(!$is_virtual && $address){
			$tmp = array('order_id'=>$id);
			$tmp['country'] = $address['country'];
			$tmp['province'] = $address['province'];
			$tmp['city'] = $address['city'];
			$tmp['county'] = $address['county'];
			$tmp['address'] = $address['address'];
			$tmp['mobile'] = $address['mobile'];
			$tmp['tel'] = $address['tel'];
			$tmp['email'] = $address['email'];
			$tmp['fullname'] = $address['fullname'];
			$tmp['zipcode'] = $address['zipcode'];
			$this->model('order')->save_address($tmp);
		}
		$pricelist = $this->model('site')->price_status_all();
		if($pricelist){
			foreach($pricelist as $key=>$value){
				$tmp_price = '0.00';
				if($key == 'product'){
					$tmp_price = $product_price;
				}
				if($key == 'shipping'){
					$tmp_price = $freight_price;
				}
				if($is_virtual && $key == 'shipping'){
					continue;
				}
				$tmp = array('order_id'=>$id,'code'=>$key,'price'=>$tmp_price);
				$this->model('order')->save_order_price($tmp);
			}
		}
		//删除购物车信息
		$this->model('cart')->delete($this->cart_id);
		$this->session->unassign('cart');
		//填写订单日志
		$note = P_Lang('订单创建成功，订单编号：{sn}',array('sn'=>$sn));
		$log = array('order_id'=>$id,'addtime'=>$this->time,'who'=>$user['user'],'note'=>$note);
		$this->model('order')->log_save($log);
		//增加订单通知
		$param = 'id='.$oid."&status=create";
		$this->model('task')->add_once('order',$param);
		$taskurl = api_url('task','index','',true);
		$this->lib('async')->start($taskurl);
		$payment = $this->get('payment');
		$urlext = $this->session->val('user_id') ? 'id='.$id : 'sn='.$sn.'&passwd='.$main['passwd'];
		if(!$payment){
			$this->_location($this->url('order','payment',$urlext));
			exit;
		}
		$urlext .= "&payment=".$payment;
		$integral_val = $this->get('integral_val');
		if($integral_val){
			foreach($integral_val as $key=>$value){
				if($value && $key && intval($value) && intval($key)){
					$urlext.= "&integral_val[".intval($key)."]=".intval($value);
				}
			}
		}
		$this->_location($this->url('payment','create',$urlext));
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
		$this->view($tplfile);
	}

	/**
	 * 获取订单信息，无论成功或是失败均返回数据或布尔值
	 * @参数 id 订单ID号
	 * @参数 sn 订单编号
	 * @参数 passwd 订单密码
	 * @返回 
	 * @更新时间 
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
			//$value['info'] = phpok("_arc",'title_id='.$value['tid']);
			//读取他评论过的
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
}