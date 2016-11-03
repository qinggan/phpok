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

	//取得订单列表
	function get_list($condition='',$offset=0,$psize=20)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$offset = intval($offset);
		$psize = intval($psize);
		$sql .= " ORDER BY addtime DESC,id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	function get_count($condition="")
	{
		$sql = "SELECT count(o.id) FROM ".$this->db->prefix."order o ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	//取得订单的最大ID号，再此基础上+1
	function maxid()
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
		if($id){
			return $this->db->update_array($data,"order",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"order");
		}
	}

	//存储商品信息
	public function save_product($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"order_product",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"order_product");
		}
	}

	function save_address($data,$id=0)
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
		return $this->db->get_one($sql);
	}

	function address($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_address WHERE order_id='".$id."'";
		return $this->db->get_one($sql);
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

	//取得订单下的产品信息
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
		foreach($rslist AS $key=>$value){
			if($value['ext']){
				$value['ext'] = unserialize($value['ext']);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function invoice($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_invoice WHERE order_id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function product_delete($id)
	{
		if(!$id) return false;
		$rs = $this->product_one($id);
		if(!$rs) return false;
		$oid = $rs['order_id'];
		$sql = "DELETE FROM ".$this->db->prefix."order_product WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function product_one($id)
	{
		if(!$id){
			return false;
		}
		return $this->db->get_one("SELECT * FROM ".$this->db->prefix."order_product WHERE id='".$id."'");
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
		$sql = "UPDATE ".$this->db->prefix."order SET status='".$status."' WHERE id='".$id."'";
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
		if(!$data['who'] && $this->session->val('user_name')){
			$data['who'] = $this->session->val('user_name');
		}
		if(!$data['addtime']){
			$data['addtime'] = $this->time;
		}
		return $this->db->insert_array($data,'order_log');
	}


	public function save_payment($data,$id=0)
	{
		if($id){
			return $this->db->update_array($data,'order_payment',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'order_payment');
		}
	}

	public function order_payment($order_id,$payment_id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_payment WHERE order_id='".$order_id."'";
		if($payment_id){
			$sql .= " AND payment_id='".$payment_id."' ";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 订单全部支付记录
	 * @参数 $id 订单ID
	 * @返回 false 或 多组数组
	 * @更新时间 2016年10月03日
	**/
	public function payment_all($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_payment WHERE order_id='".intval($id)."'";
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
		$sql = "SELECT SUM(price) totalprice FROM ".$this->db->prefix."order_payment WHERE order_id='".$order_id."' AND dateline>0";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return 0;
		}
		return $rs['totalprice'];
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
		$price = $rs['price'];
		if(round($paid_price,2) != round($price,2)){
			return round(($price - $paid_price),4);
		}
		return '0.00';
	}

	public function order_payment_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order_payment WHERE id='".$id."'";
		return $this->db->query($sql);
	}

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
	 * 取得指定会员下的余额
	 * @参数 $user_id 会员ID
	 * @返回 false 或 余额多维数组列表
	 * @更新时间 2016年08月04日
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
			$tmp = array('id'=>$value['id'],'title'=>$value['title'],'identifier'=>$value['identifier']);
			$tmp['unit'] = $value['unit'];
			$tmp['val'] = $val;
			$tmp['price'] = round($val*$value['cash_ratio']/100,$value['dnum']);
			$wlist[] = $tmp;
		}
		if(!$wlist){
			return false;
		}
		return $wlist;
	}

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
				$maxid = $this->model('order')->maxid();
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

	//保存订单各种状态下的价格
	public function save_order_price($data)
	{
		return $this->db->insert_array($data,'order_price');
	}
}

?>