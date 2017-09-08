<?php
/**
 * 取得全部标识
 * @package phpok\model\www
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月22日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class id_model extends id_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_ctrl($identifier,$site_id=0)
	{
		$plist = $this->id_project($site_id,true);
		if($plist && $plist[$identifier]){
			return 'project';
		}
		$clist = $this->id_cate($site_id,true);
		if($clist && $clist[$identifier]){
			return 'cate';
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE site_id='".$site_id."' AND identifier='".$identifier."' AND status=1";
		$chk = $this->db->get_one($sql);
		if($chk && $chk['id']){
			return 'content';
		}
		return false;
	}
}