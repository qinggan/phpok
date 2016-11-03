<?php
/***********************************************************
	Filename: {phpok}model/content.php
	Note	: 内容
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年9月24日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class content_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	//取得单个主题信息
	function get_one($id,$status=true)
	{
		$sql  = "SELECT * FROM ".$this->db->prefix."list WHERE site_id='".$this->site_id."' AND ";
		$sql .= is_numeric($id) ? " id='".$id."' " : " identifier='".$id."' ";
		if($status){
			$sql.= " AND status=1 ";
		}
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
			$sql = "SELECT * FROM ".$this->db->prefix."list_".$rs['module_id']." WHERE id='".$rs['id']."'";
			$ext_rs = $this->db->get_one($sql);
			if($ext_rs){
				$rs = array_merge($ext_rs,$rs);
			}
		}
		//读取属性
		$sql = "SELECT * FROM ".$this->db->prefix."list_attr WHERE tid='".$rs['id']."'";
		$attrlist = $this->db->get_all($sql);
		if($attrlist){
			$vids = array();
			$attrs = array();
			foreach($attrlist as $key=>$value){
				$vids[] = $value['vid'];
				if(!$attrs[$value['aid']]){
					$attrs[$value['aid']] = array('id'=>$value['aid']);
					$attrs[$value['aid']]['rslist'][$value['vid']] = $value;
				}else{
					$attrs[$value['aid']]['rslist'][$value['vid']] = $value;
				}
			}
			unset($attrlist);
			$vids = array_unique($vids);
			$alist = $this->model('options')->get_all('id');
			$vlist = $this->model('options')->values_list("id IN(".implode(",",$vids).")",0,999,'id');
			foreach($attrs as $key=>$value){
				$value['title'] = $alist[$key]['title'];
				foreach($value['rslist'] as $k=>$v){
					$v['title'] = $vlist[$k]['title'];
					$v['val'] = $vlist[$k]['val'];
					$v['pic'] = $vlist[$k]['pic'];
					$value['rslist'][$k] = $v;
				}
				$attrs[$key] = $value;
			}
			$rs['attrlist'] = $attrs;
		}

		$ext = $this->model('ext')->get_all('list-'.$rs['id'],false);
		if($ext){
			$rs = array_merge($rs,$ext);
		}		
		return $rs;
	}

	//通过主题ID获取对应的模块ID
	function get_mid($id)
	{
		$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs["module_id"];
	}

	//获取扩展字段并格式化内容
	function ext_list($mid,$ids)
	{
		if(!$mid || !$ids) return false;
		$flist = $GLOBALS['app']->model("module")->fields_all($mid);
		if(!$flist) return false;
		//取得扩展内容
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE id IN(".$ids.")";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		foreach($rslist AS $key=>$value)
		{
			foreach($flist AS $k=>$v)
			{
				if($value[$v["identifier"]])
				{
					$v["content"] = $value[$v["identifier"]];
					$value[$v["identifier"]] = $GLOBALS['app']->lib('ext')->content_format($v);
				}
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

}
?>