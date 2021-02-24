<?php
/**
 * 易宠接口<插件配置>
 * @作者 苏相锟
 * @版本 5.7
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年01月11日 08时59分
**/
class setting_epetbar extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}
	
	/**
	 * 插件配置参数时，增加的扩展表单输出项，如果不使用，请删除这个方法
	**/
	public function index()
	{
		$condition = "module>0";
		$project_list = $this->model('project')->get_all_project($this->session->val('admin_site_id'),$condition);
		$this->assign('project_list',$project_list);
		return $this->_tpl('setting.html');
	}
	
	/**
	 * 插件配置参数时，保存扩展参数，如果不使用，请删除这个方法
	**/
	public function save()
	{
		$id = $this->_id();
		$ext = array();
		$ext['pid'] = $this->get('pid');
		$ext['app_id'] = $this->get('app_id');
		$ext['app_secret'] = $this->get('app_secret');
		$ext['session_key'] = $this->get('session_key');
		$ext['url'] = $this->get('url');
		$this->_save($ext,$id);
	}
}