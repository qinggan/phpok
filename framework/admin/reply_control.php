<?php
/***********************************************************
	Filename: {phpok}/admin/reply_control.php
	Note	: 回复内容管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年7月2日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class reply_control extends phpok_control
{
	var $popedom;
	function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("reply");
		$this->assign("popedom",$this->popedom);
	}

	//取得网站全部评论
	function index_f()
	{
		if(!$this->popedom["list"])
		{
			error("您没有权限查看评论信息");
		}
		$pageurl = $this->url("reply");
		$status = $this->get("status","int");
		$condition = "l.replydate>0";
		if($status)
		{
			$n_status = $status == 1 ? "1" : "0";
			$condition .= " AND l.id IN(SELECT DISTINCT tid FROM ".$this->db->prefix."reply WHERE status='".$n_status."')";
			$pageurl .= "&status=".$status; 
			$this->assign("status",$status);
		}
		//关键字
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (l.title LIKE '%".$keywords."%' OR l.id IN(SELECT DISTINCT tid FROM ".$this->db->prefix."reply WHERE content LIKE '%".$keywords."%' OR adm_content LIKE '%".$keywords."%')) ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = $this->config["psize"] ? $this->config["psize"] : 30;
		$total = $this->model('reply')->get_all_total($condition);
		if($total>0)
		{
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('reply')->get_all($condition,$offset,$psize,"id");
			$this->assign("rslist",$rslist);
			if($total>$psize)
			{
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=(total)/(psize)&always=1");
				$this->assign("pagelist",$pagelist);
			}
		}
		$_SESSION["last_page_url"] = $pageurl."&".$this->config["pageid"]."=".$pageid;
		$this->assign("total",$total);
		$this->view("reply_index");
	}

	function list_f()
	{
		$goback = $_SESSION["last_page_url"] ? $_SESSION["last_page_url"] : ($_SERVER["HTTP_REFERER"] ? $_SERVER["HTTP_REFERER"] : $this->url("reply"));
		if(!$this->popedom["list"])
		{
			error("您没有权限查看评论信息");
		}
		$tid = $this->get("tid","int");
		if(!$tid)
		{
			
			error('未指定留言信息',$goback,"error");
		}
		$rs = $this->model('list')->get_one($tid);
		$this->assign("rs",$rs);
		$pageurl = $this->url("reply","list","tid=".$tid);
		$status = $this->get('status',"int");
		$condition = "tid='".$tid."' AND parent_id=0";
		if($status)
		{
			$n_status = $status == 1 ? "1" : "0";
			$condition .= " AND status='".$n_status."'";
			$pageurl .= "&status=".$status; 
			$this->assign("status",$status);
		}
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (content LIKE '%".$keywords."%' OR adm_content LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = $this->config["psize"] ? $this->config["psize"] : 30;
		$total = $this->model('reply')->get_total($condition);
		if($total>0)
		{
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('reply')->get_list($condition,$offset,$psize,"id");
			if($rslist)
			{
				$uidlist = array();
				foreach($rslist AS $key=>$value)
				{
					if($value["uid"])
					{
						$uidlist[] = $value["uid"];
					}
				}
				$idlist = array_keys($rslist);
				$condition = "tid='".$tid."' AND parent_id IN(".implode(",",$idlist).")";
				$sublist = $this->model('reply')->get_list($condition,0,0);
				if($sublist)
				{
					foreach($sublist AS $key=>$value)
					{
						if($value["uid"]) $uidlist[] = $value["uid"];
						$rslist[$value["parent_id"]]["sublist"][$value["id"]] = $value;
					}
				}
				if($uidlist && count($uidlist)>0)
				{
					$uidlist = array_unique($uidlist);
					$this->model("user");
					$ulist = $this->model('user')->get_all_from_uid(implode(",",$uidlist),'id');
					if(!$ulist) $ulist = array();
					foreach($rslist AS $key=>$value)
					{
						if($value["uid"])
						{
							$value["uid"] = $ulist[$value["uid"]];
						}
						if($value["sublist"])
						{
							foreach($value["sublist"] AS $k=>$v)
							{
								if($v)
								{
									$v["uid"] = $ulist[$v["uid"]];
								}
								$value["sublist"][$k] = $v;
							}
						}
						$rslist[$key] = $value;
					}
				}
			}
			$this->assign("rslist",$rslist);
			if($total>$psize)
			{
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=(total)/(psize)&always=1");
				$this->assign("pagelist",$pagelist);
			}
		}
		$this->assign("total",$total);
		$this->view("reply_list");
	}

	function status_f()
	{
		if(!$this->popedom["status"]) json_exit("您没有审核权限");
		$id = $this->get("id","int");
		if(!$id) json_exit("未指定ID");
		$status = $this->get("status","int");
		$status = $status ? 1 : 0;
		$array = array("status"=>$status);
		$this->model('reply')->save($array,$id);
		json_exit("OK",true);
	}

	//删除回复
	function delete_f()
	{
		if(!$this->popedom["delete"]) json_exit("您没有删除权限");
		$id = $this->get("id","int");
		if(!$id) json_exit("未指定ID");
		$this->model('reply')->delete($id);
		json_exit("OK",true);
	}

	function edit_f()
	{
		if(!$this->popedom["modify"]) error_open("您没有修改权限");
		$id = $this->get("id","int");
		if(!$id) error_open("未指定ID");
		$rs = $this->model('reply')->get_one($id);
		if(!$rs)
		{
			error_open("评论内容不存在");
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$title_rs = $this->model('list')->get_one($rs["tid"]);
		$this->assign("title_rs",$title_rs);
		$this->view("reply_content");
	}

	function edit_save_f()
	{
		if(!$this->popedom["modify"]) error_open("您没有修改权限");
		$id = $this->get("id","int");
		if(!$id) error_open("未指定ID");
		$array = array();
		$array["star"] = $this->get("star","int");
		$array["content"] = $this->get("content",'html');
		if($this->popedom["status"])
		{
			$array["status"] = $this->get("status","int");
		}
		$this->model('reply')->save($array,$id);
		$html = '系统会在 <span class="red">2秒</span>后关闭窗口，<a href="javascript:parent.window.location.href=parent.window.location.href;void(0);">您可以点这里关闭窗口</a>';
		$html.= '<script type="text/javascript">'."\n";
		$html.= 'window.setTimeout(\'top.window.location.href=top.window.location.href\',2000)'."\n";
		$html.= "\n".'</script>';
		error_open("评论信息更新成功","ok",$html);
	}

	function adm_f()
	{
		if(!$this->popedom["modify"]) error_open("您没有修改权限");
		$id = $this->get("id","int");
		if(!$id) error_open("未指定ID");
		$rs = $this->model('reply')->get_one($id);
		if(!$rs)
		{
			error_open("评论内容不存在");
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$title_rs = $this->model('list')->get_one($rs["tid"]);
		$this->assign("title_rs",$title_rs);
		$this->view("reply_adm");
	}

	function adm_save_f()
	{
		if(!$this->popedom["reply"]) error_open("您没有回复权限");
		$id = $this->get("id","int");
		if(!$id) error_open("未指定ID");
		$array = array();
		$array["adm_content"] = $this->get("content","html",false);
		$array["adm_time"] = $this->system_time;
		$this->model('reply')->save($array,$id);
		$html = '系统会在 <span class="red">2秒</span>后关闭窗口，<a href="javascript:parent.window.location.href=parent.window.location.href;void(0);">您可以点这里关闭窗口</a>';
		$html.= '<script type="text/javascript">'."\n";
		$html.= 'window.setTimeout(\'top.window.location.href=top.window.location.href\',2000)'."\n";
		$html.= "\n".'</script>';
		error_open("管理员信息回复成功","ok",$html);
	}
	
}
?>