<?php
/**
 * 直播插件<前台应用>
 * @作者 phpok.com
 * @版本 6.0
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年06月28日 11时05分
**/
class www_oklive extends phpok_plugin
{
	public $me;
	private $pid = 0;
	private $pids = array();
	private $push_domain;
	private $pull_domain;
	private $push_key;
	private $pull_key;
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
			if($this->me['param']['oss_domain']){
				$this->oss_domain = $this->me['param']['oss_domain'];
				if(substr($this->oss_domain,-1) != '/'){
					$this->oss_domain .= '/';
				}
			}
		}
	}

	public function ap_content_index_after()
	{
		$rs = $this->tpl->val('rs');
		if($rs && $rs['project_id'] == $this->pid && $rs['vtype'] == 'live'){
			$sql = "SELECT * FROM ".$this->db->prefix."plugins_rtmp WHERE tid='".$rs['id']."'";
			$rtmp = $this->db->get_one($sql);
			if($rtmp){
				$this->assign('rtmp',$rtmp);
			}
		}
	}
	
	public function html_content_index_body()
	{
		$rs = $this->tpl->val('rs');
		if($rs['project_id'] == $this->pid && $rs['vtype'] == 'live'){
			$this->_show('www-content-index-body.html');
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