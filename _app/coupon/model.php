<?php
/**
 * 模型内容信息_适用于整个PHPOK5平台的优惠系统
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年01月02日 15时35分
**/
namespace phpok\app\model\coupon;

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class model extends \phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 检测优惠码是否存在
	 * @参数 $code 优惠码编号
	 * @参数 $id 不含当前优惠码ID
	**/
	public function check($code,$id=0)
	{
		if(!$code){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."coupon WHERE site_id='".$this->site_id."' AND code='".$code."'";
		if($id){
			$sql .= " AND id !='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 获取一条优惠码信息
	 * @参数 $id 指优惠码ID或编号
	 * @参数 $type 类型，默认为id
	**/
	public function get_one($id,$type='id')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."coupon WHERE ";
		if($type == 'id'){
			$sql .= " id='".$id."'";
		}else{
			$sql .= " site_id='".$this->site_id."' AND ".$type."='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 删除优惠码信息，删除时将会删除相关的用户已领取的操作记录及历史使用记录
	 * @参数 $id 优惠码ID
	**/
	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."coupon_user WHERE coupon_id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."coupon_history WHERE coupon_id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."coupon WHERE id='".$id."'";
		$this->db->query($sql);
	}

	public function freq_list()
	{
		$data = array();
		$data['day'] = P_Lang('每天');
		$data['week1'] = P_Lang('每周一');
		$data['week2'] = P_Lang('每周二');
		$data['week3'] = P_Lang('每周三');
		$data['week4'] = P_Lang('每周四');
		$data['week5'] = P_Lang('每周五');
		$data['week6'] = P_Lang('每周六');
		$data['week7'] = P_Lang('每周日');
		return $data;
	}

	/**
	 * 保存优惠码信息
	 * @参数 $data 数组
	 * @参数 $id 优惠码ID，不为0时表示更新操作
	**/
	public function save($data,$id=0)
	{
		if($id){
			return $this->db->update_array($data,'coupon',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'coupon');
		}
	}

	public function save_history($data,$id=0)
	{
		if($id){
			return $this->db->update_array($data,'coupon_history',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'coupon_history');
		}
	}

	/**
	 * 用户领取优惠码
	 * @参数 $id 优惠码ID
	 * @参数 $user_id 用户ID
	**/
	public function to_user($id,$user_id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."coupon_user WHERE coupon_id='".$id."' AND user_id='".$user_id."'";
		$chk = $this->db->get_one($sql);
		if($chk){
			return $chk['id'];
		}
		$data = array('coupon_id'=>$id,'user_id'=>$user_id,'dateline'=>$this->time);
		$tmp = $this->get_one($id);
		if(!$tmp){
			return false;
		}
		$data['code'] = $tmp['code'].'-'.$user_id;
		$data['startdate'] = $tmp['startdate'] > $this->time ? $tmp['startdate'] : $this->time;
		$data['stopdate'] = $tmp['stopdate'];
		return $this->db->insert_array($data,'coupon_user');
	}

	public function get_info($uid,$coupon_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."coupon_user WHERE user_id='".$uid."' AND coupon_id='".$coupon_id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 获取优惠码列表
	 * @参数 $condition 查询条件
	 * @参数 $offset 开始位置
	 * @参数 $psize 查几条，默认是20条
	 * @参数 $pri 自定义Key键，留空直接从0开始计算
	**/
	public function get_list($condition='',$offset=0,$psize=20,$pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."coupon WHERE site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		$sql .= " ORDER BY taxis ASC,dateline DESC,id DESC ";
		if($psize && intval($psize) > 0){
			$sql .= " LIMIT ".intval($offset).",".$psize;
		}
		$rslist = $this->db->get_all($sql,$pri);
		if(!$rslist){
			return false;
		}
		$freq_list = $this->freq_list();
		foreach($rslist as $key=>$value){
			//查看历史使用次数
			$value['history_count'] = $this->history_count($value['id']);
			$value['received_count'] = $this->received_count($value['id']);
			$value['freq_title'] = $freq_list[$value['freq']] ? $freq_list[$value['freq']] : $value['freq'];
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 获取优惠码数量
	 * @参数 $condition 查询条件
	**/
	public function get_total($condition='')
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."coupon WHERE site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		return $this->db->count($sql);
	}

	public function history_count($id)
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."coupon_history WHERE coupon_id='".$id."'";
		return $this->db->count($sql);
	}

	public function history_list($condition='',$offset=0,$psize=20,$pri='')
	{
		$sql  = "SELECT h.*,c.title,u.user,o.sn FROM ".$this->db->prefix."coupon_history h ";
		$sql .= " LEFT JOIN ".$this->db->prefix."coupon c ON(h.coupon_id=c.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(h.user_id=u.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."order o ON(h.order_id=o.id) ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY h.id DESC ";
		if($psize && intval($psize) > 0){
			$sql .= " LIMIT ".intval($offset).",".$psize;
		}
		$rslist = $this->db->get_all($sql,$pri);
		if(!$rslist){
			return false;
		}
		return $this->db->get_all($sql,$pri);
	}

	public function history_total($condition='')
	{
		$sql  = "SELECT count(h.id) FROM ".$this->db->prefix."coupon_history h ";
		$sql .= " LEFT JOIN ".$this->db->prefix."coupon c ON(h.coupon_id=c.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(h.user_id=u.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."order o ON(h.order_id=o.id) ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	public function received_count($id)
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."coupon_user WHERE coupon_id='".$id."'";
		return $this->db->count($sql);
	}

	public function received_total($condition='')
	{
		$sql  = "SELECT count(h.id) FROM ".$this->db->prefix."coupon_user h ";
		$sql .= " LEFT JOIN ".$this->db->prefix."coupon c ON(h.coupon_id=c.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(h.user_id=u.id) ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	public function received_list($condition='',$offset=0,$psize=20,$pri='')
	{
		$sql  = "SELECT h.*,c.title,c.discount_val,c.discount_type,c.code discount_code,u.user FROM ".$this->db->prefix."coupon_user h ";
		$sql .= " LEFT JOIN ".$this->db->prefix."coupon c ON(h.coupon_id=c.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user u ON(h.user_id=u.id) ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY h.id DESC ";
		if($psize && intval($psize) > 0){
			$sql .= " LIMIT ".intval($offset).",".$psize;
		}
		$rslist = $this->db->get_all($sql,$pri);
		if(!$rslist){
			return false;
		}
		return $this->db->get_all($sql,$pri);
	}

	/**
	 * 修改优惠码状态
	 * @参数 $id 优惠码ID
	 * @参数 $status 0禁用，1启用
	**/
	public function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."coupon SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function user_one($id,$type="id")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."coupon_user WHERE ".$type."='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$info = $this->get_one($rs['coupon_id']);
		if(!$info){
			return false;
		}
		$info['startdate'] = $rs['startdate'];
		$info['stopdate'] = $rs['stopdate'];
		$info['user_id'] = $rs['user_id'];
		$info['dateline'] = $rs['dateline'];
		return $info;
	}
	
	public function ulist($uid,$pri="")
	{
		$sql  = " SELECT u.id,u.coupon_id,u.startdate,u.stopdate,c.title,c.discount_val,c.discount_type,c.min_price,u.code ";
		$sql .= " FROM ".$this->db->prefix."coupon_user u ";
		$sql .= " LEFT JOIN ".$this->db->prefix."coupon c ON(u.coupon_id=c.id) WHERE u.user_id='".$uid."' ORDER BY u.id DESC";
		return $this->db->get_all($sql,$pri);
	}
	
	public function cart_id($id,$coupon_id=0)
	{
		$sql = "UPDATE ".$this->db->prefix."cart SET coupon_id='".$coupon_id."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
	
	public function user_coupon($cart_id)
	{
		$sql = "SELECT coupon_id FROM ".$this->db->prefix."cart WHERE id='".$cart_id."'";
		$chk = $this->db->get_one($sql);
		if(!$chk){
			return false;
		}
		return $this->get_one($chk['coupon_id']);
	}

	public function user_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."coupon_user WHERE id='".$id."'";
		return $this->db->query($sql);
	}
	
	public function to_history($cart_id,$order_id,$discount)
	{
		if(!$cart_id || !$order_id){
			return false;
		}
		$order = $this->model('order')->get_one($order_id);
		if(!$order){
			return false;
		}
		$cart = $this->model('cart')->cart_one($cart_id);
		if(!$cart || !$cart['coupon_id']){
			return false;
		}

		$sql = "UPDATE ".$this->db->prefix."cart SET coupon_id=0 WHERE id='".$cart_id."'";
		$this->db->query($sql);
		
		$coupon = $this->get_one($cart['coupon_id']);
		if(!$coupon){
			return false;
		}
		$data = array('coupon_id'=>$cart['coupon_id'],'order_id'=>$order_id);
		$data['title'] = $coupon['title'];
		$data['price'] = $discount;
		if($order['user_id']){
			$data['user_id'] = $order['user_id'];
		}
		$data['dateline'] = $this->time;
		$data['currency_id'] = $order['currency_id'];
		$currency = $this->model('currency')->get_one($order['currency_id']);
		$data['currency_rate'] = $currency['val'];
		$data['code'] = $coupon['code'];
		$this->db->insert_array($data,'coupon_history');
		//删除用户的记录
		if($order['user_id']){
			$sql = "SELECT * FROM ".$this->db->prefix."coupon_user WHERE user_id='".$order['user_id']."' AND coupon_id='".$cart['coupon_id']."'";
			$rs = $this->db->get_one($sql);
			if($rs){
				$sql = "DELETE FROM ".$this->db->prefix."coupon_user WHERE id='".$rs['id']."'";
				$this->db->query($sql);
			}
		}
		return true;
	}

}
