<?php
/*****************************************************************************************
	文件： {phpok}/form/select_form.php
	备注： 下拉菜单项
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月06日 10时29分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class select_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$opt_list = $this->model('opt')->group_all();
		$this->assign("opt_list",$opt_list);
		$rslist = $this->model("project")->get_all_project($_SESSION["admin_site_id"]);
		if($rslist){
			$p_list = $m_list = array();
			foreach($rslist AS $key=>$value){
				if(!$value["parent_id"]){
					$p_list[] = $value;
				}
				if($value["module"]){
					$m_list[] = $value;
				}
			}
			if($p_list && count($p_list)>0){
				$this->assign("project_list",$p_list);
			}
			if($m_list && count($m_list)>0){
				$this->assign("title_list",$m_list);
			}
		}
		$catelist = $this->model("cate")->root_catelist($_SESSION["admin_site_id"]);
		$this->assign("catelist",$catelist);
		$this->view($this->dir_phpok.'form/html/select_admin.html','abs-file');
	}

	public function phpok_format($rs,$appid="admin")
	{
		if(!$rs["option_list"]) $rs['option_list'] = 'default:0';
		$opt_list = explode(":",$rs["option_list"]);
		$rslist = opt_rslist($opt_list[0],$opt_list[1],$rs['ext_select']);
		$opt_list = explode(":",$rs["option_list"]);
		$group_id = $opt_list[1];
		if($rs["is_multiple"] && $rs['content']){
			$content = array();
			if($rs["content"]['info'] && is_array($rs['content']['info'])){
				foreach($rs['content']['info'] AS $key=>$value){
					$content[] = $value['val'];
				}
				$rs["content"] = $content;
			}elseif(is_string($rs['content'])){
				$rs['content'] = unserialize($rs['content']);
			}
		}else{
			if(is_array($rs['content']) && $rs['content']){
				$rs['content'] = $rs['content']['val'];
			}
		}
		if(!$rslist){
			return false;
		}
		$is_step = false;
		foreach($rslist AS $key=>$value){
			if($value["parent_id"]){
				$is_step = true;
				break;
			}
		}
		if((!$rs['content'] || !is_array($rs['content'])) && $rs['is_multiple']){
			$rs['content'] = array();
		}
		$this->assign("_is_step",$is_step);
		$this->assign('_group_id',$group_id);
		$this->assign("_rs",$rs);
		$this->assign("_rslist",$rslist);
		$file = $appid == 'admin' ? $this->dir_phpok.'form/html/select_admin_tpl.html' : $this->dir_phpok.'form/html/select_www_tpl.html';
		if(!is_file($file)){
			$file = $this->dir_phpok.'form/html/select_admin_tpl.html';
		}
		return $this->fetch($file,'abs-file');
	}

	public function phpok_get($rs,$appid='admin'){
		$ext = array();
		if($rs['ext']){
			if(is_string($rs['ext'])){
				$ext = unserialize($rs['ext']);
			}else{
				$ext = $rs['ext'];
			}
		}
		$info = $this->get($rs['identifier'],$rs['form']);
		if($ext['is_multiple'] && $info){
			return serialize($info);
		}else{
			return $info;
		}
	}

	public function phpok_show($rs,$appid="admin")
	{
		$ext = array();
		if($rs['ext']){
			if(is_string($rs['ext'])){
				$ext = unserialize($rs['ext']);
			}else{
				$ext = $rs['ext'];
			}
		}
		if(!$ext["option_list"]){
			$ext['option_list'] = 'default:0';
		}
		$opt = explode(":",$ext["option_list"]);
		if($appid == 'admin'){
			if($ext['is_multiple']){
				$info = unserialize($rs['content']);
				if(!$info){
					return false;
				}
				$list = array();
				foreach($info as $key=>$value){
					$tmp = $this->opt_rs($value,$opt[0],$opt[1]);
					if($tmp){
						$list[] = $tmp['title'];
					}
				}
				return implode('<br />',$list);
			}else{
				$info = $this->opt_rs($rs['content'],$opt[0],$opt[1]);
				if($info){
					return $info['title'];
				}
				return false;
			}
		}else{
			if($ext['is_multiple']){
				$info = unserialize($rs['content']);
				$list = array();
				foreach($info as $key=>$value){
					$tmp = $value;
					if($opt[0] == 'project'){
						$tmp = $this->call->phpok('_project',array('pid'=>$value));
					}
					if($opt[0] == 'cate'){
						$tmp = $this->call->phpok('_cate',array('cateid'=>$value));
					}
					if($opt[0] == 'title'){
						$tmp = $this->call->phpok('_arc',array('title_id'=>$value));
					}
					if($opt[0] == 'opt'){
						$tmp = $this->model('opt')->opt_val($opt[1],$value);
					}
					$list[$value] = $tmp;
				}
				return $list;
			}else{
				$tmp = $rs['content'];
				if($opt[0] == 'project'){
					$tmp = $this->call->phpok('_project',array('pid'=>$rs['content']));
				}
				if($opt[0] == 'cate'){
					$tmp = $this->call->phpok('_cate',array('cateid'=>$rs['content']));
				}
				if($opt[0] == 'title'){
					$tmp = $this->call->phpok('_arc',array('title_id'=>$rs['content']));
				}
				if($opt[0] == 'opt'){
					$tmp = $this->model('opt')->opt_val($opt[1],$rs['content']);
				}
				return $tmp;
			}
		}
	}
	
	private function opt_rs($val,$type='default',$group_id='')
	{
		$rs = array('val'=>$val,'title'=>$val);
		if($type == 'opt'){
			$tmp = $this->model('opt')->opt_val($group_id,$val);
			if(!$tmp){
				return false;
			}
			$rs['title'] = $tmp['title'];
		}
		if($type == 'project'){
			$tmp = $this->model('project')->get_one($val,false);
			if(!$tmp || !$tmp['status']){
				return false;
			}
			$rs['title'] = $tmp['title'];
		}
		if($type == 'title'){
			$tmp = $this->model('list')->call_one($val);
			if(!$tmp || !$tmp['status']){
				return false;
			}
			$rs['title'] = $tmp['title'];
		}
		if($type == 'cate'){
			$tmp = $this->model('cate')->cate_info($val,false);
			if(!$tmp || !$tmp['status']){
				return false;
			}
			$rs['title'] = $tmp['title'];
		}
		$rs['type'] = $type;
		return $rs;
	}
	//
	private function opt_rslist($type='default',$group_id=0,$info='')
	{
		//当类型为默认时
		if($type == 'default' && $info){
			$list = explode("\n",$info);
			$rslist = "";
			$i=0;
			foreach($list AS $key=>$value){
				if($value && trim($value)){
					$value = trim($value);
					$rslist[$i]['val'] = $value;
					$rslist[$i]['title'] = $value;
					$i++;
				}
			}
			return $rslist;
		}

		//表单选项
		if($type == "opt"){
			return $this->model('opt')->opt_all("group_id=".$group_id);
		}
		
		//读子项目信息
		if($type == 'project'){
			$tmplist = $this->model('project')->project_sonlist($group_id);
			if(!$tmplist) return false;
			$rslist = '';
			foreach($tmplist AS $key=>$value){
				$tmp = array("val"=>$value['id'],"title"=>$value['title']);
				$rslist[] = $tmp;
			}
			return $rslist;
		}
		//读主题列表信息
		if($type == 'title')
		{
			$tmplist = $this->model("list")->title_list($group_id);
			if(!$tmplist) return false;
			$rslist = '';
			foreach($tmplist AS $key=>$value){
				$tmp = array("val"=>$value['id'],"title"=>$value['title']);
				$rslist[] = $tmp;
			}
			return $rslist;
		}
		//读子分类信息
		if($type == 'cate')
		{
			$tmplist = $this->model('cate')->catelist_sonlist($group_id,false,0);
			if(!$tmplist) return false;
			$rslist = '';
			foreach($tmplist AS $key=>$value){
				$tmp = array("val"=>$value['id'],"title"=>$value['title']);
				$rslist[] = $tmp;
			}
			return $rslist;
		}
		return false;
	}

}
?>