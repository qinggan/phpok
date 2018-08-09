<?php
/*****************************************************************************************
	文件： {phpok}/model/api/order_model.php
	备注： 订单接口相关操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月07日 16时12分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_model extends order_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function express_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_express WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function log_delete($order_id,$order_express_id,$who='')
	{
		$sql = "DELETE FROM ".$this->db->prefix."order_log WHERE order_id='".$order_id."' ";
		$sql.= " AND order_express_id='".$order_express_id."' ";
		$sql.= " AND who='".$who."'";
		return $this->db->query($sql);
	}

	public function update_last_query_time($id)
	{
		$sql = "UPDATE ".$this->db->prefix."order_express SET last_query_time='".$this->time."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function update_end($id)
	{
		$sql = "UPDATE ".$this->db->prefix."order_express SET is_end=1 WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}