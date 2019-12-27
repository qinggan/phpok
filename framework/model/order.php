<?php
/**
 * 订单信息及管理
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年08月13日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_model_base extends phpok_model
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得订单列表
	 * @参数 $condition 查询条件，仅限主表查询
	 * @参数 $offset 起始位置，从第一个开始查为0
	 * @参数 $psize 查询数量，默认是20页
	 * @返回 false/结果集数组
	**/
	public function get_list($condition='',$offset=0,$psize=20)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$offset = intval($offset);
		$psize = intval($psize);
		$sql .= " ORDER BY addtime DESC,id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext']){
				$value['ext'] = unserialize($value['ext']);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 查询订单数量
	 * @参数 $condition 查询条件，仅限主表中使用
	 * @返回 具体订单数量
	**/
	public function get_count($condition="")
	{
		$sql = "SELECT count(o.id) FROM ".$this->db->prefix."order o ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 查询订单总金额
	 * @参数 $condition 查询条件，仅限主表中使用
	**/
	public function get_price($condition='')
	{
		$sql = "SELECT SUM(o.price) FROM ".$this->db->prefix."order o ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	public function product_count($condition="")
	{
		$sql = "SELECT SUM(op.qty) FROM ".$this->db->prefix."order_product op ";
		$sql.= "LEFT JOIN ".$this->db->prefix."order o ON(op.order_id=o.id) ";
		$sql.= "LEFT JOIN ".$this->db->prefix."list l ON(op.tid=l.id) ";
		if($condition){
			$sql.= "WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	public function product_price($condition='')
	{
		$sql = "SELECT SUM(op.price*op.qty) FROM ".$this->db->prefix."order_product op ";
		$sql.= "LEFT JOIN ".$this->db->prefix."order o ON(op.order_id=o.id) ";
		$sql.= "LEFT JOIN ".$this->db->prefix."list l ON(op.tid=l.id) ";
		if($condition){
			$sql.= "WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 取得订单的最大ID号，再此基础上+1
	**/
	public function maxid()
	{
		$sql = "SELECT MAX(id) id FROM ".$this->db->prefix."order";
		$rs = $this->db->get_one($sql);
		if(!$rs) return '1';
		return ($rs['id']+1);
	}

	/**
	 * 保存订单信息
	 * @参数 $data 数组
	 * @参数 $id 为0或空表示添加新订单
	 * @返回 true/false/订单ID号
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($data['ext'] && is_array($data['ext'])){
			$data['ext'] = serialize($data['ext']);
		}
		if($id){
			return $this->db->update_array($data,"order",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"order");
		}
	}

	/**
	 * 存储商品信息
	 * @参数 $data 产品信息，数组
	 * @参数 $id order_product表中的主键ID，为0为空表示新增
	**/
	public function save_product($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($data['ext'] && is_array($data['ext'])){
			$data['ext'] = serialize($data['ext']);
		}
		if($id){
			return $this->db->update_array($data,"order_product",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"order_product");
		}
	}

	/**
	 * 保存收件人地址
	 * @参数 $data 地址信息，数组
	 * @参数 $id order_address表中的主键ID，为0为空表示新增
	**/
	public function save_address($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"order_address",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"order_address");
		}
	}

	/**
	 * 保存发票信息
	 * @参数 $data 订单中的发票信息
	**/
	public function save_invoice($data)
	{
		return $this->db->insert_array($data,'order_invoice','replace');
	}

	/**
	 * 通过订单号取得单个订单信息
	 * @参数 $sn 订单编号
	 * @参数 $user 会员ID
	 * @返回 数组
	**/
	public function get_one_from_sn($sn,$user='')
	{
		return $this->get_one($sn,'sn',$user);
	}

	/**
	 * 取得订单信息
	 * @参数 $id 订单ID号或订单SN号
	 * @参数 $type 默认是id，支持sn和id
	 * @参数 $user 会员ID
	 * @返回 数组
	**/
	public function get_one($id,$type='id',$user='')
	{
		if(!$id){
			return false;
		}
		if($type != 'id' && $type != 'sn'){
			$type = 'id';
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order WHERE ".$type."='".$id."'";
		if($user){
			$sql .= " AND user_id='".$user."' ";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		return $rs;
	}

	/**
	 * 取得订单中的地址信息
	 * @参数 $id 订单号ID
	 * @参数 $type 地址类型
	**/
	public function address($id,$type='shipping')
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_address WHERE order_id='".$id."' AND type='".$type."'";
		$info = $this->db->get_one($sql);
		if(!$info){
			return false;
		}
		if(!trim($info['fullname']) && ($info['firstname'] || $info['lastname'])){
			$info['fullname'] = $info['firstname'].' '.$info['lastname'];
		}
		return $info;
	}

	/**
	 * 取得会员最后一次订单的地址
	 * @参数 $user_id 会员ID
	 * @参数 $is_virtual 是否使用虚拟服务里的地址，true不读地址，
	 * @返回 地址信息或false
	 * @更新时间 2016年09月08日
	**/
	public function last_address($user_id,$is_virtual=false)
	{
		if(!$user_id){
			return false;
		}
		if($is_virtual){
			$sql = "SELECT * FROM ".$this->db->prefix."order WHERE user_id='".$user_id."' ORDER BY id DESC LIMIT 1";
			$chk = $this->db->get_one($sql);
			$user = $this->model('user')->get_one($user_id);
			$email = ($chk && $chk['email']) ? $chk['email'] : $user['email'];
			$mobile = ($chk && $chk['mobile']) ? $chk['mobile'] : $user['mobile'];
			return array('email'=>$email,'mobile'=>$mobile);
		}
		$sql = "SELECT a.* FROM ".$this->db->prefix."order_address a ";
		$sql.= "LEFT JOIN ".$this->db->prefix."order o ON(a.order_id=o.id) ";
		$sql.= "WHERE o.user_id='".$user_id."' ORDER BY o.id DESC";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得订单下的产品信息
	 * @参数 $id 订单ID号
	 * @返回 数组
	**/
	public function product_list($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_product WHERE order_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext']){
				$value['ext'] = unserialize($value['ext']);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 取得订单中的发票信息
	 * @参数 $id 订单ID
	**/
	public function invoice($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_invoice WHERE order_id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 删除订单产品
	 * @参数 $id order_product中的主键ID，不是产品ID，也不是订单ID
	**/
	public function product_delete($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->product_one($id);
		if(!$rs){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."order_product WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得产品信息，仅限订单表order_product中
	 * @参数 $id order_product中的主键ID，不是产品ID，也不是订单ID
	**/
	public function product_one($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_product WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		return $rs;
	}

	/**
	 * 更新订单状态
	 * @参数 $id 订单ID
	 * @参数 $status 订单状态
	 * @参数 $note 订单状态日志说明
	 * @返回 true
	 * @更新时间 
	**/
	public function update_order_status($id,$status='',$note='')
	{
		$status_list = $this->model('site')->order_status_all();
		$status_info = $status_list[$status] ? $status_list[$status] : array('title'=>$status);
		$sql = "UPDATE ".$this->db->prefix."order SET status='".$status."',status_title='".$status_info['title']."' WHERE id='".$id."'";
		$this->db->query($sql);
		$param = 'id='.$id."&status=".$status;
		$this->model('task')->add_once('order',$param);
		$rs = $this->get_one($id);
		if(!$note){
			$note = P_Lang('订单（{sn}）状态变更为：{status}',array('sn'=>$rs['sn'],'status'=>$status_info['title']));
		}
		$who = $this->session->val('user_name') ? $this->session->val('user_name') : P_Lang('游客');
		$log = array('order_id'=>$id,'addtime'=>$this->time,'who'=>$who,'note'=>$note);
		$this->log_save($log);
		return true;
	}

	/**
	 * 订单日志
	 * @参数 $data 一维数组
	 * @返回 true 或 false 或 插件的日志ID
	 * @更新时间 2016年08月16日
	**/
	public function log_save($data)
	{
		if(!$data){
			return false;
		}
		if(!$data['addtime']){
			$data['addtime'] = $this->time;
		}
		if($this->app_id != 'admin' && $this->session->val('user_id')){
			$data['user_id'] = $this->session->val('user_id');
			if(!$data['who']){
				$data['who'] = $this->session->val('user_name');
			}
		}
		if($this->app_id == 'admin'){
			$data['admin_id'] = $this->session->val('admin_id');
			if(!$data['who']){
				$data['who'] = $this->session->val('admin_account');
			}
		}
		return $this->db->insert_array($data,'order_log');
	}

	/**
	 * 保存订单中的支付方式，对应表order_payment
	 * @参数 $data 数组
	 * @参数 $id 主键ID
	**/
	public function save_payment($data,$id=0)
	{
		if(!$data){
			return false;
		}
		if($data['ext'] && is_array($data['ext'])){
			$data['ext'] = serialize($data['ext']);
		}
		if($id){
			return $this->db->update_array($data,'order_payment',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'order_payment');
		}
	}

	/**
	 * 取得订单中的支付方式信息
	 * @参数 $order_id 订单ID
	 * @参数 $payment_id 支付方式ID，此项用于订单中有多条支付方式
	**/
	public function order_payment($order_id,$payment_id=0)
	{
		if(!$order_id){
			return false;
		}
		$condition = "";
		if($payment_id){
			$condition = "payment_id='".$payment_id."' ";
		}
		return $this->_order_payment($order_id,$condition);
	}

	public function delete_not_end_order($order_id)
	{
		if(!$order_id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."order_payment WHERE order_id='".$order_id."' AND dateline<1";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 取得订单中的未完工的支付方式信息
	 * @参数 $order_id 订单ID
	 * @参数 $payment_id 支付方式ID，此项用于订单中有多条支付方式
	**/
	public function order_payment_notend($order_id,$payment_id=0)
	{
		if(!$order_id){
			return false;
		}
		$condition = "dateline<1";
		if($payment_id){
			$condition .= " AND payment_id='".$payment_id."' ";
		}
		return $this->_order_payment($order_id,$condition);
	}

	protected function _order_payment($id,$condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_payment WHERE order_id='".$id."'";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		return $rs;
	}

	/**
	 * 订单全部支付记录
	 * @参数 $id 订单ID
	 * @返回 false 或 多组数组
	 * @更新时间 2016年10月03日
	**/
	public function payment_all($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_payment WHERE order_id='".intval($id)."' ORDER BY id ASC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext'] && is_string($value['ext'])){
				$tmp = unserialize($value['ext']);
				$value['ext'] = $tmp;
				if($tmp['wealth'] && $tmp['wealth_val']){
					$w = $this->model('wealth')->get_one($tmp['wealth']);
					$value['ext'] = P_Lang('使用财富（{title}）{price}{unit}支付',array('title'=>$w['title'],'price'=>$tmp['wealth_val'],'unit'=>$w['unit']));
				}
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	/**
	 * 检查订单是否是完成
	 * @参数 $order_id，订单ID号，如果订单未结束
	 * @返回 true 完成 或 false 未完成
	 * @更新时间 2016年08月13日
	**/
	public function check_payment_is_end($order_id)
	{
		$paid_price = $this->paid_price($order_id);
		if(!$paid_price){
			return false;
		}
		$rs = $this->get_one($order_id);
		if(!$rs){
			return false;
		}
		$price = $rs['price'];
		if(round($paid_price,2) != round($price,2)){
			return false;
		}
		return true;
	}

	/**
	 * 订单已支付金额
	 * @参数 $order_id 订单ID
	 * @返回 订单金额
	 * @更新时间 2016年08月13日
	**/
	public function paid_price($order_id)
	{
		$rs = $this->get_one($order_id);
		if(!$rs){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_payment WHERE order_id='".$order_id."' AND dateline>0";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return 0;
		}
		$paid_price = 0;
		foreach($rslist as $key=>$value){
			if(!$value['price']){
				continue;
			}
			$currency_id = (isset($value['currency_id']) && $value['currency_id']) ? $value['currency_id'] : $rs['currency_id'];
			$currency_rate = (isset($value['currency_rate']) && $value['currency_rate']) ? $value['currency_rate'] : 0;
			$price_val = price_format_val($value['price'],$currency_id,$rs['currency_id'],$rs['currency_rate'],$currency_rate);
			$paid_price += floatval($price_val);
		}
		return $paid_price;
	}

	/**
	 * 未支付的订单金额
	 * @参数 $order_id 订单ID
	 * @返回 订单金额
	 * @更新时间 2016年10月03日
	**/
	public function unpaid_price($order_id)
	{
		$paid_price = $this->paid_price($order_id);
		$rs = $this->get_one($order_id);
		if(round($paid_price,2) != round($rs['price'],2)){
			return round(($rs['price'] - $paid_price),4);
		}
		return '0.00';
	}

	/**
	 * 删除支付信息
	 * @参数 $id order_payment里的主键ID
	**/
	public function order_payment_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order_payment WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 订单价格信息 order_price中使用
	 * @参数 $order_id 订单ID
	**/
	public function order_price($order_id)
	{
		$sql = "SELECT code,price FROM ".$this->db->prefix."order_price WHERE order_id='".$order_id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$rs = array();
		foreach($rslist as $key=>$value){
			$rs[$value['code']] = $value['price'];
		}
		return $rs;
	}

	/**
	 * 订单状态列表
	**/
	public function status_list()
	{
		$list = $this->model('site')->order_status_all(false);
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$rslist[$key] = $value['title'];
		}
		return $rslist;
	}

	/**
	 * 取得指定会员下的余额及积分
	 * @参数 $user_id 会员ID
	 * @返回 false 或 余额多维数组列表
	 * @更新时间 2016年11月26日
	**/
	public function balance($user_id)
	{
		if(!$user_id){
			return false;
		}
		$wealthlist = $this->model('wealth')->get_all(1);
		if(!$wealthlist){
			return false;
		}
		$wlist = false;
		foreach($wealthlist as $key=>$value){
			if(!$value['ifcash']){
				continue;
			}
			$val = $this->model('wealth')->get_val($user_id,$value['id']);
			if(!$val){
				continue;
			}
			if($value['min_val'] && $val < $value['min_val']){
				continue;
			}
			$tmp = $value;
			$tmp['val'] = $val;
			$tmp['price'] = round($val*$value['cash_ratio']/100,$value['dnum']);
			if(!$wlist){
				$wlist = array();
			}
			if($value['ifpay']){
				$wlist['balance'][$tmp['id']] = $tmp;
			}else{
				$wlist['integral'][$tmp['id']] = $tmp;
			}
		}
		if(!$wlist){
			return false;
		}
		return $wlist;
	}

	/**
	 * 获取订单编号
	**/
	public function create_sn()
	{
		$sntype = $this->site['biz_sn'];
		if(!$sntype){
			$sntype = 'year-month-date-number';
		}
		$sn = '';
		$list = explode('-',$sntype);
		foreach($list AS $key=>$value){
			if($value == 'year'){
				$sn.= date("Y",$this->time);
			}
			if($value == 'month'){
				$sn.= date("m",$this->time);
			}
			if($value == 'date'){
				$sn.= date("d",$this->time);
			}
			if($value == 'hour'){
				$sn.= date('H',$this->time);
			}
			if($value == 'minute' || $value == 'minutes'){
				$sn.= date("i",$this->time);
			}
			if($value == 'second' || $value == 'seconds'){
				$sn.= date("s",$this->time);
			}
			if($value == 'rand' || $value == 'rands'){
				$sn .= rand(10,99);
			}
			if($value == 'time' || $value == 'times'){
				$sn .= $this->time;
			}
			if($value == 'number'){
				$condition = "FROM_UNIXTIME(addtime,'%Y-%m-%d')='".date("Y-m-d",$this->time)."'";
				$total = $this->model('order')->get_count($condition);
				if(!$total){
					$total = '0';
				}
				$total++;
				$sn .= str_pad($total,3,'0',STR_PAD_LEFT);
			}
			if($value == 'id'){
				//$sql = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA='".$this->db->database()."' AND TABLE_NAME ='".$this->db->prefix."order'";
				$sql = "SELECT max(id) FROM ".$this->db->prefix."order";
				$maxid = $this->db->count($sql);
				$maxid++;
				$sn .= str_pad($maxid,5,'0',STR_PAD_LEFT);
			}
			//包含会员信息
			if($value == 'user'){
				$sn .= $this->session->val('user_id') ? 'U'.str_pad($this->session->val('user_id'),5,'0',STR_PAD_LEFT) : 'G';
			}
			if(substr($value,0,6) == 'prefix'){
				$sn .= str_replace(array('prefix','[',']'),'',$value);
			}
		}
		return $sn;
	}

	/**
	 * 保存订单各种状态下的价格，使用表order_price
	 * @参数 $data 数组
	**/
	public function save_order_price($data)
	{
		return $this->db->insert_array($data,'order_price');
	}

	/**
	 * 积分抵扣费用
	 * @参数 $order_id 订单ID
	 * @参数 $price 价格
	**/
	public function integral_discount($order_id,$price=0)
	{
		if(!$price || !$order_id){
			return false;
		}
		$price = floatval($price);
		if($price<0){
			if(function_exists('abs')){
				$price = abs($price);
			}else{
				$price = -$price;
			}
		}
		$sql = "UPDATE ".$this->db->prefix."order_price SET price=price-".$price." WHERE code='discount' AND order_id='".$order_id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得订单里的财富基数
	 * @参数 $id 订单ID，整数
	 * @返回 false/数字
	 * @更新时间 2016年11月28日
	**/
	public function integral($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT id,tid,qty FROM ".$this->db->prefix."order_product WHERE order_id='".$id."' AND tid>0";
		$list = $this->db->get_all($sql);
		if(!$list){
			return false;
		}
		$idlist = array();
		foreach($list as $key=>$value){
			$idlist[] = $value['tid'];
		}
		$integral_list = $this->model('list')->integral_list($idlist);
		if(!$integral_list){
			return false;
		}
		$integral = 0;
		foreach($list as $key=>$value){
			if($integral_list[$value['tid']]){
				$integral += intval($integral_list[$value['tid']]) * intval($value['qty']);
			}
		}
		return $integral;
	}


	public function log_list($order_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_log WHERE order_id='".$order_id."' ORDER BY addtime ASC,id ASC";
		return $this->db->get_all($sql);
	}

	public function log_all($order_id)
	{
		return $this->log_list($order_id);
	}

	public function express_all($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_express WHERE order_id='".$id."' AND express_id!=0 ";
		$sql.= "ORDER BY addtime ASC";
		return $this->db->get_all($sql);
	}


	/**
	 * 取得订单下的统计数
	 * @参数 $uids 会员ID，多个ID用英文逗号隔开
	**/
	public function stat_count($uids)
	{
		if(!$uids){
			return false;
		}
		if(is_array($uids)){
			$uids = implode(",",$uids);
		}
		$sql = "SELECT count(id) as total,user_id FROM ".$this->db->prefix."order WHERE user_id IN(".$uids.") GROUP BY user_id";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['user_id']] = $value['total'];
		}
		return $rslist;
	}
}