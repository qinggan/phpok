<?php
/**
 * 删除文件操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月20日
**/

$filename = $this->get('filename');
$r = array('status'=>false);
if(!$filename){
	$r['info'] = P_Lang('没有传参数');
	return $r;
}
$this->lib('qiniu')->ak($extinfo['appkey']);
$this->lib('qiniu')->sk($extinfo['appsecret']);
$this->lib('qiniu')->bucket($extinfo['bucket']);
$this->lib('qiniu')->delete_file($filename);
$r['status'] = true;
return $r;