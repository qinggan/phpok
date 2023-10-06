<?php
/*****************************************************************************************
	文件： {phpok}/form/checkbox_form.php
	备注： 复选框常用项
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月12日 21时47分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class checkbox_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$opt_list = $this->model('opt')->group_all();
		$this->assign("opt_list",$opt_list);
		$rslist = $this->model('project')->get_all_project($this->session()->val('admin_site_id'));
		if($rslist){
			$p_list = $m_list = array();
			foreach($rslist as $key=>$value){
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
		$catelist = $this->model('cate')->root_catelist($site_id);
		$this->assign("catelist",$catelist);
		//读取另一个站点
		$sitelist = $this->model('site')->get_all_site('id');
		if($sitelist && count($sitelist)>1){
			unset($sitelist[$this->session->val('admin_site_id')]);
			//读取其他站点的项目及模块
			foreach($sitelist as $key=>$value){
				$rslist = $this->model("project")->get_all_project($value['id']);
				if($rslist){
					$m_list = array();
					foreach($rslist as $k=>$v){
						if(!$v["parent_id"]){
							$p_list[] = $v;
						}
						if($v['module']){
							$m_list[] = $v;
						}
					}
					if($m_list && count($m_list)>0){
						$sitelist[$key]['mlist'] = $m_list;
					}
					if($p_list && count($p_list)>0){
						$sitelist[$key]['plist'] = $p_list;
					}
				}
				$catelist = $this->model("cate")->root_catelist($value['id']);
				if($catelist){
					$sitelist[$key]['clist'] = $catelist;
				}
			}
			$this->assign('ext_sitelist',$sitelist);
		}
		$this->view($this->dir_phpok.'form/html/checkbox_admin.html','abs-file');
	}

	public function phpok_format($rs,$appid="admin")
	{
		if(!$rs["option_list"]){
			$rs['option_list'] = 'default:0';
		}
		$rslist = $this->model('form')->optlist($rs);
		if(!$rslist){
			return false;
		}
		$opt_list = explode(":",$rs["option_list"]);
		if($rs['content'] && is_string($rs['content'])){
			$rs['content'] = explode(",",$rs['content']);
		}
		$this->assign('_rs',$rs);
		$this->assign('_rslist',$rslist);
		if($appid == 'admin'){
			$data = array();
			$data['html'] = $this->fetch($this->dir_phpok.'form/html/checkbox_admin_tpl.html','abs-file');
			$data['rslist'] = $rslist;
			return $data;
		}
		return $this->fetch($this->dir_phpok.'form/html/checkbox_www_tpl.html','abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		$info = $this->get($rs['identifier'],$rs['format']);
		if(!$info){
			return false;
		}
		return implode(",",$info);
	}

	public function phpok_show($rs,$appid="admin")
	{
		if(!$rs || !$rs['content']){
			return false;
		}
		$info = explode(",",$rs['content']);
		$list = array();
		
		if($appid == 'admin'){
			foreach($info as $key=>$value){
				$tmp = $this->model('form')->optinfo($value,$rs);
				if($tmp){
					$list[] = $tmp['title'];
				}
			}
			return implode('/',$list);
		}
		foreach($info as $key=>$value){
			$tmp = $this->model('form')->optinfo($value,$rs);
			if($tmp){
				$list[] = $tmp;
			}
		}
		return $list;
	}
}