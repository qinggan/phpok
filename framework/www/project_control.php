<?php
/***********************************************************
	Filename: {phpok}/www/project_control.php
	Note	: 网站首页及APP的封面页
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-27 11:24
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class project_control extends phpok_control
{
	var $cache_list;
	var $user_groupid;
	function __construct()
	{
		parent::control();
		//判断是否有读权限
		$groupid = $this->model('usergroup')->group_id($_SESSION['user_id']);
		if(!$groupid)
		{
			error(P_Lang('无法获取前端用户组信息，请检查'),'','error');
		}
		$this->user_groupid = $groupid;
	}

	//栏目
	function index_f()
	{
		$id = $this->get("id");
		if(!$id)
		{
			error("操作异常！","","error");
		}
		$tmp = $this->model('data')->id($id,$this->site['id']);
		if(!$tmp || $tmp['type'] != 'project')
		{
			error("项目不存在",$this->url,'error',10);
		}
		$pid = $tmp['id'];
		//判断是否有阅读权限
		$this->model('popedom')->siteid($this->site['id']);
		if(!$this->model('popedom')->check($pid,$this->user_groupid,'read'))
		{
			error(P_Lang('您没有阅读权限，请联系网站管理员'),'','error');
		}
		$rs = $this->call->phpok('_project',array('pid'=>$pid,'project_ext'=>true));
		if(!$rs)
		{
			error("项目不存在或操作异常或项目未启用",$this->url('index'),"error");
		}
		$this->phpok_seo($rs);
		$this->assign("page_rs",$rs);
		if($rs['parent_id'])
		{
			$parent_rs = $this->call->phpok('_project','pid='.$rs['parent_id'].'&project_ext=1');
			if(!$parent_rs || !$parent_rs['status'])
			{
				error("父级项目未启用",$this->url,'notice',10);
			}
			$this->assign("parent_rs",$parent_rs);
		}
		if($rs["module"])
		{
			$this->load_module($rs,$parent_rs);
			exit;
		}
		$tpl = $rs["tpl_index"] ? $rs["tpl_index"] : ($rs["tpl_list"] ? $rs["tpl_list"] : $rs["tpl_content"]);
		if(!$tpl && $rs["parent_id"] && $parent_rs)
		{
			//如果父级栏目有设置了模板页
			$tpl = $parent_rs["tpl_index"] ? $parent_rs["tpl_index"] : ($parent_rs["tpl_list"] ? $parent_rs["tpl_list"] : $parent_rs["tpl_content"]);
			if(!$tpl)
			{
				$tpl = $parent_rs["identifier"]."_page";
				if(!$this->tpl->check_exists($tpl))
				{
					$tpl = '';
				}
			}
		}
		if(!$tpl) $tpl = $rs["identifier"]."_page";
		//判断是否是xml
		$xml = $this->get('xml','int');
		if($xml) $tpl .= '_xml';
		
		if(!$this->tpl->check_exists($tpl))
		{
			error('未配置模板：'.$tpl.'，请配置相应的模板','','error');
		}
		//如果是xml
		if($xml)
		{
			header('Content-Type: text/xml;');
			echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		}
		$this->view($tpl);
	}

	//项目支持模型
	function load_module($rs,$parent_rs='')
	{
		$m_rs = $this->model('module')->get_one($rs["module"]);
		if(!$m_rs || $m_rs["status"] != 1)
		{
			error("模块不存在或未启用","","error");
		}
		$this->assign("m_rs",$m_rs);
		$cate_root = $rs["cate"];
		$cateid = 0;
		if($rs["cate"])
		{
			$cate = $this->get("cate");
			if($cate)
			{
				$cate_rs = $this->call->phpok('_cate','pid='.$rs['id']."&cate=".$cate."&cate_ext=1");
				if($cate_rs)
				{
					$cateid = $cate_rs['id'];
				}
			}
			//兼容旧版的写法
			if(!$cateid) $cateid = $this->get("cid");
			if(!$cateid) $cateid = $this->get("cateid");
		}
		//获取关键字
		$keywords = $this->get("keywords");
		//获取扩展字段信息
		$ext = $this->get("ext");
		$tag = $this->get("tag");
		$uid = $this->get('uid','int');
		//判断该项目是否启用封面
		if($rs["tpl_index"] && (!$cateid || $cateid == $cate_root) && !$keywords && !$ext && !$tag && !$uid)
		{
			if(!$this->tpl->check_exists($rs["tpl_index"]))
			{
				$rs["tpl_index"] = $rs["identifier"]."_index";
			}
			$this->view($rs["tpl_index"]);
			exit;
		}
		//读取列表信息
		$tplfile = $rs["tpl_list"];
		$psize = $rs["psize"];
		if(!$psize) $psize = $this->config["psize"] ? $this->config["psize"] : 30;
		if($cateid || $cate_root)
		{
			if(!$cateid) $cateid = $cate_root;
			$cate_rs = $this->call->phpok('_cate','pid='.$rs['id'].'&cateid='.$cateid.'&cate_ext=1');
			if($cate_rs)
			{
				$this->assign("cate_rs",$cate_rs);
				if($cate_rs["tpl_list"] && $cate_rs["id"] != $cate_root) $tplfile = $cate_rs["tpl_list"];
				if($cate_rs["psize"] && $cate_rs["id"] != $cate_root) $psize = $cate_rs["psize"];
			}
		}
		$pageurl = $this->url($rs['identifier']);
		$dt = array('pid'=>$rs['id']);
		//读取列表信息
		$condition = "l.project_id=".$rs["id"]." AND l.module_id=".$rs["module"];
		if($cate_rs)
		{
			$dt['cateid'] = $cate_rs['id'];
			if($cate_rs['parent_id'] != $project_rs['cate'])
			{
				$cate_parent_rs = $this->call->phpok('_cate','pid='.$rs['id'].'&cateid='.$cate_rs['parent_id'].'&cate_ext=1');
				$this->assign('cate_parent_rs',$cate_parent_rs);
			}
			//优化某个分类下的关键字
			$this->phpok_seo($cate_rs);
			$pageurl = $this->url($rs['identifier'],$cate_rs['identifier']);
		}
		if($tag || $keywords || $ext) $pageurl .= $this->site["url_type"] == "rewrite" ? "?" : "&";
		if($tag)
		{
			$dt['tag'] = $tag;
			$pageurl .= "tag=".rawurlencode($tag)."&";
			//$condition .= " AND FIND_IN_SET('".$tag."',l.tag)>0 ";
			$this->assign("tag",$tag);
		}
		if($keywords)
		{
			$dt['keywords'] = $keywords;
			$pageurl .= "keywords=".rawurlencode($keywords)."&";
			$this->assign("keywords",$keywords);
		}
		if($ext && is_array($ext))
		{
			$c = '';
			foreach($ext AS $key=>$value)
			{
				if($key && $value)
				{
					$c[] = "ext.".$key." LIKE '%".$value."%'";
					$pageurl .= "ext[".$key."]=".rawurlencode($value)."&";
				}
			}
			if($c) $dt['sqlext'] = implode(" AND ",$c);
			$this->assign('ext',$ext);
		}
		if($uid)
		{
			$pageurl .= "uid=".$uid."&";
			$dt['user_id'] = $uid;
		}
		if(substr($pageurl,-1) == "&" || substr($pageurl,-1) == "?")
		{
			$pageurl = substr($pageurl,0,-1);
		}
		$pagename = $this->config["pageid"] ? $this->config["pageid"] : "pageid";
		$pageid = $this->get($pagename,"int");
		if(!$pageid) $pageid = 1;
		$offset = ($pageid-1) * $psize;
		$dt['offset'] = $offset;
		$dt['psize'] = $psize;
		$dt['in_text'] = 1;
		$dt['is_list'] = 1;
		$total = $this->call->phpok('_total',$dt);
		$rslist = $this->call->phpok('_arclist',$dt);
		$this->assign("pageid",$pageid);
		$this->assign("psize",$psize);
		$this->assign("pageurl",$pageurl);
		$this->assign("total",$total);
		$this->assign("rslist",$rslist);
		if(!$tplfile && $parent_rs)
		{
			$tplfile = $parent_rs['tpl_list'] ? $parent_rs['tpl_list'] : $parent_rs['identifier'].'_list';
		}
		if(!$tplfile) $tplfile = $rs["identifier"]."_list";
		if(!$this->tpl->check_exists($tplfile))
		{
			if($rs["tpl_content"] && $rslist)
			{
				reset($rslist);
				$rs = current($rslist);
				$url = $rs['identifier'] ? $this->url($rs['identifier']) : $this->url($rs['id']);
				header("Location:".$url);
				exit;
			}
			error('未配置模板：'.$tplfile.'，请配置相应的模板','','error');
			//$tplfile = "index";
		}
		//判断是否是xml
		$xml = $this->get('xml','int');
		if($xml)
		{
			 $tplfile .= '_xml';
			header('Content-Type: text/xml;');
			echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		}
		$this->view($tplfile);
		exit;
	}
}
?>