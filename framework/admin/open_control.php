<?php
/**
 * 虚弹窗口管理器
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年01月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class open_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$keytype_list = array(
			'id'=>"ID",
			'title'=>P_Lang('名称'),
			'name'=>P_Lang('文件名'),
			'ext'=>P_Lang('附件扩展名'),
			'start_date'=>P_Lang('开始时间'),
			'stop_date'=>P_Lang('结束时间')
		);
		$this->assign('keytype_list',$keytype_list);
	}

	/**
	 * 附件资源选择器
	**/
	public function upload_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定表单ID'));
		}
		$this->assign('id',$id);
		$multiple = $this->get('multiple','int');
		$this->assign('multiple',$multiple);
		$pageurl = $this->url('open','upload','id='.$id.'&multiple='.$multiple);
		$this->assign('formurl',$pageurl);
		$catelist = $this->model('res')->cate_all();
		if($catelist){
			foreach($catelist as $key=>$value){
				$types = explode(",",$value['filetypes']);
				$tmp = array();
				foreach($types as $k=>$v){
					$tmp[] = "*.".$v;
				}
				$value['typeinfos'] = implode(" , ",$tmp);
				$catelist[$key] = $value;
			}
		}
		$this->assign("catelist",$catelist);
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = 60;
		$offset = ($pageid - 1) * $psize;
		$condition = "1=1";
		$selected = $this->get('selected');
		if($selected){
			$pageurl .= '&selected='.rawurlencode($selected);
			$olist = explode(",",$selected);
			foreach($olist as $key=>$value){
				if(!$value || !intval($value)){
					unset($olist[$key]);
				}
			}
			$condition .= " AND id NOT IN(".implode(",",$olist).")";
		}
		$keywords = $this->get("keywords");
		if($keywords){
			$keywords = trim($keywords);
		}
		if($keywords){
			$keywords = preg_replace("/(\x20{2,})/"," ",$keywords);
		}
		if($keywords){
			$kwlist = explode(" ",$keywords);
			$tmplist = array();
			foreach($kwlist as $key=>$value){
				if(is_numeric($value)){
					$tmplist[] = "id='".$value."'";
					$tmplist[] = "attr LIKE '%".$value."%'";
				}
				$tmplist[] = "title LIKE '%".$value."%'";
				$tmplist[] = "name LIKE '%".$value."%'";
				if(!is_numeric($value) && strlen($value)<5){
					$tmplist[] = "ext LIKE '%".$value."%'";
				}
				$tmplist[] = "note LIKE '%".$value."%'";
			}
			$condition .= " AND (".implode(" OR ",$tmplist).")";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$cate_id = $this->get("cate_id","int");
		if($cate_id){
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$daylist = array();
		$daylist[] = array('value'=>1,"title"=>P_Lang('一天内'));
		$daylist[] = array('value'=>7,"title"=>P_Lang('一周内'));
		$daylist[] = array('value'=>15,"title"=>P_Lang('半个月内'));
		$daylist[] = array('value'=>31,"title"=>P_Lang('一个月内'));
		$daylist[] = array('value'=>183,"title"=>P_Lang('半年内'));
		$daylist[] = array('value'=>366,"title"=>P_Lang('一年内'));
		$this->assign('daylist',$daylist);
		$day = $this->get('day','int');
		if($day){
			$stime = strtotime(date("Y-m-d",$this->time)) - $day*24*3600;
			$condition .= " AND addtime>=".$stime;
			$pageurl .= "&day=".$$day;
			$this->assign("day",$day);
		}
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$total = $this->model('res')->get_count($condition);
		if($total>$psize){
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		}
		$this->assign("rslist",$rslist);
		$this->assign("total",$total);
		$this->assign("pagelist",$pagelist);
		$this->assign("pageurl",$pageurl);
		$this->assign('pageid',$pageid);
		$this->assign('psize',$psize);
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
		$btns = form_edit('upload','','upload','cate_id='.$cate_id.'&manage_forbid=1&auto_forbid=0&is_multiple=1&is_refresh=1');
		$this->assign('upload_buttons',$btns);
		$this->view('open_upload');
	}


	/**
	 * 附件选择器
	**/
	public function input_f()
	{
		$id = $this->get("id");
		if(!$id){
			$id = "content";
		}
		$this->assign('id',$id);
		$catelist = $this->model('res')->cate_all();
		if($catelist){
			foreach($catelist as $key=>$value){
				$types = explode(",",$value['filetypes']);
				$tmp = array();
				foreach($types as $k=>$v){
					$tmp[] = "*.".$v;
				}
				$value['typeinfos'] = implode(" , ",$tmp);
				$catelist[$key] = $value;
			}
		}
		$this->assign("catelist",$catelist);
		$pageurl = $this->url('open','input','id='.$id);
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$this->assign('formurl',$pageurl);
		$psize = $this->config['psize'];
		$offset = ($pageid - 1) * $psize;
		$condition = "1=1";
		$keywords = $this->get("keywords");
		if($keywords){
			$keywords = trim($keywords);
		}
		if($keywords){
			$keywords = preg_replace("/(\x20{2,})/"," ",$keywords);
		}
		if($keywords){
			$kwlist = explode(" ",$keywords);
			$tmplist = array();
			foreach($kwlist as $key=>$value){
				if(is_numeric($value)){
					$tmplist[] = "id='".$value."'";
					$tmplist[] = "attr LIKE '%".$value."%'";
				}
				$tmplist[] = "title LIKE '%".$value."%'";
				$tmplist[] = "name LIKE '%".$value."%'";
				if(!is_numeric($value) && strlen($value)<5){
					$tmplist[] = "ext LIKE '%".$value."%'";
				}
				$tmplist[] = "note LIKE '%".$value."%'";
			}
			$condition .= " AND (".implode(" OR ",$tmplist).")";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$cate_id = $this->get("cate_id","int");
		if($cate_id){
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('res')->get_count($condition);
		$this->assign("total",$total);
		if($total>$psize){
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("pageurl",$pageurl);
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
		$btns = form_edit('upload','','upload','cate_id='.$cate_id.'&manage_forbid=1&auto_forbid=0&is_refresh=1&is_multiple=1');
		$this->assign('upload_buttons',$btns);
		$this->view('open_input');
	}

	function get_list($pageurl,$ext="")
	{
		$formurl = $pageurl;
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 28;
		$offset = ($pageid - 1) * $psize;
		# 关键字
		$condition = "1=1";
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (title LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%' OR id LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$cate_id = $this->get("cate_id","int");
		if($cate_id)
		{
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$start_date = $this->get("start_date");
		if($start_date)
		{
			$condition .= " AND addtime>=".strtotime($start_date)." ";
			$pageurl .= "&start_date=".strtolower($start_date);
			$this->assign("start_date",$start_date);
		}
		$stop_date = $this->get("stop_date",'html');
		if($stop_date)
		{
			$condition .= " AND addtime<=".strtotime($stop_date)." ";
			$pageurl .= "&stop_date=".strtolower($stop_date);
			$this->assign("stop_date",$stop_date);
		}
		if($ext)
		{
			$extlist = explode(",",$ext);
			$ext_string = implode("','",$extlist);
			$condition .= " AND ext IN('".$ext_string."') ";
		}
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('res')->get_count($condition);
		$this->assign("total",$total);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign("pagelist",$pagelist);
	}

	/**
	 * 弹出按钮选择主题框
	**/
	public function title_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error('未指定ID');
		}
		$rs = $this->model('fields')->one($id);
		if(!$rs){
			$this->error(P_Lang('字段不存在'));
		}
		if(!$rs['form_btn']){
			$this->error(P_Lang('无扩展按钮'));
		}
		$tmp = explode(":",$rs['form_btn']);
		if($tmp[0] != 'title' || !$tmp[1]){
			$this->error(P_Lang('未指定项目'));
		}
		$pid = intval($tmp[1]);
		$formurl = $url = $this->url('open','title','id='.$id);
		$project = $this->model('project')->get_one($pid);
		if(!$project){
			$this->error('项目不存在');
		}
		if(!$project['module']){
			$this->error('项目未绑定模块');
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error('模块信息不存在');
		}
		$flist = $this->model('fields')->flist($module['id']);
		$list = array();
		if($flist){
			foreach($flist as $key=>$value){
				$list[$value['identifier']] = $value['title'];
			}
		}
		if(!$module['mtype']){
			$list['title'] = $project['alias_title'] ? $project['alias_title'] : P_Lang('主题');
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1) * $psize;
		$keywords = $this->get('keywords');
		if($keywords){
			$url .= "&keywords=".rawurlencode($keywords);
			$this->assign('keywords',$keywords);
		}
		if($module['mtype']){
			$condition = "project_id='".$project['id']."'";
			if($keywords){
				$tmp_c = array();
				foreach($list as $key=>$value){
					$tmp_c[] = $key." LIKE '%".$keywords."%'";
				}
				$condition .= " AND (".implode(" OR ",$tmp_c).")";
			}
			$total = $this->model('list')->single_count($module['id'],$condition);
			if($total>0){
				$tmp = array($field);
				$tmp2 = explode(",",$showid);
				$tmp = array_merge($tmp,$tmp2);
				$tmp = array_unique($tmp);
				$rslist = $this->model('list')->single_list($module['id'],$condition,$offset,$psize,$project['orderby'],'id,'.implode(",",$tmp));
				if($rslist){
					$this->assign('rslist',$rslist);
				}
			}
		}else{
			$condition = "l.project_id='".$project['id']."'";
			if($keywords){
				$tmp_c = array();
				foreach($list as $key=>$value){
					if($key == 'title'){
						$tmp_c[] = 'l.'.$key." LIKE '%".$keywords."%'";
					}else{
						$tmp_c[] = 'ext.'.$key." LIKE '%".$keywords."%'";
					}
					
				}
				$condition .= " AND (".implode(" OR ",$tmp_c).")";
			}
			$total = $this->model('list')->get_total($module['id'],$condition);
			if($total>0){
				$rslist = $this->model('list')->get_list($module['id'],$condition,$offset,$psize,$project['orderby']);
				if($rslist){
					$this->assign('rslist',$rslist);
				}
			}
		}
		$this->assign("total",$total);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($url,$total,$pageid,$psize,$string);
		$this->assign("pagelist",$pagelist);
		$this->assign("pageurl",$url);
		$this->assign("formurl",$formurl);
		$this->assign('field',$rs['ext_field']);
		$this->assign('pid',$pid);
		$this->assign('id',$id);
		$showlist = $rs['ext_layout'];
		$showlist = array_unique($showlist);
		$tmplist = array();
		foreach($showlist as $key=>$value){
			$tmplist[$value] = $list[$value];
		}
		$this->assign('showlist',$tmplist);
		$this->view('open_title2');
	}

	public function content_f()
	{
		$this->config('is_ajax',true);
		$fid = $this->get('fid','int');
		$id = $this->get('id','int');
		if(!$fid || !$id){
			$this->error('参数不完整');
		}
		$rs = $this->model('fields')->one($fid);
		if(!$rs){
			$this->error(P_Lang('字段不存在'));
		}
		if(!$rs['form_btn']){
			$this->error(P_Lang('无扩展按钮'));
		}
		$tmp = explode(":",$rs['form_btn']);
		if($tmp[0] != 'title' || !$tmp[1]){
			$this->error(P_Lang('未指定项目'));
		}
		$pid = intval($tmp[1]);
		$project = $this->model('project')->get_one($pid);
		if(!$project){
			$this->error('项目不存在');
		}
		if(!$project['module']){
			$this->error('项目未绑定模块');
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error('模块信息不存在');
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error('模块信息不存在');
		}
		if($module['mtype']){
			$info = $this->model('list')->single_one($id,$module['id']);
		}else{
			$info = $this->model('list')->get_one($id,false);
		}
		$olist = $this->model('fields')->flist($module['id'],'identifier');
		$nlist = $this->model('fields')->flist($rs['ftype'],'identifier');
		$field = $rs['ext_field'];
		if(!$field){
			$field = $rs['identifier'];
		}
		$data = array();
		$list = explode(",",$field);
		foreach($list as $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			$tmp = explode(":",$value);
			if(!$tmp[1]){
				$tmp[1] = $tmp[0];
			}
			if($tmp[0] && $info[$tmp[0]] && $tmp[0] == 'title'){
				$data[$tmp[1]] = array('type'=>'text','value'=>$info['title']);
				continue;
			}
			$old = $olist[$tmp[0]];
			$new = $nlist[$tmp[1]];
			//判断新的是否有进阶
			$tmplist = $this->model('form')->optlist($new);
			$is_step = false;
			if($tmplist){
				foreach($tmplist as $k=>$v){
					if($v["parent_id"]){
						$is_step = true;
						break;
					}
				}
			}
			if($is_step){
				$opt = explode(":",$new["option_list"]);
				$data[$tmp[1]] = array('type'=>'select_more','value'=>$info[$tmp[0]],'gid'=>$opt[1],'gtype'=>$opt[0]);
			}else{
				$data[$tmp[1]] = array('type'=>$new['form_type'],'value'=>$info[$tmp[0]]);
			}
		}
		$this->success($data);
	}

	/**
	 * 网址列表，这里读的是项目的网址列表
	**/
	public function url_f()
	{
		$id = $this->get("id");
		if(!$id){
			$id = "content";
		}
		$this->assign("id",$id);
		$pid = $this->get("pid");
		if($pid){
			$p_rs = $this->model('project')->get_one($pid);
			$type = $this->get("type");
			if(!$p_rs){
				$this->error(P_Lang('项目不存在'));
			}
			if($type == "cate" && $p_rs["cate"]){
				$catelist = $this->model('cate')->get_all($p_rs["site_id"],1,$p_rs["cate"]);
				$this->assign("rslist",$catelist);
				$this->assign("p_rs",$p_rs);
				$this->view("open_url_cate");
			}else{
				$pageid = $this->get($this->config["pageid"],"int");
				$psize = $this->config["psize"];
				if(!$psize) $psize = 20;
				if(!$pageid) $pageid = 1;
				$offset = ($pageid - 1) * $psize;
				$pageurl = $this->url("open","url","pid=".$pid."&type=list&id=".$id);
				$condition = "l.site_id='".$p_rs["site_id"]."' AND l.project_id='".$pid."' AND l.parent_id='0' ";
				$keywords = $this->get("keywords");
				if($keywords){
					$condition .= " AND l.title LIKE '%".$keywords."%' ";
					$pageurl .= "&keywords=".rawurlencode($keywords);
					$this->assign("keywords",$keywords);
				}
				$rslist = $this->model('list')->get_list($p_rs["module"],$condition,$offset,$psize,$p_rs["orderby"]);
				if($rslist){
					$sub_idlist = array_keys($rslist);
					$sub_idstring = implode(",",$sub_idlist);
					$con_sub = "l.site_id='".$p_rs["site_id"]."' AND l.project_id='".$pid."' AND l.parent_id IN(".$sub_idstring.") ";
					$sublist = $this->model('list')->get_list($p_rs["module"],$con_sub,0,0,$p_rs["orderby"]);
					if($sublist){
						foreach($sublist AS $key=>$value){
							$rslist[$value["parent_id"]]["sonlist"][$value["id"]] = $value;
						}
					}
				}
				//读子主题
				$total = $this->model('list')->get_total($p_rs["module"],$condition);
				$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
				$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
				$this->assign("pagelist",$pagelist);
				$this->assign("p_rs",$p_rs);
				$this->assign("rslist",$rslist);
				$this->view("open_url_list");
			}
		}else{
			$condition = " p.status='1' ";
			$rslist = $this->model('project')->get_all_project($_SESSION["admin_site_id"],$condition);
			$this->assign("rslist",$rslist);
		}
		$this->assign("id",$id);
		$this->view("open_url");
	}

	/**
	 * 读取用户列表
	**/
	public function user_f()
	{
		$id = $this->get("id");
		if(!$id){
			$id = "user";
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config["psize"] ? $this->config['psize'] : 30;
		$keywords = $this->get("keywords");
		$multi = $this->get("multi","int");
		$page_url = $this->url("open","user","id=".$id);
		if($multi){
			$page_url .= "&multi=1";
			$this->assign("multi",$multi);
		}
		$grouplist = $this->model('usergroup')->get_all("is_guest !=1");
		$this->assign('grouplist',$grouplist);
		$condition = "1=1";
		if($keywords){
			$this->assign("keywords",$keywords);
			$condition .= " AND u.user LIKE '%".$keywords."%'";
			$page_url.="&keywords=".rawurlencode($keywords);
		}
		$group_id = $this->get('group_id','int');
		if($group_id){
			$this->assign("group_id",$group_id);
			$condition .= " AND u.group_id='".$group_id."'";
			$page_url.="&group_id=".rawurlencode($group_id);
		}
		$offset = ($pageid - 1) * $psize;
		$count = $this->model('user')->get_count($condition);
		if($count){
			$rslist = $this->model('user')->get_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=2';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($page_url,$count,$pageid,$psize,$string);
			$this->assign("total",$count);
			$this->assign("rslist",$rslist);
			$this->assign("id",$id);
			$this->assign("pagelist",$pagelist);
		}
		$this->view("open_user_list");
	}

	/**
	 * 用户选择
	**/
	public function user2_f()
	{
		$id = $this->get("id");
		if(!$id) $id = "user";
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 39;
		$keywords = $this->get("keywords");
		$page_url = $this->url("open","user2","id=".$id);
		$condition = "1=1";
		if($keywords){
			$this->assign("keywords",$keywords);
			$condition .= " AND u.user LIKE '%".$keywords."%'";
			$page_url.="&keywords=".rawurlencode($keywords);
		}
		$offset = ($pageid - 1) * $psize;
		$rslist = $this->model('user')->get_list($condition,$offset,$psize);
		$count = $this->model('user')->get_count($condition);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=2';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($page_url,$count,$pageid,$psize,$string);
		$this->assign("total",$count);
		$this->assign("rslist",$rslist);
		$this->assign("id",$id);
		$this->assign("pagelist",$pagelist);
		$this->view("open_user_list2");
	}

	/**
	 * 样式生成器
	 * @参数 id 要保存的样式ID
	 * @参数 vid 要应用到的效果ID
	**/
	public function style_f()
	{
		$id = $this->get('id');
		$vid = $this->get('vid');
		if(!$id){
			$id = 'style';
		}
		if(!$vid){
			$vid = 'title';
		}
		$this->assign('id',$id);
		$this->assign('vid',$vid);
		$html = form_edit('color','','text','form_btn=color&form_style=width:100px&ext_include_3=1');
		$this->assign('colorhtml',$html);
		$html = form_edit('bgcolor','','text','form_btn=color&form_style=width:100px&ext_include_3=1');
		$this->assign('bgcolorhtml',$html);
		$this->view('open_style');
	}
}