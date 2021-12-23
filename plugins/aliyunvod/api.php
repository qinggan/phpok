<?php
/**
 * 阿里云视频库插件<接口应用>
 * @package phpok\plugins
 * @作者 phpok.com
 * @版本 4.8.000
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年09月13日 16时21分
**/
class api_aliyunvod extends phpok_plugin
{
	public $me;
	private $regoin_id = 'cn-shanghai';
	private $end_point = 'https://vod.cn-shanghai.aliyuncs.com';
	private $access_key = '';
	private $access_secret = '';
	private $stock_id = 0;
	private $key_m = 'abc147258369';
	private $key_s = 'abc963852741';
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
		if($this->me && $this->me['param']){
			if($this->me['param']['regoin_id']){
				$this->regoin_id = $this->me['param']['regoin_id'];
			}
			if($this->me['param']['access_id']){
				$this->access_key = $this->me['param']['access_id'];
			}
			if($this->me['param']['access_secret']){
				$this->access_secret = $this->me['param']['access_secret'];
			}
			if($this->me['param']['end_point']){
				$this->end_point = $this->me['param']['end_point'];
			}
			if($this->me['param']['video_stock']){
				$this->stock_id = $this->me['param']['video_stock'];
			}
		}
	}
	
	public function play_auth()
	{
		if(!$this->regoin_id || !$this->access_key || !$this->access_secret){
			$this->error('参数不完整，请配置');
		}
		$vid = $this->get('videoid');
		if(!$vid){
			$this->error('未指定视频ID');
		}
		$this->lib('aliyun')->regoin_id($this->regoin_id);
		$this->lib('aliyun')->access_key($this->access_key);
		$this->lib('aliyun')->access_secret($this->access_secret);
		$info = $this->lib('aliyun')->client();
		echo "<pre>".print_r($info,true)."</pre>";
		if(!$info){
			$this->error('配置错误');
		}
		if(is_array($info) && !$info['status']){
			$this->error($info['error']);
		}
		$data = $this->lib('aliyun')->play_auth($vid);
		if(!$data){
			$this->error('配置错误');
		}
		if(is_array($data) && !$data['status']){
			$this->error($data['error']);
		}
		$play_auth = $data['info']->PlayAuth;
		if(!$play_auth){
			$this->error('获取播放权限失败');
		}
		$this->success($play_auth);
	}

	public function url_reload()
	{
		$myurl = $this->get('myurl');
		$type = $this->get('type');
		$randtime = rand(15,60);
		$mytime = $this->time+$randtime;
		$urldata = parse_url($myurl);
		$str = $urldata['path'].'-'.$mytime.'-0-0-'.($type ? $this->key_m : $this->key_s);
		$link = $urldata['scheme'].'://'.$urldata['host'].$urldata['path'].'?auth_key='.$mytime.'-0-0-'.md5($str);
		$data = array('link'=>$link);
		$data['time'] = $randtime-4;
		$this->success($data);
	}
}