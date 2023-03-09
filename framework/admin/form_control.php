<?php
/**
 * 自定义表单的字段异步处理
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 6.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2023年3月1日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class form_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	public function config_f()
	{
		$id = $this->get("id");
		if(!$id){
			exit(P_Lang('未指定ID'));
		}
		$eid = $this->get("eid");
		$etype = $this->get("etype");
		if(!$etype){
			$etype = "ext";
		}
		if($eid && $etype) {
			$this->_getinfo($eid,$etype);
		}
		$this->lib('form')->config($id);
	}

	public function fields_f()
	{
		$pid = $this->get('pid','int');
		if(!$pid){
			$this->error(P_Lang('未指定项目'));
		}
		$project = $this->model('project')->get_one($pid,false);
		if(!$project['module']){
			$this->error(P_Lang('未张定模块'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模块不存在'));
		}
		$list = array();
		if(!$module['mtype']){
			$list['title'] = $project['alias_title'] ? $project['alias_title'] : P_Lang('主题');
		}
		$fields = $this->model('fields')->flist($project['module']);
		if($fields){
			foreach($fields as $key=>$value){
				$list[$value['identifier']] = $value['title'];
			}
		}
		$this->success($list);
	}

	private function _getinfo($eid=0,$etype='ext')
	{
		if(!$eid){
			return false;
		}
		if($etype == "fields"){
			$rs = $this->model('fields')->default_one($eid);
		}elseif($etype == "module"){
			$rs = $this->model('module')->field_one($eid);
		}elseif($etype == "user"){
			$rs = $this->model('user')->field_one($eid);
		}else{
			$rs = $this->model('ext')->get_one($eid);
		}
		$this->assign('rs',$rs);
		$this->assign('etype',$etype);
		$this->assign('eid',$eid);
		return $rs;
	}

	/**
	 * 项目字段信息
	**/
	public function project_fields_f()
	{
		$this->config('is_ajax',true);
		$pid = $this->get('pid','int');
		if(!$pid){
			$this->error(P_Lang('未指定项目ID'));
		}
		$project = $this->model('project')->get_one($pid);
		if(!$project){
			$this->error(P_Lang('项目信息不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('项目未绑定模块'));
		}
		$eid = $this->get('eid','int');
		$etype = $this->get('etype');
		if(!$etype){
			$etype = 'ext';
		}
		$rs = array();
		if($eid && $etype){
			$rs = $this->_getinfo($eid,$etype);
		}
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
		if(!$flist){
			$this->success();
		}
		$form_show_editing = $rs['form_show_editing'] ? explode(",",$rs['form_show_editing']) : array();
		$data = array('show'=>$flist,'used'=>$flist);
		if($form_show_editing){
			foreach($data['show'] as $key=>$value){
				if(in_array($key,$form_show_editing)){
					$value['status'] = true;
					$data['show'][$key] = $value;
				}
			}
		}
		$form_field_used = $rs['form_field_used'] ? explode(",",$rs['form_field_used']) : array();
		if($form_field_used){
			foreach($data['used'] as $key=>$value){
				if(in_array($key,$form_show_editing)){
					$value['status'] = true;
					$data['used'][$key] = $value;
				}
			}
		}
		$this->success($data);
	}

	/**
	 * 项目主题快速添加及修改
	**/
	public function quickadd_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定字段ID'));
		}
		$field = $this->model('module')->field_one($id);
		if(!$field){
			$this->error(P_Lang('字段信息不存在'));
		}
		if(!$field['form_pid']){
			$this->error(P_Lang('扩展项目没有配置成功'));
		}
		$pid = $field['form_pid'];
		$identifier = $field['identifier'];
		if(!$identifier){
			$this->error(P_Lang('配置无效，未指定标识'));
		}
		$this->assign('id',$id);
		$this->assign('pid',$pid);
		$this->assign('identifier',$identifier);
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if($project['cate']){
			$catelist = $this->model('cate')->get_all($project["site_id"],1,$project["cate"]);
			$catelist = $this->model('cate')->cate_option_list($catelist);
			$this->assign("catelist",$catelist);
		}
		if(!$project['module']){
			$this->error(P_Lang('项目没有绑定模块'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模块信息不存在，请检查'));
		}
		$this->assign('p_rs',$project);
		$this->assign('m_rs',$module);
		$tid = $this->get('tid','int');
		if($tid){
			if($module['mtype']){
				$rs = $this->model('list')->single_one($tid,$project['module']);
			}else{
				$rs = $this->model('list')->get_one($tid,false);
			}
			$this->assign('rs',$rs);
		}
		$ext_list = $this->model('module')->fields_all($module['id']);
		$extlist = array();
		foreach(($ext_list ? $ext_list : array()) as $key=>$value){
			if($rs && $rs[$value["identifier"]]){
				$value["content"] = $rs[$value["identifier"]];
			}
			$extlist[] = $this->lib('form')->format($value);
		}
		$this->assign('extlist',$extlist);
		$this->view('form_quickadd');
	}

	/**
	 * 快速添加对应的保存操作
	**/
	public function quick_save_f()
	{
		$this->config('is_ajax',true);
		$pid = $this->get('pid','int');
		if(!$pid){
			$this->error(P_Lang('未指定项目'));
		}
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('项目没有绑定模块'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模块信息不存在，请检查'));
		}
		$extlist = $this->model('module')->fields_all($project["module"]);
 		if(!$extlist){
	 		$extlist = array();
 		}
		$id = $this->get('tid','int');
		//独立运行
		if($module['mtype']){
			if($id){
				$rs = $this->model('list')->single_one($id,$project['module']);
			}
			$array = array();
			$array["project_id"] = $project['id'];
			$array['site_id'] = $project['site_id'];
			foreach($extlist as $key=>$value){
				if($rs[$value['identifier']]){
					$value['content'] = $rs[$value['identifier']];
				}
				$array[$value['identifier']] = $this->lib('form')->get($value);
			}
			if($id){
				$array['id'] = $id;
				$state = $this->model('list')->single_save($array,$project["module"]);
				if(!$state){
					$this->error(P_Lang('更新数据失败，请检查'));
				}
			}else{
				$id = $this->model('list')->single_save($array,$project["module"]);
				if(!$id){
					$this->error(P_Lang('保存数据失败，请检查'));
				}
			}
			$this->success($id);
		}
		$title = $this->get('title');
		if(!$title){
			$tmptitle = $project['alias_title'] ? $project['alias_title'] : P_Lang('主题');
			$this->error(P_Lang('{title}不能为空',array('title'=>$tmptitle)));
		}
		$cate_id = 0;
		if($project['cate']){
			$cate_id = $this->get('cate_id','int');
			if(!$cate_id){
				$this->error(P_Lang('分类不能为空'));
			}
		}
		$array = array('title'=>$title);
		$array['cate_id'] = $cate_id;
		if(!$id){
			$array["dateline"] = $this->time;
			$array['project_id'] = $project['id'];
			$array["status"] = 1;
			$array["module_id"] = $project["module"];
			$array["site_id"] = $project["site_id"];
			$id = $this->model('list')->save($array);
			if(!$id){
				$this->error(P_Lang('保存信息失败，请联系管理员'));
			}
		}else{
			$status = $this->model('list')->save($array,$id);
			if(!$status){
				$this->error(P_Lang('更新信息失败'));
			}
			$rs = $this->model('list')->get_one($id);
		}
		//更新扩展表信息
 		$tmplist = array();
 		$tmplist["id"] = $id;
 		$tmplist["site_id"] = $project["site_id"];
 		$tmplist["project_id"] = $project['id'];
		foreach($extlist as $key=>$value){
			if($rs[$value['identifier']]){
				$value['content'] = $rs[$value['identifier']];
			}
			$tmplist[$value["identifier"]] = $this->lib('form')->get($value);
		}
		$this->model('list')->save_ext($tmplist,$project["module"]);
		$this->success($id);
	}

	/**
	 * 重加载扩展信息
	**/
	public function redata_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定字段ID'));
		}
		$field = $this->model('module')->field_one($id);
		if(!$field){
			$this->error(P_Lang('字段信息不存在'));
		}
		if(!$field['form_pid']){
			$this->error(P_Lang('扩展项目没有配置成功'));
		}
		$pid = $field['form_pid'];
		$identifier = $field['identifier'];
		if(!$identifier){
			$this->error(P_Lang('配置无效，未指定标识'));
		}
		$this->assign('_id',$id);
		$this->assign('_identifier',$identifier);
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('项目没有绑定模块'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模块信息不存在，请检查'));
		}
		$this->assign('p_rs',$project);
		$this->assign('m_rs',$module);
		$content = $this->get('content');
		$html = $this->lib('form')->cls('extitle')->content_format($field,$content,$project,$module);
		$this->success($html);
	}

	/**
	 * 读取主题列表
	**/
	public function quicklist_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定字段ID'));
		}
		$field = $this->model('module')->field_one($id);
		if(!$field){
			$this->error(P_Lang('字段信息不存在'));
		}
		if(!$field['form_pid']){
			$this->error(P_Lang('扩展项目没有配置成功'));
		}
		$pid = $field['form_pid'];
		$layoutids = $field['form_show_editing'] ? $field['form_show_editing'] : array();
		$identifier = $field['identifier'];
		if(!$identifier){
			$this->error(P_Lang('配置无效，未指定标识'));
		}
		$this->assign('id',$id);
		$this->assign('identifier',$identifier);
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('项目没有绑定模块'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模块信息不存在，请检查'));
		}
		$this->assign('p_rs',$project);
		$this->assign('m_rs',$module);
		$mlist = $this->model('module')->fields_all($module['id'],"identifier");
		$pageurl = $this->url('form','quicklist','id='.$id);
		$ext = $this->get('ext');
		if($ext && is_array($ext)){
			foreach($ext as $key=>$value){
				$pageurl .= "&ext[".$key."]=".rawurlencode($value);
			}
			$this->assign('ext',$ext);
		}
		$psize = $this->config["psize"] ? $this->config["psize"] : "30";
		if($project['psize'] && $project['psize'] > $psize){
			$psize = $project['psize'];
		}
		if(!$this->config["pageid"]){
			$this->config["pageid"] = "pageid";
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		if($module['mtype']){
			$keywords = $this->get('keywords');
			$condition = "project_id='".$project['id']."' AND site_id='".$project['site_id']."' ";
			if($keywords){
				$clist = array();
				foreach($mlist as $key=>$value){
					$clist[] = $value['identifier']." LIKE '%".$keywords."%'";
				}
				$condition .= " AND (".implode(" OR ",$clist).") ";
				$pageurl .= "&keywords=".rawurlencode($keywords);
				$this->assign('keywords',$keywords);
			}
			if($ext && is_array($ext)){
				foreach($ext as $key=>$value){
					if($value != ''){
						$condition .= " AND ".$key."='".$value."'";
					}
				}
			}
			$total = $this->model('list')->single_count($module['id'],$condition);
			if($total>0){
				$rslist = $this->model('list')->single_list($module['id'],$condition,$offset,$psize,$project['orderby']);
			}
			if($mlist){
				$layout = array();
				if($project['cate']){
					$layout['catename'] = P_Lang('分类');
				}
				foreach($mlist as $key=>$value){
					if($value['identifier'] && in_array($value['identifier'],$layoutids)){
						$layout[$value['identifier']] = $value['title'];
					}
				}
				$this->assign('layout',$layout);
			}
		}else{
			$this->model('list')->is_biz(false);
			$this->model('list')->multiple_cate(false);
			$this->model('list')->is_user(false);
			$keywords = $this->get('keywords');
			$condition = "l.project_id='".$project['id']."' AND l.site_id='".$project['site_id']."' ";
			if($keywords){
				$clist = array();
				$clist[] = "l.title LIKE '%".$keywords."%'";
				foreach($mlist as $key=>$value){
					$clist[] = "ext.".$value['identifier']." LIKE '%".$keywords."%'";
				}
				$condition .= " AND (".implode(" OR ",$clist).") ";
				$pageurl .= "&keywords=".rawurlencode($keywords);
				$this->assign('keywords',$keywords);
			}
			if($ext && is_array($ext)){
				foreach($ext as $key=>$value){
					if($value != ''){
						$condition .= " AND ext.".$key."='".$value."'";
					}
				}
			}
			$total = $this->model('list')->get_total($module['id'],$condition);
			if($total>0){
				$rslist = $this->model('list')->get_list($module['id'],$condition,$offset,$psize,$project['orderby']);
				if($rslist){
					foreach($rslist as $key=>$value){
						$value['dateline'] = date("Y-m-d H:i",$value['dateline']);
						$rslist[$key] = $value;
					}
				}
			}
			$tmptitle = $project['alias_title'] ? $project['alias_title'] : P_Lang('主题');
			$layout = array();
			if(in_array('title',$layoutids)){
				$layout['title'] = $tmptitle;
			}
			if($project['cate']){
				$layout['catename'] = P_Lang('分类');
			}
			foreach($mlist as $key=>$value){
				if($value['identifier'] && in_array($value['identifier'],$layoutids)){
					$layout[$value['identifier']] = $value['title'];
				}
			}
			$this->assign('layout',$layout);
		}
		if($total>0){
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=1';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
			$this->assign("rslist",$rslist);
		}
		$maxcount = $this->get('maxcount','int');
		if(!$maxcount){
			$maxcount = 9999;
		}
		$this->assign('maxcount',$maxcount);
		$this->view("form_quicklist");
	}

	/**
	 * 删除操作
	**/
	public function quickdelete_f()
	{
		$fid = $this->get('fid','int');
		$id = $this->get('id','int');
		if(!$id || !$fid){
			$this->error(P_Lang('参数不完整，请检查'));
		}
		$field = $this->model('module')->field_one($fid);
		if(!$field){
			$this->error(P_Lang('字段信息不存在'));
		}
		$delete = false;
		if($field['form_true_delete']){
			$delete = true;
		}
		if(!$delete){
			$this->success();
		}
		$pid = $field['form_pid'];
		if(!$pid){
			$this->error(P_Lang('字段没有配置好'));
		}
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('项目没有绑定模块'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模块信息不存在，请检查'));
		}
		if($module['mtype']){
			$rs = $this->model('list')->single_one($id,$project['module']);
			if(!$rs){
				$this->error(P_Lang('数据不存在'));
			}
			$this->model('list')->single_delete($id,$project['module']);
			$this->success();
		}
		$rs = $this->model('list')->get_one($id,false);
		if(!$rs){
			$this->error(P_Lang('数据不存在'));
		}
		$this->model('list')->delete($id,$project['module']);
		$this->success();
	}

	/**
	 * 预览
	**/
	public function preview_f()
	{
		$id = $this->get('id','int');
		$pid = $this->get('pid','int');
		if(!$id || !$pid){
			$this->error(P_Lang('参数不完整，请检查'));
		}
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('项目没有绑定模块'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模块信息不存在，请检查'));
		}
		$this->assign('p_rs',$project);
		$this->assign('m_rs',$module);
		if($module['mtype']){
			$rs = $this->model('list')->single_one($id,$project['module']);
		}else{
			$rs = $this->model('list')->get_one($id,false);
		}
		if(!$rs){
			$this->error(P_Lang('数据不存在'));
		}
		$this->assign('rs',$rs);
		$this->assign("id",$rs["id"]);
		$mlist = $this->model('module')->fields_all($project["module"]);
		$this->assign('mlist',$mlist);
		$this->view('form_quickview');
	}

	public function selectpage_f()
	{
		$mid = $this->get('mid','int');
		if(!$mid){
			$this->error('未指定数据来源');
		}
		$module = $this->model('module')->field_one($mid);
		if(!$module){
			$this->error('字段信息不存在');
		}
		$tmp = explode(":",$module['option_list']);
		if($tmp[0] != 'title'){
			$this->error('其他数据接口正在开发中');
		}
		$pid = intval($tmp[1]);
		$field_id = $module['field_value'] ? $module['field_value'] : 'id';
		$field_val = $module['field_show'] ? $module['field_show'] : 'id';
		if(!$pid){
			$this->error('未指定项目ID');
		}
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('项目没有绑定模块'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模块信息不存在，请检查'));
		}

		$pageid = $this->get('pageNumber','int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->get('pageSize','int');
		if(!$psize){
			$psize = 10;
		}
		$offset = ($pageid-1)*$psize;
		$keywords = $this->get('q_word');
		if($module['mtype']){
			$condition = "project_id='".$project['id']."' AND status=1 AND hidden=0";
			if($keywords){
				if(is_array($keywords)){
					$tlist = array();
					foreach($keywords as $key=>$value){
						$tmpvalue = str_replace(' ','%',$value);
						$tlist[] = "`".$field_val."` LIKE '%".$tmpvalue."%'";
					}
					$condition .= " AND (".implode(" OR ",$tlist).")";
				}else{
					$tmp_keywords = str_replace(' ','%',$keywords);
					$condition .= " AND `".$field_val."` LIKE '%".$keywords."%'";
				}
			}
			$total = $this->db->count("SELECT count(id) FROM ".$this->db->prefix.$module['id']." WHERE ".$condition);
			if(!$total){
				$this->error('暂无数据');
			}
			$fields = $field_id.','.$field_val;
			$orderby = $project['orderby'] ? $project['orderby'] : 'sort DESC,id DESC';
			$sql = "SELECT ".$fields." FROM ".$this->db->prefix.$module['id']." WHERE ".$condition." ORDER BY ".$orderby." LIMIT ".$offset.",".$psize;
			$rslist = $this->db->get_all($sql);
			$data = array('pageSize'=>$psize,'pageNumber'=>$pageid,'totalRow'=>$total,'totalPage'=>ceil($total/$psize));
			$data['list'] = $rslist;
			$this->success($data);
		}
		$fields = array();
		$field_id_type = 'l';
		$field_val_type = 'l';
		$tmplist = $this->model('fields')->flist($project['module'],'identifier');
		if($tmplist){
			if($tmplist[$field_id]){
				$fields[] = 'ext.'.$field_id;
				$field_id_type = 'ext';
			}else{
				$fields[] = 'l.'.$field_id;
				$field_id_type = 'l';
			}
			if($tmplist[$field_val]){
				$fields[] = 'ext.'.$field_val;
				$field_val_type = 'ext';
			}else{
				$fields[] = 'l.'.$field_val;
				$field_val_type = 'l';
			}
		}

		$condition = "l.project_id='".$project['id']."' AND l.status=1 AND l.hidden=0";
		if($keywords){
			if(is_array($keywords)){
				$tlist = array();
				foreach($keywords as $key=>$value){
					$tmpvalue = str_replace(' ','%',$value);
					$tlist[] = $field_val_type.".".$field_val." LIKE '%".$tmpvalue."%'";
				}
				$condition .= " AND (".implode(" OR ",$tlist).")";
			}else{
				$tmp_keywords = str_replace(' ','%',$keywords);
				$condition .= " AND ".$field_val_type.".".$field_val." LIKE '%".$keywords."%'";
			}
		}
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."list_".$module['id']." ext ON(l.id=ext.id) WHERE ".$condition;
		$total = $this->db->count($sql);
		if(!$total){
			$this->error('暂无数据');
		}
		$tmp = implode(",",$fields);
		$orderby = $project['orderby'] ? $project['orderby'] : 'l.sort DESC,l.id DESC';
		$sql = "SELECT ".$tmp." FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."list_".$module['id']." ext ON(l.id=ext.id) WHERE ".$condition." ORDER BY ".$orderby." LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql);
		$data = array('pageSize'=>$psize,'pageNumber'=>$pageid,'totalRow'=>$total,'totalPage'=>ceil($total/$psize));
		$data['list'] = $rslist;
		$this->success($data);
	}

	public function tplfile_f()
	{
		$fid = $this->get('fid','int');
		if(!$fid){
			$this->error('未绑定字段');
		}
		$rs = $this->model('fields')->one($fid);
		$this->assign('rs',$rs);
		$str = '';
		if($rs['tplfile'] && file_exists($this->dir_root.$rs['tplfile'])){
			$str = $this->lib('file')->cat($this->dir_root.$rs['tplfile']);
		}
		if($rs['codetpl']){
			$str = $rs['codetpl'];
		}
		if($str){
			$content = $this->fetch($str,'content');
			$this->assign('content',$content);
			$this->assign('content_tpl',$str);
		}
		$this->view("form_tplfile");
	}
}