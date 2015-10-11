<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/module_model.php
	备注： 模块扩展
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月05日 21时03分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class module_model extends module_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function module_next_taxis()
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."module";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	public function fields_next_taxis($mid)
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."module_fields WHERE module_id='".$mid."'";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}
}

?>