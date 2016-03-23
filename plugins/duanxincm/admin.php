<?php
/*****************************************************************************************
	文件： plugins/duanxincm/admin.php
	备注： 短信后台操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月04日 12时55分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_duanxincm extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
	}

	public function clearsms()
	{
		$sql = "DELETE FROM ".$this->db->prefix."plugin_duanxincm WHERE status=1";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."plugin_duanxincm WHERE etime<=".$this->time;
		$this->db->query($sql);
		$this->json(true);
	}
}
?>