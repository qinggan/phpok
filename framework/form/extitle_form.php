<?php
/**
 * 扩展模型定义
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年01月20日
**/
class extitle_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$rslist = $this->model("project")->get_all_project($this->session->val('admin_site_id'),"p.module>0");
		$this->assign("opt_list",$rslist);
		$rs = $this->tpl->val('rs',$rs);
		if($rs && $rs['form_pid']){
			$form_show_editing = array();
			if($rs['form_show_editing']){
				$form_show_editing = is_string($rs['form_show_editing']) ? explode(",",$rs['form_show_editing']) : $rs['form_show_editing'];
			}
			$this->assign('form_show_editing',$form_show_editing);
			$form_field_used = array();
			if($rs['form_field_used']){
				$form_field_used = is_string($rs['form_field_used']) ? explode(",",$rs['form_field_used']) : $rs['form_field_used'];
			}
			$this->assign('form_field_used',$form_field_used);
			$project = $this->model('project')->get_one($rs['form_pid']);
			if($project && $project['module']){
				$module = $this->model('module')->get_one($project['module']);
				$flist = array();
				if(!$module['mtype']){
					$tmptitle = $project['alias_title'] ? $project['alias_title'] : P_Lang('主题');
					$flist['title'] = array('title'=>$tmptitle,'status'=>false);
				}
				$elist = $this->model('module')->fields_all($project['module']);
				if($elist){
					foreach($elist as $key=>$value){
						$flist[$value['identifier']] = array('title'=>$value['title'],'status'=>false);
					}
				}
				$this->assign('fields_list',$flist);
			}
		}
		$this->view($this->dir_phpok.'form/html/extitle_admin.html','abs-file');
	}

	public function phpok_format($rs,$appid="admin")
	{
		if(!$rs){
			return P_Lang('参数异常');
		}
		if(!$rs['form_pid']){
			return P_Lang('字段没有配置好，不能执行');
		}
		$project = $this->model('project')->get_one($rs['form_pid'],false);
		if(!$project){
			return P_Lang('项目不存在');
		}
		if(!$project['module']){
			return P_Lang('项目没有绑定模块');
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			return P_Lang('模块信息不存在，请检查');
		}
		if($rs['form_is_single']){
			$rs['form_maxcount'] = 1;
		}else{
			if(!$rs['form_maxcount']){
				$rs['form_maxcount'] = 9999;
			}
		}
		$this->assign("_rs",$rs);
		$this->assign("_id",$rs['id']);
		$this->assign("_identifier",$rs['identifier']);
		//格式化内容及项目信息
		$this->assign('p_rs',$project);
		$this->assign('m_rs',$module);
		$content = $rs['content'];
		$html = $this->content_format($rs,$content,$project,$module,true);
		$this->assign('_html',$html);
		return $this->fetch($this->dir_phpok.'form/html/extitle_admin_tpl.html','abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		return $this->get($rs['identifier']);
	}

	public function phpok_show($rs,$appid="admin")
	{
		if(!$rs){
			return P_Lang('参数异常');
		}
		if(!$rs['form_pid']){
			return P_Lang('字段没有配置好，不能执行');
		}
		$project = $this->model('project')->get_one($rs['form_pid'],false);
		if(!$project){
			return P_Lang('项目不存在');
		}
		if(!$project['module']){
			return P_Lang('项目没有绑定模块');
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			return P_Lang('模块信息不存在，请检查');
		}
		if(!$rs['content']){
			return false;
		}
		$flist = $this->model('module')->fields_all($project['module'],'identifier');
		if(is_array($rs['content'])){
			if(isset($rs['content']['id'])){
				$tmp = $rs['content']['id'];
				$rs['content'] = array($tmp);
			}else{
				$tmp = array();
				foreach($rs['content'] as $key=>$value){
					$tmp[] = $value['id'];
				}
				$rs['content'] = $tmp;
			}
			$rs['content'] = $rs['content']['id'];
		}
		$list = explode(",",$rs['content']);
		if($list){
			foreach($list as $key=>$value){
				if(!$value){
					unset($list[$key]);
				}
			}
		}
		if(!$list){
			return false;
		}
		$list = array_unique($list);
		if($module['mtype']){
			$condition = "id IN(".implode(",",$list).")";
			$orderby = "SUBSTRING_INDEX('".implode(",",$list)."',id,1)";
			$list = $this->model('list')->single_list($module['id'],$condition,0,0,$orderby);
		}else{
			$this->model('list')->is_biz(($project['is_biz'] ? true : false));
			$this->model('list')->multiple_cate(false);
			$this->model('list')->is_user(($project['is_userid'] ? true : false));
			$condition = "l.id IN(".implode(",",$list).")";
			$orderby = "SUBSTRING_INDEX('".implode(",",$list)."',l.id,1)";
			$list = $this->model('list')->get_list($module['id'],$condition,0,0,$orderby);
		}
		if(!$list){
			return false;
		}
		if($appid == 'admin'){
			$tmplist = array();
			foreach($list as $key=>$value){
				$tmplist[] = '<a href="javascript:$.admin_list.extitle_view('.$value['id'].','.$project['id'].');void(0);">'.$value['id'].'</a>';
			}
			return implode(" / ",$tmplist);
		}
		$fields = $rs['form_field_used'];
		if(!$fields){
			return $list;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$tmp = $value;
			foreach($fields as $k=>$v){
				$tmp[$v] = $flist[$v] ? $this->lib('form')->show($flist[$v],$value[$v]) : $value[$v];
			}
			$rslist[$key] = $tmp;
		}
		if($rs['form_is_single']){
			return current($rslist);
		}
		return $rslist;
	}

	/**
	 * 通过内容生成HTML内容
	 * @参数 $field 模块字段内容属性
	 * @参数 $content 值
	 * @参数 $project 项目信息
	 * @参数 $module 模块信息
	**/
	public function content_format($rs,$content,$project,$module,$empty_reback=false)
	{
		if($rs['form_is_single']){
			$rs['form_maxcount'] = 1;
		}else{
			if(!$rs['form_maxcount']){
				$rs['form_maxcount'] = 9999;
			}
		}
		$this->assign("_rs",$rs);
		$this->assign('_true_delete',$rs['form_true_delete']);
		$list = array();
		if($content){
			$list = explode(",",$content);
			foreach($list as $key=>$value){
				if(!$value){
					unset($list[$key]);
				}
			}
			$list = array_unique($list);
			if($module['mtype']){
				$condition = "id IN(".implode(",",$list).")";
				$orderby = "SUBSTRING_INDEX('".implode(",",$list)."',id,1)";
				$list = $this->model('list')->single_list($module['id'],$condition,0,0,$orderby);
			}else{
				$this->model('list')->is_biz(false);
				$this->model('list')->multiple_cate(false);
				$this->model('list')->is_user(false);
				$condition = "l.id IN(".implode(",",$list).")";
				$orderby = "SUBSTRING_INDEX('".implode(",",$list)."',l.id,1)";
				$list = $this->model('list')->get_list($module['id'],$condition,0,0,$orderby);
			}
		}
		if(!$list && $empty_reback){
			return false;
		}
		$flist = $this->model('module')->fields_all($project['module'],'identifier');
		$showids = $rs['form_show_editing'] ? $rs['form_show_editing'] : array();
		if(!$rs['form_show_editing']){
			$showids = array();
			if(!$module['mtype']){
				$showids[] = 'title';
			}
			foreach($flist as $key=>$value){
				$showids[] = $value['identifier'];
			}
		}
		$layout = array();
		if($project['cate']){
			$layout['catename'] = P_Lang('分类');
		}
		foreach($showids as $key=>$value){
			if($value == 'title'){
				$layout[$value] = $project['alias_title'] ? $project['alias_title'] : P_Lang('主题');
				continue;
			}
			if($flist[$value]){
				$layout[$value] = $flist[$value]['title'];
			}
		}
		$this->assign('layout',$layout);
		$rslist = array();
		if($list){
			foreach($list as $key=>$value){
				$tmp = array('id'=>$value['id'],'project_id'=>$value['project_id']);
				if($project['cate'] && $value['catename']){
					$tmp['catename'] = $value['catename'];
				}
				foreach($showids as $k=>$v){
					$tmp[$v] = $value[$v];
				}
				$rslist[$key] = $tmp;
			}
		}
		foreach($rslist as $key=>$value){
			foreach($layout as $k=>$v){
				if(is_array($value[$k])){
					$c_list = $value[$k]['_admin'];
					if($c_list['type'] == 'pic'){
						$tmp = '<img src="'.$c_list['info'].'" width="28px" height="28px" border="0" style="border:1px solid #dedede;padding:1px;" />';
					}else{
						if(is_array($c_list['info'])){
							$tmp = implode(' / ',$c_list['info']);
						}else{
							$tmp = $c_list['info'] ? $c_list['info'] : '-';
						}
					}
					$value[$k] = $tmp;
				}
			}
			$rslist[$key] = $value;
		}
		$this->assign('rslist',$rslist);
		return $this->fetch($this->dir_phpok.'form/html/extitle_admin_tpl_list.html','abs-file');
	}
}
