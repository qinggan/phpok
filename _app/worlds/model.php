<?php
/**
 * 模型内容信息_管理全球国家及州/省信息
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年05月27日 19时51分
**/
namespace phpok\app\model\worlds;
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

	public function check_is_country($id)
	{
		$info = $this->get_one($id);
		if(!$info['pid']){
			return false;
		}
		$parent = $this->get_one($info['pid']);
		if(!$parent){
			return false;
		}
		if(!$parent['pid']){
			return true;
		}
		return $parent;
	}

	/**
	 * 自定义查询
	 * @参数 $condition 查询条件
	 * @参数 $orderby 自定义排序
	 * @参数 $pri 指定主键ID
	**/
	public function get_all($condition='',$orderby='',$pri='',$offset=0,$psize=0)
	{
		if(!$orderby){
			$orderby = " taxis ASC,name_en ASC ";
		}
		$sql = "SELECT * FROM ".$this->db->prefix."world_location WHERE ".$condition." ORDER BY ".$orderby;
		if($psize && intval($psize)>0){
			$sql .= " LIMIT ".intval($offset).",".intval($psize);
		}
		return $this->db->get_all($sql,$pri);
	}

	public function update_pid($id,$pid=0)
	{
		$sql = "UPDATE ".$this->db->prefix."world_location SET pid='".$pid."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."world_location SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//获取单条信息
	public function get_one($id,$field='id')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."world_location WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function parent_all($id,$pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."world_location WHERE pid='".$id."'";
		return $this->db->get_all($sql,$pri);
	}

	public function price_all($id)
	{
		if(!$id){
			return false;
		}
		if(is_array($id)){
			$id = implode(",",$id);
		}
		$sql  = " SELECT p.*,w.name,w.name_en,w.currency_id,c.title currency_title FROM ".$this->db->prefix."world_price p ";
		$sql .= " LEFT JOIN ".$this->db->prefix."world_location w ON(p.region_id=w.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."currency c ON(w.currency_id=c.id) ";
		$sql .= " WHERE p.tid IN(".$id.")";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['tid']][$value['vtype']][$value['region_id']] = $value;
		}
		return $rslist;
	}

	/**
	 * 读取全球价格
	 * @参数 $id 主题ID
	 * @参数 $type 类型，仅支持 price 价格，freight 运费，excise 消费税，tariff 关税
	 * @参数 $region_id 国家ID（地区ID），数字，对应 world_location 表里的主键ID
	 * @返回 数组或空 
	**/
	public function pricelist($id,$type='price',$region_id=0)
	{
		if(!$type){
			$type = 'price';
		}
		$sql  = " SELECT p.*,w.name,w.name_en,w.currency_id,c.title currency_title FROM ".$this->db->prefix."world_price p ";
		$sql .= " LEFT JOIN ".$this->db->prefix."world_location w ON(p.region_id=w.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."currency c ON(w.currency_id=c.id) ";
		$sql .= " WHERE p.tid='".$id."' AND p.vtype='".$type."'";
		if($region_id){
			$sql .= " AND region_id='".$region_id."'";
		}
		$sql .= " ORDER BY w.taxis ASC";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$country = $this->check_is_country($value['region_id']);
			if($country && is_array($country)){
				$value['country'] = $country;
			}
			$rslist[$value['region_id']] = $value;
		}
		return $rslist;
	}

	public function price_info($tid,$type='price',$region_id=0)
	{
		$sql  = "SELECT * FROM ".$this->db->prefix."world_price WHERE tid='".$tid."' ";
		if($type){
			$sql .= " AND vtype='".$vtype."'";
		}
		if($region_id){
			$sql .= " AND region_id='".$region_id."'";
		}
		return $this->db->get_all($sql);
	}

	public function price_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."world_price WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function price_save($tid,$data=array(),$vtype="price")
	{
		if(!$data || !is_array($data) || !$tid || !$vtype){
			return false;
		}
		$ids = array_keys($data);
		$sql = "SELECT region_id FROM ".$this->db->prefix."world_price WHERE tid='".$tid."' AND vtype='".$vtype."'";
		$tmplist = $this->db->get_all($sql,'region_id');
		if($tmplist){
			$old_ids = array_keys($tmplist);
			//查看相同地址，执行更新
			$updates = array_intersect($ids,$old_ids);
			//查找新增地址，执行添加
			$adds = array_diff($ids,$old_ids);
			//查找无效地址，执行删除
			$deletes = array_diff($old_ids,$ids);
			if($deletes){
				$sql = "DELETE FROM ".$this->db->prefix."world_price WHERE tid='".$tid."' AND vtype='".$vtype."' AND region_id IN(".implode(",",$deletes).")";
				$this->db->query($sql);
			}
			if($adds){
				foreach($adds as $key=>$value){
					$tmp = array('region_id'=>$value,'tid'=>$tid,'val'=>$data[$value],'vtype'=>$vtype);
					$this->db->insert($tmp,'world_price');
				}
			}
			if($updates){
				foreach( $updates as $key => $value ){
					$sql = "UPDATE ".$this->db->prefix."world_price SET val='".$data[$value]."' WHERE tid='".$tid."' AND vtype='".$vtype."' AND region_id='".$value."'";
					$this->db->query($sql);
				}
			}
			return true;
		}
		foreach($data as $key=>$value){
			$tmp = array('region_id'=>$key,'tid'=>$tid,'val'=>$value,'vtype'=>$vtype);
			$this->db->insert($tmp,'world_price');
		}
		return true;
	}

	public function price_delete($tid,$vtype='')
	{
		$sql = "DELETE FROM ".$this->db->prefix."world_price WHERE tid='".$tid."' ";
		if($vtype){
			$sql .= " AND vtype='".$vtype."'";
		}
		return $this->db->query($sql);
	}

	public function del($id)
	{
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."world_location WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function excise($price='',$qty='',$country_id='',$province='',$city='')
	{
		if(!$price && !$qty){
			return false;
		}
		if(!$country_id){
			return false;
		}
		
	}

	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update($data,"world_location",array('id'=>$id));
		}
		return $this->db->insert($data,'world_location');
	}

	public function group_countries()
	{
		$continent = $this->get_all('pid=0 AND status=1');
		if(!$continent){
			return false;
		}
		$ids = array();
		foreach($continent as $key=>$value){
			$ids[] = $value['id'];
		}
		$condition = "pid IN(".implode(",",$ids).") AND status=1";
		$tmplist = $this->get_all($condition);
		if(!$tmplist){
			return false;
		}
		foreach($tmplist as $key=>$value){
			$sublist[$value['pid']][] = $value;
		}
		$grouplist = array();
		foreach($continent as $key=>$value){
			if($sublist[$value['id']]){
				$value['rslist'] = $sublist[$value['id']];
				$grouplist[$key] = $value;
			}
		}
		return $grouplist;
	}
}
