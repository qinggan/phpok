<?php
/**
 * 购物车相关全局操作
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年08月17日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cart_model_base extends phpok_model
{
	/**
	 * 购物车ID，仅限内部使用
	**/
	private $_id;

	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 获取购物车ID，如果不存在，系统会尝试创建
	 * @参数 $sessid 用户Session ID
	 * @参数 $uid 会员ID，为0表示游客下单
	 * @返回 数字ID
	**/
	public function cart_id($sessid='',$uid=0)
	{
		if(!$sessid){
			$sessid = $this->session->sessid();
		}
		if(!$uid && $this->session->val('user_id')){
			$uid = $this->session->val('user_id');
		}
		$sql = "SELECT id FROM ".$this->db->prefix."cart WHERE session_id='".$sessid."'";
		if($uid){
			$sql .= " OR user_id='".$uid."'";
		}
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			$array = array('session_id'=>$sessid,'user_id'=>$uid,'addtime'=>$this->time);
			$this->_id = $this->db->insert_array($array,'cart');
			return $this->_id;
		}
		if($tmplist && count($tmplist) == 1){
			$rs = current($tmplist);
			$array = array('session_id'=>$sessid,'user_id'=>$uid,'addtime'=>$this->time);
			$this->db->update_array($array,'cart',array('id'=>$rs['id']));
			$this->_id = $rs['id'];
			return $this->_id;
		}
		//合并购物车
		$array = array('session_id'=>$sessid,'user_id'=>$uid,'addtime'=>$this->time);
		$this->_id = $this->db->insert_array($array,'cart');
		$idlist = array();
		foreach($tmplist as $key=>$value){
			$sql = "UPDATE ".$this->db->prefix."cart_product SET cart_id='".$this->_id."' WHERE cart_id='".$value['id']."'";
			$this->db->query($sql);
			$idlist[] = $value['id'];
		}
		$sql = "DELETE FROM ".$this->db->prefix."cart WHERE id IN(".implode(",",$idlist).")";
		$this->db->query($sql);
		return $this->_id;
	}

	/**
	 * 取得购物车信息
	 * @参数 $cart_id 购物车ID，留空使用系统的$this->_id
	 * @参数 $condition fixed 条件查询，当为数组时表示多个ID，当为数字时表示单个ID
	 * @返回 false 或购物车里的产品信息
	 * @更新时间 2016年08月19日
	**/
	public function get_all($cart_id='',$condition='')
	{
		if(!$cart_id){
			$cart_id = $this->_id;
			if(!$cart_id){
				return false;
			}
		}
		if($condition && is_numeric($condition)){
			$condition = "id='".$condition."'";
		}
		if($condition && is_array($condition)){
			if($condition){
				$condition = "id IN(".implode(",",$condition).")";
			}
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		if($condition && is_string($condition)){
			$sql .= " AND ".$condition;
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext']){
				$value['_attrlist'] = $this->product_ext_to_array($value['ext'],$value['tid']);
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	/**
	 * 取得购物车里的产品详细信息
	 * @参数 $id 购物车产品（表cart_product）里的id
	 * @返回 数组
	 * @更新时间 2016年09月01日
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."cart_product WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 更新购物车里的产品数量
	 * @参数 $id 购物车产品（表cart_product）里的id
	 * @参数 $qty 数量，不能小于1
	 * @返回 true 或 false
	**/
	public function update($id,$qty=1)
	{
		$id = intval($id);
		if(!$id){
			return false;
		}
		$qty = intval($qty);
		if($qty < 1){
			$qty = 1;
		}
		$sql = "UPDATE ".$this->db->prefix."cart_product SET qty='".$qty."' WHERE id='".$id."'";
		$this->db->query($sql);
		$rs = $this->get_one($id);
		if($rs){
			$this->update_cart_time($rs['cart_id']);
		}
		return true;
	}

	/**
	 * 添加产品数据
	 * @参数 $data 数组
	 * @返回 false 或插入的id
	**/
	public function add($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$this->update_cart_time($data['cart_id']);
		$data['ext'] = $this->product_ext_to_string($data['ext']);
		return $this->db->insert_array($data,'cart_product');
	}

	/**
	 * 更新购物车操作时间
	 * @参数 $cart_id 购物车ID
	**/
	public function update_cart_time($cart_id)
	{
		$sql = "UPDATE ".$this->db->prefix."cart SET addtime='".$this->time."' WHERE id='".$cart_id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得购物车下的产品数量
	 * @参数 $cart_id 购物车ID
	**/
	public function total($cart_id)
	{
		$sql = "SELECT SUM(qty) FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		return $this->db->count($sql);
	}

	/**
	 * 删除购物车信息
	 * @参数 $cart_id 购物车ID
	 * @参数 $ids 要删除的产品ID，数组
	**/
	public function delete($cart_id,$ids='')
	{
		$condition = '';
		if($ids && is_numeric($ids)){
			$condition = "id='".$ids."'";
		}
		if($ids && is_array($ids)){
			$condition = "id IN(".implode(",",$ids).")";
		}
		if($condition){
			$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."' AND ".$condition;
			$this->db->query($sql);
			return true;
		}
		$sql = "DELETE FROM ".$this->db->prefix."cart WHERE id='".$cart_id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 删除产品
	 * @参数 $id 主键ID，这里说明下，不是产品ID
	**/
	public function delete_product($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 清空购物车下的产品数据
	 * @参数 $cart_id 购物车ID
	**/
	public function clear_cart($cart_id='')
	{
		if(!$cart_id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 清空超过24小时的购物车
	**/
	public function clear_expire_cart()
	{
		$oldtime = $this->time - 24 * 60 *60;
		$sql = "DELETE FROM ".$this->db->prefix."cart WHERE addtime<".$oldtime;
		$this->db->query($sql);
		$sql = "SELECT id FROM ".$this->db->prefix."cart LIMIT 1";
		$tmp = $this->db->get_one($sql);
		if($tmp){
			$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE cart_id NOT IN(SELECT id FROM ".$this->db->prefix."cart)";
			$this->db->query($sql);
			return true;
		}
		$sql = "TRUNCATE ".$this->db->prefix."cart_product";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 计算运费
	 * @参数 $data 数组，里面包含：number数量，weight重量，volume体积
	 * @参数 $province 省份
	 * @参数 $city 城市
	 * @返回 false 或 实际运费
	 * @更新时间 2016年09月11日
	**/
	public function freight_price($data,$province='',$city='')
	{
		if(!$data || !$province || !$city){
			return false;
		}
		if(!$this->site['biz_freight']){
			return false;
		}
		$freight = $this->model('freight')->get_one($this->site['biz_freight']);
		if(!$freight){
			return false;
		}
		$param_val = 'fixed';
		$data['fixed'] = 'fixed';
		if($data[$freight['type']]){
			$param_val = $data[$freight['type']];
		}
		if(!$param_val){
			return false;
		}
		$zone_id = $this->model('freight')->zone_id($freight['id'],$province,$city);
		if(!$zone_id){
			return false;
		}
		$val = $this->model('freight')->price_one($zone_id,$param_val);
		if($val){
			if(strpos($val,'N') !== false){
				$val = str_replace("N",$param_val,$val);
				eval("\$val = $val;");
			}
			return $val;
		}
		return false;
	}

	/**
	 * 购物车产品属性扩展数组数据格式化为字串，如果值都是数字，则用英文逗号隔开，非数字则用serialize序列化
	 * @参数 $data 要格式化的内容，必须是数组
	**/
	public function product_ext_to_string($data='')
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$is_num = true;
		foreach($data as $key=>$value){
			if(!is_numeric($key) || !is_numeric($value)){
				$is_num = false;
				break;
			}
		}
		if($is_num){
			return implode(",",$data);
		}
		return serialize($data);
	}

	/**
	 * 购物车产品中的扩展属性数据转化为数组，多数字组合则读取产品属性表，反之则用unserialize反序列化
	 * @参数 $data 要格式化的数据，字串
	 * @参数 $tid 产品ID（仅限数据为数字及英文逗号组成）
	**/
	public function product_ext_to_array($data='',$tid=0)
	{
		if(!$data || ($data && is_array($data))){
			return false;
		}
		if(strpos($data,':{') !== false){
			$list = unserialize($data);
			if(!$list){
				return false;
			}
			$tmparray = array();
			foreach($list as $key=>$value){
				$tmp = array('title'=>$key,'val'=>$value,'content'=>$value);
				$tmparray[] = $tmp;
			}
			return $tmparray;
		}
		if(!$tid){
			return explode(",",$data);
		}
		$sql = "SELECT a.title,v.title content,v.val FROM ".$this->db->prefix."list_attr l ";
		$sql.= "LEFT JOIN ".$this->db->prefix."attr a ON(l.aid=a.id AND a.site_id=".$this->site_id.") ";
		$sql.= "LEFT JOIN ".$this->db->prefix."attr_values v ON(l.vid=v.id AND l.aid=v.aid) ";
		$sql.= "WHERE l.tid='".$tid."' AND l.id IN(".$data.") ";
		return $this->db->get_all($sql);
	}

	/**
	 * 产品属性参数比较，如果相同返回 true，不同返回 false
	 * @参数 $data 属性1
	 * @参数 $check 属性2
	**/
	public function product_ext_compare($data,$check)
	{
		if(!$data && !$check){
			return true;
		}
		if(($data && !$check) ||(!$data && $check)){
			return false;
		}
		if(is_string($data)){
			$data = $this->product_ext_to_array($data);
		}
		if(is_string($check)){
			$check = $this->product_ext_to_array($check);
		}
		$status = false;
		$diff1 = array_diff($data,$check);
		$diff2 = array_diff($check,$data);
		if($diff1 || $diff2){
			return false;
		}
		return true;
	}
}