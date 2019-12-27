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
		$id = $this->get('id');
		$cateid = $this->get('cateid','int');
		$cate = $this->get('cate');
		$ext = $this->get('ext');
		if($keywords){
			$keywords = str_replace(array("　","，",",","｜","|","、","/","\\","／","＼","+","＋")," ",$keywords);
			$keywords = trim($keywords);
			
		}
		$project = array();
		if($id){
			$project = $this->model('project')->get_one($id,false);
		}
		$cate_rs = array();
		if($cate){
			$cate_rs = $this->model('cate')->get_one($cate,'identifier',false);
		}
		if($cateid){
			$cate_rs = $this->model('cate')->get_one($cateid,'id',false);
		}
		if($keywords || $project || $cate_rs || $ext){
			$this->load_search($keywords,$project,$cate_rs,$ext);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'search_index';
		}
		$this->plugin('ap-load-search');
		$this->view($tplfile);
	}

	private function load_search($keywords='',$project='',$cate_rs='',$ext='')
	{
		$pageurl = $this->url('search');
		if(strpos($pageurl,'?') === false){
			$pageurl .= "?";
		}else{
			$pageurl .= "&";
		}
		$kc = array();
		$my_mid = 0;
		if($project && is_array($project)){
			$condition .= "l.project_id='".$project['id']."' AND l.module_id='".$project['module']."'";
			$pageurl .= "id=".$project['identifier']."&";
			$this->assign('page_rs',$project);
			if($cate_rs){
				$cate_ids = array($cate_rs['id']);
				$this->model('cate')->get_sonlist_id($cate_ids,$cate_rs['id'],1);
				$condition .= " AND l.cate_id IN(".implode(",",$cate_ids).") ";
				$pageurl .= "cateid=".$cate_rs['id']."&";
				$this->assign('cateid',$cateid);
				$this->assign('cate_rs',$cate_rs);
			}
			if($ext){
				$sql = "SELECT id FROM ".$this->db->prefix."list_".$project['module']." WHERE project_id='".$project['id']."' ";
				foreach($ext as $key=>$value){
					$sql.= " AND ".$key."='".$value."' ";
					$pageurl .= "ext[".$key."]=".rawurlencode($value)."&";
				}
				$condition .= " AND l.id IN(".$sql.") ";
				$this->assign('ext',$ext);
			}
		}else{
			//取得符合搜索的项目
			$condition = "status=1 AND hidden=0 AND is_search !=0 AND module>0";
			$list = $this->model('project')->project_all($this->site['id'],'id',$condition);
			if(!$list){
				$this->error(P_Lang('您的网站没有允许可以搜索的信息'),$this->url,10);
			}
			$pids = $mids = $projects = array();
			foreach($list as $key=>$value){
				$pids[] = $value["id"];
				$mids[] = $value['module'];
				$projects[$value['id']] = $value['identifier'];
			}
			$mids = array_unique($mids);
			$condition = "l.project_id IN(".implode(",",$pids).") AND l.module_id IN(".implode(",",$mids).") ";
			if(count($mids) == 1 && $keywords){
				$my_mid = implode(",",$mids);
				$module = $this->model('module')->get_one($my_mid);
				if($module && !$module['mtype'] && $module['tbl'] == 'list'){
					$flist = $this->model('fields')->flist($module['id']);
					if($flist){
						foreach($flist as $key=>$value){
							if($value['search'] == 1){
								$kc[] = " ext.".$value['identifier']."='".$keywords."' ";
							}
							if($value['search'] == 2){
								$kc[] = " ext.".$value['identifier']." LIKE '%".str_replace(' ','%',$keywords)."%' ";
							}
						}
					}
				}
			}
		}
		$this->assign('searchurl',substr($pageurl,0,-1));
		if($keywords){
			$klist = explode(" ",$keywords);
			$kwlist = array();
			foreach($klist as $key=>$value){
				$kwlist[] = '<i>'.$value.'</i>';
				$kc[] = " l.seo_title LIKE '%".$value."%'";
				$kc[] = " l.seo_keywords LIKE '%".$value."%'";
				$kc[] = " l.seo_desc LIKE '%".$value."%'";
				$kc[] = " l.title LIKE '%".$value."%'";
				$kc[] = " l.tag LIKE '%".$value."%'";
			}
			$condition.= "AND (".implode(" OR ",$kc).") ";
			$pageurl .= "keywords=".rawurlencode($keywords)."&";
		}
		$total = $this->model('search')->get_total($condition,$my_mid);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1) * $psize;
		$idlist = $this->model('search')->id_list($condition,$offset,$psize,$my_mid);
		if($idlist){
			$rslist = array();
			foreach($idlist as $key=>$value){
				$info = $this->call->phpok('_arc',array('title_id'=>$value['id'],'site'=>$this->site['id']));
				if($info){
					$info['_title'] = str_replace($klist,$kwlist,$info['title']);
					$rslist[] = $info;
				}
			}
			$this->assign("rslist",$rslist);
		}
		$this->assign("pageurl",substr($pageurl,0,-1));
		$this->assign("total",$total);
		$this->assign("pageid",$pageid);
		$this->assign("psize",$psize);
		$this->assign("keywords",$keywords);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,'list');
		if(!$tplfile){
			$tplfile = 'search_list';
		}
		$this->plugin('ap-load-search');
		$this->view($tplfile);
		exit;
	}
}