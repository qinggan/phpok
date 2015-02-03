<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/html_model.php
	备注： HTML生成工具涉及到的Model信息
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年7月30日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class html_model extends html_model_base
{
	function __construct()
	{
		parent::__construct();
	}

	//获取模板信息
	function get_tpl($siteid,$mobile=false)
	{
		$sql = "SELECT tpl_id FROM ".$this->db->prefix."site WHERE id=".intval($siteid)." AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs['tpl_id'])
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."tpl WHERE id=".intval($rs['tpl_id']);
		return $this->db->get_one($sql);
	}

	//获取模板文件
	function list_tpl($dir,$ext='html')
	{
		$tlist = $this->lib('file')->ls($dir);
		if(!$tlist || count($tlist)<1)
		{
			return false;
		}
		$ext_length = strlen($ext);
		foreach($tlist as $key=>$value)
		{
			if(is_dir($value))
			{
				unset($tlist[$key]);
				continue;
			}
			$value = basename($value);
			if(substr($value,-$ext_length) != $ext)
			{
				unset($tlist[$key]);
				continue;
			}
			$tlist[$key] = substr($value,0,-($ext_length+1));
		}
		sort($tlist);
		return $tlist;
	}

	//取得ID对应的日期目录
	function subject_folder($id,$type="Ym/d/")
	{
		$sql = "SELECT dateline FROM ".$this->db->prefix."list WHERE id='".$id."' LIMIT 1";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs['dateline'])
		{
			return false;
		}
		return date($type,$rs['dateline']);
	}

	//取得指定项目下的模块主题数
	function get_subject_total($pid=0,$mid=0,$site_id=0,$cateid='')
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."list WHERE site_id=".intval($site_id)." ";
		$sql.= " AND project_id=".intval($pid)." AND module_id=".intval($mid)." AND status=1 ";
		if($cateid)
		{
			$sql.= " AND cate_id IN(".$cateid.") ";
		}
		return $this->db->count($sql);
	}


	//取得分类，及其子分类
	function get_catelist($siteid=0,$cateid=0)
	{
		$list = array();
		$this->_subcate($list,$cateid,$siteid);
		return $list;
	}

	function title_list($site_id=0,$pid=0,$mid=0)
	{
		$sql = "SELECT id,title,dateline FROM ".$this->db->prefix."list WHERE site_id=".intval($site_id)." AND ";
		$sql.= "project_id=".intval($pid)." AND module_id=".intval($mid)." AND status=1 ";
		return $this->db->get_all($sql,'id');
	}
	
	//取得分类下的子项信息
	function _subcate(&$list,$id=0,$siteid=0)
	{
		$sql = "SELECT id,parent_id,title,psize FROM ".$this->db->prefix."cate WHERE site_id='".$siteid."' AND parent_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if($rslist)
		{
			foreach($rslist as $key=>$value)
			{
				$list[$value['id']] = $value;
				$this->_subcate($list,$value['id'],$siteid);
			}
		}
	}

	function project($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id=".intval($id)." AND status=1";
		return $this->db->get_one($sql);
	}
}

?>