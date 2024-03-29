<?php
/*****************************************************************************************
	文件： {phpok}/model/www/tpl_model.php
	备注： 模板
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年11月17日 00时18分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tpl_model extends tpl_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tpl WHERE id='".$id."'";
		$cache_id = $this->cache->id($sql);
		$rs = $this->cache->get($cache_id);
		if($rs){
			return $rs;
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$this->cache->save($cache_id,$rs);
		return $rs;
	}
}

?>