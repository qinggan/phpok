<?php
/**
 * 后台操作涉及到的日志，如日志删除
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年05月07日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class log_model extends log_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 删除日志操作
	 * @参数 $condition 删除条件
	**/
	public function delete($condition='')
	{
		$sql = "DELETE FROM ".$this->db->prefix."log ";
		if($condition){
			$sql.= "WHERE ".$condition." ";
		}
		return $this->db->query($sql);
	}
}
