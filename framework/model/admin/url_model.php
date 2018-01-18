<?php
/**
 * 网址格式生成规范
 * @file framework/model/admin/url_model.php
 * @author phpok.com <admin@phpok.com>
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @version 4.5.0
 * @date 2016年01月27日
 */

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}


class url_model extends url_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function url($ctrl='index',$func='index',$ext='')
	{
		return $this->url_ctrl($ctrl,$func,$ext);
	}
}