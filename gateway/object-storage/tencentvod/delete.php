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
if(!$extinfo['region'] || !$extinfo['SecretId'] || !$extinfo['SecretKey']){
	$r['info'] = P_Lang('参数不完整，请配置');
	return $r;
}
if($rs['attr'] && $rs['attr']['vid']){
	$this->lib('tvod')->config($extinfo['SecretId'],$extinfo['SecretKey'],$extinfo['region']);
	$this->lib('tvod')->media_delete($rs['attr']['vid'],($extinfo['deleteAct'] == 'yes' ? true : false));
}
$r['status'] = true;
return $r;