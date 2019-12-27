<?php
/**
 * 百度地图地址编码<插件安装>
 * @package phpok\plugins
 * @作者 phpok
 * @版本 4.9.032
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年07月07日 09时51分
**/
class install_bmap extends phpok_plugin
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
        $id = $this->_id();
        if(is_file($this->me['path'].'plugin-'.$id.'-icon.xml')){
	        $this->lib('file')->cp($this->me['path'].'plugin-'.$id.'-icon.xml',$this->dir_data.'plugin-'.$id.'-icon.xml');
        }
        $ext = array();
        $ext['address'] = $this->get('address');
        $ext['apikey'] = $this->get('apikey');
        $ext['company'] = $this->get('company');
		$ext['tel'] = $this->get('tel');
		
        $this->_save($ext,$id);
	}
	
	
}