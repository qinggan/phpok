<?php
/**
 * 百度地图地址编码<后台应用>
 * @package phpok\plugins
 * @作者 phpok
 * @版本 4.9.032
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年07月07日 09时51分
**/
class admin_bmap extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}

    public function html_plugin_index_foot()
    {
        $this->_show('admin_preview_btn.html');
	}

	/**
	 * 百度地图参数
	**/
	public function set_config()
    {
        $rs = $this->me['param'];
        $this->assign('rs',$rs);
        $this->_view('set_config.html');
    }

    /**
     * 保存参数(请不要写到扩展参数)
     */
    public function config_save()
    {
        $id = $this->me['id'];
        $ext = array();
        $ext['address'] = $this->get('address');
        $ext['apikey'] = $this->get('apikey');
        $ext['tel'] = $this->get('tel');
        $ext['lng'] = $this->get('lng');
        $ext['lat'] = $this->get('lat');
        $ext['address2'] = $this->get('address2');
        $ext['company'] = $this->get('company');
        $this->_save($ext,$id);
        $this->success();
    }
}