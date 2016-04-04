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
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("order");
		$this->assign("popedom",$this->popedom);
	}

	//显示订单列表
	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1)*$psize;
		$pageurl = $this->url('order');
		$statuslist = $this->model('order')->status_list();
		$this->assign('statuslist',$statuslist);

		$condition = "1=1";
		$status = $this->get("status");
		if($status){
			$condition .= " AND o.status='".$status."'";
			$pageurl .= "&status=".rawurlencode($status);
			$this->assign('status',$status);
		}
		$keytype = $this->get('keytype');
		if(!$keytype){
			$keytype = 'sn';
		}
		$keywords = $this->get('keywords');
		if($keywords){
			if($keytype == 'sn' || $keytype == 'email'){
				$condition .= " AND o.".$keytype." LIKE '%".$keywords."%'";
			}elseif($keytype == 'user'){
				$condition .= " AND u.user LIKE '%".$keywords."%'";
			}elseif($keytype == 'protitle'){
				$condition .= " AND o.id IN(SELECT order_id FROM ".$this->db->prefix."order_product WHERE title LIKE '%".$keywords."%')";
			}
			$pageurl .= "&keywords=".rawurlencode($keywords)."&keytype=".$keytype;
			$this->assign('keytype',$keytype);
			$this->assign('keywords',$keywords);
		}
		$price_min = $this->get('price_min');
		if($price_min != ''){
			$condition .= " AND o.price>=".$price_min;
			$pageurl .= "&price_min=".rawurlencode($price_min);
			$this->assign('price_min',$price_min);
		}
		$price_max = $this->get('price_max');
		if($price_max != ''){
			$condition .= " AND o.price<=".$price_max;
			$pageurl .= "&price_max=".rawurlencode($price_max);
			$this->assign('price_max',$price_max);
		}
		$date_start = $this->get('date_start');
		if($date_start){
			$condition .= " AND o.addtime>=".strtotime($date_start);
			$pageurl .= "&date_start=".rawurlencode($date_start);
			$this->assign('date_start',$date_start);
		}
		$date_stop = $this->get('date_stop');
		if($date_stop){
			$condition .= " AND o.addtime<=".strtotime($date_stop);
			$pageurl .= "&date_stop=".rawurlencode($date_stop);
			$this->assign('date_stop',$date_stop);
		}
		$paytype = $this->get('paytype','int');
		if($paytype){
			$condition .= " AND p.payment_id='".$paytype."'";
			$pageurl .= "&paytype=".$paytype;
			$this->assign('paytype',$paytype);
		}
		$total = $this->model('order')->get_count($condition);
		if($total>0){
			$paylist = $this->model('payment')->get_all('','id');
			if(!$paylist){
				$paylist = array();
			}else{
				$this->assign('paylist',$paylist);
			}
			$tmp = array();
			foreach($paylist AS $key=>$value){
				$tmp[$value['id']] = $value;
			}
			$paylist = $tmp;
			$rslist = $this->model('order')->get_list($condition,$offset,$psize);
			if(!$rslist) $rslist = array();
			foreach($rslist AS $key=>$value){
				//如果不符合条件，跳过
				if(!$value['pay_id'] || !$paylist[$value['pay_id']] || !$value['ext']) continue;
				$code = $paylist[$value['pay_id']]['code'];
				if(!$this->plugin[$code] || !$this->plugin[$code]['method']) continue;
				if(!in_array('format',$this->plugin[$code]['method'])) continue;
				$extlist = $this->plugin[$code]['obj']->format($value['ext']);
				if($extlist && is_array($extlist)){
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
		$this->view("order_list");
	}

	//物流维护管理
	public function express_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('order')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('订单信息不存在'));
		}
		$this->assign('rs',$rs);
		//判断订单是否需要物流
		$address = $this->model('order')->address($id);
		if(!$address){
			$this->error(P_Lang('该订单未设置收货地址，请先设置'));
		}
		$rslist = $this->model('order')->express_all($id);
		$loglist = $this->model('order')->log_list($id);
		if($rslist && $loglist){
			foreach($rslist as $key=>$value){
				foreach($loglist as $k=>$v){
					if($v['order_express_id'] == $value['id']){
						$rslist[$key]['invoicelist'][] = $v;
					}
				}
				$rslist[$key]['invoice_total'] = $rslist[$key]['invoicelist'] ? (count($rslist[$key]['invoicelist']) + 1) : 1;
			}
		}
		$this->assign('rslist',$rslist);
		$expresslist = $this->model('express')->get_all();
		$this->assign('expresslist',$expresslist);
		$this->view('order_express');
	}

	public function express_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$express_id = $this->get('express_id','int');
		if(!$express_id){
			$this->json(P_Lang('未指定物流公司'));
		}
		$code = $this->get('code');
		if(!$code){
			$this->json(P_Lang('未填写物流单号'));
		}
		$express = $this->model('express')->get_one($express_id);
		$array = array('order_id'=>$id,'express_id'=>$express_id,'code'=>$code,'addtime'=>$this->time);
		$array['title'] = $express['title'];
		$array['homepage'] = $express['homepage'];
		$array['company'] = $express['company'];
		$insert = $this->model('order')->express_save($array);
		if(!$insert){
			$this->json(P_Lang('写入失败'));
		}
		//增加订单日志
		$data = array('order_id'=>$id,'order_express_id'=>$insert,'note'=>P_Lang('您的订单已经拣货完毕，待出库交付{title}，运单号为：{code}',array('title'=>$express['title'],'code'=>$code)));
		$this->model('order')->log_save($data);
		//更新订单状态
		$this->model('order')->update_order_status($id,'shipped');
		$this->json(true);
	}

	public function express_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('order')->express_one($id);
		if(!$rs){
			$this->json(P_Lang('物流信息不存在'));
		}
		if($rs['is_end']){
			$this->json(P_Lang('物流数据已完成，不能执行删除'));
		}
		$this->model('order')->express_delete($id);
		$this->json(true);
	}
	//删除订单操作
	public function delete_f()
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
	public function status_f()
	{
		$id = $this->get('id');
		if(!$this->popedom['status']) $this->json(P_Lang('您没有权限执行此操作'));
		$array = array('status'=>'CHECKED');
		$this->model('order')->save($array,$id);
		$this->json(P_Lang('审核成功'),true);
	}

	//查看订单信息
	public function info_f()
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

	public function set_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			if(!$this->popedom['add']){
				error(P_Lang('您没有权限执行此操作'),$this->url('order'),'error');
			}
			$rs = array('status'=>'create');
		}else{
			if(!$this->popedom['modify']){
				error(P_Lang('您没有权限执行此操作'),$this->url('order'),'error');
			}
			$this->assign('id',$id);
			$rs = $this->model('order')->get_one($id);
			$rslist = $this->model('order')->product_list($id);
			$this->assign('rslist',$rslist);
			$address = $this->model('order')->address($id);
			$this->assign('shipping',$address);
			$invoice = $this->model('order')->invoice($id);
			$this->assign('invoice',$invoice);
		}
		$this->assign('rs',$rs);
		$site_rs = $this->model('site')->get_one($_SESSION['admin_site_id']);
		$this->assign("site_rs",$site_rs);

		//读取网站货币
		$currency_list = $this->model('currency')->get_list();
		$this->assign("currency_list",$currency_list);
		//付款方式
		$paylist = $this->model('payment')->opt_all($_SESSION['admin_site_id']);
		$this->assign('paylist',$paylist);

		$statuslist = $this->model('site')->order_status_all(true);
		$this->assign('statuslist',$statuslist);

		//读取订单价格循环
		$pricelist = $this->model('site')->price_status_all(true);
		$this->assign('pricelist',$pricelist);

		$price = $this->model('order')->order_price($id);
		$this->assign('price',$price);

		$payinfo = $this->model('order')->order_payment($id);
		$this->assign('payinfo',$payinfo);

		$loglist = $this->model('order')->log_list($id);
		$this->assign('loglist',$loglist);
		
		$this->view("order_set");
	}

	//删除产品信息
	function product_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定产品ID'));
		}
		if(!$this->popedom['modify']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$product_info = $this->model('order')->product_one($id);
		$this->model('order')->product_delete($id);
		$info = P_Lang('管理员删除订单产品');
		$log = array('order_id'=>$product_info['order_id'],'note'=>$info);
		$this->model('order')->log_save($log);
		$this->json(true);
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
		if($exinclude){
			$tmp = explode(",",$exinclude);
			$exinclude = '';
			foreach($tmp AS $key=>$value){
				$value = intval($value);
				if($value) $exinclude[] = $value;
			}
			$exinclude = implode(',',$exinclude);
			$condition .= " AND l.id NOT IN(".$exinclude.")";
			$pageurl .= "&exinclude=".rawurlencode($exinclude);
			$formurl .= "&exinclude=".rawurlencode($exinclude);
		}
		$keywords = $this->get('keywords');
		if($keywords){
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
		if($total<1){
			error_open(P_Lang('没有产品信息'));
		}
		$rslist = $this->model('list')->get_all($condition,$offset,$psize,'id');
		if($rslist){
			$bizlist = $this->model('list')->biz_all(array_keys($rslist));
			foreach($bizlist as $key=>$value){
				foreach($value as $k=>$v){
					$rslist[$key][$k] = $v;
				}
			}
		}
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
	private function modify_save($id)
	{
		$old = $this->model('order')->get_one($id);
		if(!$old){
			$this->json(P_Lang('订单信息不存在'));
		}
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
		$main['note'] = $this->get('note');
		$main['status'] = $this->get('status');
		$main['email'] = $this->get('email');
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
			if(!$tmp_qty){
				$tmp_qty = 1;
			}
			$tmp_id = intval($pro_tid[$key]);
			$tmp_price = floatval($pro_price[$key]);
			$array = array(
				'tid'=>$tmp_id,
				'title'=>$tmp_title,
				'price'=>$tmp_price,
				'qty'=>$tmp_qty,
				'thumb'=>$pro_thumb[$key],
				'order_id'=>$id
			);
			if($tmp_id){
				$product = $this->model('list')->get_one($tmp_id,false);
				if($product){
					$array['weight'] = $product['weight'];
					$array['unit'] = $product['unit'];
					$array['volume'] = $product['volume'];
				}
			}
			if($value && $value != 'add'){
				$this->model('order')->save_product($array,$value);
			}else{
				$this->model('order')->save_product($array);
				$info = P_Lang('管理员增加产品信息');
				$log = array('order_id'=>$id,'note'=>$info);
				$this->model('order')->log_save($log);
			}
		}
		//存储收货地址
		$shipping = $this->address('s');
		if($shipping){
			$shipping['order_id'] = $id;
			$sid = $this->get('s-id','int');
			$this->model('order')->save_address($shipping,$sid);
		}
		//删除已有扩展价格内容
		$this->model('order')->delete_order_price($id);
		$ext_price = $this->get('ext_price');
		if($ext_price && is_array($ext_price)){
			foreach($ext_price as $key=>$value){
				$tmp = array('order_id'=>$id,'code'=>$key,'price'=>$value);
				$this->model('order')->save_order_price($tmp);
			}
		}
		//存储发票
		$invoice = array('type'=>$this->get('invoice_type'),'title'=>$this->get('invoice_title'),'content'=>$this->get('invoice_content'));
		if($invoice['type'] && $invoice['title']){
			$invoice['order_id'] = $id;
			$this->model('order')->save_invoice($invoice);
		}
		$pay_id = $this->get('pay_id');
		$pay_price = $this->get('pay_price');
		if($pay_price && $pay_id){
			$old_payment = $this->model('order')->order_payment($id);
			$old_id = $old_payment ? $old_payment['id'] : 0;
			if(!$old_payment){
				$info = P_Lang('管理员录入支付信息');
				$log = array('order_id'=>$id,'note'=>$info);
				$this->model('order')->log_save($log);
			}else{
				if($old_payment['payment_id'] != $pay_id){
					$info = P_Lang('管理员变更支付信息');
					$log = array('order_id'=>$id,'note'=>$info);
					$this->model('order')->log_save($log);
				}
			}
			$payment = $this->model('payment')->get_one($pay_id);
			$pay_date = $this->get('pay_date');
			$pay_date = $pay_date ? strtotime($pay_date) : $this->time;
			$array = array('order_id'=>$id,'payment_id'=>$pay_id,'title'=>$payment['title'],'price'=>$pay_price);
			$array['dateline'] = $pay_date;
			$this->model('order')->save_payment($array,$old_id);
		}else{
			$old_payment = $this->model('order')->order_payment($id);
			if($old_payment){
				$array = array('dateline'=>0);
				$this->model('order')->save_payment($array,$old_payment['id']);
			}
		}
		$this->model('order')->save($main,$id);
		if($main['status'] != $old['status']){
			$param = 'id='.$id."&status=".$main['status'];
			$this->model('task')->add_once('order',$param);
		}
		$this->json(true);
	}

	//添加存储信息
	private function add_save()
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
		$main['user_id'] = $this->get('user_id');
		$main['addtime'] = $this->time;
		$main['status'] = $this->get('status');
		$main['passwd'] = $passwd;
		$main['note'] = $this->get('note');
		$main['email'] = $this->get('email');
		$main['price'] = $this->get('price','float');
		$main['currency_id'] = $this->get('currency_id','int');
		$order_id = $this->model('order')->save($main);
		if(!$order_id){
			$this->json(P_Lang('订单创建失败，请检查'));
		}
		//读取扩展价格的存储
		$ext_price = $this->get('ext_price');
		if($ext_price && is_array($ext_price)){
			foreach($ext_price as $key=>$value){
				$tmp = array('order_id'=>$order_id,'code'=>$key,'price'=>$value);
				$this->model('order')->save_order_price($tmp);
			}
		}
		//保存相应的产品信息
		$prolist = $this->get('pro_id');
		$pro_title = $this->get('pro_title');
		$pro_thumb = $this->get('pro_thumb');
		$pro_tid = $this->get('pro_tid');
		$pro_price = $this->get('pro_price');
		$pro_qty = $this->get('pro_qty');
		if(!$prolist || !is_array($prolist)){
			$this->model('order')->delete($order_id);
			$this->json(P_Lang('没有指定产品信息'));
		}
		$total_price = 0;
		$total_qty = 0;
		foreach($prolist AS $key=>$value){
			$tmp_title = $pro_title[$key];
			if(!$tmp_title || !trim($tmp_title)){
				continue;
			}
			$tmp_qty = intval($pro_qty[$key]);
			if(!$tmp_qty){
				$tmp_qty = 1;
			}
			$total_qty += $tmp_qty;
			$tmp_price = floatval($pro_price[$key]);
			$total_price += $tmp_price * $tmp_qty;
			$tmp_id = intval($pro_tid[$key]);
			$tmp = array('tid'=>$tmp_id,'title'=>$tmp_title,'price'=>$tmp_price,'qty'=>$tmp_qty,'thumb'=>$pro_thumb[$key]);
			if($tmp_id){
				$product = $this->model('list')->get_one($tmp_id,false);
				if($product){
					$tmp['weight'] = $product['weight'];
					$tmp['unit'] = $product['unit'];
					$tmp['volume'] = $product['volume'];
				}
			}
			$tmp['order_id'] = $order_id;
			$this->model('order')->save_product($tmp);
		}
		//保存订单地址信息
		$address = $this->address('s');
		if($address && $address['fullname'] && $address['address'] && ($address['mobile'] || $address['tel'])){
			$address['order_id'] = $order_id;
			$this->model('order')->save_address($address);
		}
		$invoice = array('type'=>$this->get('invoice_type'),'title'=>$this->get('invoice_title'),'content'=>$this->get('invoice_content'));
		if($invoice['type'] && $invoice['title']){
			$invoice['order_id'] = $order_id;
			$this->model('order')->save_invoice($invoice);
		}
		//保存日志
		$adminer = $this->model('admin')->get_one($_SESSION['admin_id']);
		$who = $adminer['fullname'] ? $adminer['fullname'].'('.$adminer['account'].')' : $adminer['account'];
		$info = P_Lang('管理员创建订单，等待系统确认');
		$log = array('order_id'=>$order_id,'note'=>$info);
		$this->model('order')->log_save($log);
		//判断在线支付
		$pay_id = $this->get('pay_id');
		$pay_price = $this->get('pay_price');
		if($pay_price && $pay_id){
			$payment = $this->model('payment')->get_one($pay_id);
			$pay_date = $this->get('pay_date');
			$pay_date = $pay_date ? strtotime($pay_date) : $this->time;
			$array = array('order_id'=>$order_id,'payment_id'=>$pay_id,'title'=>$payment['title'],'price'=>$pay_price);
			$array['dateline'] = $pay_date;
			$this->model('order')->save_payment($array);
			$info = P_Lang('管理员录入支付信息');
			$log = array('order_id'=>$order_id,'note'=>$info);
			$this->model('order')->log_save($log);
		}
		$param = 'id='.$order_id."&status=".$main['status'];
		$this->model('task')->add_once('order',$param);
		$this->json(true);
	}

	//地址库
	function address($type="s",$sid=0)
	{
		$array = array();
		$array['country'] = $this->get($type."-country");
		$array['province'] = $this->get($type."-province");
		$array['city'] = $this->get($type."-city");
		$array['county'] = $this->get($type."-county");
		$array['address'] = $this->get($type."-address");
		$array['mobile'] = $this->get($type."-mobile");
		$array['tel'] = $this->get($type."-tel");
		$array['email'] = $this->get($type."-email");
		$array['fullname'] = $this->get($type."-fullname");
		return $array;
	}

	public function payment_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$_SESSION['admin_rs']['if_system']){
			$this->json(P_Lang('您没有权限，仅限系统管理员操作'));
		}
		$this->model('order')->order_payment_delete($id);
		$this->json(true);
	}
}

?>