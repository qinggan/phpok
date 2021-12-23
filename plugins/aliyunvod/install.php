<?php
/**
 * 阿里云视频库插件<插件安装>
 * @package phpok\plugins
 * @作者 phpok.com
 * @版本 4.8.000
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年09月13日 16时21分
**/
class install_aliyunvod extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}
	
	/**
	 * 插件安装时，增加的扩展表单输出项，如果不使用，请删除这个方法
	**/
	public function index()
	{
		$condition = "module>0";
		$project_list = $this->model('project')->project_all($this->session->val('admin_site_id'),'id',$condition);
		$this->assign('project_list',$project_list);
		return $this->_tpl('setting.html');
	}
	
	/**
	 * 插件安装时，保存扩展参数，如果不使用，请删除这个方法
	**/
	public function save()
	{
		$id = $this->_id();
		$ext = array();
		$ext['video_stock'] = $this->get('video_stock','int');
		$ext['access_id'] = $this->get('access_id');
		$ext['access_secret'] = $this->get('access_secret');
		$ext['regoin_id'] = $this->get('regoin_id');
		$ext['end_point'] = $this->get('end_point');
		$ext['aliyun_accout'] = $this->get('aliyun_accout');
		$this->_save($ext,$id);
	}
	
	
}