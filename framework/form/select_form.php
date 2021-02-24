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
		$rslist = opt_rslist($opt_list[0],$opt_list[1],$rs['ext_select']);
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
		if(!$rslist){
			return false;
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
		$ext = array();
		if($rs['ext']){
			$ext = is_string($rs['ext']) ? unserialize($rs['ext']) : $rs['ext'];
		}
		$info = $this->get($rs['identifier'],$rs['form']);
		if($ext['is_multiple'] && $info){
			return serialize($info);
		}
		return $info;
	}

	public function phpok_show($rs,$appid="admin")
	{
		$ext = array();
		if($rs['ext']){
			$ext = is_string($rs['ext']) ? unserialize($rs['ext']) : $rs['ext'];
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
			}
			$info = $this->opt_rs($rs['content'],$opt[0],$opt[1]);
			if($info){
				if(is_array($info['title'])){
					return implode(' / ',$info['title']);
				}
				return $info['title'];
			}
			return false;
		}
		if($ext['is_multiple']){
			$info = unserialize($rs['content']);
			if(!$info){
				$info = array();
			}
			$list = array();
			foreach($info as $key=>$value){
				$list[$value] = $this->opt_rs($value,$opt[0],$opt[1]);
			}
			return $list;
		}
		return $this->opt_rs($rs['content'],$opt[0],$opt[1]);
	}
	
	private function opt_rs($val,$type='default',$group_id='')
	{
		$rs = array('val'=>$val,'title'=>$val);
		if($type == 'opt'){
			$group_rs = $this->model('opt')->group_one($group_id);
			//检查是否是联动数据
			if($group_rs && $group_rs['link_symbol'] && strpos($val,$group_rs['link_symbol']) !== false){
				$list = explode($group_rs['link_symbol'],$val);
				$list2 = array();
				$parent_id = 0;
				foreach( $list as $key => $value ){
					if(!$value || !trim($value)){
						continue;
					}
					$value = trim($value);
					$condition = "val='".$value."' AND group_id='".$group_id."' AND parent_id='".$parent_id."'";
					$opt_data = $this->model('opt')->opt_one_condition($condition);
					if($opt_data){
						$list2[$key] = array('val'=>$value,'title'=>$opt_data['title']);
						$parent_id = $opt_data['id'];
					}
				}
				$tmp = array('title'=>array(),'val'=>array(),'type'=>$type);
				foreach($list2 as $key=>$value){
					if($value && is_array($value)){
						$tmp['title'][$key] = $value['title'];
						$tmp['val'][$key] = $value['val'];
					}
				}
				return $tmp;
			}
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
			if(!$val){
				return false;
			}
			$project = $this->model('project')->get_one($group_id,false);
			if(!$project || !$project['module']){
				$rs['title'] = '未知';
			}else{
				$module = $this->model('module')->get_one($project['module']);
				if($module['mtype']){
					$tmp = $this->model('list')->single_one($val,$project['module']);
				}else{
					$tmp = $this->model('list')->call_one($val);
				}
				if(!$tmp){
					$rs['title'] = '无';
				}else{
					$rs['title'] = $tmp['title'] ? $tmp['title'] : $tmp;
				}
			}
		}
		if($type == 'cate'){
			//获取分类信息
			if(strpos($val,',') !== false){
				$tmplist = $this->model('cate')->catelist_cid($val,false);
				if(!$tmplist){
					return false;
				}
				$tmp = array('title'=>array(),'val'=>array(),'type'=>$type);
				foreach($tmplist as $key=>$value){
					$tmp['title'][$key] = $value['title'];
					$tmp['val'][$key] = $value['id'];
				}
				return $tmp;
			}
			$tmp = $this->model('cate')->cate_info($val,false);
			if(!$tmp || !$tmp['status']){
				return false;
			}
			$rs['title'] = $tmp['title'];
		}
		if($type == 'user'){
			if($group_id == 'grouplist'){
				$tmp = $this->model('usergroup')->get_one($val);
				if($tmp){
					$rs['title'] = $tmp['title'];
				}
			}
		}
		if($type == 'gateway'){
			if($group_id == 'express'){
				$tmp = $this->model('express')->get_one($val);
				if($tmp){
					$rs['title'] = $tmp['title'];
				}
			}
		}
		$rs['type'] = $type;
		return $rs;
	}

}