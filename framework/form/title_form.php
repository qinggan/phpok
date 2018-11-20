<?php
/*****************************************************************************************
	文件： {phpok}/form/title_form.php
	备注： 主题选择维护
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月13日 14时04分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class title_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$opt_list = $this->model("project")->get_all_project($site_id,"p.module>0 AND m.mtype!=1");
		$this->assign("opt_list",$opt_list);
		$this->view($this->dir_phpok.'form/html/title_admin.html','abs-file');
	}

	public function phpok_format($rs,$appid="admin")
	{
		if(!$rs["optlist_id"]){
			return P_Lang('未指定选项组');
		}
		$idlist = $rs["optlist_id"];
		if(!$idlist || !is_array($idlist)){
			return P_Lang('未指定项目，请配置');
		}
		$project_id = implode(",",$idlist);
		$project_list = $this->model("project")->title_list($project_id);
		if($project_list){
			$open_title = implode(" / ",$project_list) ." - 主题列表";
		}else{
			$open_title = "主题资源";
		}
		$condition = " l.project_id IN(".$project_id.") ";
		$total = $this->model("list")->get_all_total($condition);
		if($rs["is_multiple"]){
			$content = $rs["content"] ? explode(",",$rs["content"]) : array();
			$rs["content"] = $content;
		}
		$this->assign("_project_id_btn",$project_id);
		$this->assign("_rs",$rs);
		$this->assign("_open_title",$open_title);
		return $this->fetch($this->dir_phpok.'form/html/title_admin_tpl.html','abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		return $this->get($rs['identifier']);
	}

	public function phpok_show($rs,$appid="admin")
	{
		if(!$rs || !$rs['ext']){
			return false;
		}
		$ext = $rs['ext'];
		if(is_string($rs['ext'])){
			$ext = unserialize($rs['ext']);
		}
		if(!$rs['content']){
			return false;
		}
		$list = explode(',',$rs['content']);
		foreach($list as $key=>$value){
			if(!$value || !trim($value) || !intval($value)){
				unset($list[$key]);
			}
		}
		$rs['content'] = implode(",",$list);
		if(!$rs['content']){
			return false;
		}
		if($appid == 'admin'){
			$condition = "l.id IN(".$rs['content'].")";
			$rslist = $this->model('list')->get_all($condition);
			if(!$rslist){
				return false;
			}
			$list = array();
			foreach($rslist as $key=>$value){
				$list[] = $value['title'];
			}
			return implode('<br />',$list);
		}
		if($ext['is_multiple']){
			$condition = "l.id IN(".$rs['content'].") AND status=1";
			return $this->model('list')->get_all($condition,0,999);
		}
		return $this->model('list')->simple_one($rs['content']);
	}
}
?>