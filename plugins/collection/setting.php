<?php
/*****************************************************************************************
	文件： plugins/collection/setting.php
	备注： 采集器<插件配置>
	版本： 4.x
	网站： www.phpok.com
	作者： phpok.com
	时间： 2015年08月24日 08时45分
*****************************************************************************************/
class setting_collection extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
	}
	//插件配置参数时，增加的扩展表单输出项
	public function index()
	{
		$top_menu_list = $this->model('sysmenu')->get_list(0,1);
		$this->assign('top_menu_list',$top_menu_list);
		$rescatelist = $this->model('rescate')->get_all();
		$this->assign('res_catelist',$rescatelist);
		return $this->_tpl('install.html');
	}
	//插件配置参数时，保存扩展参数
	public function save()
	{
		$id = $this->plugin_id();
		$ext = array();
		$ext['rescate'] = $this->get('rescate','int');
		$this->_save($ext,$id);
	}


}