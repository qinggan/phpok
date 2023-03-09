<?php
/**
 * 删除文件操作
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 6.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2022年2月26日
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
if(!$extinfo['region'] || !$extinfo['SecretId'] || !$extinfo['SecretKey'] || !$extinfo['bucket']){
	$r['info'] = P_Lang('参数不完整，请配置');
	return $r;
}
//验证文件是否符合删除条件
$isCanDelete = false;
if($extinfo['bucket_doamin']){
	if(substr($extinfo['bucket_doamin'],-1) != '/'){
		$extinfo['bucket_doamin'] .= "/";
	}
	$tmp = str_replace($extinfo['bucket_doamin'],'',$rs['filename']);
	if($tmp && $tmp != $rs['filename']){
		$isCanDelete = true;
		$filename = $tmp;
	}
}
if(!$isCanDelete){
	$bucket_domain = 'https://'.$extinfo['bucket'].'.cos.'.$extinfo['region'].'.myqcloud.com/';
	$tmp = str_replace($bucket_domain,'',$rs['filename']);
	if($tmp && $tmp != $rs['filename']){
		$isCanDelete = true;
		$filename = $tmp;
	}
}
if(!$isCanDelete){
	$r['info'] = P_Lang('文件验证不能通示，不能删除');
	return $r;
}

$this->lib('tencentcos')->region($extinfo['region']);
$this->lib('tencentcos')->secret_id($extinfo['SecretId']);
$this->lib('tencentcos')->secret_key($extinfo['SecretKey']);
$this->lib('tencentcos')->bucket($extinfo['bucket']);
$this->lib('tencentcos')->client();
$this->lib('tencentcos')->del($filename);
$r['status'] = true;
return $r;