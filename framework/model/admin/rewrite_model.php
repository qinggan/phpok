<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/rewrite_model.php
	备注： 伪静态页后台相关操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月03日 12时58分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class rewrite_model extends rewrite_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	public function save($data)
	{
		return $this->db->insert_array($data,'rewrite','replace');
	}

	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."rewrite WHERE id='".$id."' AND site_id='".$this->site_id."'";
		return $this->db->query($sql);
	}
}

?>