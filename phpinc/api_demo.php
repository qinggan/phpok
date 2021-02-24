<?php
/**
 * 测试演示用的
 * @作者 苏相锟 <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html 
 * @时间 2020年11月7日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
$this->lib('curl')->user_agent($this->lib('server')->agent());
$info = $this->lib('curl')->get_json('https://www.phpok.com/apix-24918?_appid=3&_sign=8e4a5ba033e1b805f95cfb863771fbab&params[keywords]=%E6%B5%8B%E8%AF%95%E4%B8%80%E4%B8%8B%EF%BC%81&params[first]=0');
echo "<pre>".print_r($info,true)."</pre>";