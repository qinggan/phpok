<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/rewrite_control.php
	备注： Rewrite规则配置
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月02日 14时18分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class rewrite_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('rewrite');
		$this->assign("popedom",$this->popedom);
		$this->model('rewrite')->site_id($_SESSION['admin_site_id']);
	}

	public function index_f()
	{
		if(!$this->popedom["list"])
		{
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$optlist = $this->model('rewrite')->type_all();
		$rslist = $this->model('rewrite')->get_all();
		if($rslist)
		{
			foreach($rslist as $key=>$value)
			{
				$value['title'] = '未知';
				if($optlist[$key])
				{
					$value['title'] = $optlist[$key];
					unset($optlist[$key]);
				}
				$rslist[$key] = $value;
			}
		}
		$this->assign('rslist',$rslist);
		if($optlist && count($optlist)>0)
		{
			$this->assign('optlist',$optlist);
		}
		$this->view('rewrite_index');
	}

	public function set_f()
	{
		if(!$this->popedom['set'])
		{
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if($id)
		{
			$rs = $this->model('rewrite')->get_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}
		else
		{
			$optlist = $this->model('rewrite')->type_all();
			$rslist = $this->model('rewrite')->get_all();
			if($rslist)
			{
				foreach($rslist as $key=>$value)
				{
					if($optlist[$key])
					{
						$value['title'] = $optlist[$key];
						unset($optlist[$key]);
					}
				}
			}
			$this->assign("optlist",$optlist);
		}
		$this->view("rewrite_set");
	}

	public function save_f()
	{
		if(!$this->popedom['set'])
		{
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id)
		{
			$this->json(P_Lang('未指定ID'));
		}
		$urltype = $this->get('urltype');
		if(!$urltype)
		{
			$this->json(P_Lang("未指定网址格式"));
		}
		$array = array('id'=>$id,'site_id'=>$_SESSION['admin_site_id'],'urltype'=>$urltype);
		$this->model('rewrite')->save($array);
		$this->json(true);
	}

	public function delete_f()
	{
		if(!$this->popedom['set'])
		{
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id)
		{
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('rewrite')->delete($id);
		$this->json(true);
	}
}


?>