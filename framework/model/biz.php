<?php
/**
 * 电商相关操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年01月16日
**/

class biz_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function unitlist()
	{
		$xmlfile = $this->dir_data."xml/unit.xml";
		if(!file_exists($xmlfile)){
			return false;
		}
		return $this->lib('xml')->read($xmlfile);
	}
}
