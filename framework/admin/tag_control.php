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
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("tag");
		$this->assign("popedom",$this->popedom);
		$this->model('tag')->site_id($_SESSION['admin_site_id']);
	}

	public function index_f()
	{
		if(!$this->popedom["list"])
		{
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$pageurl = $this->url('tag');
		//获取当前系统拥有Tag数
		$keywords = $this->get('keywords');
		$condition = "1=1";
		if($keywords)
		{
			$condition .= " AND title LIKE '%".$keywords."%' ";
			$pageurl .= "&title=".rawurlencode($keywords);
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
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("rslist",$rslist);
			$this->assign('pagelist',$pagelist);
		}
		$this->view('tag_index');
	}

	public function save_f()
	{
		$id = $this->get('id','int');
		$popedom = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom])
		{
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get('title');
		if(!$title)
		{
			$this->json(P_Lang('关键字名称不能为空'));
		}
		$chk = $this->model('tag')->chk_title($title,$id);
		if($chk)
		{
			$this->json(P_Lang('关键字已存在，请检查'));
		}
		$data = array('title'=>$title,'url'=>$this->get('url'),'target'=>$this->get('target','int'));
		$data['site_id'] = $_SESSION['admin_site_id'];
		$data['alt'] = $this->get('alt');
		$data['is_global'] = $this->get('is_global','int');
		$data['replace_count'] = $this->get('replace_count','int');
		if($id)
		{
			$this->model('tag')->save($data,$id);
			$this->json(true);
		}
		else
		{
			$insert_id = $this->model('tag')->save($data);
			if(!$insert_id)
			{
				$this->json(P_Lang('添加失败，请检查'));
			}
			$this->json(true);
		}
	}

	public function delete_f()
	{
		if(!$this->popedom['delete'])
		{
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id)
		{
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('tag')->delete($id);
		$this->json(true);
	}
}

?>