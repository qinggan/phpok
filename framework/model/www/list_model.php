<?php
/**
 * 仅限WEB接口调用的内容块
 * @author phpok.com <admin@phpok.com>
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @version 5.0.0
 * @date 2016年02月05日
 */
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class list_model extends list_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function add_hits($id)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET hits=hits+1 WHERE id='".$id."'";
		return $this->db->query($sql,false);
	}

	public function get_hits($id)
	{
		$sql = "SELECT hits FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		return $rs['hits'];
	}
}

?>