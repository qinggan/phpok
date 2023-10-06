<?php
/*****************************************************************************************
	文件： {phpok}/form/radio_form.php
	备注： 单选框处理操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月27日 20时18分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class radio_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$opt_list = $this->model('opt')->group_all();
		$this->assign('opt_list',$opt_list);
		$rslist = $this->model('project')->get_all_project($this->session->val('admin_site_id'));
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
		$catelist = $this->model('cate')->root_catelist($_SESSION['admin_site_id']);
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
		$html = $this->dir_phpok."form/html/radio_admin.html";
		$this->view($html,"abs-file",false);
	}

	public function phpok_format($rs,$appid='admin')
	{
		if(!$rs["option_list"]){
			$rs['option_list'] = 'default:0';
		}
		$rslist = $this->model('form')->optlist($rs);
		if(!$rslist){
			return false;
		}
		$opt_list = explode(":",$rs["option_list"]);
		if($rs["content"] && is_array($rs['content'])){
			$rs['content'] = $rs['content']['val'];
		}
		$this->assign('_rs',$rs);
		$this->assign('_rslist',$rslist);
		if($appid == 'admin'){
			$data = array();
			$data['html'] = $this->fetch($this->dir_phpok.'form/html/radio_admin_tpl.html','abs-file');
			$data['rslist'] = $rslist;
			return $data;
		}
		return $this->fetch($this->dir_phpok.'form/html/radio_www_tpl.html','abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		$info = $this->get($rs['identifier'],$rs['format']);
		if(!$info){
			return false;
		}
		if($rs['is_add'] && $info == '_'){
			$info = $this->get($rs['identifier'].'_extadd',$rs['format']);
		}
		return $info;
	}

	//输出内容
	public function phpok_show($rs,$appid='admin')
	{
		if(!$rs["option_list"]){
			$rs['option_list'] = 'default:0';
		}
		$opt = explode(":",$rs["option_list"]);
		if($appid == 'admin'){
			$info = $this->model('form')->optinfo($rs['content'],$rs);
			if($info && $info['title']){
				return $info['title'];
			}
			return false;
		}
		return $this->model('form')->optinfo($rs['content'],$rs);
	}
}