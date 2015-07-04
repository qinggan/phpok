<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/workflow_model.php
	备注： 工作流后台管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年06月20日 14时04分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class workflow_model extends workflow_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_tid($id)
	{
		$sql = " SELECT w.*,a.account,l.title FROM ".$this->db->prefix."workflow w ";
		$sql.= " JOIN ".$this->db->prefix."adm a ON(w.admin_id=a.id) ";
		$sql.= " JOIN ".$this->db->prefix."list l ON(w.tid=l.id) ";
		$sql.= " WHERE w.tid='".$id."'";
		return $this->db->get_one($sql);
	}
}

?>