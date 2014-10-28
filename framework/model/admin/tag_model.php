<?php
/*****************************************************************************************
	文件： {phpok}/model/tag.php
	备注： Tag标签在后台的调用
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月25日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_model extends phpok_model
{
	private $popedom;
	function __construct()
	{
		parent::model();
		$this->popedom = appfile_popedom("tag");
		$this->assign("popedom",$this->popedom);
	}

	function get_list($condition="",$offset=0,$psize=30)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tag WHERE ".$condition;
		$sql.= " ORDER BY id DESC LIMIT ".$offset.",".$psize;
	}

	function get_total($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."tag WHERE ".$condition;
		return $this->db->count($sql);
	}
}

?>