<?php
/**
 * 搜索结果
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年2月25日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class search_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}
	
	public function index_f()
	{
		$keywords = $this->get('keywords');
		if(!$keywords){
			$this->error(P_Lang('请输入要搜索的关键字'));
		}
		//取得符合搜索的项目
		$condition = "status=1 AND hidden=0 AND is_search !=0 AND module>0";
		$list = $this->model('project')->project_all($this->site['id'],'id',$condition);
		if(!$list){
			$this->error(P_Lang('您的网站没有允许可以搜索的信息'));
		}
		$pids = $mids = $projects = array();
		foreach($list as $key=>$value){
			$pids[] = $value["id"];
			$mids[] = $value['module'];
			$projects[$value['id']] = $value['identifier'];
		}
		$mids = array_unique($mids);
		$condition = "l.project_id IN(".implode(",",$pids).") AND l.module_id IN(".implode(",",$mids).") ";
		$klist = explode(" ",$keywords);
		$kc = array();
		foreach($klist as $key=>$value){
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
		$r = array('keywords'=>$keywords);
		if($idlist){
			$rslist = array();
			foreach($idlist AS $key=>$value){
				$info = $this->call->phpok('_arc',array('title_id'=>$value['id'],'site'=>$this->site['id']));
				if($info){
					$info['_title'] = str_replace($klist,$kwlist,$info['title']);
					$rslist[] = $info;
				}
			}
			$r['rslist'] = $rslist;
		}
		$r['total'] = $total;
		$r['pageid'] = $pageid;
		$r['psize'] = $psize;
		$this->success($r);
	}
}
