<?php
/*****************************************************************************************
	文件： {phpok}/admin/tag_control.php
	备注： Tag标签管理工具
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月25日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		$pageurl = $this->url('tag');
		//获取当前系统拥有Tag数
		$keywords = $this->get('keywords');
		$condition = "1=1";
		if($keywords)
		{
			$condition .= " AND title LIKE '%".$keywords."%' ";
			$pageurl .= "&title=".rawurlencode($keywords);
		}
		$status = $this->get('status','int');
		if($status)
		{
			$condition .= $status == 1 ? ' AND status=1' : " AND status=0";
			$pageurl .= "&status=".rawurlencode($status);
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid)
		{
			$pageid = 1;
		}
		$offset = ($pageid - 1) * $psize;
		$total = $this->model('tag')->get_total($condition);
		if($total>0)
		{
			$rslist = $this->model('tag')->get_list($condition,$offset,$psize);
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=(total)/(psize)&always=1");
			$this->assign("rslist",$rslist);
			$this->assign('pagelist',$pagelist);
		}
		$this->view('tag_index');
	}
}

?>