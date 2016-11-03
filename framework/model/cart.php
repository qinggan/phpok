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
	 * @返回 false 或购物车里的产品信息
	 * @更新时间 2016年08月19日
	**/
	public function get_all($cart_id='')
	{
		if(!$cart_id){
			$cart_id = $this->_id;
			if(!$cart_id){
				return false;
			}
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist AS $key=>$value){
			if($value['tid'] && $value['ext']){
				$sql = "SELECT a.title,v.title content,v.val FROM ".$this->db->prefix."list_attr l ";
				$sql.= "LEFT JOIN ".$this->db->prefix."attr a ON(l.aid=a.id AND a.site_id=".$this->site_id.") ";
				$sql.= "LEFT JOIN ".$this->db->prefix."attr_values v ON(l.vid=v.id AND l.aid=v.aid) ";
				$sql.= "WHERE l.tid='".$value['tid']."' AND l.id IN(".$value['ext'].") ";
				$value['_attrlist'] = $this->db->get_all($sql);
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
		return $this->db->insert_array($data,'cart_product');
	}

	public function update_cart_time($cart_id)
	{
		$sql = "UPDATE ".$this->db->prefix."cart SET addtime='".$this->time."' WHERE id='".$cart_id."'";
		return $this->db->query($sql);
	}

	public function total($cart_id)
	{
		$sql = "SELECT SUM(qty) FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		return $this->db->count($sql);
	}

	public function delete($cart_id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."cart WHERE id='".$cart_id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		$this->db->query($sql);
		return true;
	}

	public function delete_product($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function clear_expire_cart()
	{
		$oldtime = $this->time - 24 * 60 *60;
		$sql = "SELECT id FROM ".$this->db->prefix."cart WHERE addtime<".$oldtime;
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist) return true;
		$idlist = array_keys($rslist);
		$idstring = implode(',',$idlist);
		$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE cart_id IN(".$idstring.")";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."cart WHERE id IN(".$idstring.")";
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
			echo "<pre>".print_r(5,true)."</pre>";
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
}
?>