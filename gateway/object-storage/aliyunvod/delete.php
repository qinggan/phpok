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

$r = array('status'=>false);
$id = $this->get('id','int');
if(!$id){
	$r['info'] = P_Lang('没有传参数');
	return $r;
}
$rs = $this->model('res')->get_one($id);
if(!$rs){
	$r['info'] = P_Lang('附件记录不存在');
	return $r;
}
if(!$extinfo['regoin_id'] || !$extinfo['appkey'] || !$extinfo['appsecret']){
	$r['info'] = P_Lang('参数不完整，请配置');
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
if($rs['attr'] && $rs['attr']['vid']){
	if($extinfo['vtype'] == 'video'){
		$this->lib('aliyun')->video_delete($rs['attr']['vid']);
	}else{
		$this->lib('aliyun')->image_delete($rs['attr']['vid']);
	}
}
$r['status'] = true;
return $r;