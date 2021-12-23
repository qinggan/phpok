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
if(!$extinfo['appkey'] || !$extinfo['appsecret'] || !$extinfo['regoin_id']){
	$this->error('参数不完整，请配置');
}
$vid = $this->get('video_id');
if(!$vid){
	$r['info'] = P_Lang('未指定VideoId');
	return $r;
}
$cate_id = $this->get('cate_id','int');
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
if($extinfo['vtype'] == 'video'){
	$data = $this->lib('aliyun')->video_info($vid);
	$play = $this->lib('aliyun')->play_url($vid);
	if(!$data || !$play){
		$r['info'] = P_Lang('获取失败');
		return $r;
	}
	if(!$data['status']){
		$r['info'] = $data['error'];
		return $r;
	}
	if(!$play['status']){
		$r['info'] = $play['error'];
		return $r;
	}
	$info = $data['info']->Video;
	$filename = $play['info']->PlayInfoList->PlayInfo[0]->PlayURL;
	$media_type = $play['info']->VideoBase->MediaType;
	if(strpos($filename,'?') !== false){
		$filename = strstr($filename,'?',true);
	}
	$tmp = strstr(basename($filename),'.');
	$ext = str_replace(".",'',$tmp);
	$data = array();
	if($cate_id){
		$data['cate_id'] = $cate_id;
	}
	$audio_type = array('mp3','wma','wave','ape','flac','aac');
	$video_type = array('mp4','mpeg','mpg','webm','flv');
	if(in_array($ext,$video_type)){
		$data['mime_type'] = 'video/'.$ext;
	}
	if(in_array($ext,$audio_type)){
		$data['mime_type'] = 'audio/'.$ext;
		if($ext == 'mp3'){
			$data['mime_type'] = 'audio/x-mpeg';
		}
	}

	$data['title'] = $info->Title;
	$data['filename'] = $filename;
	$data['name'] = basename($filename);
	$data['ext'] = $ext;
	$data['ico'] = $info->CoverURL ? $info->CoverURL : 'images/filetype-large/'.$data['ext'].'.jpg';
	$data['session_id'] = $this->session->sessid();
	$data['user_id'] = $this->session->val('user_id');
	$data['admin_id'] = $this->session->val('admin_id');
	$data['addtime'] = $this->time;
	$data['attr'] = array('filesize'=>$info->Size,'vid'=>$info->VideoId,'times'=>$info->Duration);
	$insert_id = $this->model('res')->save($data);
	if(!$insert_id){
		$this->lib('aliyun')->video_delete($vid);
		$r['info'] = P_Lang('附件信息写数据库失败');
		return $r;	
	}
	$data['id'] = $insert_id;
	$r['info'] = $data;
	$r['status'] = true;
	return $r;
}
$data = $this->lib('aliyun')->image_info($vid);
if(!$data){
	$r['info'] = P_Lang('获取失败');
	return $r;
}
if(!$data['status']){
	$r['info'] = $data['error'];
	return $r;
}
$info = $data['info']->ImageInfo;
$filename = $info->URL;
if(strpos($filename,'?') !== false){
	$filename = strstr($filename,'?',true);
}
$name = $info->Mezzanine->OriginalFileName;
$tmp = strstr(basename($filename),'.');
$ext = str_replace(".",'',$tmp);
$data = array();
if($cate_id){
	$data['cate_id'] = $cate_id;
}
$data['title'] = $info->Title;
$data['filename'] = $filename;
$data['name'] = $name;
$data['ext'] = $ext;
$data['mime_type'] = 'image/'.$ext;
$data['ico'] = $filename;
$data['session_id'] = $this->session->sessid();
$data['user_id'] = $this->session->val('user_id');
$data['admin_id'] = $this->session->val('admin_id');
$data['addtime'] = $this->time;
$data['attr'] = array('filesize'=>$info->Mezzanine->FileSize,'width'=>$info->Mezzanine->Width,'height'=>$info->Mezzanine->Height,'vid'=>$vid);
$insert_id = $this->model('res')->save($data);
if(!$insert_id){
	$this->lib('aliyun')->image_delete($vid);
	$r['info'] = P_Lang('附件信息写数据库失败');
	return $r;	
}
$data['id'] = $insert_id;
$r['info'] = $data;
$r['status'] = true;
return $r;