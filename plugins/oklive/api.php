<?php
/**
 * 直播插件<接口应用>
 * @作者 phpok.com
 * @版本 6.0
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年06月28日 11时05分
**/
class api_oklive extends phpok_plugin
{
	public $me;
	private $pid = 0;
	private $pids = array();
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

	/**
	 * 登记查看直播访问情况
	**/
	public function addview()
	{
		$id = $this->get('tid','int');
		if(!$id){
			$this->error('未指定主题');
		}
		$rs = $this->model('content')->get_one($id);
		if(!$rs){
			$this->error('数据异常');
		}
		if(!$rs['vtype'] || $rs['vtype'] != 'live'){
			$this->error('仅用于直播');
		}
		$session_id = $this->session->sessid();
		$sql = "SELECT * FROM ".$this->db->prefix."plugins_views WHERE session_id='".$session_id."' AND tid='".$id."'";
		if($this->session->val('user_id')){
			$sql .= " AND user_id='".$this->session->val('user_id')."'";
		}
		$sql .= " AND logintime>=".($this->time-3600*12);
		$chk = $this->db->get_one($sql);
		if($chk){
			$data = array('lasttime'=>$this->time);
			$this->db->update($data,"plugins_views",array('id'=>$chk['id']));
		}else{
			$data = array('user_id'=>$this->session->val('user_id'));
			$data['tid'] = $id;
			$data['session_id'] = $session_id;
			$data['vtype'] = $rs['vtype'];
			$data['logintime'] = $this->time;
			$data['lasttime'] = $this->time;
			$this->db->insert($data,'plugins_views');
		}
		$this->success();
	}
	
	public function vlive2vod()
	{
		$id = $this->get('tid','int');
		if(!$id){
			$this->error('未指定主题');
		}
		$rs = phpok("_arc","title_id=".$id);
		if(!$rs || !$rs['status']){
			$this->error('视频不存在');
		}
		if($rs['vtype'] != 'vlive'){
			$this->error('不支持，请检查');
		}
		$data = array("vtype"=>"vod");
		$data['ismove'] = 1;
		$this->db->update($data,"list_".$rs['module_id'],array('id'=>$rs['id']));
		$this->success();
	}

	/**
	 * 直接推流生成点播推送
	**/
	public function live2vod()
	{
		$info = file_get_contents("php://input");
		if(!$info){
			exit('end');
		}
		$info = $this->lib('json')->decode($info);
		if(!$info['stream']){
			exit('end');
		}
		if(!$info['uri'] || !$info['duration']){
			exit('end');
		}
		$tmp = explode("-",$info['stream']);
		$id = $tmp[1];
		$time = $tmp[2];
		$rs = $this->model('list')->get_one($id,false);
		if(!$rs){
			exit('end');
		}
		$data = array();
		if($rs['tovod']){
			$data['vtype'] = 'vod';
		}
		$data['video'] = $info['uri'];
		$this->db->update($data,"list_".$rs['module_id'],array('id'=>$rs['id']));
		//删除推流记录
		$sql = "DELETE FROM ".$this->db->prefix."plugins_rtmp WHERE tid='".$id."'";
		$this->db->query($sql);
		//检测是否有直播统计缓存
		exit('ok');
	}
}