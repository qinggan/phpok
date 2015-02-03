<?php
/***********************************************************
	Filename: {phpok}/admin/project_control.php
	Note	: 项目任务处理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-26 11:50
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class project_control extends phpok_control
{
	var $popedom;
	function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("project");
		$this->assign("popedom",$this->popedom);
	}

	function index_f()
	{
		if(!$this->popedom["list"]) error("你没有查看权限");
		$site_id = $_SESSION["admin_site_id"];
		$rslist = $this->model('project')->get_all_project($site_id);
		$this->assign("rslist",$rslist);
		$this->view("project_index");
	}

	function set_f()
	{
		if(!$this->popedom["set"]) error("你没有权限");
		$site_id = $_SESSION["admin_site_id"];
		$id = $this->get("id","int");
		$idstring = "";
		if($id)
		{
			$this->assign("id",$id);
			$rs = $this->model('project')->get_one($id);
			$this->assign("rs",$rs);
			$ext_module = "project-".$id;
		}
		else
		{
			$rs = array();
			$ext_module = "add-project";
			$parent_id = $this->get("pid");
			$rs["parent_id"] = $parent_id;
			$this->assign("rs",$rs);
		}		
		$parent_list = $this->model('project')->get_all($site_id,0);
		$this->assign("parent_list",$parent_list);
		$this->assign("ext_module",$ext_module);
		$forbid = array("id","identifier");
		$forbid_list = $this->model('ext')->fields("project,id");
		$forbid = array_merge($forbid,$forbid_list);
		$forbid = array_unique($forbid);
		$this->assign("ext_idstring",implode(",",$forbid));
		// 加载模块列表
		$module_list = $this->model('module')->get_all();
		$this->assign("module_list",$module_list);
		// 加载分类
		$site_id = $_SESSION["admin_site_id"];
		$catelist = $this->model('cate')->root_catelist($site_id);
		$this->assign("catelist",$catelist);
		//加载货币
		$currency_list = $this->model('currency')->get_list();
		$this->assign('currency_list',$currency_list);
		//邮件模板列表
		$emailtpl = $this->model('email')->simple_list($site_id);
		$this->assign("emailtpl",$emailtpl);

		//获取可配置的权限
		$c_rs = $this->model('sysmenu')->get_one_condition("appfile='list' AND parent_id>0");
		$gid = $c_rs["id"];
		unset($c_rs);
		//取得该ID配置的权限字段
		$popedom_list = $this->model('popedom')->get_all("pid=0 AND gid='".$gid."'",false,false);
		$this->assign("popedom_list",$popedom_list);
		//取得已经配置的字段列表
		if($id)
		{
			$popedom_list2 = $this->model('popedom')->get_all("pid='".$id."' AND gid='".$gid."'",false,false);
			if($popedom_list2)
			{
				$m_plist = array();
				foreach($popedom_list2 AS $key=>$value)
				{
					$m_plist[] = $value["identifier"];
				}
				$this->assign("popedom_list2",$m_plist);
			}
		}
		//备注
		$note_content = form_edit('admin_note',$rs['admin_note'],"editor","btn_image=1&height=300");
		$this->assign('note_content',$note_content);
		//后台图标
		$icolist = $this->lib('file')->ls('images/ico/');
		if(($rs['ico'] && !in_array($rs['ico'],$icolist)) || !$rs['ico'])
		{
			$rs['ico'] = 'images/ico/default.png';
		}
		//$ico_input = form_edit('ico',$rs['ico'],'text','form_btn=image&width=500');
		$this->assign('icolist',$icolist);
		$grouplist = $this->model('usergroup')->get_all("status=1");
		if($grouplist)
		{
			foreach($grouplist as $key=>$value)
			{
				$tmp_popedom = array('read'=>false,'post'=>false,'reply'=>false,'post1'=>false,'reply1'=>false);
				$tmp = $value['popedom'] ? unserialize($value['popedom']) : false;
				if($tmp && $tmp[$_SESSION['admin_site_id']])
				{
					$tmp = $tmp[$_SESSION['admin_site_id']];
					$tmp = explode(",",$tmp);
					foreach($tmp_popedom as $k=>$v)
					{
						if($id && in_array($k.':'.$id,$tmp))
						{
							$tmp_popedom[$k] = true;
						}
						else
						{
							if(!$id && $k == 'read')
							{
								$tmp_popedom[$k] = true;
							}
						}
					}
				}
				$value['popedom'] = $tmp_popedom;
				$grouplist[$key] = $value;
			}
		}
		$this->assign('grouplist',$grouplist);
		$this->view("project_set");
	}

	function content_f()
	{
		if(!$this->popedom["set"]) error("你没有权限");
		$id = $this->get("id","int");
		if(!$id) error("未指定内容ID",$this->url("project"),"error");
		$this->assign("id",$id);
		$rs = $this->model('project')->get_one($id);
		$this->assign("rs",$rs);
		$ext_module = "project-".$id;
		$this->assign("ext_module",$ext_module);
		$forbid = array("id","identifier");
		$forbid_list = $this->model('ext')->fields("project,id");
		$forbid = array_merge($forbid,$forbid_list);
		$forbid = array_unique($forbid);
		$extlist = get_phpok_ext($ext_module,implode(",",$forbid));
		$this->assign('extlist',$extlist);
		$this->view("project_content");
	}

	//取得模块的扩展字段
	function mfields_f()
	{
		if(!$this->popedom['set'])
		{
			$this->json(P_Lang('无权限，请联系超级管理员开放权限'));
		}
		$id = $this->get('id','int');
		if(!$id)
		{
			$this->json(P_Lang('未指定ID'));
		}
		$rslist = $this->model('module')->fields_all($id);
		if(!$rslist) $this->json('',true);

		$list = array();
		foreach($rslist AS $key=>$value)
		{
			if($value["field_type"] != "longtext" && $value["field_type"] != "longblob" && $value["field_type"] != "text")
			{
				$list[] = array("id"=>$value["id"],"identifier"=>$value["identifier"],"title"=>$value["title"]);
			}
		}
		$this->json($list,true);
	}

	function save_f()
	{
		if(!$this->popedom["set"]) error("你没有权限");
		$site_id = $_SESSION["admin_site_id"];
		$error_url = $this->url("project","set");
		$id = $this->get("id","int");
		if($id)
		{
			$error_url .= "&id=".$id;
		}
		$title = $this->get("title");
		$identifier = $this->get("identifier");
		$module = $this->get("module","int");
		$cate = $this->get("cate","int");
		$tpl_index = $this->get("tpl_index");
		$tpl_list = $this->get("tpl_list");
		$tpl_content = $this->get("tpl_content");
		$taxis = $this->get("taxis","int");
		if(!$title)
		{
			error("名称不能为空",$error_url,"error");
		}
		$check_rs = $this->check_identifier($identifier,$id,$site_id);
		if($check_rs != "ok")
		{
			error($check_rs,$error_url,"error");
		}
		$array = array();
		if(!$id)
		{
			$array["site_id"] = $_SESSION["admin_site_id"];
		}
		$array["parent_id"] = $this->get("parent_id","int");
		$array["module"] = $module;
		$array["cate"] = $cate;
		$array["title"] = $title;
		$array["nick_title"] = $this->get("nick_title");
		$array["alias_title"] = $this->get("alias_title");
		$array["alias_note"] = $this->get("alias_note");
		$array["psize"] = $this->get("psize","int");
		$array["taxis"] = $taxis;
		$array["tpl_index"] = $tpl_index;
		$array["tpl_list"] = $tpl_list;
		$array["tpl_content"] = $tpl_content;
		$array["ico"] = $this->get("ico");
		$array["orderby"] = $this->get("orderby");
		$array["status"] = $this->get("lock","checkbox") ? 0 : 1;
		$array["hidden"] = $this->get("hidden","checkbox");
		$array["identifier"] = $identifier;
		$array["seo_title"] = $this->get("seo_title");
		$array["seo_keywords"] = $this->get("seo_keywords");
		$array["seo_desc"] = $this->get("seo_desc");
		$array["subtopics"] = $this->get("subtopics",'checkbox'); //子主题属性
		$array["is_search"] = $this->get("is_search",'checkbox');
		$array["is_tag"] = $this->get("is_tag",'checkbox');
		$array["is_biz"] = $this->get("is_biz",'checkbox');
		$array["currency_id"] = $this->get("currency_id",'int');
		//管理员备注功能，适用于添加内容时的友情提示
		$array["admin_note"] = $this->get("admin_note","html");
		//添加发布相关功能
		$array['post_status'] = $this->get('post_status','checkbox');
		$array['comment_status'] = $this->get('comment_status','checkbox');
		$array['post_tpl'] = $this->get('post_tpl');
		$array['etpl_admin'] = $this->get('etpl_admin');
		$array['etpl_user'] = $this->get('etpl_user');
		$array['etpl_comment_admin'] = $this->get('etpl_comment_admin');
		$array['etpl_comment_user'] = $this->get('etpl_comment_user');
		$array['is_attr'] = $this->get('is_attr','checkbox');
		$array['tag'] = $this->get('tag');
		$ok_url = $this->url("project");
		$c_rs = $this->model('sysmenu')->get_one_condition("appfile='list' AND parent_id>0");
		$gid = $c_rs["id"];
		unset($c_rs);
		if($id)
		{
			$action = $this->model('project')->save($array,$id);
			if(!$action)
			{
				error("数据更新失败，请联系管理员",$this->url("project","set","id=".$id),"error");
			}
			$rs = $this->model('project')->get_one($id);
			//权限配置
			$popedom = $this->get("_popedom","int");
			if($popedom && is_array($popedom))
			{
				$str = implode(",",$popedom);
				$tlist = array();
				$newlist = $this->model('popedom')->get_all("id IN(".$str.")",false,false);
				if($newlist)
				{
					foreach($newlist AS $key=>$value)
					{
						$tmp_condition = "pid='".$id."' AND gid='".$gid."' AND identifier='".$value["identifier"]."'";
						$tmp = $this->model('popedom')->get_one_condition($tmp_condition);
						if(!$tmp)
						{
							$tmp_value = $value;
							unset($tmp_value["id"]);
							$tmp_value["pid"] = $id;
							$this->model('popedom')->save($tmp_value);
						}
						$tlist[] = $value["identifier"];
					}
					//现在计算全部的
					$alist = $this->model('popedom')->get_all("gid='".$gid."' AND pid='".$id."'",false,false);
					if($alist)
					{
						foreach($alist AS $key=>$value)
						{
							if(!in_array($value["identifier"],$tlist))
							{
								$this->model('popedom')->delete($value["id"]);
							}
						}
					}
				}
			}
			//配置前端权限
			$this->_save_user_group($id);
			$this->_save_tag($id);
			error("编辑成功",$ok_url,"ok");
		}
		else
		{
			$id = $this->model('project')->save($array);
			if(!$id)
			{
				error("添加失败，请联系管理员",$this->url("project","set"),"error");
			}
			//更新权限
			$popedom = $this->get("_popedom","int");
			if($popedom && is_array($popedom))
			{
				$str = implode(",",$popedom);
				$newlist = $this->model('popedom')->get_all("id IN(".$str.")",false,false);
				if($newlist)
				{
					foreach($newlist AS $key=>$value)
					{
						$tmp_condition = "pid='".$id."' AND gid='".$gid."' AND identifier='".$value["identifier"]."'";
						$tmp = $this->model('popedom')->get_one_condition($tmp_condition);
						if(!$tmp)
						{
							$tmp_value = $value;
							unset($tmp_value["id"]);
							$tmp_value["pid"] = $id;
							$this->model('popedom')->save($tmp_value);
						}
					}
				}
			}
			$this->_save_user_group($id);
			$this->_save_tag($id);
			error("添加成功",$ok_url,"ok");
		}
	}

	private function _save_tag($id)
	{
		$rs = $this->model('project')->get_one($id,false);
		if($rs['tag'])
		{
			$this->model('tag')->update_tag($rs['tag'],'p'.$id,$_SESSION['admin_site_id']);
		}
		else
		{
			$this->model('tag')->stat_delete('p'.$id,"title_id");
		}
		return true;
	}

	private function _save_user_group($id)
	{
		$grouplist = $this->model('usergroup')->get_all("status=1");
		if(!$grouplist)
		{
			return false;
		}
		$tmp_popedom = array('read','post','reply','post1','reply1');
		foreach($grouplist as $key=>$value)
		{
			$tmp = false;
			$plist = $value['popedom'] ? unserialize($value['popedom']) : false;
			if($plist && $plist[$_SESSION['admin_site_id']])
			{
				$tmp = $plist[$_SESSION['admin_site_id']];
				$tmp = explode(",",$tmp);
			}
			foreach($tmp_popedom as $k=>$v)
			{
				$checked = $this->get("p_".$v."_".$value['id'],'checkbox');
				if($checked)
				{
					$tmp[] = $v.":".$id;
				}
				else
				{
					foreach($tmp as $kk=>$vv)
					{
						if($vv == $v.":".$id)
						{
							unset($tmp[$kk]);
						}
					}
				}
			}
			if($tmp)
			{
				$tmp = array_unique($tmp);
				$tmp = implode(",",$tmp);
				$plist[$_SESSION['admin_site_id']] = $tmp;
			}
			else
			{
				$plist[$_SESSION['admin_site_id']] = array();
			}
			$this->model('usergroup')->save(array('popedom'=>serialize($plist)),$value['id']);
		}
	}

	function content_save_f()
	{
		if(!$this->popedom["set"]) error("你没有权限");
		$id = $this->get("id","int");
		if(!$id) error("未指定内容ID",$this->url("project"),"error");
		$title = $this->get("title");
		if(!$title)
		{
			error("名称不能为空",$this->url("project","content","id=".$id),"error");
		}
		$array = array("title"=>$title);
		$this->model('project')->save($array,$id);
		ext_save("project-".$id);
		$this->model('temp')->clean("project-".$id,$_SESSION["admin_id"]);
		error("项目内容更新成功",$this->url("project"),"ok");
	}

	function check_identifier($sign,$id=0,$site_id=0)
	{
		if(!$sign)
		{
			return "标识串不能为空";
		}
		$sign = strtolower($sign);
		//字符串是否符合条件
		if(!preg_match("/[a-z][a-z0-9\_\-\.]+/",$sign))
		{
			return "标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头！";
		}
		if(!$site_id) $site_id = $_SESSION["admin_site_id"];
		$rs = $this->model('id')->check_id($sign,$site_id,$id);
		if($rs) return '标识符已被使用';
		return 'ok';
	}

	// 验证字串是否符合要求
	function identifier_f()
	{
		$id = $this->get("id");
		$sign = $this->get("sign");
		$check_rs = $this->check_identifier($sign,$id);
		if($check_rs != "ok") $this->json($check_rs);
		$this->json('验证通过',true);
	}

	//删除项目操作
	function delete_f()
	{
		if(!$this->popedom['set'])
		{
			$this->json(P_Lang('无权限，请联系超级管理员开放权限'));
		}
		$id = $this->get('id','int');
		if(!$id)
		{
			$this->json(P_Lang('未指定ID'));
		}
		//判断是否有子项目
		$list = $this->model('project')->get_son($id);
		if($list)
		{
			$this->json("已存在子项目，请先进入删除子项目");
		}
		$rs = $this->model('project')->get_one($id,false);
		if(!$rs) $this->json('项目信息不存在');
		$this->model('project')->delete_project($id);
		//删除关键词记录
		$this->model('tag')->stat_delete('p'.$id,"title_id");
		$this->json("删除成功",true);		
	}

	# 设置页面状态
	function status_f()
	{
		if(!$this->popedom['set'])
		{
			$this->json(P_Lang('无权限，请联系超级管理员开放权限'));
		}
		$id = $this->get('id','int');
		if(!$id)
		{
			$this->json(P_Lang('未指定ID'));
		}
		$status = $this->get("status","int");
		$this->model('project')->status($id,$status);
		$this->json("设置成功",true);
	}

	function sort_f()
	{
		$sort = $this->get('sort');
		if(!$sort || !is_array($sort))
		{
			$this->json("更新排序失败");
		}
		foreach($sort AS $key=>$value)
		{
			$key = intval($key);
			$value = intval($value);
			$this->model('project')->update_taxis($key,$value);
		}
		$this->json("更新排序成功",true);
	}

	//取得根分类
	function rootcate_f()
	{
		$catelist = $this->model('cate')->root_catelist($_SESSION['admin_site_id']);
		$this->json($catelist,true);
	}
}
?>