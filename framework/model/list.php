<?php
/***********************************************************
	Filename: {phpok}/model/list.php
	Note	: 读取内容列表
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-04-15 02:17
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class list_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	function ext_fields($mid,$prefix="ext")
	{
		$sql = "SELECT identifier FROM ".$this->db->prefix."module_fields WHERE module_id='".$mid."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		if(!$prefix) $prefix = 'ext';
		$list = "";
		foreach($rslist AS $key=>$value)
		{
			$list[] = 'ext.'.$value['identifier'];
		}
		return implode(",",$list);
	}

	function get_list($mid,$condition="",$offset=0,$psize=0,$orderby="")
	{
		if(!$mid) return false;
		$field = $this->ext_fields($mid,'ext');
		$field = $field ? $field.",l.*,u.user _user" : "l.*,u.user _user";
		$field.= ",b.price,b.currency_id,b.weight,b.volume,b.unit";
		$sql = "SELECT ".$field." FROM ".$this->db->prefix."list l ";
		$sql.= " LEFT JOIN ".$this->db->prefix."list_".$mid." ext ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id AND u.status=1) ";
		$sql.= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
		$sql.= " LEFT JOIN ".$this->db->prefix."list_cate c ON(l.id=c.id) ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		if(!$orderby){
			$orderby = "l.sort DESC,l.dateline DESC,l.id DESC";
		}
		$sql .= " ORDER BY ".$orderby." ";
		if($psize && intval($psize)){
			$offset = intval($offset);
			$sql.= " LIMIT ".$offset.",".$psize;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		$cid_list = array();
		foreach($rslist AS $key=>$value)
		{
			$cid_list[$value["cate_id"]] = $value["cate_id"];
		}
		$m_rs = $GLOBALS['app']->lib('ext')->module_fields($mid);
		if($m_rs)
		{
			//读取更新
			foreach($rslist AS $key=>$value)
			{
				foreach($value AS $k=>$v)
				{
					if($m_rs[$k])
					{
						$value[$k] = $GLOBALS['app']->lib('ext')->content_format($m_rs[$k],$v);
					}
				}
				$rslist[$key] = $value;
			}
		}
		$cid_string = implode(",",$cid_list);
		if($cid_string)
		{
			$catelist = $GLOBALS['app']->lib('ext')->cate_list($cid_string);
			foreach($rslist AS $key=>$value)
			{
				if($value["cate_id"])
				{
					$value["cate_id"] = $catelist[$value["cate_id"]];
					$rslist[$key] = $value;
				}
			}
		}
		return $rslist;
	}

	//
	function get_total($mid,$condition="")
	{
		if(!$mid) return false;
		$sql = " SELECT count(DISTINCT l.id) FROM ".$this->db->prefix."list l ";
		$sql.= " LEFT JOIN ".$this->db->prefix."list_".$mid." ext ";
		$sql.= " ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
		$sql.= " LEFT JOIN ".$this->db->prefix."list_cate c ON(l.id=c.id) ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	//取得当前一个主题的信息
	//id，主题id
	//format，是否格式化
	function get_one($id,$format=true)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_biz WHERE id='".$id."'";
		$biz_rs = $this->db->get_one($sql);
		if($biz_rs){
			foreach($biz_rs as $key=>$value){
				$rs[$key] = $value;
			}
			unset($biz_rs);
		}
		if($rs['module_id']){
			$ext_rs = $this->get_ext($rs['module_id'],$id);
			if(!$ext_rs) return $rs;
			if(!$format){
				$rs = array_merge($ext_rs,$rs);
				return $rs;
			}
			$flist = $this->model('module')->fields_all($rs['module_id'],'identifier');
			if(!$flist){
				return $rs;
			}
			foreach($flist AS $key=>$value){
				$content = $ext_rs[$value['identifier']];
				$content = $GLOBALS['app']->lib('ext')->content_format($value,$content);
				$rs[$value['identifier']] = $content;
			}
		}
		return $rs;
	}

	function call_one($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list l ";
		$sql.= " WHERE l.id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_ext($mid,$id)
	{
		if(!$mid || !$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs;
	}

	function get_ext_list($mid,$id)
	{
		if(!$mid || !$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE id IN(".$id.")";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		return $rslist;
	}

	function save($data,$id=0)
	{
		if(!$data || !is_array($data) || count($data) < 1){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"list",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"list");
		}
	}

	function save_ext($data,$mid)
	{
		if(!$data || !is_array($data) || !$mid) return false;
		return $this->db->insert_array($data,"list_".$mid,"replace");
	}

	function update_ext($data,$mid,$id)
	{
		if(!$data || !is_array($data) || !$mid || !$id) return false;
		return $this->db->update_array($data,"list_".$mid,array("id"=>$id));
	}

	//批量删除
	function pl_delete($condition='',$mid=0)
	{
		$sql = "SELECT id,module_id FROM ".$this->db->prefix."list WHERE ".$condition;
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist)
		{
			return false;
		}
		$id_list = array_keys($rslist);
		$ids = implode(",",$id_list);
		//删除全部回复
		$sql = "DELETE FROM ".$this->db->prefix."reply WHERE tid IN(".$ids.")";
		$this->db->query($sql);
		//删除关键字记录
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE title_id IN(".$ids.")";
		$this->db->query($sql);
		//
		foreach($rslist AS $key=>$value)
		{
			if(!$mid && $value['module_id'])
			{
				$mid = $value['module_id'];
			}
		}
		if($mid)
		{
			$sql = "DELETE FROM ".$this->db->prefix."list_".$mid." WHERE id IN(".$ids.")";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE id IN(".$ids.")";
		$this->db->query($sql);
		return true;
	}


	//检测主表中的唯一性
	function main_only_check($field,$val,$site_id=0,$project_id=0,$mid=0)
	{
		if(!$field || !$val) return true;
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE ".$field."='".$val."'";
		if($site_id)
		{
			$sql .= " AND site_id='".$site_id."'";
		}
		if($project_id)
		{
			$sql .= " AND project_id='".$project_id."'";
		}
		if($mid)
		{
			$sql .= " AND module_id='".$mid."'";
		}
		return $this->db->get_one($sql);
	}

	function ext_only_check($field,$val,$mid,$site_id=0,$project_id=0)
	{
		if(!$field || !$val || !$mid) return true;
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE ".$field."='".$val."'";
		if($site_id)
		{
			$sql .= " AND site_id='".$site_id."'";
		}
		if($project_id)
		{
			$sql .= " AND project_id='".$project_id."'";
		}
		return $this->db->get_one($sql);
		
	}

	function get_next($id,$cate_id=0,$project_id=0,$module_id=0,$site_id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list l ";
		$sql.= " WHERE l.status='1' AND l.hidden='0' ";
		if($cate_id)
		{
			$sql .= " AND l.cate_id='".$cate_id."' ";
		}
		if($project_id)
		{
			$sql .= " AND l.project_id='".$project_id."' ";
		}
		if($module_id)
		{
			$sql .= " AND l.module_id='".$module_id."' ";
		}
		if($site_id)
		{
			$sql .= " AND l.site_id='".$site_id."' ";
		}
		$sql .= " AND l.id>".$id." ";
		$sql .= " ORDER BY l.sort DESC,l.dateline ASC,l.id ASC LIMIT 1";
		return $this->db->get_one($sql);
	}

	function get_prev($id,$cate_id=0,$project_id=0,$module_id=0,$site_id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list l ";
		$sql.= " WHERE l.status='1' AND l.hidden='0' ";
		if($cate_id)
		{
			$sql .= " AND l.cate_id='".$cate_id."' ";
		}
		if($project_id)
		{
			$sql .= " AND l.project_id='".$project_id."' ";
		}
		if($module_id)
		{
			$sql .= " AND l.module_id='".$module_id."' ";
		}
		if($site_id)
		{
			$sql .= " AND l.site_id='".$site_id."' ";
		}
		$sql .= " AND l.id<".$id." ";
		$sql .= " ORDER BY l.sort DESC,l.dateline DESC,l.id DESC LIMIT 1";
		return $this->db->get_one($sql);
	}

	//根据当前主题ID，取得其上下主题
	function next_prev($id)
	{
		if(!$id) return false;
		$sql = "SELECT l.*,p.orderby FROM ".$this->db->prefix."list l ";
		$sql.= " JOIN ".$this->db->prefix."project p ON(l.project_id=p.id) ";
		$sql.= " WHERE l.status=1 AND p.status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		//
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id=".intval($id)." AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		$sql = "SELECT orderby FROM ".$this->db->prefix."project WHERE id=".intval($rs['project_id'])." AND status=1";
		$o_rs = $this->db->get_one($sql);
		$orderby = $o_rs['orderby'] ? $o_rs['orderby'] : 'l.sort DESC,l.dateline DESC,l.id DESC';
	}


	function attr_list()
	{
		$xmlfile = $this->dir_root."data/xml/attr.xml";
		if(!file_exists($xmlfile)){
			$array = array("h"=>"头条","c"=>"推荐","a"=>"特荐");
			return $array;
		}
		return $this->lib('xml')->read($xmlfile);
	}

	function title_list($pid=0)
	{
		if(!$pid) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE project_id IN(".$pid.") AND status='1' ORDER BY sort ASC,dateline DESC,id DESC";
		return $this->db->get_all($sql);
	}

	function get_all($condition="",$offset=0,$psize=30,$pri="")
	{
		$sql = "SELECT l.* FROM ".$this->db->prefix."list l ";
		if($condition){
			$sql.= " WHERE ".$condition;
		}
		$sql .= " ORDER BY l.dateline DESC,l.id DESC ";
		if($psize && $psize>0){
			$offset = intval($offset);
			$sql.= " LIMIT ".$offset.",".$psize;
		}
		return $this->db->get_all($sql,$pri);
	}

	function get_all_total($condition="")
	{
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l ";
		if($condition)
		{
			$sql.= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	function pending_info($site_id=0)
	{
		$sql = "SELECT count(l.id) total,p.title,p.id pid,p.parent_id FROM ".$this->db->prefix."list l ";
		$sql.= "JOIN ".$this->db->prefix."project p ON(l.project_id=p.id) ";
		$sql.= " WHERE l.status!=1 AND l.site_id='".$site_id."' AND p.status=1";
		$sql.= " GROUP BY l.project_id ORDER BY p.taxis ASC,p.id DESC ";
		return $this->db->get_all($sql);
	}

	function get_mid($id)
	{
		$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs["module_id"])
		{
			return false;
		}
		return $rs["module_id"];
	}

	//
	function simple_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_one_condition($condition="",$mid=0)
	{
		if(!$condition || !$mid) return false;
		$sql = "SELECT l.*,ext.id _id FROM ".$this->db->prefix."list l ";
		$sql.= "JOIN ".$this->db->prefix."list_".$mid." ext ON(l.id=ext.id) WHERE ".$condition." ORDER BY l.id DESC";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		$ext_rs = $this->get_ext($rs["module_id"],$rs["id"]);
		if($ext_rs) $rs = array_merge($ext_rs,$rs);
		return $rs;
	}

	public function arc_all($project,$condition='',$field='*',$offset=0,$psize=0,$orderby='')
	{
		$sql  = " SELECT ".$field." FROM ".$this->db->prefix."list l ";
		$sql .= " JOIN ".$this->db->prefix."list_".$project['module']." ext ";
		$sql .= " ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
		if(strpos($field,'b.price') !== false){
			$sql .= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
		}
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		if($orderby){
			$sql .= " ORDER BY ".$orderby." ";
		}
		if($psize){
			$sql .= " LIMIT ".intval($offset).",".$psize;
		}
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		//ulist，会员信息
		//clist，分类信息
		//elist，扩展主题信息
		$user_id_list = $idlist = $ulist = $elist = array();
		foreach($rslist as $key=>$value){
			$idlist[] = $value['id'];
			if($value['user_id']){
				$ulist[] = intval($value['user_id']);
				$user_id_list[$value['id']] = $value['user_id']; 
			}
			$elist[] = 'list-'.$value['id'];
		}
		//读取会员信息
		$user_ids = implode(",",array_unique($ulist));
		if($user_ids){
			$condition = "u.id IN(".$user_ids.") AND u.status=1";
			$ulist = $this->model('user')->get_list($condition,0,0);
			if($ulist){
				foreach($user_id_list as $key=>$value){
					if($ulist[$value]){
						$rslist[$key]['user'] = $ulist[$value];
					}
				}
			}
		}
		//读取主题分类信息
		if($project['cate']){
			$title_ids = implode(",",array_unique($idlist));
			$sql = "SELECT lc.id,lc.cate_id,c.title,c.identifier FROM ".$this->db->prefix."list_cate lc ";
			$sql.= "LEFT JOIN ".$this->db->prefix."cate c ON(lc.cate_id=c.id) WHERE lc.id IN(".$title_ids.") ";
			$tmplist = $this->db->get_all($sql);
			if($tmplist){
				foreach($tmplist as $key=>$value){
					$tmp = $value;
					$tmp['url'] = $this->url($project['identifier'],$value['identifier']);
					unset($tmp['id']);
					$rslist[$value['id']]['catelist'][$value['cate_id']] = $tmp;
					$cate_id = $rslist[$value['id']]['cate_id'];
					if($cate_id && $cate_id == $value['cate_id']){
						$rslist[$value['id']]['cate'] = $tmp;
					}
				}
			}
		}

		//读取主题的扩展
		$elist = array_unique($elist);
		$tmplist = $this->model('ext')->get_all($elist,true);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$k = explode('-',$key);
				$rslist[$k[1]] = array_merge($value,$rslist[$k[1]]);
			}
		}
		return $rslist;
	}

	public function arc_count($mid,$condition='')
	{
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l ";
		$sql .= " JOIN ".$this->db->prefix."list_".$mid." ext ";
		$sql .= " ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."list_biz b ON(l.id=b.id) ";
		if($condition)
		{
			$sql .= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	public function delete($id,$mid=0)
	{
		if(!$mid){
			$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
			$rs = $this->db->get_one($sql);
			$mid = $rs['module_id'];
		}
		//删除扩展主题信息
		if($mid){
			$sql = "DELETE FROM ".$this->db->prefix."list_".$mid." WHERE id='".$id."'";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$this->db->query($sql);
		//删除相关的回复信息
		$sql = "DELETE FROM ".$this->db->prefix."reply WHERE tid='".$id."'";
		$this->db->query($sql);
		//删除Tag相关
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE title_id='".$id."'";
		$this->db->query($sql);
		//删除扩展分类
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id='".$id."'";
		$this->db->query($sql);
		//删除主题自身的扩展字段
		$sql = "SELECT id FROM ".$this->db->prefix."ext WHERE module='list-".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$value['id']."'";
				$this->db->query($sql);
			}
			$sql = "DELETE FROM ".$this->db->prefix."ext WHERE module='list-".$id."'";
			$this->db->query($sql);
		}
		return true;
	}

	public function subtitle_ids($id)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE parent_id='".$id."' AND status=1 ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		return array_keys($rslist);
	}

	public function biz_attrlist($tid,$aid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list_attr WHERE tid='".$tid."' ";
		if($aid){
			$sql .= " AND aid='".$aid."'";
		}
		$sql.= " ORDER BY aid ASC,taxis ASC";
		return $this->db->get_all($sql);
	}

}
?>