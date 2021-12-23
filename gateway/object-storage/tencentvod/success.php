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
if(!$extinfo['region'] || !$extinfo['SecretId'] || !$extinfo['SecretKey']){
	$r['info'] = P_Lang('参数不完整，请配置');
	return $r;
}
$vid = $this->get('file_id');
if(!$vid){
	$r['info'] = P_Lang('未指定file_id');
	return $r;
}

$cate_id = $this->get('cate_id','int');

$this->lib('tvod')->config($extinfo['SecretId'],$extinfo['SecretKey'],$extinfo['region']);
$info = $this->lib('tvod')->media_info($vid);
if(!$info){
	$r['info'] = P_Lang('配置错误');
	return $r;
}
if(is_array($info) && !$info['status']){
	$r['info'] = $info['info'];
	return $r;
}
$audio_type = array('mp3','wma','wave','ape','flac','aac');
$video_type = array('mp4','mpeg','mpg','webm','flv');

$base = $info['info']['basicInfo'];
$ext = $base['type'];
$data = array();
if($cate_id){
	$data['cate_id'] = $cate_id;
}
$data['title'] = $base['name'];
$data['filename'] = $base['sourceVideoUrl'];
$data['name'] = $base['name'];
$data['ext'] = $ext;

if($ext && (in_array($ext,$audio_type) || in_array($ext,$video_type))){
	if(in_array($ext,$video_type)){
		$data['mime_type'] = 'video/'.$ext;
	}
	if(in_array($ext,$audio_type)){
		$data['mime_type'] = 'audio/'.$ext;
		if($ext == 'mp3'){
			$data['mime_type'] = 'audio/x-mpeg';
		}
	}
	$data['ico'] = $base['coverUrl'] ? $base['coverUrl'] : 'images/filetype-large/'.$ext.'.jpg';
}else{
	$data['mime_type'] = 'image/'.$ext;
	$data['ico'] = $base['coverUrl'] ? $base['coverUrl'] : $base['sourceVideoUrl'];
}
$data['session_id'] = $this->session->sessid();
$data['user_id'] = $this->session->val('user_id');
$data['admin_id'] = $this->session->val('admin_id');
$data['addtime'] = $this->time;
$data['attr'] = array('vid'=>$vid,'filesize'=>$base['size'],'times'=>$base['duration'],'playerId'=>$base['playerId']);
$insert_id = $this->model('res')->save($data);
if(!$insert_id){
	$this->lib('tvod')->media_delete($vid,true);
	$r['info'] = P_Lang('附件信息写数据库失败');
	return $r;	
}
$data['id'] = $insert_id;
$r['info'] = $data;
$r['status'] = true;
return $r;