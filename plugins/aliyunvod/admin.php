<?php
/**
 * 阿里云视频库插件<后台应用>
 * @package phpok\plugins
 * @作者 phpok.com
 * @版本 4.8.000
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年09月13日 16时21分
**/
class admin_aliyunvod extends phpok_plugin
{
	public $me;
	private $regoin_id = 'cn-shanghai';
	private $end_point = 'https://vod.cn-shanghai.aliyuncs.com';
	private $access_key = '';
	private $access_secret = '';
	private $stock_id = 0;
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
	
	public function html_list_edit_foot()
	{
		$pid = $this->tpl->val('pid');
		if($pid && $pid == $this->stock_id){
			$this->_show('admin_list_edit.html');
		}
	}

	public function html_form_quickadd_foot()
	{
		$pid = $this->tpl->val('pid');
		if($pid && $pid == $this->stock_id){
			$this->_show('admin_list_edit.html');
		}
	}

	public function ap_list_action_after()
	{
		$pid = $this->tpl->val('pid');
		if(!$pid || $pid != $this->stock_id){
			return false;
		}
		$rslist = $this->tpl->val('rslist');
		if(!$rslist){
			return false;
		}
		$remote = array();
		foreach($rslist as $key=>$value){
			if(!$value['longtime'] && $value['videoid']){
				$remote[] = $value['videoid'];
			}
		}
		if($remote && count($remote)>0){
			$this->lib('aliyun')->regoin_id($this->regoin_id);
			$this->lib('aliyun')->access_key($this->access_key);
			$this->lib('aliyun')->access_secret($this->access_secret);
			$info = $this->lib('aliyun')->client();
			if(!$info){
				$this->error('插件（ AliyunVod ）配置错误，请检查');
			}
			if(is_array($info) && !$info['status']){
				$this->error('插件（AliyunVod）错误：'.$info['error']);
			}
			$tmplist = array();
			foreach($remote as $key=>$value){
				$data = $this->lib('aliyun')->video_info($value);
				if($data && $data['status'] && $data['info']){
					$info = $data['info']->Video;
					$time = $info->Duration;
					$thumb = $info->CoverURL;
					$tmplist[$value] = array('time'=>$time,'thumb'=>$thumb);
				}
			}
			$remote = $tmplist;
		}
		foreach($rslist as $key=>$value){
			if($remote && $remote[$value['videoid']]){
				$value['longtime'] = $remote[$value['videoid']]['time'];
				if(!$value['thumb']){
					$value['thumb'] = $remote[$value['videoid']]['thumb'];
				}
				$this->model('list')->update_ext(array('longtime'=>$value['longtime'],'thumb'=>$value['thumb']),$value['module_id'],$value['id']);
			}
			if($value['longtime']){
				$value['longtime'] = $this->_time($value['longtime']);
				$rslist[$key] = $value;
			}
		}
		$this->assign('rslist',$rslist);
	}

	private function _time($seconds=0)
	{
		if($seconds<60){
			if($seconds<10){
				return '00:00:0'.round($seconds);
			}
			return '00:00:'.round($seconds);
		}
		if($seconds >= 60 && $seconds < 3600){
			//执行分
			$min = intval($seconds/60);
			$sec = $seconds%60;
			return '00:'.($min<10 ? '0'.$min : $min).':'.($sec<10 ? '0'.$sec : $sec);
		}
		$hour = intval($seconds/3600);
		$info = $hour < 10 ? '0'.$hour : $hour;
		$secs = $seconds/3600;
		if($secs < 60){
			if($secs<10){
				return $info.':00:0'.round($secs);
			}
			return $info.':00:'.round($secs);
		}
		if($secs >= 60 && $secs < 3600){
			//执行分
			$min = intval($secs/60);
			$sec = $secs%60;
			return $info.':'.($min<10 ? '0'.$min : $min).':'.($sec<10 ? '0'.round($sec) : round($sec));
		}
		return $seconds;
	}

	public function videolist()
	{
		if(!$this->regoin_id || !$this->access_key || !$this->access_secret){
			$this->error('参数不完整，请配置');
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$this->lib('aliyun')->regoin_id($this->regoin_id);
		$this->lib('aliyun')->access_key($this->access_key);
		$this->lib('aliyun')->access_secret($this->access_secret);
		$info = $this->lib('aliyun')->client();
		if(!$info){
			$this->error('配置错误');
		}
		if(is_array($info) && !$info['status']){
			$this->error($info['error']);
		}
		$data = $this->lib('aliyun')->video_list($pageid,$psize);
		if(!$data){
			$this->error('配置错误');
		}
		if(is_array($data) && !$data['status']){
			$this->error($data['error']);
		}
		$total = $data['info']->Total;
		if(!$total){
			$this->error('没有视频');
		}
		$pageurl = $this->url('plugin','exec','id=aliyunvod&exec=videolist');
		$string = 'home=首页&prev=上页&next=下页&last=尾页&half=2&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign('pagelist',$pagelist);
		$obj = $data['info']->VideoList;
		$rslist = array();
		foreach($obj->Video as $key=>$value){
			$tmp = array('title'=>$value->Title,'size'=>phpok_filesize($value->Size,false));
			$tmp['dateline'] = strtotime($value->CreateTime);
			$tmp['thumb'] = $value->CoverURL;
			$tmp['time'] = $this->_time($value->Duration);
			$tmp['videoid'] = $value->VideoId;
			$rslist[] = $tmp;
		}
		$this->assign('rslist',$rslist);
		$this->_view("admin_videolist.html");
	}

	public function vodauthinfo()
	{
		if(!$this->regoin_id || !$this->access_key || !$this->access_secret){
			$this->error('参数不完整，请配置');
		}
		$title = $this->get('title');
		if(!$title){
			$this->error('名称不能为空');
		}
		$filename = $this->get('filename');
		if(!$filename){
			$this->error('文件名不能为空');
		}
		$note = $this->get('info');
		$thumb = $this->get('thumb');
		if($thumb && substr($thumb,0,7) != 'http://' && substr($thumb,0,8) != 'https://'){
			$thumb = $this->config['url'].$thumb;
		}
		$tag = $this->get('tag');
		$this->lib('aliyun')->regoin_id($this->regoin_id);
		$this->lib('aliyun')->access_key($this->access_key);
		$this->lib('aliyun')->access_secret($this->access_secret);
		$info = $this->lib('aliyun')->client();
		if(!$info){
			$this->error('配置错误');
		}
		if(is_array($info) && !$info['status']){
			$this->error($info['error']);
		}
		$data = $this->lib('aliyun')->create_upload_video($filename,$title,$thumb,$note,$tag);
		if(!$data){
			$this->error('获取失败');
		}
		if(!$data['status']){
			$this->error($data['error']);
		}
		$this->success($data['info']);
	}


	public function videotime()
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
		if(!$info){
			$this->error('配置错误');
		}
		if(is_array($info) && !$info['status']){
			$this->error($info['error']);
		}
		$data = $this->lib('aliyun')->video_info($vid);
		if(!$data){
			$this->error('获取失败');
		}
		if(!$data['status']){
			$this->error($data['error']);
		}
		$info = $data['info']->Video;
		$time = $info->Duration;
		$thumb = $info->CoverURL;
		$this->success(array('time'=>$time,'thumb'=>$thumb));
	}
	
	
	
	/**
	 * 更新或添加保存完主题后触发动作，如果不使用，请删除这个方法
	 * @参数 $id 主题ID
	 * @参数 $project 项目信息，数组
	 * @返回 true 
	**/
	public function system_admin_title_success($id,$project)
	{
		//PHP代码;
	}
	
	
}