<?php
/*****************************************************************************************
	文件： {phpok}/model/express.php
	备注： 物流管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月07日 11时51分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class express_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."express WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function get_all()
	{
		$sql = "SELECT id,title,company,homepage,code FROM ".$this->db->prefix."express";
		return $this->db->get_all($sql);
	}
}

?>