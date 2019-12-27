<?php
/**
 * 百度地图地址编码<前台应用>
 * @package phpok\plugins
 * @作者 phpok
 * @版本 4.9.032
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年07月07日 09时51分
**/
class www_bmap extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}

    public function map()
    {
        $this->_view('map.html');
	}
	
}