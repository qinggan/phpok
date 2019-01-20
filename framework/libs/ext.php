<?php
/***********************************************************
	Filename: {phpok}/libs/ext.php
	Note	: 扩展表内容读取及格式化
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年7月20日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ext_lib
{
	var $app;
	//连接数据库类
	var $db;
	function __construct()
	{
		//
	}

	function title_list($id)
	{
		$id = $this->safe_format($id);
		if(!$id) return false;
		$sql = "SELECT l.* FROM ".$GLOBALS['app']->db->prefix."list l ";
		//$sql.= "JOIN ".$GLOBALS['app']->db->prefix."id id ON(l.id=id.id AND id.type_id='content' AND l.site_id=id.site_id) ";
		$sql.= "WHERE l.id IN(".$id.") ORDER BY SUBSTRING_INDEX('".$id."',l.id,1)";
		$rslist = $GLOBALS['app']->db->get_all($sql,"id");
		if(!$rslist) return false;
		//获取模块列表
		$id_list = $mid_list = $cid_list = array();
		foreach($rslist AS $key=>$value)
		{
			$mid_list[$value["module_id"]] = $value["module_id"];
			$cid_list[$value["cate_id"]] = $value["cate_id"];
			$id_list[$value["module_id"]][$value["id"]] = $value["id"];
		}
		foreach($mid_list AS $key=>$value)
		{
			$m_rs = $this->module_fields($value);
			if($m_rs)
			{
				$idstring = implode(",",$id_list[$value]);
				$extlist = $this->title_ext($idstring,$value,true);
				if(!$extlist) $extlist = array();
				foreach($extlist AS $k=>$v)
				{
					unset($v["cate_id"],$v["project_id"],$v["site_id"]);
					foreach($m_rs AS $kk=>$vv)
					{
						$content = $v[$vv["identifier"]];
						if($content)
						{
							//if(strlen($content)>255) $content = phpok_cut($content,255,"……");
							$content = $this->content_format($vv,$content);
							$v[$vv["identifier"]] = $content;
						}
					}
					//合并主题
					$rslist[$v["id"]] = array_merge($rslist[$v["id"]],$v);
				}
				unset($extlist);
			}
		}
		//获取分类相关信息
		if(count($cid_list)>0)
		{
			$cate_string = implode(",",$cid_list);
			$catelist = $this->cate_list($cate_string);
			foreach($rslist AS $key=>$value)
			{
				if($value["cate_id"])
				{
					$value["cate_id"] = $catelist[$value["cate_id"]];
					$rslist[$key] = $value;
				}
			}
		}
		if($rslist && count($rslist)>0)
		{
			return $rslist;
		}
		return false;
	}

	//取得项目的单个主题信息
	function title_info($id)
	{
		if(!$id) return false;
		$sql = "SELECT l.*,id.phpok identifier FROM ".$GLOBALS['app']->db->prefix."list l ";
		$sql.= "JOIN ".$GLOBALS['app']->db->prefix."id id ON(l.id=id.id AND l.site_id=id.site_id AND id.type_id='content')";
		$sql.= " WHERE l.id='".$id."'";
		$rs = $GLOBALS['app']->db->get_one($sql);
		if(!$rs) return false;
		if($rs["module_id"])
		{
			$mid = $rs["module_id"];
			$flist = $this->module_fields($mid);
			$ext_rs = $this->title_ext($id,$mid,false);
			if($ext_rs && $flist)
			{
				foreach($flist AS $key=>$value)
				{
					$content = $ext_rs[$value["identifier"]];
					if($content)
					{
						$content = $this->content_format($value,$content);
						$rs[$value["identifier"]] = $content;
					}
				}
				unset($ext_rs,$flist);
			}
		}
		return $rs;
	}

	function title_ext($id,$mid,$islist=false)
	{
		if(!$id || !$mid) return false;
		$sql = "SELECT * FROM ".$GLOBALS['app']->db->prefix."list_".$mid." WHERE id IN(".$id.") ORDER BY SUBSTRING_INDEX('".$id."',id,1)";
		$rslist = $GLOBALS['app']->db->get_all($sql,"id");
		if(!$rslist) return false;
		if($islist)
		{
			return $rslist;
		}
		else
		{
			return $rslist[$id];
		}
	}

	//取得附件列表
	function res_list($id)
	{
		$id = $this->safe_format($id);
		if(!$id){
			return false;
		}
		return $GLOBALS['app']->model('res')->get_list_from_id($id,true);
	}

	//取得单个附件信息
	function res_info($id)
	{
		if(!$id){
			return false;
		}
		return $GLOBALS['app']->model('res')->get_one($id,true);
	}

	//取得会员列表
	function user_list($id)
	{
		$id = $this->safe_format($id);
		if(!$id) return false;
		$idlist = explode(",",$id);
		$rslist = array();
		foreach($idlist AS $key=>$value)
		{
			$rs = $this->user_info($value);
			if($rs)
			{
				$rslist[] = $rs;
			}
		}
		if($rslist && count($rslist)>0)
		{
			return $rslist;
		}
		return false;
	}

	function user_info($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$GLOBALS['app']->db->prefix."user WHERE id='".$id."'";
		$rs = $GLOBALS['app']->db->get_one($sql);
		if(!$rs) return false;
		$sql = "SELECT * FROM ".$GLOBALS['app']->db->prefix."user_ext WHERE id='".$id."'";
		$ext_rs = $GLOBALS['app']->db->get_one($sql);
		if($ext_rs)
		{
			unset($ext_rs["id"]);
			//读取扩展会员表信息
			$flist = $this->user_fields();
			if($flist)
			{
				foreach($flist AS $key=>$value)
				{
					$content = $this->content_format($value,$ext_rs[$value["identifier"]]);
					if($content)
					{
						$rs[$value["identifier"]] = $content;
					}
				}
				unset($flist,$ext_rs);
			}
		}
		return $rs;
	}

	//取得分类列表
	function cate_list($id)
	{
		$id = $this->safe_format($id);
		if(!$id) return false;
		//取得分类列表
		return $GLOBALS['app']->model('cate')->catelist_cid($id,true);
	}

	function cate_info($id)
	{
		if(!$id) return false;
		$list = $GLOBALS['app']->model('cate')->catelist_cid($id,true);
		if($list && $list[$id]) return $list[$id];
		return false;
	}

	//取得自定义选项列表
	function opt_list($id,$gid=0)
	{
		if(!$gid || !$id){
			return false;
		}
		$idlist = unserialize($id);
		if(!$idlist){
			$idlist = array();
		}
		$rslist = false;
		foreach($idlist AS $key=>$value)
		{
			$rs = $this->opt_info($value,$gid,0);
			if($rs) $rslist[] = $rs;
		}
		return $rslist;
	}
	
	function opt_info($id,$gid,$parent_id=0)
	{
		if(!$gid || !$id)
		{
			return false;
		}
		$parent_id = intval($parent_id);
		$opt_list = $GLOBALS["app"]->model("opt")->opt_all("group_id=".$gid);
		if(!$opt_list)
		{
			return false;
		}
		$rs = false;
		foreach($opt_list AS $key=>$value)
		{
			if($value["val"] == $id && $value["parent_id"] == $parent_id)
			{
				$rs = array("id"=>$value["id"],"title"=>$value["title"],"val"=>$value["val"]);
				break;
			}
		}
		return $rs;
	}
	
	function project_info($id)
	{
		$rs = $GLOBALS['app']->model('project')->get_one($id);
		if(!$rs || !$rs["status"]) return false;
		return $rs;
	}

	//格式化内容
	function content_format($rs,$content="")
	{
		if(!$rs) return false;
		if(!$content) $content = $rs['content'];
		$info = $GLOBALS['app']->lib('form')->show($rs,$content);
		if($info)
		{
			return $info;
		}
		if($rs["ext"])
		{
			if(is_string($rs["ext"])) $rs["ext"] = unserialize($rs["ext"]);
		}
		$ext = $rs["ext"] ? $rs["ext"] : "";
		$rs["ext"] = array();
		if($ext)
		{			
			foreach($ext AS $key=>$value)
			{
				$rs["ext"][$key] = $value;
			}
		}
		if($content) $rs["content"] = $content;
		$format_name = "_format_".$rs["form_type"];
		$list = get_class_methods($this);
		if(in_array($format_name,$list))
		{
			return $this->$format_name($rs);
		}
		return $content;
	}

	//格式化单选框
	function _format_radio($rs)
	{
		if(!$rs || !$rs["content"]) return false;
		$rs["ext"]["is_multiple"] = false;
		return $this->_global_format($rs);
	}

	function _format_editor($rs)
	{
		if(!$rs['content']) return false;
		if($GLOBALS['app']->app_id == "admin") return $rs['content'];
		preg_match_all("/\[download[:|：]*([0-9]*)\](.+)\[\/download\]/isU",$rs['content'],$list);
		if(!$list || count($list) < 1) return $rs['content'];
		$idlist = "";
		foreach($list[0] AS $key=>$value)
		{
			$tmpid = $list[1][$key] ? $list[1][$key] : intval($list[2][$key]);
			if($tmpid) $idlist[] = $tmpid;
		}
		if(!$idlist) return $rs['content'];
		$attrlist = $GLOBALS['app']->model('res')->reslist($idlist,false);
		foreach($list[0] AS $key=>$value)
		{
			$tmpid = $list[1][$key] ? $list[1][$key] : intval($list[2][$key]);
			$content = "";
			if($tmpid && $attrlist[$tmpid])
			{
				$tmp = ($list[2][$key] && $list[2][$key] != $tmpid) ? $list[2][$key] : $attrlist[$tmpid]["title"];
				$tmpfile = rawurlencode($attrlist[$tmpid]['filename']);
				$content = '<a href="'.$GLOBALS['app']->config['www_file'].'?dfile='.$tmpfile.'" title="'.$tmp.'" target="_blank">'.$tmp.'</a>';
			}
			$rs['content'] = str_replace($value,$content,$rs['content']);
		}
		return $rs['content'];
		
	}

	//格式化文本框信息
	function _format_text($rs)
	{
		if(!$rs || !$rs["content"]) return false;
		if($rs["ext"]["form_btn"] == "date" && $rs["format"] == "time")
		{
			$info = date("Y-m-d",$rs["content"]);
			$_admin = array("id"=>$rs["content"],"type"=>"txt","info"=>$info);
			return array("info"=>$rs["content"],"_admin"=>$_admin);
		}
		elseif($rs["ext"]["form_btn"] == "datetime" && $rs["format"] == "time")
		{
			$info = date("Y-m-d H:i",$rs["content"]);
			$_admin = array("id"=>$rs["content"],"type"=>"txt","info"=>$info);
			return array("info"=>$rs["content"],"_admin"=>$_admin);
		}
		else
		{
			return $rs["content"];
		}
	}

	//格式化密码框
	function _format_password($rs)
	{
		if(!$rs || !$rs["content"]) return false;
		if($rs["ext"]["password_type"] == "md5")
		{
			$_admin = array("id"=>$rs["content"],"type"=>"txt","info"=>"MD5加密");
			return array("info"=>$rs["content"],"_admin"=>$_admin);
		}
		elseif($rs["ext"]["password_type"] == "show")
		{
			$info = substr($rs["content"],0,1)."*****".substr($rs["content"],-1);
			$_admin = array("id"=>$rs["content"],"type"=>"txt","info"=>$info);
			return array("info"=>$rs["content"],"_admin"=>$_admin);
		}
		$_admin = array("id"=>$rs["content"],"type"=>"txt","info"=>$rs["content"]);
		return array("info"=>$rs["content"],"_admin"=>$_admin);
	}
	
	//格式化复选框
	function _format_checkbox($rs)
	{
		if(!$rs || !$rs["content"]) return false;
		$rs["ext"]["is_multiple"] = true;
		return $this->_global_format($rs);
	}
	
	//格式化下拉菜单选项信息
	function _format_select($rs)
	{
		if(!$rs || !$rs["content"]) return false;
		return $this->_global_format($rs);
		
	}

	function _global_format($rs)
	{
		if(!$rs['ext']['option_list']) return $rs["content"];
		$tmp = explode(":",$rs["ext"]["option_list"]);
		if($tmp[0] == "opt")
		{
			return $this->_format_opt($rs);
		}
		elseif($tmp[0] == "project")
		{
			return $this->_format_project($rs);
		}
		elseif($tmp[0] == "cate")
		{
			return $this->_format_cate($rs);
		}
		elseif($tmp[0] == "title")
		{
			return $this->_format_title($rs);
		}
	}

	function _format_opt($rs)
	{
		if(!$rs || !$rs["content"]) return false;
		$tmp = explode(":",$rs["ext"]["option_list"]);
		if(!$tmp[1]) return $rs["content"];
		//多项选择，不存在联动说法
		if($rs["ext"]["is_multiple"])
		{
			$list = $this->opt_list($rs["content"],$tmp[1]);
			if(!$list) return false;
			$_admin = array("id"=>$rs["content"],"type"=>"txt");
			foreach($list AS $key=>$value)
			{
				$_admin["info"][] = $value["title"];
			}
			return array("info"=>$list,"_admin"=>$_admin);
		}
		$gid = $tmp[1];
		unset($tmp);
		$tmp = explode("|",$rs["content"]);
		if(count($tmp)>1)
		{
			//存在联动信息
			$list = array();
			foreach($tmp AS $key=>$value)
			{
				if($key<1)
				{
					$tmp_rs = $this->opt_info($value,$gid,0);
					if($tmp_rs)
					{
						$list[$key] = $tmp_rs;
					}
					
				}
				else
				{
					$parent_rs = $list[($key-1)];
					if($parent_rs)
					{
						$tmp_rs = $this->opt_info($value,$gid,$parent_rs["id"]);
						if($tmp_rs)
						{
							$list[$key] = $tmp_rs;
						}
					}
				}
			}
			unset($tmp,$tmp_rs);
			$_admin = array("id"=>$rs["content"],"type"=>"txt");
			foreach($list AS $key=>$value)
			{
				$_admin["info"][] = $value["title"];
			}
			return array("info"=>$list,"_admin"=>$_admin);
		}
		$list = $this->opt_info($rs["content"],$gid,0);
		$_admin = array("id"=>$rs["content"],"type"=>"txt","info"=>$list["title"]);
		$list["_admin"] = $_admin;
		return $list;
	}

	//格式化项目信息
	function _format_project($rs)
	{
		if(!$rs || !$rs["content"]) return false;
		if($rs["ext"]["is_multiple"])
		{
			$list = $this->project_list($rs["content"]);
			if(!$list) return false;
			$_admin = array("id"=>$rs["content"],"type"=>"txt");
			foreach($list AS $key=>$value)
			{
				$_admin["info"][] = $value["title"];
			}
			return array("info"=>$list,"_admin"=>$_admin);
		}
		$list = $this->project_info($rs["content"]);
		if(!$list) return false;
		$_admin = array("id"=>$rs["content"],"type"=>"txt","info"=>$list["title"]);
		$list["_admin"] = $_admin;
		return $list;
	}

	//格式化分类信息
	function _format_cate($rs)
	{
		if(!$rs || !$rs["content"]) return false;
		if($rs["ext"]["is_multiple"])
		{
			$list = $this->cate_list($rs["content"]);
			if(!$list) return false;
			$_admin = array("id"=>$rs["content"],"type"=>"txt");
			foreach($list AS $key=>$value)
			{
				$_admin["info"][] = $value["title"];
			}
			return array("info"=>$list,"_admin"=>$_admin);
		}
		$list = $this->cate_info($rs["content"]);
		if(!$list) return false;
		$_admin = array("id"=>$rs["content"],"type"=>"txt","info"=>$list["title"]);
		$list["_admin"] = $_admin;
		return $list;
	}
	
	//格式化会员信息
	function _format_user($rs)
	{
		if(!$rs || !$rs["content"]) return false;
		if($rs["ext"]["is_multiple"])
		{
			$list = $this->user_list($rs["content"]);
			if(!$list) return false;
			$_admin = array("id"=>$rs["content"],"type"=>"txt");
			foreach($list AS $key=>$value)
			{
				$_admin["info"][] = $value["user"];
			}
			return array("info"=>$list,"_admin"=>$_admin);
		}
		$list = $this->user_info($rs["content"]);
		if(!$list) return false;
		$_admin = array("id"=>$rs["content"],"type"=>"txt","info"=>$list["user"]);
		$list["_admin"] = $_admin;
		return $list;
	}

	//格式化附件
	function _format_upload($rs)
	{
		if(!$rs || !$rs["content"]) return false;
		if($rs["ext"]["is_multiple"])
		{
			$list = $this->res_list($rs["content"]);
			if(!$list) return false;
			$_admin = array("id"=>$rs["content"],"type"=>"pic");
			$tmp = current($list);
			$_admin["info"] = $tmp["ico"];
			return array("info"=>$list,"_admin"=>$_admin);
		}
		$list = $this->res_info($rs["content"]);
		if(!$list) return false;
		$_admin = array("id"=>$rs["content"],"type"=>"pic","info"=>$list["ico"]);
		$list["_admin"] = $_admin;
		return $list;
	}

	//针对单个主题的格式化
	function _format_title($rs)
	{
		if(!$rs || !$rs["content"]) return false;
		if($rs["ext"]["is_multiple"])
		{
			$list = $this->title_list($rs["content"]);
			if(!$list) return false;
			$_admin = array("id"=>$rs["content"],"type"=>"txt");
			foreach($list AS $key=>$value)
			{
				$_admin["info"][] = $value["title"];
			}
			return array("info"=>$list,"_admin"=>$_admin);
		}
		$list = $GLOBALS['app']->model('list')->call_one($rs['content']);
		if(!$list) return false;
		$_admin = array("id"=>$rs["content"],"type"=>"txt","info"=>$list["title"]);
		$list["_admin"] = $_admin;
		return $list;
	}

	//格式化网址信息
	function _format_url($rs)
	{
		if(!$rs || !$rs["content"]) return false;
		$content = unserialize($rs["content"]);
		$_admin = array("id"=>0,"type"=>"txt","info"=>$content["default"]);
		if($GLOBALS['app']->app_id == "admin")
		{
			$list = array("default"=>$content["default"],"rewrite"=>$content["rewrite"],"_admin"=>$_admin);
			return $list;
		}
		$url = $GLOBALS['app']->site["url_type"] == "rewrite" ? $content["rewrite"] : $content["default"];
		if(!$url) $url = $content['default'];
		return $url;
	}


	//取得扩展模块字段内容信息
	function module_fields($mid)
	{
		if(!$mid) return false;
		$list = $GLOBALS['app']->model("module")->fields_all($mid,"identifier");
		if($list)
		{
			foreach($list AS $key=>$value)
			{
				if($value["ext"]) $value["ext"] = unserialize($value["ext"]);
				$list[$key] = $value;
			}
			return $list;
		}
		return false;
	}

	//读取会员扩展模板字段内容信息
	function user_fields()
	{
		$sql = "SELECT * FROM ".$GLOBALS['app']->db->prefix."fields WHERE ftype='user' ORDER BY taxis ASC,id DESC";
		$rslist = $GLOBALS['app']->db->get_all($sql);
		return $rslist;
	}

	function ext_fields($module,$show_content=true)
	{
		$fe = $show_content ? "extc" : "ext";
		if($fe == "extc")
		{
			$sql = "SELECT e.identifier,e.id,e.module,e.title,e.field_type,e.note,e.form_type,e.ext,c.content FROM ".$GLOBALS['app']->db->prefix."ext e ";
			$sql.= "LEFT JOIN ".$GLOBALS['app']->db->prefix."extc c ON(e.id=c.id) ";
			$sql.= " WHERE e.module='".$module."' ";
			$sql.= " ORDER BY e.taxis ASC,e.id DESC";
		}
		else
		{
			$sql = "SELECT * FROM ".$GLOBALS['app']->db->prefix."ext ";
			$sql.= " WHERE module='".$module."' ";
			$sql.= " ORDER BY taxis ASC,id DESC";
		}
		$rslist = $GLOBALS['app']->db->get_all($sql);
		if(!$rslist) return false;
		$extlist = "";
		foreach($rslist AS $key=>$value)
		{
			if($value["ext"])
			{
				$value["ext"] = unserialize($value["ext"]);
			}
			$extlist[$value["id"]] = $value;
		}
		return $extlist;
	}
	
	//安全格式化外部传输过来的数据
	function safe_format($id)
	{
		if(!$id) return false;
		$idlist = explode(",",$id);
		$tmp = array();
		foreach($idlist AS $key=>$value)
		{
			if(intval($value))
			{
				$tmp[] = intval($value);
			}
		}
		$id = implode(",",$tmp);
		if(!$id) return false;
		return $id;
	}
}
?>