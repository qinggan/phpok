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
if(!$extinfo['regoin_id'] || !$extinfo['bucket'] || !$extinfo['appkey'] || !$extinfo['appsecret']){
	$this->error('参数不完整，请配置');
}
$filename = $this->get('filename');
if(!$filename){
	$r['info'] = P_Lang('未指定文件');
	return $r;
}
$cate_id = $this->get('cate_id','int');
$this->lib('aliyun')->regoin_id($extinfo['regoin_id']);
$this->lib('aliyun')->access_key($extinfo['appkey']);
$this->lib('aliyun')->access_secret($extinfo['appsecret']);
$this->lib('aliyun')->oss_bucket($extinfo['bucket']);
$this->lib('aliyun')->end_point($extinfo['end_point']);
$info = $this->lib('aliyun')->oss_client();
if(!$info){
	$r['info'] = P_Lang('配置错误');
	return $r;
}
if(!$info['status']){
	$r['info'] = $info['error'];
	return $r;
}
$chk = $this->lib('aliyun')->oss_chk($filename);
if(!$chk){
	$r['info'] = '文件不存在';
	return $r;
}
//写入到数据库中
$bucket_domain = $extinfo['bucket_doamin'];
if(!$bucket_domain){
	$bucket_domain = 'https://'.$extinfo['bucket'].'.'.$extinfo['end_point'].'/';
}
if(substr($bucket_domain,-1) != '/'){
	$bucket_domain .= "/";
}
$cate_rs = $this->model('rescate')->get_one($cate_id);
if(!$cate_rs){
	$cate_rs = $this->model('rescate')->get_default();
}

$data = array();
if($cate_id){
	$data['cate_id'] = $cate_rs['id'];
}
$tmp = basename($filename);
$tmplist = explode(".",$tmp);
$ext = $tmplist[(count($tmplist)-1)];
if(!$ext){
	$ext = 'zip';
}
$title = $this->get('title');
if(!$title){
	$title = str_replace('.'.$ext,'',$tmp);
}
$ext = strtolower($ext);
$thumb = '';
if(!in_array($ext,array('jpg','gif','png','jpeg'))){
	$thumb = 'images/filetype-large/'.$ext.'.jpg';
}
$data['title'] = $title;
$data['filename'] = $bucket_domain.$filename;
$data['name'] = basename($filename);
$data['ext'] = $ext;
$data['ico'] = $thumb;
$data['session_id'] = $this->session()->sessid();
$data['user_id'] = $this->session()->val('user_id');
$data['admin_id'] = $this->session()->val('admin_id');
$data['addtime'] = $this->time;
$data['attr'] = array('vid'=>$filename);
$insert_id = $this->model('res')->save($data);
if(!$insert_id){
	$this->lib('aliyun')->oss_delete($filename);
	$r['info'] = P_Lang('附件信息写数据库失败');
	return $r;	
}
//针对图片，生成缩略图
if(in_array($ext,array('jpg','gif','png','jpeg'))){
	$t = $this->lib('aliyun')->oss_ico($filename,$insert_id,$ext);
	if($t && $t['status'] && $t['info']){
		$tmpdata = array("ico"=>$bucket_domain.$t['info']);
		$data['ico'] = $bucket_domain.$t['info'];
		$data['mime_type'] = "image/".$ext;
		$this->model('res')->save($tmpdata,$insert_id);
	}
}
$data['id'] = $insert_id;
$r['info'] = $data;
$r['status'] = true;
return $r;