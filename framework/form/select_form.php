<?php
/**
 * 下拉菜单项
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2017年12月11日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

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
		$rslist = $this->model("project")->get_all_project($this->session->val('admin_site_id'));
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
		$catelist = $this->model("cate")->root_catelist($this->session->val('admin_site_id'));
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
		//网关列表
		$gateway_list = $this->model('gateway')->group_all();
		$this->assign('gateway_list',$gateway_list);

		$this->view($this->dir_phpok.'form/html/select_admin.html','abs-file');
	}

	public function cssjs()
	{
		$this->addjs('js/form.select.js');
	}

	public function phpok_format($rs,$appid="admin")
	{
		$this->cssjs();
		if(!$rs["option_list"]){
			$rs['option_list'] = 'default:0';
		}
		$opt_list = explode(":",$rs["option_list"]);
		$rslist = $this->model('form')->optlist($rs);
		if(!$rslist){
			return false;
		}
		$group_id = $opt_list[1];
		if($rs["is_multiple"] && $rs['content']){
			$content = array();
			if($rs["content"] && isset($rs["content"]['info']) && is_array($rs['content']['info'])){
				foreach($rs['content']['info'] as $key=>$value){
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
		$is_step = false;
		foreach($rslist as $key=>$value){
			if($value["parent_id"]){
				$is_step = true;
				break;
			}
		}
		if((!$rs['content'] || !is_array($rs['content'])) && $rs['is_multiple']){
			$rs['content'] = array();
		}
		if(!$rs['is_multiple'] && is_array($rs['content']) && $opt_list[0] == 'opt'){
			$opt_rs = $this->model('opt')->group_one($opt_list[1]);
			$symbol = $opt_rs['link_symbol'] ? $opt_rs['link_symbol'] : ',';
			$rs['content'] = implode($symbol,$rs['content']);
		}
		if($rs['is_cate_group'] && $opt_list[0] == 'title'){
			$grouplist = array();
			foreach($rslist as $key=>$value){
				if(!$value['cate_id'] && !$grouplist[0]){
					$grouplist[0] = P_Lang('未知分组');
				}
				if($value['cate_id'] && !$grouplist[$value['cate_id']]){
					$grouplist[$value['cate_id']] = $value['catename'];
				}
			}
			if($grouplist && count($grouplist)>0){
				$this->assign("_grouplist",$grouplist);
			}
		}
		if($rs['width']){
			$rs['form_style'] = $rs['form_style'] ? $rs['form_style'].';width:'.$rs['width'].'px' : 'width:'.$rs['width'].'px';
		}
		$this->assign("_is_step",$is_step);
		$this->assign('_group_id',$group_id);
		$this->assign('_group_type',$opt_list[0]);
		$this->assign("_rs",$rs);		
		$this->assign("_rslist",$rslist);
		
		$file = $appid == 'admin' ? $this->dir_phpok.'form/html/select_admin_tpl.html' : $this->dir_phpok.'form/html/select_www_tpl.html';
		return $this->fetch($file,'abs-file');
	}

	public function phpok_get($rs,$appid='admin'){
		$info = $this->get($rs['identifier'],$rs['form']);
		if($rs['is_multiple'] && $info){
			return serialize($info);
		}
		return $info;
	}

	public function phpok_show($rs,$appid="admin")
	{
		if(!$rs["option_list"]){
			$rs['option_list'] = 'default:0';
		}
		$opt = explode(":",$rs["option_list"]);
		if($appid == 'admin'){
			if($rs['is_multiple']){
				$info = unserialize($rs['content']);
				if(!$info){
					return false;
				}
				$list = array();
				foreach($info as $key=>$value){
					$tmp = $this->model('form')->optinfo($value,$rs);
					if($tmp){
						$list[] = $tmp['title'];
					}
				}
				return implode('<br />',$list);
			}
			$info = $this->model('form')->optinfo($rs['content'],$rs);
			if($info){
				if(is_array($info['title'])){
					return implode(' / ',$info['title']);
				}
				return $info['title'];
			}
			return false;
		}
		if($rs['is_multiple']){
			$info = unserialize($rs['content']);
			if(!$info){
				$info = array();
			}
			$list = array();
			foreach($info as $key=>$value){
				$list[$key] = $this->model('form')->optinfo($value,$rs);
			}
			return $list;
		}
		return $this->model('form')->optinfo($rs['content'],$rs);
	}
}