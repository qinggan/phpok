<?php
/**
 * 七牛获取Token请求
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月18日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

$r = array('status'=>false);
if(!$extinfo){
	$r['info'] = P_Lang('参数不完整，请检查');
	return $r;
}

//非管理员，检测是否有上传权限
if(!$this->session->val('admin_id')){
	if(!$this->session->val('user_id') && !$this->site['upload_guest']){
		$r['info'] = P_Lang('你没有上传权限');
		return $r;
	}
	if($this->session->val('user_id') && !$this->site['upload_user']){
		$r['info'] = P_Lang('你没有上传权限');
		return $r;
	}
}
$this->lib('qiniu')->ak($extinfo['appkey']);
$this->lib('qiniu')->sk($extinfo['appsecret']);
$this->lib('qiniu')->bucket($extinfo['bucket']);
$this->lib('qiniu')->url($this->url.$this->config['api_file']);
$info = $this->lib('qiniu')->token();
$r['info'] = $info;
$r['status'] = true;
return $r;