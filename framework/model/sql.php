<?php
/*****************************************************************************************
	文件： {phpok}/model/sql.php
	备注： 数据库备份相关Model管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年3月19日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class sql_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	//读取全部表信息
	function tbl_all()
	{
		$sql = "SHOW TABLE STATUS FROM ".$this->db->data;
		return $this->db->get_all($sql);
	}
}

?>