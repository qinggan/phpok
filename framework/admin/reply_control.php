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
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
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
				$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
				$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
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
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$tid = $this->get("tid","int");
		if(!$tid){
			error(P_Lang('未指定ID'),$goback,"error");
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
				$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
				$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
				$this->assign("pagelist",$pagelist);
			}
		}
		$this->assign("total",$total);
		$this->view("reply_list");
	}

	function status_f()
	{
		if(!$this->popedom['status']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id) $this->json(P_Lang('未指定ID'));
		$status = $this->get("status","int");
		$status = $status ? 1 : 0;
		$array = array("status"=>$status);
		$this->model('reply')->save($array,$id);
		$this->json("OK",true);
	}

	//删除回复
	function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id) $this->json(P_Lang('未指定ID'));
		$this->model('reply')->delete($id);
		$this->json(true);
	}

	function edit_f()
	{
		if(!$this->popedom["modify"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get("id","int");
		if(!$id) error_open(P_Lang('未指定ID'));
		$rs = $this->model('reply')->get_one($id);
		if(!$rs){
			error_open(P_Lang('数据记录不存在'));
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$title_rs = $this->model('list')->get_one($rs["tid"]);
		$this->assign("title_rs",$title_rs);
		$edit_content = form_edit('content',$rs['content'],'editor','width=680&height=180');
		$this->assign('edit_content',$edit_content);
		$this->view("reply_content");
	}

	public function edit_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$array = array();
		$array["star"] = $this->get("star","int");
		$array["content"] = $this->get("content",'html');
		$array["status"] = $this->get("status","int");
		$this->model('reply')->save($array,$id);
		$this->json(true);
	}

	public function adm_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$rs = $this->model('reply')->get_one($id);
		if(!$rs){
			error(P_Lang('数据记录不存在'));
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$title_rs = $this->model('list')->get_one($rs["tid"]);
		$this->assign("title_rs",$title_rs);
		$edit_content = form_edit('content',$rs['adm_content'],'editor','width=680&height=180');
		$this->assign('edit_content',$edit_content);
		$this->view("reply_adm");
	}

	public function adm_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$array = array();
		$array["adm_content"] = $this->get("content","html");
		$array["adm_time"] = $this->time;
		$this->model('reply')->save($array,$id);
		$this->json(true);
	}
	
}
?>