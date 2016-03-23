<?php
/***********************************************************
	Filename: {phpok}/www/search_control.php
	Note	: 搜索
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-04-20 23:51
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class search_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		//查询
		$keywords = $this->get('keywords');
		if($keywords){
			$keywords = str_replace(array("　","，",",","｜","|","、","/","\\","／","＼","+","＋","-","－","_","＿")," ",$keywords);
			$keywords = trim($keywords);
			if($keywords){
				$this->load_search($keywords);
			}
		}
		$this->view("search_index");
	}

	private function load_search($keywords)
	{
		if(!$keywords) return false;
		//取得符合搜索的项目
		$condition = "status=1 AND hidden=0 AND is_search !=0 AND module>0";
		$list = $this->model('project')->project_all($this->site['id'],'id',$condition);
		if(!$list){
			error(P_Lang('您的网站没有允许可以搜索的信息'),$this->url,"error",10);
		}
		$pids = $mids = $projects = array();
		foreach($list AS $key=>$value){
			$pids[] = $value["id"];
			$mids[] = $value['module'];
			$projects[$value['id']] = $value['identifier'];
		}
		$mids = array_unique($mids);
		$condition = "l.project_id IN(".implode(",",$pids).") AND l.module_id IN(".implode(",",$mids).") ";
		$klist = explode(" ",$keywords);
		$kc = array();
		$kwlist = array();
		foreach($klist AS $key=>$value){
			$kwlist[] = '<i>'.$value.'</i>';
			$kc[] = " l.seo_title LIKE '%".$value."%'";
			$kc[] = " l.seo_keywords LIKE '%".$value."%'";
			$kc[] = " l.seo_desc LIKE '%".$value."%'";
			$kc[] = " l.title LIKE '%".$value."%'";
			$kc[] = " l.tag LIKE '%".$value."%'";
		}
		$condition.= "AND (".implode(" OR ",$kc).") ";
		$total = $this->model('search')->get_total($condition);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1) * $psize;
		$idlist = $this->model('search')->id_list($condition,$offset,$psize);
		if($idlist){
			$rslist = array();
			foreach($idlist AS $key=>$value){
				$info = $this->call->phpok('_arc',array('title_id'=>$value['id'],'site'=>$this->site['id']));
				if($info){
					$info['_title'] = str_replace($klist,$kwlist,$info['title']);
					$rslist[] = $info;
				}
			}
			$this->assign("rslist",$rslist);
		}
		$pageurl = $this->url('search','','keywords='.rawurlencode($keywords));
		$this->assign("pageurl",$pageurl);
		$this->assign("total",$total);
		$this->assign("pageid",$pageid);
		$this->assign("psize",$psize);
		$this->assign("keywords",$keywords);
		$this->view("search_list");
		exit;
	}
}
?>