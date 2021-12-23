<?php
/**
 * 生成VideoId，提交到服务端，增加登记
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月19日
**/

$r = array('status'=>false);
if(!$extinfo['regoin_id'] || !$extinfo['appkey'] || !$extinfo['appsecret']){
	$r['info'] = P_Lang('参数不完整，请配置');
	return $r;
}
$title = $this->get('title');
if(!$title){
	$r['info'] = P_Lang('名称不能为空');
	return $r;
}

$this->lib('aliyun')->regoin_id($extinfo['regoin_id']);
$this->lib('aliyun')->access_key($extinfo['appkey']);
$this->lib('aliyun')->access_secret($extinfo['appsecret']);
$info = $this->lib('aliyun')->client();
if(!$info){
	$r['info'] = P_Lang('配置错误');
	return $r;
}
if(is_array($info) && !$info['status']){
	$r['info'] = $info['error'];
	return $r;
}
$data = $this->lib('aliyun')->create_upload_image($title);
if(!$data){
	$r['info'] = P_Lang('获取失败');
	return $r;
}
if(!$data['status']){
	$r['info'] = $data['error'];
	return $r;
}
$r['info'] = $data['info'];
$r['status'] = true;
return $r;