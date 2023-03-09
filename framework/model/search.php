<?php
/**
 * 搜索，支持自定义扩展字段的搜索
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年7月4日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class search_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	//取得查询结果数量
	function get_total($condition="",$mid=0,$ext=array())
	{
		$total = 0;
		$listsql = array();
		if($mid && is_array($mid)){
			foreach($mid as $key=>$value){
				$sql  = " SELECT ext.id FROM ".$this->db->prefix."list_".$value." ext JOIN ".$this->db->prefix."list l ON(ext.id=l.id) ";
				$sql .= " WHERE l.status=1 AND l.hidden=0 ";
				if($ext && $ext[$value]){
					$sql .= " AND (".implode(" OR ",$ext[$value]).") ";
				}
				$listsql[] = $sql;
			}
			$sql  = " SELECT count(l.id) FROM ".$this->db->prefix."list l WHERE l.status=1 AND l.hidden=0 ";
			if($condition){
				$sql .= " AND ".$condition." ";
			}
			if($listsql){
				$sql .= " AND l.id IN(".implode(" UNION ",$listsql).")";
			}
			$total = $this->db->count($sql);
			return $total;
		}
		$sql  = "SELECT count(l.id) FROM ".$this->db->prefix."list l ";
		if($mid){
			$sql .= " LEFT JOIN ".$this->db->prefix."list_".$mid." ext ON(l.id=ext.id) ";
		}
		$sql .= " WHERE l.status=1 AND l.hidden=0 ";
		if($condition){
			$sql.= " AND ".$condition;
		}
		return $this->db->count($sql);
	}

	//查询ID数量
	function id_list($condition="",$offset=0,$psize=30,$mid=0,$ext=array(),$orderby='')
	{
		if($mid && is_array($mid)){
			$sqlist = array();
			foreach($mid as $key=>$value){
				$mycondition = $condition;
				$sql  = "SELECT l.id,l.dateline FROM ".$this->db->prefix."list l ";
				$sql .= " LEFT JOIN ".$this->db->prefix."list_".$value." ext ON(l.id=ext.id) ";
				if($ext && $ext[$value]){
					$tmp = implode(" OR ",$ext[$value]);
					if($mycondition){
						$mycondition .= " AND (".$tmp.") ";
					}else{
						$mycondition = $tmp;
					}
				}
				$sql .= " WHERE l.status=1 AND l.hidden=0 ";
				if($mycondition){
					$sql .= " AND ".$mycondition;
				}
				$sqlist[] = $sql;
			}
			$sql = implode(" UNION ",$sqlist);
			if(!$orderby){
				$sql .= " ORDER BY dateline DESC,id DESC ";
			}else{
				$sql .= " ORDER BY ".$orderby;
			}
			$sql .= " LIMIT ".$offset.",".$psize;
			return $this->db->get_all($sql);
		}
		$sql  = "SELECT l.id FROM ".$this->db->prefix."list l ";
		if($mid){
			$sql .= " LEFT JOIN ".$this->db->prefix."list_".$mid." ext ON(l.id=ext.id) ";
		}
		$sql .= " WHERE l.status=1 AND l.hidden=0 ";
		if($condition){
			$sql.= " AND ".$condition;
		}
		if($orderby){
			$sql .= " ORDER BY ".$orderby." ";
		}else{
			$sql .= " ORDER BY l.sort DESC,l.dateline DESC,l.id DESC ";
		}
		$sql.= " LIMIT ".intval($offset).",".intval($psize);
		return $this->db->get_all($sql);
	}

	/**
	 * 保存搜索的关键字
	**/
	public function save($title)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."search WHERE site_id='".$this->site_id."' AND title='".$title."'";
		$chk = $this->db->get_one($sql);
		if(!$chk){
			$data = array('site_id'=>$this->site_id,'title'=>$title);
			$data['dateline'] = $this->time;
			$data['hits'] = 1;
			$this->db->insert($data,"search");
		}else{
			$sql = "UPDATE ".$this->db->prefix."search SET dateline='".$this->time."',hits=hits+1 WHERE id='".$chk['id']."'";
			$this->db->query($sql);
		}
		return true;
	}

	/**
	 * 获取关键字
	**/
	public function keywords($condition='',$psize=10,$orderby='')
	{
		$sql  = "SELECT * FROM ".$this->db->prefix."search ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		if($orderby){
			$sql .= " ORDER BY ".$orderby;
		}
		if($psize){
			$sql .= " LIMIT ".$psize;
		}
		return $this->db->get_all($sql);
	}

	public function get_one($id,$field='id',$site_id=0)
	{
		if(is_numeric($field)){
			$site_id = $field;
			$field = 'id';
		}
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$sql  = "SELECT * FROM ".$this->db->prefix."search WHERE ".$field."='".$id."'";
		$sql .= " AND site_id='".$site_id."'";
		return $this->db->get_one($sql);
	}
}