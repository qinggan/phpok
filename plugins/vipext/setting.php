<?php
/**
 * PHPOK-VIP插件扩展<插件配置>
 * @作者 phpok.com
 * @版本 5.3.135
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年11月11日 10时21分
**/
class setting_vipext extends phpok_plugin
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
		$ext['email_pid'] = $this->get('email_pid');
		$ext['email_field'] = $this->get('email_field');
		$ext['email_reply'] = $this->get('email_reply');
		$ext['email_fullname'] = $this->get('email_fullname');
		$this->_save($ext,$id);
	}
}