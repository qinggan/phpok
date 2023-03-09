<?php
/**
 * 附件上传成功后，提交到服务端，增加登记
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月19日
**/

$r = array('status'=>false);
if(!$extinfo['region'] || !$extinfo['SecretId'] || !$extinfo['SecretKey'] || !$extinfo['bucket']){
	$this->error('参数不完整，请配置');
}
$filename = $this->get('filename');
if(!$filename){
	$r['info'] = P_Lang('未指定文件');
	return $r;
}
$cate_id = $this->get('cate_id','int');
$this->lib('tencentcos')->region($extinfo['region']);
$this->lib('tencentcos')->secret_id($extinfo['SecretId']);
$this->lib('tencentcos')->secret_key($extinfo['SecretKey']);
$this->lib('tencentcos')->bucket($extinfo['bucket']);
$info = $this->lib('tencentcos')->client();
if(!$info){
	$r['info'] = P_Lang('配置错误');
	return $r;
}
if(!$info['status']){
	$r['info'] = $info['error'];
	return $r;
}
$chk = $this->lib('tencentcos')->meta($filename);
if(!$chk){
	$r['info'] = '文件不存在';
	return $r;
}
if(!$chk['status']){
	$r['info'] = $chk['error'];
	return $r;
}
$title = $this->get('title');
if(!$title){
	$title = str_replace('.'.$ext,'',$tmp);
}

if($chk['info'] && $chk['info']['Metadata'] && $chk['info']['Metadata']['title']){
	$title = rawurldecode($chk['info']['Metadata']['title']);
}

//写入到数据库中
$bucket_domain = $extinfo['bucket_doamin'];
if(!$bucket_domain){
	$bucket_domain = 'https://'.$extinfo['bucket'].'.cos.'.$extinfo['region'].'.myqcloud.com/';
}
if(substr($bucket_domain,-1) != '/'){
	$bucket_domain .= "/";
}
$cate_rs = $this->model('rescate')->get_one($cate_id);
if(!$cate_rs){
	$cate_rs = $this->model('rescate')->get_default();
}

$data = array();
$data['cate_id'] = $cate_rs['id'];
$tmp = basename($filename);
$tmplist = explode(".",$tmp);
$ext = $tmplist[(count($tmplist)-1)];
if(!$ext){
	$ext = 'zip';
}
$ext = strtolower($ext);
if(!in_array($ext,array('jpg','gif','png','jpeg'))){
	$thumb = 'images/filetype-large/'.$ext.'.jpg';
}else{
	$thumb = $bucket_domain.$this->lib('tencentcos')->ico($filename);
	$data['mime_type'] = "images/".$ext;
}
$data['title'] = $title;
$data['filename'] = $bucket_domain.$filename;
$data['name'] = basename($filename);
$data['ext'] = $ext;
$data['ico'] = $thumb;
$data['session_id'] = $this->session->sessid();
$data['user_id'] = $this->session->val('user_id');
$data['admin_id'] = $this->session->val('admin_id');
$data['addtime'] = $this->time;
$insert_id = $this->model('res')->save($data);
if(!$insert_id){
	$this->lib('tencentcos')->del($filename);
	$r['info'] = P_Lang('附件信息写数据库失败');
	return $r;	
}
$data['id'] = $insert_id;
$r['info'] = $data;
$r['status'] = true;
return $r;