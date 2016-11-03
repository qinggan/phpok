<?php
/**
 * 订单管理，编辑和删除等相关操作
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年10月03日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_control extends phpok_control
{
	/**
	 * 权限
	**/
	private $popedom;

	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("order");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 显示订单列表
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
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
			$condition .= " AND o.id IN(SELECT order_id FROM ".$this->db->prefix."order_payment WHERE payment_id='".$paytype."') ";
			$pageurl .= "&paytype=".$paytype;
			$this->assign('paytype',$paytype);
		}
		$total = $this->model('order')->get_count($condition);
		if(!$total){
			$this->view("order_list");
		}
		$paylist = $this->model('payment')->get_all('','id');
		if(!$paylist){
			$paylist = array();
		}
		$this->assign('paylist',$paylist);
		$rslist = $this->model('order')->get_list($condition,$offset,$psize);
		if(!$rslist){
			$rslist = array();
		}
		foreach($rslist as $key=>$value){
			if(!$value['status_title']){
				$value['status_title'] = $statuslist[$value['status']] ? $statuslist[$value['status']] : $value['status'];
				$rslist[$key] = $value;
			}
		}
		$this->assign('rslist',$rslist);
		$this->assign('total',$total);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign('pagelist',$pagelist);
		$this->view("order_list");
	}

	/**
	 * 结束订单操作
	 * @参数 id 订单ID号
	 * @参数 act 动作，仅限cancel，stop，end
	 * @返回 JSON数据
	 * @更新时间 2016年10月04日
	**/
	public function end_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定订单ID'));
		}
		$act = $this->get('act','system');
		if(!$act){
			$this->error(P_Lang('未指定要操作的动作'));
		}
		if(!in_array($act,array('cancel','stop','end'))){
			$this->error(P_Lang('执行操作不符合要求'));
		}
		if(!$this->popedom[$act]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rs = $this->model('order')->get_one($id);
		if($rs['status'] == $act || in_array($rs['status'],array('cancel','stop','end'))){
			$this->error(P_Lang('动作已执行过，不能重复执行了'));
		}
		$statuslist = $this->model('order')->status_list();
		$this->model('order')->update_order_status($id,$act,$statuslist[$act]);
		//如果订单动作是完成
		if($act == 'end'){
			//赠送订单积分
			$this->model('wealth')->order($id,P_Lang('订单完成赚送积分'));
		}
		$this->success();
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
		$tip = P_Lang('您的订单已经拣货完毕，待出库交付{title}，运单号为：{code}',array('title'=>$express['title'],'code'=>$code));
		$data = array('order_id'=>$id,'order_express_id'=>$insert,'note'=>$tip);
		$rs = $this->model('order')->get_one($id);
		if($rs['status'] != 'shipping'){
			$this->model('order')->log_save($data);
			$this->model('order')->update_order_status($id,'shipping');
		}
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

	//查看订单信息
	public function info_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}

		$rs = $this->model('order')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('订单信息不存在'));
		}
		//订单状态
		$statuslist = $this->model('order')->status_list();
		if(!$rs['status_title']){
			$rs['status_title'] = $statuslist[$rs['status']] ? $statuslist[$rs['status']] : $rs['status'];
		}
		$this->assign('rs',$rs);
		$this->assign('statuslist',$statuslist);
		//取得订单的地址
		$address = $this->model('order')->address($id);
		$this->assign('shipping',$address);
		//订单下的产品列表
		$rslist = $this->model('order')->product_list($id);
		$this->assign('rslist',$rslist);
		//取得支付记录
		$paylist = $this->model('order')->payment_all($id);
		$this->assign('paylist',$paylist);
		$paid_price = $this->model('order')->paid_price($id);
		$this->assign('paid_price',$paid_price);
		$unpaid_price = $this->model('order')->unpaid_price($id);
		$this->assign('unpaid_price',$unpaid_price);
		if($rs['user_id']){
			$user = $this->model('user')->get_one($rs['user_id']);
			$this->assign('user',$user);
		}
		$loglist = $this->model('order')->log_list($id);
		$this->assign('loglist',$loglist);

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
	public function product_delete_f()
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
	public function prolist_f()
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
	public function product_f()
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
	public function thumb_f()
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

	/**
	 * 存储订单信息
	**/
	public function save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$sn = $this->get('sn');
			if(!$sn){
				$this->error(P_Lang('订单编号不能为空'));
			}
			if(!preg_match('/^[a-z0-9A-Z\_\-]+$/u',$sn)){
				$this->error(P_Lang('订单编号不合要求，限字母、数字、下划线及中划线'));
			}
			$rs = $this->model('order')->get_one_from_sn($sn);
			if($rs){
				$this->error(P_Lang('订单编号已被使用，请换个编号'));
			}
		}else{
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$old = $this->model('order')->get_one($id);
			if(!$old){
				$this->error(P_Lang('订单信息不存在'));
			}
		}
		$main = array();
		$main['user_id'] = $this->get('user_id','int');
		$main['price'] = $this->get('price','float');
		$main['currency_id'] = $this->get('currency_id','int');
		if($main['currency_id']){
			$main['currency_rate'] = $this->model('currency')->rate($main['currency_id']);
		}
		$main['status'] = $id ? $this->get('status') : 'create';
		if($main['status']){
			$statuslist = $this->model('order')->status_list();
			$main['status_title'] = $statuslist[$main['status']] ? $statuslist[$main['status']] : '';
		}
		$passwd = $this->get('passwd');
		if(!$passwd){
			$this->error(P_Lang('订单密码不能为空'));
		}
		if(!preg_match('/^[a-z0-9A-Z\_\-]+$/u',$passwd)){
			$this->error(P_Lang('订单密码不合要求，限字母、数字、下划线及中划线'));
		}
		$main['passwd'] = $passwd;
		$main['note'] = $this->get('note');
		$main['email'] = $this->get('email');
		$main['mobile'] = $this->get('mobile');
		if(!$id){
			$main['sn'] = $sn;
			$main['addtime'] = $this->time;
		}
		//检查产品数据是否完整
		$pro_id = $this->get('pro_id');
		if(!$pro_id || !is_array($pro_id)){
			$this->error(P_Lang('产品信息为空，订单异常，请添加产品或删除订单信息'));
		}
		if(!$id){
			$order_id = $this->model('order')->save($main);
			if(!$order_id){
				$this->error(P_Lang('订单创建失败，请检查'));
			}
			$act_id = $order_id;
		}else{
			$this->model('order')->save($main,$id);
			$act_id = $id;
		}
		$this->_save_products($act_id);
		$this->_save_address($act_id);
		$this->_save_invoice($act_id);
		$this->_save_price($act_id);
		if($id){
			if($main['status'] && $main['status'] != $old['status']){
				$this->model('order')->update_order_status($id,$main['status'],$main['status_title']);
			}else{
				$log = array('order_id'=>$order_id,'note'=>P_Lang('管理员编辑订单'));
				$this->model('order')->log_save($log);
			}
		}else{
			$log = array('order_id'=>$order_id,'note'=>P_Lang('管理员创建订单'));
			$this->model('order')->log_save($log);
			$param = 'id='.$order_id."&status=".$main['status'];
			$this->model('task')->add_once('order',$param);
		}
		$this->success();
	}

	private function _save_price($order_id=0)
	{
		$this->model('order')->delete_order_price($order_id);
		$pricelist = $this->model('site')->price_status_all(false);
		$ext_price = $this->get('ext_price');
		if($ext_price && is_array($ext_price) && $pricelist){
			foreach($ext_price as $key=>$value){
				$value = abs($value);
				if($pricelist[$key] && $pricelist[$key]['action'] != 'add'){
					$value = -$value;
				}
				$tmp = array('order_id'=>$order_id,'code'=>$key,'price'=>$value);
				$this->model('order')->save_order_price($tmp);
			}
		}
		return true;
	}

	/**
	 * 保存发票
	 * @参数 $order_id 订单ID
	**/
	private function _save_invoice($order_id=0)
	{
		$array = array();
		$array['type'] = $this->get('invoice_type');
		$array['title'] = $this->get('invoice_title');
		$array['content'] = $this->get('invoice_content');
		$array['note'] = $this->get('invoice_note');
		$array['order_id'] = $order_id;
		if($array['title'] && $array['type']){
			$this->model('order')->save_invoice($array);
		}
		return true;
	}


	private function _save_products($order_id=0)
	{
		$pro_tmp = $this->get('pro_tmp');
		$pro_id = $this->get('pro_id');
		$pro_title = $this->get('pro_title');
		$pro_thumb = $this->get('pro_thumb');
		$pro_tid = $this->get('pro_tid','int');
		$pro_price = $this->get('pro_price','float');
		$pro_qty = $this->get('pro_qty','int');
		$pro_weight = $this->get('pro_weight','float');
		$pro_volume = $this->get('pro_volume','float');
		$pro_note = $this->get('pro_note');
		$pro_virtual = $this->get('pro_virtual','int');
		$pro_unit = $this->get('pro_unit');
		$prolist = array();
		//删除不存在的商品
		$idlist = false;
		foreach($pro_id as $key=>$value){
			if($value && is_numeric($value)){
				if(!$idlist){
					$idlist = array();
				}
				$idlist[] = $value;
			}
		}
		if($idlist){
			$this->model('order')->order_product_clearup($order_id,$idlist);
		}
		foreach($pro_id as $key=>$value){
			$tmp_title = $pro_title[$key];
			if(!$tmp_title || !trim($tmp_title)){
				continue;
			}
			$tmp_qty = intval($pro_qty[$key]);
			if(!$tmp_qty){
				$tmp_qty = 1;
			}
			$array = array(
				'order_id'=>$order_id,
				'tid'=>intval($pro_tid[$key]),
				'title'=>$tmp_title,
				'price'=>floatval($pro_price[$key]),
				'qty'=>$tmp_qty,
				'thumb'=>$pro_thumb[$key],
				'weight'=>$pro_weight[$key],
				'volume'=>$pro_volume[$key],
				'unit'=>$pro_unit[$key],
				'note'=>$pro_note[$key],
				'is_virtual'=>$pro_virtual[$key]
			);
			//属性
			$tmp_ext_id = $pro_tmp[$key];
			$ext_title = $this->get('ext_title_'.$tmp_ext_id);
			$ext_content = $this->get('ext_content_'.$tmp_ext_id);
			if($ext_title && $ext_content && is_array($ext_title) && is_array($ext_content)){
				$tmp_ext = array();
				foreach($ext_title as $k=>$v){
					if($v && $ext_content[$k]){
						$tmp_ext[] = array('title'=>$v,'content'=>$ext_content[$k]);
					}
				}
				if($tmp_ext && count($tmp_ext)>0){
					$array['ext'] = serialize($tmp_ext);
				}
			}
			if($value && $value != 'add'){
				$this->model('order')->save_product($array,$value);
			}else{
				$this->model('order')->save_product($array);
			}
		}
		return true;
	}

	/**
	 * 保存订单地址
	 * @参数 $order_id 订单ID号
	 * @返回 true
	**/
	private function _save_address($order_id)
	{
		$array = array();
		$array['country'] = $this->get("s-country");
		$array['province'] = $this->get("s-province");
		$array['city'] = $this->get("s-city");
		$array['county'] = $this->get("s-county");
		$array['address'] = $this->get("s-address");
		$array['mobile'] = $this->get("s-mobile");
		$array['tel'] = $this->get("s-tel");
		$array['email'] = $this->get("s-email");
		$array['fullname'] = $this->get("s-fullname");
		$array['order_id'] = $order_id;
		$sid = $this->get('s-id','int');
		$this->model('order')->save_address($array,$sid);
		return true;
	}

	public function payment_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		if(!$_SESSION['admin_rs']['if_system']){
			$this->error(P_Lang('您没有权限，仅限系统管理员操作'));
		}
		$this->model('order')->order_payment_delete($id);
		$this->success();
	}

	/**
	 * 检测是否支持物流
	 * @参数 id 订单ID
	**/
	public function express_check_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定订单ID'));
		}
		$total = $this->model('order')->check_need_express($id);
		$this->success($total);
	}

	/**
	 * 支付记录
	 * @参数 id 订单ID
	**/
	public function payment_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定订单ID'));
		}
		$rs = $this->model('order')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('订单不存在'));
		}
		if(in_array($rs['status'],array('cancel','stop','end'))){
			$this->error(P_Lang('订单已结束/取消/完成，不能执行此操作'));
		}
		$this->assign('rs',$rs);
		if($rs['currency_id']){
			$currency = $this->model('currency')->get_one($rs['currency_id']);
			$this->assign('currency',$currency);
		}
		$loglist = $this->model('order')->payment_all($id);
		$this->assign('loglist',$loglist);
		$paylist = $this->model('payment')->get_all('','id');
		$this->assign('paylist',$paylist);
		//隐藏录入
		$payend = $this->model('order')->check_payment_is_end($id);
		if(!$payend){
			$unpaid_price = $this->model('order')->unpaid_price($id);
			$this->assign('unpaid_price',$unpaid_price);
		}
		$this->assign('payend',$payend);
		$this->view('order_payment');
	}

	/**
	 * 保存支付方法
	 * @参数 id 订单ID
	 * @参数 payment_id 支付方法
	 * @参数 title 自设支付方法名称
	 * @参数 price 支付金额
	 * @参数 dateline 支付时间
	 * @返回 JSON数据
	**/
	public function payment_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定订单ID'));
		}
		$rs = $this->model('order')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('订单不存在'));
		}
		if(in_array($rs['status'],array('cancel','stop','end'))){
			$this->error(P_Lang('订单已结束/取消/完成，不能执行此操作'));
		}
		$payment_id = $this->get('payment_id');
		if(!$payment_id){
			$this->error(P_Lang('未设置支付方式'));
		}
		if($payment_id && $payment_id != 'other'){
			$payment_rs = $this->model('payment')->get_one($payment_id);
			$title = $payment_rs ? $payment_rs['title'] : '';
		}
		if(!$title){
			$title = $this->get('title');
			if(!$title){
				$this->error(P_Lang('支付方式名称不能为空'));
			}
		}
		if($this->model('order')->check_payment_is_end($id)){
			$this->error(P_Lang('订单金额已付清，不能再录入'));
		}
		$dateline = $this->get('dateline');
		if($dateline){
			$dateline.= " ".date("H:i:s",$this->time);
			$dateline = strtotime($dateline);
		}
		if(!$dateline){
			$dateline = $this->time;
		}
		$price = $this->get('price','float');
		if($price<0.01){
			$this->error(P_Lang('支付金额不能少于0.01元'));
		}
		$unpaid_price = $this->model('order')->unpaid_price($id);
		if($unpaid_price < $price){
			$price = $unpaid_price;
		}
		$array = array('order_id'=>$id,'payment_id'=>intval($payment_id),'title'=>$title);
		$array['price'] = $price;
		$array['startdate'] = $dateline;
		$array['dateline'] = $dateline;
		$note = $this->get('note');
		if($note){
			$tmp = array(P_Lang('备注')=>$note);
			$array['ext'] = serialize($tmp);
		}
		$this->model('order')->save_payment($array);
		//判断
		if($rs['status'] == 'create'){
			$act = $this->model('order')->check_payment_is_end($id) ? 'paid' : 'unpaid';
			$this->model('order')->update_order_status($id,$act);
		}
		if($rs['status'] == 'unpaid' && $this->model('order')->check_payment_is_end($id)){
			$this->model('order')->update_order_status($id,'paid');
		}
		$tmp = array('title'=>$title,'price'=>price_format($price,$rs['currency_id'],$rs['currency_id']));
		$tip = P_Lang('录入支付信息，支付方式：{title}，支付金额：{price}',$tmp);
		$data = array('order_id'=>$id,'order_express_id'=>0,'note'=>$tip);
		$this->model('order')->log_save($data);
		$this->success();
	}
}