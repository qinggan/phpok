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
		$rslist = $this->model("project")->get_all_project($_SESSION["admin_site_id"]);
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
		$catelist = $this->model("cate")->root_catelist($_SESSION["admin_site_id"]);
		$this->assign("catelist",$catelist);
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
			if($rs["content"]['info'] && is_array($rs['content']['info'])){
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
		if($rs['is_multiple'] && $opt_list[0] == 'title'){
			$grouplist = array();
			foreach($rslist as $key=>$value){
				if(!$value['cate_id'] && !$grouplist[0]){
					$grouplist[0] = P_Lang('未知分组');
				}
				if($value['cate_id'] && !$grouplist[$value['cate_id']]){
					$grouplist[$value['cate_id']] = $value['catename'];
				}
			}
			if($grouplist && count($grouplist)>1){
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
			$tmp = $this->model('list')->call_one($val);
			if(!$tmp || !$tmp['status']){
				return false;
			}
			$rs['title'] = $tmp['title'];
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
		$rs['type'] = $type;
		return $rs;
	}

	//
	private function opt_rslist($type='default',$group_id=0,$info='')
	{
		//当类型为默认时
		if($type == 'default' && $info){
			$list = explode("\n",$info);
			$rslist = array();
			foreach($list as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				if(strpos($value,':') !== false){
					$tmp2 = explode(":",$value);
					if(!$tmp2[1]){
						$tmp2[1] = $tmp2[0];
					}
					$rslist[] = array('val'=>$tmp2[0],'title'=>$tmp2[1]);
				}else{
					$rslist[] = array('val'=>trim($value),'title'=>trim($value));
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
			foreach($tmplist as $key=>$value){
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
			foreach($tmplist as $key=>$value){
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
			foreach($tmplist as $key=>$value){
				$tmp = array("val"=>$value['id'],"title"=>$value['title']);
				$rslist[] = $tmp;
			}
			return $rslist;
		}
		return false;
	}
}