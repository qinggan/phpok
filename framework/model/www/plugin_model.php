<?php
/*****************************************************************************************
	文件： {phpok}/model/www/plugin_model.php
	备注： 插件前台
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年11月03日 15时28分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class plugin_model extends plugin_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_one($id)
	{
		$list = $this->get_all(1);
		if(!$list || ($list && !$list[$id])){
			return false;
		}
		return $list[$id];
	}

	public function get_all($status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."plugins ";
		if($status){
			$sql .= "WHERE status=1 ";
		}
		$sql .= " ORDER BY taxis ASC,id DESC";
		$cache_id = $this->cache->id($sql);
		$rslist = $this->cache->get($cache_id);
		if($rslist){
			return $rslist;
		}
		$this->db->cache_set($cache_id);
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$this->cache->save($cache_id,$rslist);
		return $rslist;
	}
}

?>