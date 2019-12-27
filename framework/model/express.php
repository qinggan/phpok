<?php
/**
 * 物流管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年5月26日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

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