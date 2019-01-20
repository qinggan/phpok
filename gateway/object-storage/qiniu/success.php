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
$ext = $this->get('ext');
if(!$ext){
	$r['info'] = P_Lang('未指定附件类型');
	return $r;
}
$cate_id = $this->get('cate_id','int');
$cate_rs = $this->model('rescate')->get_one($cate_id);
if(!$cate_rs){
	$cate_rs = $this->model('rescate')->get_default();
}
if(!$cate_rs){
	$cate_rs = array('id'=>0,'filetypes'=>'jpg,png,gif,jpeg,mp4,rmvb,mp3');
}
$tmp = explode(",",$cate_rs['filetypes']);
if(!in_array($ext,$tmp)){
	$r['info'] = P_Lang('附件格式不符合系统要求');
	return $r;
}
$filename = $this->get('filename');
if(!$filename){
	$this->error(P_Lang('附件不能为空'));
}
$name = $this->get('name');
if(!$name){
	$name = basename($filename);
}
$check = $this->model('res')->get_name($name);
if($check){
	$r['info'] = $check;
	$r['status'] = true;
	return $r;
}
$title = $this->get('title');
if(!$title){
	$title = basename($filename);
}
$ico = 'images/filetype-large/'.$ext.'.jpg';
$extlist = array('png','gif','jpeg','jpg');
//裁剪缩略图
if(in_array($ext,$extlist)){
	$ico = $filename.'?imageView2/1/w/200';
}else{
	if(!is_file($this->dir_root.$ico)){
		$ico = 'images/filetype-large/unknown.jpg';
	}
}

$data = array('cate_id'=>$cate_rs['id']);
$data['name'] = $name;
$data['ext'] = $ext;
$data['filename'] = $filename;
$data['title'] = $this->get('title');
$data['ico'] = $ico;
$data['attr'] = array('hash'=>$this->get('hash'));
$data['session_id'] = $this->session->sessid();
$data['user_id'] = $this->session->val('user_id');
$data['admin_id'] = $this->session->val('admin_id');
$data['addtime'] = $this->time;
$insert_id = $this->model('res')->save($data);
if(!$insert_id){
	//删除七牛接口附件信息
	$this->lib('qiniu')->ak($extinfo['appkey']);
	$this->lib('qiniu')->sk($extinfo['appsecret']);
	$this->lib('qiniu')->bucket($extinfo['bucket']);
	$this->lib('qiniu')->delete_file($name);
	$r['info'] = P_Lang('附件信息写数据库失败');
	return $r;	
}

$data['id'] = $insert_id;
$r['info'] = $data;
$r['status'] = true;

return $r;