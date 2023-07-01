<?php
/**
 * 电商相关操作
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
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
