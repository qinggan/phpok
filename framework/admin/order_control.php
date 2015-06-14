<?php
/***********************************************************
	Filename: {phpok}/admin/order_control.php
	Note	: 订单管理，编辑和删除等相关操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月18日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_control extends phpok_control
{
	function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("order");
		$this->assign("popedom",$this->popedom);
	}

	//显示订单列表
	function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid) $pageid = 1;
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1)*$psize;
		$pageurl = $this->url('order');
		$condition = "1=1";
		//是否指定状态
		$status = $this->get("status");
		if($status)
		{
			$condition .= " AND status='".strtoupper($status)."'";
			$pageurl .= "&status=".rawurlencode($status);
			$this->assign('status',$status);
		}
		//搜索关键字
		$keywords = $this->get('keywords');
		if($keywords)
		{
			$condition .= " AND sn LIKE '%".$keywords."%'";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign('keywords',$keywords);
		}
		//付款
		$pay_status = $this->get('pay_status');
		if($pay_status)
		{
			$condition .= " AND pay_status='".strtoupper($pay_status)."'";
			$pageurl .= "&pay_status=".rawurlencode($pay_status);
			$this->assign('pay_status',$pay_status);
		}
		//金额范围
		$price_min = $this->get('price_min','float');
		if($price_min != '')
		{
			$condition .= " AND price>=".$price_min;
			$pageurl .= "&price_min=".rawurlencode($price_min);
			$this->assign('price_min',$price_min);
		}
		$price_max = $this->get('price_max','float');
		if($price_max != '')
		{
			$condition .= " AND price_max<=".$price_max;
			$pageurl .= "&price_max=".rawurlencode($price_max);
			$this->assign('price_max',$price_max);
		}
		//设置下单时间
		$date_start = $this->get('date_start');
		if($date_start)
		{
			$condition .= " AND addtime>=".strtotime($date_start);
			$pageurl .= "&date_start=".rawurlencode($date_start);
			$this->assign('date_start',$date_start);
		}
		$date_stop = $this->get('date_stop');
		if($date_stop)
		{
			$condition .= " AND addtime<=".strtotime($date_stop);
			$pageurl .= "&date_stop=".rawurlencode($date_stop);
			$this->assign('date_stop',$date_stop);
		}
		$total = $this->model('order')->get_count($condition);
		if($total>0)
		{
			$paylist = $this->model('payment')->get_all($_SESSION['admin_site_id']);
			if(!$paylist) $paylist = array();
			$tmp = array();
			foreach($paylist AS $key=>$value)
			{
				$tmp[$value['id']] = $value;
			}
			$paylist = $tmp;
			$rslist = $this->model('order')->get_list($condition,$offset,$psize);
			if(!$rslist) $rslist = array();
			foreach($rslist AS $key=>$value)
			{
				//如果不符合条件，跳过
				if(!$value['pay_id'] || !$paylist[$value['pay_id']] || !$value['ext']) continue;
				$code = $paylist[$value['pay_id']]['code'];
				if(!$this->plugin[$code] || !$this->plugin[$code]['method']) continue;
				if(!in_array('format',$this->plugin[$code]['method'])) continue;
				$extlist = $this->plugin[$code]['obj']->format($value['ext']);
				if($extlist && is_array($extlist))
				{
					$value['extlist'] = $extlist;
				}
				$rslist[$key] = $value;
			}
			$this->assign('rslist',$rslist);
			$this->assign('total',$total);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('pagelist',$pagelist);
		}
		//订单状态列表
		$statuslist = $this->model('order')->status_list();
		$this->assign('statuslist',$statuslist);
		$this->view("order_list");
	}

	//删除订单操作
	function delete_f()
	{
		$id = $this->get('id');
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		//删除订单
		if(!$id) $this->json(P_Lang('未指定订单ID号'));
		$this->model('order')->delete($id);
		$this->json(P_Lang('删除成功'),true);
	}

	//更新订单状态
	function status_f()
	{
		$id = $this->get('id');
		if(!$this->popedom['status']) $this->json(P_Lang('您没有权限执行此操作'));
		$array = array('status'=>'CHECKED');
		$this->model('order')->save($array,$id);
		$this->json(P_Lang('审核成功'),true);
	}

	//查看订单信息
	function info_f()
	{
		$id = $this->get('id','int');
		if(!$id) error_open(P_Lang('未指定ID'));
		
		$rs = $this->model('order')->get_one($id);
		if(!$rs) error_open(P_Lang('订单信息不存在'));
		if($rs['pay_id'])
		{
			//取得付款方式
			$payment = $this->model('payment')->get_one($rs['pay_id']);
			if($this->plugin[$payment['code']] && $this->plugin[$payment['code']]['method'] && in_array('format',$this->plugin[$payment['code']]['method']) && $rs['ext'])
			{
				$ext = $this->plugin[$payment['code']]['obj']->format($rs['ext'],true);
				if($ext && is_array($ext))
				{
					$rs['extlist'] = $ext;
				}
			}
		}
		$this->assign('rs',$rs);
		//订单状态
		$statuslist = $this->model('order')->status_list();
		$this->assign('statuslist',$statuslist);
		//取得订单的地址
		$address = $this->model('order')->address_list($id);
		$this->assign('shipping',$address['shipping']);
		$this->assign('billing',$address['billing']);
		//订单下的产品列表
		$rslist = $this->model('order')->product_list($id);
		$this->assign('rslist',$rslist);
		$this->view("order_info");
	}

	function set_f()
	{
		$id = $this->get('id','int');
		if(!$id)
		{
			if(!$this->popedom['add']) error(P_Lang('您没有权限执行此操作'),$this->url('order'),'error');
		}
		else
		{
			if(!$this->popedom['modify']) error(P_Lang('您没有权限执行此操作'),$this->url('order'),'error');
			$rs = $this->model('order')->get_one($id);
			$this->assign('rs',$rs);
			//读取产品列表
			$rslist = $this->model('order')->product_list($id);
			if(!$rslist) $rslist = array();
			foreach($rslist AS $key=>$value)
			{
				if($value['thumb']) $value['thumb'] = $this->model('res')->get_one($value['thumb']);
				$rslist[$key] = $value;
			}
			$this->assign('rslist',$rslist);
			//取得订单的地址
			$address = $this->model('order')->address_list($rs['id']);
			if($address['shipping']['city']) $address['shipping']['city'] = str_replace(array('(',')'),'',$address['shipping']['city']);
			if($address['shipping']['county']) $address['shipping']['county'] = str_replace(array('(',')'),'',$address['shipping']['county']);
			$this->assign('shipping',$address['shipping']);
			if($address['billing']['city']) $address['billing']['city'] = str_replace(array('(',')'),'',$address['billing']['city']);
			if($address['billing']['county']) $address['billing']['county'] = str_replace(array('(',')'),'',$address['billing']['county']);
			$this->assign('billing',$address['billing']);
		}
		$site_rs = $this->model('site')->get_one($_SESSION['admin_site_id']);
		$this->assign("site_rs",$site_rs);
		$this->assign('id',$id);
		//读取支付方式
		$paylist = $this->model('payment')->get_all($_SESSION['admin_site_id']);
		$this->assign("paylist",$paylist);
		//读取网站货币
		$currency_list = $this->model('currency')->get_list();
		$this->assign("currency_list",$currency_list);
		//付款方式
		$paylist = $this->model('payment')->opt_all($_SESSION['admin_site_id']);
		$this->assign('paylist',$paylist);
		
		$this->view("order_set");
	}

	//删除产品信息
	function product_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id) $this->json(P_Lang('未指定产品ID'));
		if(!$this->popedom['modify']) $this->json(P_Lang('您没有权限执行此操作'));
		$this->model('order')->product_delete($id);
		$this->json(P_Lang('删除成功'),true);
	}

	//选择商品
	function prolist_f()
	{
		$id = $this->get('id');
		if(!$id) $id = 'add';
		$currency_id = $this->get('currency_id','int');
		$this->assign('currency_id',$currency_id);
		$formurl = $pageurl = $this->url('order','prolist','id='.$id.'&currency_id='.$currency_id);
		$project = $this->model('project')->project_all($_SESSION['admin_site_id'],'id','is_biz != 0');
		if(!$project)
		{
			error_open(P_Lang('您的站点没有启用电子商务功能项目'));
		}
		$condition = "l.site_id IN(0,".$_SESSION['admin_site_id'].") AND l.status=1";
		$idlist = array_keys($project);
		$condition.= " AND l.project_id IN(".implode(',',$idlist).")";
		$exinclude = $this->get('exinclude');
		if($exinclude)
		{
			$tmp = explode(",",$exinclude);
			$exinclude = '';
			foreach($tmp AS $key=>$value)
			{
				$value = intval($value);
				if($value) $exinclude[] = $value;
			}
			$exinclude = implode(',',$exinclude);
			$condition .= " AND l.id NOT IN(".$exinclude.")";
			$pageurl .= "&exinclude=".rawurlencode($exinclude);
			$formurl .= "&exinclude=".rawurlencode($exinclude);
		}
		$keywords = $this->get('keywords');
		if($keywords)
		{
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$condition .= " AND l.title LIKE '%".$keywords."%' ";
			$this->assign('keywords',$keywords);
		}
		$this->assign('pageurl',$pageurl);
		$this->assign('formurl',$formurl);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid) $pageid = 1;
		$psize = 20;
		$offset = ($pageid - 1) * $psize;
		$total = $this->model('list')->get_all_total($condition);
		if($total<1)
		{
			error_open(P_Lang('没有产品信息'));
		}
		$rslist = $this->model('list')->get_all($condition,$offset,$psize);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign('pagelist',$pagelist);
		$this->assign('rslist',$rslist);
		$this->assign('id',$id);
		$this->view("order_prolist");
	}

	//取得产品信息
	function product_f()
	{
		$id = $this->get('id','int');
		if(!$id) $this->json(P_Lang('未指定产品ID'));

		$rs = $this->model('list')->get_one($id);
		if(!$rs) $this->json(P_Lang('产品信息不存在'));
		$currency_id = $this->get("currency_id",'int');
		$rs['price'] = price_format_val($rs['price'],$rs['currency_id'],$currency_id);
		$this->json($rs,true);
	}

	//图片库
	function thumb_f()
	{
		$id = $this->get('id');
		if(!$id) $id = 'add';
		$formurl = $pageurl = $this->url("order","thumb","id=".$id);
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 32;
		$offset = ($pageid - 1) * $psize;
		$condition = "ext IN ('gif','jpg','png','jpeg') ";
		$cate_id = $this->get("cate_id","int");
		if($cate_id)
		{
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (title LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%' OR id LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$total = $this->model('res')->get_count($condition);
		if($total>0)
		{
			$rslist = $this->model('res')->get_list($condition,$offset,$psize,false);
			$this->assign("rslist",$rslist);
			$this->assign("pageurl",$pageurl);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=4';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("formurl",$formurl);
		$this->assign("id",$id);
		//读取附件分类
		$catelist = $this->model('res')->cate_all();
		$this->assign("catelist",$catelist);
		$config = $this->model('res')->type_list();
		$file_type = "*.*";
		$file_type_desc = P_Lang('文件');
		if($type && $config['picture'])
		{
			$file_type = $config[$type]["type"];
			$file_type_desc = $config[$type]["name"];
		}
		$this->assign("file_type",$file_type);
		$this->assign("file_type_desc",$file_type_desc);
		$this->view("order_picture");
	}

	//存储订单信息
	function save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			if(!$this->popedom['add']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
			$this->add_save();
		}
		if(!$this->popedom['modify']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$this->modify_save($id);
	}

	//编辑存储信息
	function modify_save($id)
	{
		$main = array();
		$passwd = $this->get('passwd');
		if(!$passwd){
			$this->json(P_Lang('订单密码不能为空'));
		}
		if(!preg_match('/^[a-z0-9A-Z\_\-]+$/u',$passwd)){
			$this->json(P_Lang('订单密码不合要求，限字母、数字、下划线及中划线'));
		}
		$main['passwd'] = $passwd;
		$main['price'] = $this->get('price','float');
		$main['currency_id'] = $this->get('currency_id','int');
		$main['user_id'] = $this->get('user_id','int');
		$main['pay_price'] = $this->get('pay_price','float');
		if(!$main['pay_price']){
			$main['pay_price'] = $main['price'];
		}
		$main['pay_status'] = $this->get('pay_status');
		$main['pay_end'] = $this->get('pay_end','int');
		$main['pay_date'] = 0;
		if($main['pay_end']){
			$main['pay_id'] = $this->get('pay_id','int');
			if($main['pay_id']){
				$payment_rs = $this->model('payment')->get_one($main['pay_id']);
				if($payment_rs && $payment_rs['currency']){
					$main['pay_title'] = $payment_rs['title'];
					$currency_rs = $this->model('currency')->get_one($payment_rs['currency'],'code');
					if($currency_rs){
						$main['pay_currency'] = $currency_rs['id'];
						$main['pay_currency_code'] = $currency_rs['code'];
						$main['pay_currency_rate'] = $currency_rs['val'];
					}
				}
				$pay_date = $this->get('pay_date');
				$main['pay_date'] = $pay_date ? strtotime($pay_date) : $this->time;
			}else{
				$main['pay_currency'] = '0';
				$main['pay_currency_code'] = 'CNY';
				$main['pay_currency_rate'] = '1.0';
			}
		}
		$main['note'] = $this->get('note');
		$main['status'] = $this->get('status');
		$this->model('order')->save($main,$id);
		$prolist = $this->get('pro_id');
		$pro_title = $this->get('pro_title');
		$pro_thumb = $this->get('pro_thumb');
		$pro_tid = $this->get('pro_tid');
		$pro_price = $this->get('pro_price');
		$pro_qty = $this->get('pro_qty');
		if(!$prolist || !is_array($prolist)){
			$this->json(P_Lang('产品信息为空，订单异常，请添加产品或删除订单信息'));
		}
		foreach($prolist AS $key=>$value){
			$tmp_title = $pro_title[$key];
			if(!$tmp_title || !trim($tmp_title)){
				continue;
			}
			$tmp_qty = intval($pro_qty[$key]);
			if(!$tmp_qty) $tmp_qty = 1;
			$total_qty += $tmp_qty;
			$tmp_price = floatval($pro_price[$key]);
			$total_price += $tmp_price;
			$array = array(
				'tid'=>intval($pro_tid[$key]),
				'title'=>$tmp_title,
				'price'=>$tmp_price,
				'qty'=>$tmp_qty,
				'thumb'=>intval($pro_thumb[$key]),
				'order_id'=>$id
			);
			if($value && $value != 'add'){
				$this->model('order')->save_product($array,$value);
			}else{
				$this->model('order')->save_product($array);
			}
		}
		//存储收货地址
		$shipping = $this->address('s');
		if($shipping){
			$shipping['order_id'] = $id;
			$sid = $this->get('s-id','int');
			$this->model('order')->save_address($shipping,$sid);
		}
		$billing = $this->address('b');
		if($billing['fullname']){
			$billing['order_id'] = $id;
			$bid = $this->get('b-id','int');
			$this->model('order')->save_address($billing,$bid);
		}
		$this->json(true);
	}

	//添加存储信息
	function add_save()
	{
		$main = array();
		$sn = $this->get('sn');
		if(!$sn){
			$this->json(P_Lang('订单编号不能为空'));
		}
		if(!preg_match('/^[a-z0-9A-Z\_\-]+$/u',$sn)){
			$this->json(P_Lang('订单编号不合要求，限字母、数字、下划线及中划线'));
		}
		$rs = $this->model('order')->get_one_from_sn($sn);
		if($rs){
			$this->json(P_Lang('订单编号已被使用，请换个编号'));
		}
		$main['sn'] = $sn;
		$passwd = $this->get('passwd');
		if(!$passwd){
			$this->json(P_Lang('订单密码不能为空'));
		}
		if(!preg_match('/^[a-z0-9A-Z\_\-]+$/u',$passwd)){
			$this->json(P_Lang('订单密码不合要求，限字母、数字、下划线及中划线'));
		}
		$main['passwd'] = $passwd;
		$prolist = $this->get('pro_id');
		$pro_title = $this->get('pro_title');
		$pro_thumb = $this->get('pro_thumb');
		$pro_tid = $this->get('pro_tid');
		$pro_price = $this->get('pro_price');
		$pro_qty = $this->get('pro_qty');
		if(!$prolist || !is_array($prolist)) $this->json(P_Lang('订单未创建相应的产品信息'));
		$plist = '';
		$total_price = 0;
		$total_qty = 0;
		foreach($prolist AS $key=>$value)
		{
			//如果产品名称为空，同跳过
			$tmp_title = $pro_title[$key];
			if(!$tmp_title || !trim($tmp_title)) continue;
			//产品数量
			$tmp_qty = intval($pro_qty[$key]);
			if(!$tmp_qty) $tmp_qty = 1;
			$total_qty += $tmp_qty;
			//产品价格
			$tmp_price = floatval($pro_price[$key]);
			$total_price += $tmp_price;
			//合并成为数组
			$array = array(
				'tid'=>intval($pro_tid[$key]),
				'title'=>$tmp_title,
				'price'=>$tmp_price,
				'qty'=>$tmp_qty,
				'thumb'=>intval($pro_thumb[$key])
			);
			$plist[] = $array;
		}
		if(!$plist) $this->json(P_Lang('产品信息为空'));
		$shipping = $this->address('s');
		$site_rs = $this->model('site')->get_one($_SESSION['admin_site_id']);
		$billing = false;
		if($site_rs['biz_billing']){
			$billing = $this->address('b');
		}
		//存储订单操作
		$main['price'] = $this->get('price','float');
		if(!$main['price']){
			$main['price'] = $total_price;
		}
		$main['currency_id'] = $this->get('currency_id','int');
		$main['user_id'] = $this->get('user_id','int');
		$main['addtime'] = $this->time;
		$main['qty'] = $total_qty;
		$main['status'] = $this->get('status');
		$main['pay_end'] = $this->get('pay_end','int');
		$main['pay_id'] = $this->get('pay_id','int');
		if($main['pay_id']){
			$payment_rs = $this->model('payment')->get_one($main['pay_id']);
			if($payment_rs && $payment_rs['currency']){
				$main['pay_title'] = $payment_rs['title'];
				$currency_rs = $this->model('currency')->get_one($payment_rs['currency'],'code');
				if($currency_rs){
					$main['pay_currency'] = $currency_rs['id'];
					$main['pay_currency_code'] = $currency_rs['code'];
					$main['pay_currency_rate'] = $currency_rs['val'];
				}
			}
		}
		$main['pay_price'] = $this->get('pay_price','float');
		if(!$main['pay_price']){
			$main['pay_price'] = $main['price'];
		}
		$pay_date = $this->get('pay_date');
		if(!$pay_date){
			$pay_date = date("Y-m-d H:i",$this->time);
		}
		$main['pay_date'] = strtotime($pay_date);
		$main['pay_status'] = $this->get('pay_status');
		$main['note'] = $this->get('note');
		$order_id = $this->model('order')->save($main);
		if(!$order_id){
			$this->json(P_Lang('订单创建失败，写入数据库出错'));
		}
		foreach($plist AS $key=>$value){
			$value['order_id'] = $order_id;
			$this->model('order')->save_product($value);
		}
		if($shipping){
			$shipping['order_id'] = $order_id;
			$this->model('order')->save_address($shipping);
		}
		if($billing){
			$billing['order_id'] = $order_id;
			$this->model('order')->save_address($billing);
		}
		$this->json(P_Lang('订单创建成功'),true);
	}

	//地址库
	function address($type='s',$sid=0)
	{
		$array = array();
		$array['country'] = $this->get($type."-country");
		$array['province'] = $this->get($type."-province");
		$array['city'] = $this->get($type."-city");
		$array['county'] = $this->get($type."-county");
		$array['address'] = $this->get($type."-address");
		$array['zipcode'] = $this->get($type."-zipcode");
		$array['mobile'] = $this->get($type."-mobile");
		$array['tel'] = $this->get($type."-tel");
		$array['email'] = $this->get($type."-email");
		$array['fullname'] = $this->get($type."-fullname");
		$array['gender'] = $this->get($type."-gender",'int');
		$array['type_id'] = $type == 's' ? 'shipping' : 'billing';
		return $array;
	}
}

?>