<?php
/*****************************************************************************************
	文件： plugins/locoy/setting.php
	备注： 火车头采集器数据扩展项
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月16日 21时40分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class setting_locoy extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->tpl->assign('plugin',$this->me);
	}

	public function index()
	{
		$rslist = $this->model('project')->get_all_project($_SESSION['admin_site_id']);
		if($rslist){
			foreach($rslist as $key=>$value){
				if(!$value['module']){
					unset($rslist[$key]);
				}
			}
		}
		$this->assign("plist",$rslist);
		return $this->plugin_tpl('setting.html');
	}

	public function save()
	{
		$id = $this->plugin_id();
		$ext = array();
		$ext['pid'] = $this->get('pid','int');
		$this->plugin_save($ext,$id);
	}
}

?>