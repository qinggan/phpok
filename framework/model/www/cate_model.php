<?php
/*****************************************************************************************
	文件： {phpok}/model/www/cate.php
	备注： 前端分类读取
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年11月05日 10时47分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cate_model extends cate_model_base
{
	function __construct()
	{
		parent::__construct();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	//读取当前分类信息
	public function get_one($id,$field="id",$ext=true)
	{
		$cate_all = $this->cate_all($this->site_id);
		if(!$cate_all){
			return false;
		}
		$rs = false;
		foreach($cate_all as $key=>$value){
			if($value[$field] == $id){
				$rs = $value;
				break;
			}
		}
		return $rs;
	}

	//前端读取分类，带格式化
	public function get_all($site_id=0,$status=0,$pid=0)
	{
		$cate_all = $this->cate_all($siteid);
		$tmplist = array();
		$this->_format($tmplist,$cate_all,$pid);
	}

	//格式化分类数组
	private function _format(&$rslist,$tmplist,$parent_id=0,$layer=0)
	{
		foreach($tmplist AS $key=>$value)
		{
			if($value["parent_id"] == $parent_id)
			{
				$is_end = true;
				foreach($tmplist AS $k=>$v)
				{
					if($v["parent_id"] == $value["id"])
					{
						$is_end = false;
						break;
					}
				}
				$value["_layer"] = $layer;
				$value["_is_end"] = $is_end;
				$rslist[] = $value;
				//执行子级
				$new_layer = $layer+1;
				$this->_format($rslist,$tmplist,$value["id"],$new_layer);
			}
		}
	}

	//前端中涉及到的缓存
	public function cate_all($site_id=0,$status=0)
	{
		$siteid = intval($site_id);
		if(!$siteid){
			$siteid = $this->site_id;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE site_id='".$siteid."' AND status=1 ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$extlist = $GLOBALS['app']->model('ext')->cate();
		if($extlist){
			foreach($rslist as $key=>$value){
				$tmpid = 'cate-'.$value['id'];
				if($extlist[$tmpid]){
					$value = array_merge($value,$extlist[$tmpid]);
				}
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	//读取子类
	public function sublist(&$catelist,$parent_id=0,$cate_all=0)
	{
		if(!$cate_all)
		{
			$cate_all = $this->cate_all();
		}
		if(!$cate_all || !is_array($cate_all))
		{
			return false;
		}
		foreach($cate_all as $key=>$value)
		{
			if($value['parent_id'] == $parent_id)
			{
				$catelist[] = $value;
				$this->sublist($catelist,$value['id'],$cate_all);
			}
		}
	}

	//生成适用于select的下拉菜单中的参数
	public function cate_option_list($list)
	{
		if(!$list || !is_array($list)) return false;
		$rslist = array();
		foreach($list AS $key=>$value)
		{
			$value["_space"] = "";
			for($i=0;$i<$value["_layer"];$i++)
			{
				$value["_space"] .= "&nbsp; &nbsp;│";
			}
			if($value["_is_end"] && $value["_layer"])
			{
				$value["_space"] .= "&nbsp; &nbsp;├";
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function get_root_id($id)
	{
		$rs = $this->get_one($id);
		if(!$rs)
		{
			return false;
		}
		if(!$rs['parent_id'])
		{
			return $rs['id'];
		}
		else
		{
			return $this->get_root_id($rs['parent_id']);
		}
	}
	
}

?>