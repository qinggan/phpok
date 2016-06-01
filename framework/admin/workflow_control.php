<?php
/*****************************************************************************************
	文件： {phpok}/admin/workflow_control.php
	备注： 工作流管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年06月20日 15时42分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class workflow_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function title_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$idlist = explode(',',$id);
		$endlist = $rslist = false;
		foreach($idlist as $key=>$value){
			$tmp = $this->model('workflow')->get_tid($value);
			if($tmp){
				if(!$endlist){
					$endlist = array();
				}
				$endlist[$value] = $tmp;
			}else{
				if(!$rslist){
					$rslist = array();
				}
				$rslist[] = $value;
			}
		}
		if(!$rslist){
			$this->error(P_Lang('指定的主题已经授权且已完成维护'));
		}
		$ids = implode(",",$rslist);
		$this->assign('ids',$ids);
		$rslist = $this->model('list')->get_all("l.id IN(".$ids.")",0,999,'id');
		$this->assign('rslist',$rslist);
		$this->assign('endlist',$endlist);
		$alist = $this->model('admin')->all_manager();
		if(!$alist){
			$this->error(P_Lang('系统没有普通管理员，不能执行此操作'));
		}
		$this->assign('alist',$alist);
		$this->view('workflow_title');
	}

	public function addok_f()
	{
		$id = $this->get('id');
		$admin_id = $this->get('admin_id','int');
		$note = $this->get('note');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$admin_id){
			$this->json(P_Lang('未指定管理员'));
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$tmp = $this->model('workflow')->get_tid($value);
			if($tmp){
				continue;
			}
			$array = array('tid'=>$value,'admin_id'=>$admin_id,'dateline'=>$this->time,'note'=>$note);
			$this->model('workflow')->save($array);
		}
		$this->json(true);
	}

	public function manage_f()
	{
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('workflow')->get_all('',$offset,$psize);
		$this->assign('rslist',$rslist);
		$total = $this->model('workflow')->total('');
		$pageurl = $this->url('workflow','manage');
		$this->assign('total',$total);
		$this->assign('psize',$psize);
		$this->assign('pageid',$pageid);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign('pagelist',$pagelist);
		$this->view('workflow_manage');
	}

	public function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$_SESSION['admin_rs']['if_system']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$this->model('workflow')->delete($id);
		$this->json(true);
	}

	public function reset_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$rs = $this->model('workflow')->get_one($id);
		if(!$rs){
			error(P_Lang('数据不存在'));
		}
		$this->assign('rs',$rs);
		$alist = $this->model('admin')->all_manager();
		if(!$alist){
			$this->error(P_Lang('系统没有普通管理员，不能执行此操作'));
		}
		$this->assign('alist',$alist);
		$this->view('workflow_reset');
	}

	public function reset_ok_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('workflow')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('数据不存在'));
		}
		$admin_id = $this->get('admin_id','int');
		$note = $this->get('note');
		if(!$admin_id){
			$this->json(P_Lang('未指定管理员'));
		}
		$array = array('admin_id'=>$admin_id,'dateline'=>$this->time,'note'=>$note);
		$this->model('workflow')->update($array,$id);
		$this->json(true);
	}

	public function list_f()
	{
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('workflow')->get_all('admin_id='.$_SESSION['admin_id'],$offset,$psize);
		$this->assign('rslist',$rslist);
		$total = $this->model('workflow')->total('admin_id='.$_SESSION['admin_id']);
		$pageurl = $this->url('workflow','list');
		$this->assign('total',$total);
		$this->assign('psize',$psize);
		$this->assign('pageid',$pageid);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign('pagelist',$pagelist);
		$this->view('workflow_list');
	}

	public function step_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'),$this->url('workflow','list'),'error');
		}
		$wf = $this->model('workflow')->get_one($id);
		if(!$wf){
			error(P_Lang('数据不存在'),$this->url('workflow','list'),'error');
		}
		$this->assign("wf",$wf);
		//更新操作
		$array = array('actting'=>1);
		$this->model('workflow')->update($array,$id);
		//读取内容
		$rs = $this->model('list')->get_one($wf['tid'],false);
		$pid = $rs["project_id"];
		$extcate = $this->model('list')->ext_catelist($wf['tid']);
		if(!$extcate){
			$extcate = array();
		}
		if(!$pid){
			error(P_Lang('操作异常'),$this->url('workflow','list'),"error");
		}
		$p_rs = $this->model('project')->get_one($pid);
		if(!$p_rs){
			error(P_Lang('操作异常'),$this->url('workflow','list'),"error");
		}
		$m_rs = $this->model('module')->get_one($p_rs["module"]);
		$ext_list = $this->model('module')->fields_all($p_rs["module"]);
		$extlist = array();
		foreach(($ext_list ? $ext_list : array()) AS $key=>$value)
		{
			if($value["ext"]){
				$ext = unserialize($value["ext"]);
				foreach($ext AS $k=>$v){
					$value[$k] = $v;
				}
			}
			$idlist[] = strtolower($value["identifier"]);
			if($rs[$value["identifier"]]){
				$value["content"] = $rs[$value["identifier"]];
			}
			$extlist[] = $this->lib('form')->format($value);
		}
		$this->assign("extlist",$extlist);
		$this->assign("p_rs",$p_rs);
		$this->assign("m_rs",$m_rs);
		$this->assign("pid",$pid);
		$plist = array($p_rs);
		if($p_rs["parent_id"]){
			$this->model('project')->get_parentlist($plist,$p_rs["parent_id"]);
			krsort($plist);
		}
		$this->assign("plist",$plist);
		if($rs["id"]){
			$this->assign("id",$rs["id"]);
		}
		$this->assign("rs",$rs);
		if($p_rs['is_attr']){
			$attrlist = $this->model('list')->attr_list();
			$this->assign("attrlist",$attrlist);
		}
		$this->lib('form')->cssjs();
		$this->view("workflow_edit");
	}

	private function check_identifier($sign,$id=0,$site_id=0)
	{
		if(!$sign){
			return P_Lang('标识串不能为空');
		}
		$sign = strtolower($sign);
		//字符串是否符合条件
		if(!preg_match("/[a-z][a-z0-9\_\-\.]+/",$sign)){
			return P_Lang('标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头');
		}
		if(!$site_id){
			$site_id = $_SESSION["admin_site_id"];
		}
		$check = $this->model('id')->check_id($sign,$site_id,$id);
		if($check){
			return P_Lang('标识符已被使用');
		}
		return 'ok';
	}

	public function save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$wf = $this->model('workflow')->get_one($id);
		if(!$wf){
			$this->json(P_Lang('工作任务不存在'));
		}
		$rs = $this->model('list')->get_one($wf['tid']);
		$pid = $rs["project_id"];
		$parent_id = $rs["parent_id"];
		$p_rs = $this->model('project')->get_one($pid);
		if(!$p_rs){
			$this->json(P_Lang('操作异常，无法取得项目信息'));
		}
		$array = array();
		//更新标识串
 		$array['identifier'] = $this->get("identifier");
 		if($array['identifier']){
	 		$check = $this->check_identifier($array['identifier'],$wf['tid'],$p_rs["site_id"]);
	 		if($check != 'ok'){
		 		$this->json($check);
	 		}
 		}
		$array["project_id"] = $p_rs['id'];
		$array["module_id"] = $p_rs["module"];
		$array["site_id"] = $p_rs["site_id"];
		$this->model('list')->save($array,$wf['tid']);
 		if($p_rs["module"]){
	 		$ext_list = $this->model('module')->fields_all($p_rs["module"]);
	 		$tmplist = array();
	 		$tmplist["id"] = $wf['tid'];
	 		$tmplist["site_id"] = $p_rs["site_id"];
	 		$tmplist["project_id"] = $pid;
	 		if(!$ext_list) $ext_list = array();
			foreach($ext_list AS $key=>$value){
				if($rs[$value['identifier']]){
					$value['content'] = $rs[$value['identifier']];
				}
				$tmplist[$value["identifier"]] = $this->lib('form')->get($value);
			}
			$this->model('list')->save_ext($tmplist,$p_rs["module"]);
		}
		$is_end = $this->get('is_end','int');
		$array = array('is_end'=>$is_end);
		if($is_end){
			$array['endtime'] = $this->time;
		}
		$this->model('workflow')->update($array,$id);
 		$this->json(true);
	}
}

?>