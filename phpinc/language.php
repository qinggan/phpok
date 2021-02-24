<?php
/**
 * 加载多国家信息（同一个站点多个国家使用，多个语言使用）
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年1月18日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

$condition = "status=1 AND lang_code!=''";

$countrylist = $app->model('worlds')->get_all($condition);
//echo "<pre>".print_r($countrylist,true)."</pre>";

//$this->assign('is_end',$is_end);
//$this->assign('rslist',$rslist);
//$this->assign('leadtitle',$leadtitle);
//$this->assign('leadtype',$leadtype);
//$this->assign('leader',$leader);
//$this->assign('keywords',$keywords);
////读取站点
//$sitelist = $this->model('site')->get_all_site('id');
//$this->assign('sitelist',$sitelist);
////读取语言
//$langlist = $this->model('lang')->get_list();
//$this->assign('langlist',$langlist);