<?php
/**
 * 直播插件<插件安装>
 * @作者 phpok.com
 * @版本 6.0
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年06月28日 11时05分
**/
class install_oklive extends phpok_plugin
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
		return $this->_tpl('setting.html');
	}
	
	/**
	 * 插件安装时，保存扩展参数，如果不使用，请删除这个方法
	**/
	public function save()
	{
		//复制图片到 _data 目录
		if(file_exists($this->me['path'].'bg.jpg')){
			$this->lib('file')->cp($this->me['path'].'bg.jpg',$this->dir_data.'bg.jpg');
		}
		//安装表
		if(file_exists($this->me['path'].'install.sql')){
			phpok_loadsql($this->db,$this->me['path'].'install.sql',true);
		}
		$id = $this->_id();
		$ext = array();
		$ext['push'] = $this->get('push');
		$ext['pushkey'] = $this->get('pushkey');
		$ext['pull'] = $this->get('pull');
		$ext['pullkey'] = $this->get('pullkey');
		$ext['oss_domain'] = $this->get('oss_domain');
		$this->_save($ext,$id);
	}
	
	
}