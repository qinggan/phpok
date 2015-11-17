<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/plugin_model.php
	备注： 插件的后台读写操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年11月12日 09时55分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class plugin_model extends plugin_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	//取得下一个默认排序
	public function get_next_taxis()
	{
		$sql = "SELECT taxis FROM ".$this->db->prefix."plugins";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return 10;
		}
		$taxis = 0;
		foreach($rslist as $key=>$value){
			if($value['taxis'] != 255 && $value['taxis']>$taxis){
				$taxis = $value['taxis'];
			}
		}
		if(!$taxis){
			return 10;
		}
		$next = ($taxis+10)<255 ? ($taxis+10) : 255;
		return $next;
	}
}

?>