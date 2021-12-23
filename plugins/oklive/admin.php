<?php
/**
 * 直播插件<后台应用>
 * @作者 phpok.com
 * @版本 6.0
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年06月28日 11时05分
**/
class admin_oklive extends phpok_plugin
{
	public $me;
	private $pid = 0;
	private $pids = array();
	private $push_domain = '';
	private $pull_domain = '';
	private $push_key = '';
	private $pull_key = '';
	private $oss_domain = '';
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
		$this->pid = $this->me["pid"];
		$this->pids = $this->me["pids"];
		if($this->me && $this->me["param"]){
			if($this->me['param']['push']){
				$this->push_domain = $this->me['param']['push'];
				if(substr($this->push_domain,-1) != '/'){
					$this->push_domain .= '/';
				}
			}
			if($this->me['param']['pull']){
				$this->pull_domain = $this->me['param']['pull'];
				if(substr($this->pull_domain,-1) != '/'){
					$this->pull_domain .= '/';
				}
			}
			if($this->me['param']['pushkey']){
				$this->push_key = $this->me['param']['pushkey'];
			}
			if($this->me['param']['pullkey']){
				$this->pull_key = $this->me['param']['pullkey'];
			}
		}
	}

	public function html_list_action_body()
	{
		$rs = $this->tpl->val('rs');
		if($this->pid == $rs['id']){
			$this->_show('admin-list-action-body.html');
		}
	}

	public function html_list_edit_body()
	{
		$pid = $this->tpl->val('pid');
		if($pid == $this->pid){
			$this->_show('admin-list-edit-body.html');
		}
	}

	public function live_ctrl()
	{
		$tid = $this->get('tid','int');
		if(!$tid){
			$this->error('未指定ID');
		}
		$rs = $this->model('list')->get_one($tid,false);
		if(!$rs){
			$this->error('主题不存在');
		}
		$this->assign('rs',$rs);
		$project = $this->model('project')->get_one($rs['project_id']);
		if(!$project){
			$this->error('项目不存在');
		}
		if($rs['vtype'] == 'live'){
			$time = $this->time;
			$this->assign('obs_server',$this->push_domain.'live/');
			$rtmp = $project['identifier'].'-'.$rs['id'].'-'.$time;
			if($this->push_key){
				$tmp = url_auth($this->push_domain.'live/'.$rtmp,$this->push_key,3600*12);
				$rtmp .= strstr($tmp,'?');
			}
			$this->assign('rtmp',$this->push_domain.$rs['vtype'].'/'.$rtmp);
			$this->assign('obs_key',$rtmp);
			$this->assign('mytime',$time);
		}
		$this->_view("admin-live-ctrl.html");
	}

	public function live_start()
	{
		$tid = $this->get('tid','int');
		if(!$tid){
			$this->error(P_Lang('未指定ID'));
		}
		$mytime = $this->get('mytime');
		if(!$mytime){
			$this->error(P_Lang('未指定时间'));
		}
		$sql = "SELECT * FROM ".$this->db->prefix."plugins_rtmp WHERE tid='".$tid."'";
		$chk = $this->db->get_one($sql);
		if($chk){
			$this->error('直播已经开启！不能重复开启');
		}
		$rs = $this->model('list')->get_one($tid,false);
		if(!$rs){
			$this->error('主题不存在');
		}
		$project = $this->model('project')->get_one($rs['project_id']);
		if(!$project){
			$this->error('项目不存在');
		}
		$data = array('livetime'=>$mytime);
		$this->db->update($data,"list_".$rs['module_id'],array('id'=>$rs['id']));
		$time = $mytime;
		$this->assign('obs_server',$this->push_domain.$rs['vtype'].'/');
		$code = $project['identifier'].'-'.$rs['id'].'-'.$time;
		$rtmp = $this->push_domain.$rs['vtype'].'/'.$code;
		if($this->push_key){
			$rtmp = url_auth($rtmp,$this->push_key,3600*12);
		}
		//生成直播流
		$player_url = $this->pull_domain.$rs['vtype'].'/'.$code.".m3u8";
		if($this->pull_key){
			$player_url = url_auth($player_url,$this->pull_key,3600*12);
		}
		$player_flv = $this->pull_domain.$rs['vtype'].'/'.$code.".flv";
		if($this->pull_key){
			$player_flv = url_auth($player_flv,$this->pull_key,3600*12);
		}
		$data = array('tid'=>$rs['id']);
		$data['push'] = $rtmp;
		$data['pull'] = $player_url;
		$data['pull_flv'] = $player_flv;
		$data['dateline'] = $time;
		$insert_id = $this->db->insert($data,'plugins_rtmp');
		if(!$insert_id){
			$this->error('创建直播失败');
		}
		$this->success();
	}

	public function live_stop()
	{
		$tid = $this->get('tid','int');
		if(!$tid){
			$this->error(P_Lang('未指定ID'));
		}
		$sql = "SELECT * FROM ".$this->db->prefix."plugins_rtmp WHERE tid='".$tid."'";
		$chk = $this->db->get_one($sql);
		if(!$chk){
			$this->error('直播已经结束了');
		}
		//
		$sql = "DELETE FROM ".$this->db->prefix."plugins_rtmp WHERE tid='".$tid."'";
		$this->db->query($sql);
		$this->success();
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