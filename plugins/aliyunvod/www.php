<?php
/**
 * 阿里云视频库插件<前台应用>
 * @package phpok\plugins
 * @作者 phpok.com
 * @版本 4.8.000
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年09月13日 16时21分
**/
class www_aliyunvod extends phpok_plugin
{
	public $me;
	private $video_pid = 0;
	private $regoin_id = 'cn-shanghai';
	private $end_point = 'https://vod.cn-shanghai.aliyuncs.com';
	private $access_id = '';
	private $access_secret = '';
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
		if($this->me && $this->me['param']){
			$this->video_pid = $this->me['param']['video_stock'];
			if($this->me['param']['regoin_id']){
				$this->regoin_id = $this->me['param']['regoin_id'];
			}
			if($this->me['param']['access_id']){
				$this->access_id = $this->me['param']['access_id'];
			}
			if($this->me['param']['access_secret']){
				$this->access_secret = $this->me['param']['access_secret'];
			}
			if($this->me['param']['end_point']){
				$this->end_point = $this->me['param']['end_point'];
			}
		}
	}

	public function ap_project_index_after()
	{
		if(!$this->video_pid){
			return false;
		}
		$page_rs = $this->tpl->val('page_rs');
		if($page_rs['id'] != $this->video_pid){
			return false;
		}
		$rslist = $this->tpl->val('rslist');
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			$value['longtime'] = $this->_time($value['longtime']);
			if($value['thumbfile']){
				$value['thumb'] = $value['thumbfile']['gd']['video'];
			}
			if(!$value['thumb']){
				$value['thumb'] = 'images/video.png';
			}
			$value['is_vip'] = false;
			if($value['videoid'] && $this->session->val('user_id') && ($this->session->val('user_gid') == 6 || $this->session->val('user_gid') == 7)){
				$value['is_vip'] = true;
			}
			$rslist[$key] = $value;
		}
		$this->assign('rslist',$rslist);
	}

	private function _time($times=0)
	{
		if(!$times || !intval($times) || $times <=0){
			return '未知';
		}
		if($times > 3600){
			$hour = floor($times/3600);
			$minute = floor(($times-3600 * $hour)/60);
			$second = floor((($times-3600 * $hour) - 60 * $minute) % 60);
			if($hour < 10){
				$hour = '0'.$hour;
			}
			if($minute < 10){
				$minute = '0'.$minute;
			}
			if($second < 10){
				$second = '0'.$second;
			}
			return $hour.':'.$minute.':'.$second;
		}
		if($times > 60){
			$minute = floor($times/60);
			$second = floor(($times-60 * $minute)%60);
			if($minute < 10){
				$minute = '0'.$minute;
			}
			if($second < 10){
				$second = '0'.$second;
			}
			return $minute.':'.$second;
		}
		if($times < 10){
			$times = '0'.$times;
		}
		return '00:'.$times;
	}
	
	public function ap_content_index_after()
	{
		if(!$this->video_pid){
			return false;
		}
		$page_rs = $this->tpl->val('page_rs');
		if($page_rs['id'] != $this->video_pid){
			return false;
		}
		$rs = $this->tpl->val('rs');
		$tolink = $this->get('tolink');
		if($tolink == 'vqq'){
			$this->_location($rs['vqq']);
		}
		if($tolink == 'youku'){
			$this->_location($rs['youku']);
		}
		if($tolink == 'baidupan'){
			$this->_location($rs['baidupan']);
		}

		if($tolink == 'bilibili'){
			$this->_location($rs['bilibili']);
		}
		
		if($rs['longtime']){
			$longtime = $this->_time($rs['longtime']);
			$this->assign('longtime',$longtime);
		}
		if($rs['notvip'] && $rs[$rs['notvip']]){
			$video = $this->_video($rs[$rs['notvip']],$rs['notvip']);
			$this->assign('video',$video);
		}

		if(!$rs['videoid'] || !$this->session->val('user_id')){
			return false;
		}
		$this->lib('aliyun')->regoin_id($this->regoin_id);
		$this->lib('aliyun')->access_key($this->access_id);
		$this->lib('aliyun')->access_secret($this->access_secret);
		$info = $this->lib('aliyun')->client();
		if(!$info){
			return false;
		}
		if(is_array($info) && !$info['status']){
			return false;
		}
		//error_reporting(E_ALL ^ E_NOTICE);
		//$tmp = $this->lib('aliyun')->play_info($rs['videoid']);
		$data = $this->lib('aliyun')->play_auth($rs['videoid']);
		if(!$data){
			return false;
		}
		if(is_array($data) && !$data['status']){
			return false;
		}
		$play_auth = $data['info']->PlayAuth;
		if(!$play_auth){
			return false;
		}
		$this->assign('aliyun_playauth',$play_auth);
	}

	private function _video($url,$type='vqq')
	{
		if(!$type){
			$type = 'vqq';
		}
		if($type == 'vqq'){
			$tmp = parse_url($url);
			$filename = basename($tmp['path']);
			$filename = substr($filename,0,-5);
			$video = '//'.$tmp['host'].'/iframe/player.html?vid='.$filename.'&tiny=0&auto=0';
			return $video;
		}
		if($type == 'youku'){
			$tmp = parse_url($url);
			$filename = basename($tmp['path']);
			$filename = substr($filename,3,-5);
			$video = '//player.youku.com/embed/'.$filename;
			return $video;
		}
		if($type == 'bilibili'){
			$tmp = parse_url($url);
			$filename = basename($tmp['path']);
			$video = '//player.bilibili.com/player.html?bvid='.$filename;
			return $video;
		}
	}
	
	
	
	/**
	 * 针对不同项目，配置不同的主题查询条件，如果不使用，请删除这个方法
	 * @参数 $project 项目信息，数组
	 * @参数 $module 模块信息，数组
	 * @返回 $dt数组或false 
	**/
	public function system_www_arclist($project,$module)
	{
		//$dt = array();
		//$dt["fields"] = "id,thumb";
		//$this->assign("dt",$dt);
	}
	
	
}