<?php
/**
 * 前端分类读取
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年02月04日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cate_model extends cate_model_base
{
	private $tmpdata = array();
	public function __construct()
	{
		parent::__construct();
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
	public function cate_all($site_id=0,$status=0,$orderby='')
	{
		$cache_id = $this->cache->id('www'.$site_id.'1'.$orderby);
		if($this->tmpdata && $this->tmpdata[$cache_id]){
			return $this->tmpdata[$cache_id];
		}
		$siteid = intval($site_id);
		if(!$siteid){
			$siteid = $this->site_id;
		}
		$rslist = parent::cate_all($siteid,true,$orderby);
		if(!$rslist){
			return false;
		}
		$this->tmpdata[$cache_id] = $rslist;
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
}