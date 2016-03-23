<?php
/*****************************************************************************************
	文件： {phpok}/model/url.php
	备注： URL网址生成，解读Model
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月03日 20时39分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class url_model_base extends phpok_model
{
	protected $baseurl = '';
	protected $ctrl_id = "c";
	protected $func_id = "f";
	protected $phpfile = 'index.php';
	protected $page_id = 'pageid';
	protected $nocache = '';
	private $protected_id = array('js','ajax','inp');
	private $url_appid = 'www';
	public $urltype = 'default';
	
	public function __construct()
	{
		parent::model();
		if($this->config['debug']){
			$this->nocache = '0.'.$this->time;
		}
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	public function page_id($pageid)
	{
		$this->page_id = $pageid;
	}

	public function base_url($url='')
	{
		$this->base_url = $url;
	}

	public function ctrl_id($ctrlid)
	{
		$this->ctrl_id = $ctrlid;
	}

	public function app_file($appfile)
	{
		$this->phpfile = $appfile;
	}

	public function func_id($funcid)
	{
		$this->func_id = $funcid;
	}

	public function url_appid($appid='www')
	{
		$this->url_appid = $appid;
	}

	public function url($ctrl='index',$func='index',$ext='')
	{
		if($this->url_appid == 'www' && $this->urltype == 'rewrite'){
			return $this->url_rewrite($ctrl,$func,$ext);
		}
		return $this->url_default($ctrl,$func,$ext);
	}

	public function set_type($type='default')
	{
		if(!in_array($type,array('default','rewrite'))){
			$type = 'default';
		}
		$this->urltype = $type;
	}

	//保护字段
	public function protected_ctrl($info)
	{
		if(!$info){
			$info = array("js",'ajax','inp');
		}
		if(is_string($info)){
			$info = explode(",",$info);
		}
		$this->protected_id = $info;
	}

	public function url_default($ctrl='index',$func='index',$ext='')
	{
		if(in_array($ctrl,$this->protected_id)){
			return $this->url_ctrl($ctrl,$func,$ext);
		}
		$url = $this->base_url.$this->phpfile."?id=".$ctrl;
		if($func && preg_match("/^[a-z0-9A-Z\_\-]+$/u",$func)){
			$url .= substr($func,0,1) == "&" ? $func : '&cate='.$func;
		}
		if($ext && $ext != "&"){
			if(substr($ext,0,1) == "&"){
				$ext = substr($ext,1);
			}
			$url .= "&".$ext;
		}
		return $url;
	}

	protected function url_ctrl($ctrl='index',$func='index',$ext='')
	{
		$url = $this->base_url.$this->phpfile.'?';
		if($ctrl != 'index'){
			$url .= $this->ctrl_id.'='.$ctrl.'&';
		}
		if($func && $func != 'index'){
			$url .= $this->func_id.'='.$func.'&';
		}
		if($ext){
			$url .= $ext;
		}
		if(substr($url,-1) == "&" || substr($url,-1) == "?"){
			$url = substr($url,0,-1);
		}
		if($this->nocache){
			if(strpos($url,'?') === false){
				$url .= "?_noCache=".$this->nocache;
			}else{
				$url .= "&_noCache=".$this->nocache;
			}
		}
		return $url;
	}


	public function url_rewrite($ctrl='index',$func='index',$ext='')
	{
		$data = array();
		$rule_id = false;
		if($ctrl == 'project' || $ctrl == 'content'){
			$rule_id = $ctrl;
			$data['ctrl'] = $ctrl;
			if($rule_id == 'project' && $func && $func != 'index'){
				$data['cate'] = $func;
			}
		}
		if($ctrl && $this->protected_id && in_array($ctrl,$this->protected_id)){
			$rule_id = $ctrl;
			$data['ctrl'] = $ctrl;
			if($func && $func != 'index' && preg_match("/[a-zA-Z][a-zA-Z0-9\_\-\.]+/",$func)){
				$data['func'] = $func;
			}
		}
		if(!$rule_id){
			if(is_numeric($ctrl)){
				$rule_id = 'content';
				$data['id'] = $ctrl;
				$data['ctrl'] = 'content';
				if($func && $func != 'index' && is_numeric($func)){
					$data['pageid'] = $func;
				}
			}else{
				$this->id_list('identifier');
				$rule_id = $this->ilist[$ctrl] ? 'content' : 'project';
				$data['ctrl'] = $rule_id;
				$data['id'] = $ctrl;
				if($rule_id == 'project' && $func && $func != 'index'){
					$data['cate'] = $func;
				}
			}
		}
		if(!$rule_id){
			return $this->url_default($ctrl,$func,$ext);
		}
		if($ext){
			if(is_string($ext)){
				parse_str($ext,$tmp);
			}else{
				$tmp = $ext;
			}
			foreach($tmp as $key=>$value){
				$data[$key] = $value;
			}
		}
		$rs = false;
		foreach($this->rule_list as $key=>$value){
			if(!in_array($rule_id,$value['ctrl'])){
				continue;
			}
			if($data['func'] && $value['func'] && !in_array($data['func'],$value['func'])){
				continue;
			}
			if(!$data['func'] && $value['func']){
				continue;
			}
			if($value['var']){
				$chk = true;
				foreach($value['var'] as $k=>$v){
					if(!$data[$v]){
						$chk = false;
						break;
					}
				}
				if(!$chk){
					continue;
				}
			}
			$rs = $value;
			break;
		}
		if(!$rs){
			return $this->url_default($ctrl,$func,$ext);
		}
		$url = $rs['format'];
		foreach($data as $key=>$value){
			$url = str_replace('['.$key.']',$value,$url);
		}
		return $url;
	}


	private function set_rule($url)
	{
		$this->rule = $url;
	}

	private function _url_rule($ctrl='index',$func='index',$ext='')
	{
		if(!$this->rule)
		{
			return $this->_url_rewrite_default($ctrl,$func,$ext);
		}
		if($this->rule_id == 'content')
		{
			return $this->_url_content($ctrl,$func,$ext);
		}
		if($this->rule_id == 'project')
		{
			return $this->_url_project($ctrl,$func,$ext);
		}
		return $this->_url_rewrite_default($ctrl,$func,$ext);
	}

	private function _url_project($ctrl,$func='',$ext='')
	{
		$array = array('project_root'=>'','project'=>'','cate_root'=>'','cate'=>'','identifier'=>'','pageid'=>'');
		$project_rs = false;
		foreach($this->plist as $key=>$value)
		{
			if($value['identifier'] == $ctrl)
			{
				$project_rs = $value;
			}
		}
		$array['project'] = $project_rs['identifier'];
		if($project_rs['parent_id'])
		{
			$array['project_root'] = $this->plist[$project_rs['parent_id']]['identifier'];
		}
		if($project_rs['cate'] && $this->clist[$project_rs['cate']])
		{
			$array['cate_root'] = $this->clist[$project_rs['cate']]['identifier'];
		}
		if($func)
		{
			$array['cate'] = is_numeric($func) ? $this->clist[$func]['identifier'] : $func;
		}
		if($ext && is_string($ext))
		{
			$list = array();
			parse_str($ext,$list);
			$ext = $list;
		}
		if($ext && is_array($ext))
		{
			if($ext[$this->page_id])
			{
				$array['pageid'] = $ext[$this->page_id];
				unset($ext[$this->page_id]);
			}
			$ext = http_build_query($ext);
		}
		$url = $this->rule;
		foreach($array as $key=>$value)
		{
			$url = str_replace('{'.$key.'}',$value,$url);
		}
		$url = preg_replace("/(\/{2,})/","/",$url);
		if(substr($url,0,1) == '/')
		{
			$url = substr($url,1);
		}
		if(substr($url,-1) == '/')
		{
			$url = substr($url,0,-1);
		}
		$url = $this->base_url.$url;
		return $url;
	}

	private function _url_content($ctrl,$func='',$ext='')
	{
		$array = array('project_root'=>'','project'=>'','cate_root'=>'','cate'=>'','identifier'=>'','pageid'=>'');
		if(is_numeric($ctrl)){
			if(!$this->ilist[$ctrl]){
				$sql = "SELECT id,project_id,cate_id,identifier FROM ".$this->db->prefix."list WHERE id='".$ctrl."'";
				$rs = $this->db->get_one($sql);
			}else{
				$rs = $this->ilist[$ctrl];
			}
		}else{
			$rs = false;
			foreach($this->ilist as $key=>$value){
				if($value['identifier'] == $ctrl){
					$rs = $value;
				}
			}
		}
		if(!$rs)
		{
			return false;
		}
		$project_rs = $this->plist[$rs['project_id']];
		$array['identifier'] = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
		$array['project'] = $project_rs['identifier'];
		if($project_rs['parent_id'])
		{
			$parent_rs = $this->plist[$project_rs['parent_id']];
			$array['project_root'] = $parent_rs['identifier'];
		}
		if($project_rs['cate'])
		{
			$cate_root = $this->clist[$project_rs['cate']];
			$array['cate_root'] = $cate_root['identifier'];
		}
		if($rs['cate_id'])
		{
			$cate_rs = $this->clist[$rs['cate_id']];
			$array['cate'] = $cate_rs['identifier'];
		}
		$url = $this->rule;
		foreach($array as $key=>$value)
		{
			$url = str_replace('{'.$key.'}',$value,$url);
		}
		$url = preg_replace("/(\/{2,})/","/",$url);
		if(substr($url,0,1) == '/')
		{
			$url = substr($url,1);
		}
		if(substr($url,-1) == '/')
		{
			$url = substr($url,0,-1);
		}
		$url = $this->base_url.$url;
		return $url;
	}

	public function rules($rslist)
	{
		$this->rule_list = $rslist;
	}

	public function global_list()
	{
		$sql_1  = "SELECT id,project_id,cate_id,identifier FROM ".$this->db->prefix."list WHERE ";
		$sql_1 .= "site_id='".$this->site_id."' AND status=1 AND identifier!=''";
		$sql_2  = "SELECT id,parent_id,identifier FROM ".$this->db->prefix."cate WHERE ";
		$sql_2 .= "site_id='".$this->site_id."' AND status=1";
		$sql_3  = "SELECT id,parent_id,identifier FROM ".$this->db->prefix."project WHERE ";
		$sql_3 .= "site_id='".$this->site_id."' AND status=1";
		$cache_id = $this->cache->id($sql_1.'-'.$sql_2.'-'.$sql_3);
		$this->db->cache_set($cache_id);
		$rslist = $this->cache->get($cache_id);
		if($rslist){
			$this->ilist = $rslist['ilist'];
			$this->clist = $rslist['clist'];
			$this->plist = $rslist['plist'];
			return true;
		}
		$this->ilist = $this->db->get_all($sql_1,'id');
		$this->clist = $this->db->get_all($sql_2,'id');
		$this->plist = $this->db->get_all($sql_3,'id');
		$rslist = array('ilist'=>$this->ilist,'clist'=>$this->clist,'plist'=>$this->plist);
		$this->cache->save($cache_id,$rslist);
		return true;
	}

	public function id_list($pri='id')
	{
		$cache_id = $this->cache->id('url','id_list',$this->site_id,$pri);
		$rslist = $this->cache->get($cache_id);
		if($rslist){
			$this->ilist = $rslist;
			return $rslist;
		}else{
			$sql = "SELECT id,project_id,cate_id,identifier FROM ".$this->db->prefix."list WHERE ";
			$sql.= "site_id='".$this->site_id."' AND status=1 AND identifier!=''";
			$this->ilist = $this->db->get_all($sql,$pri);
			if($this->ilist && $cache_id){
				$this->cache->save($cache_id,$this->ilist);
			}
			return $this->ilist;
		}
	}

	public function cate_list()
	{
		$sql = "SELECT id,parent_id,identifier FROM ".$this->db->prefix."cate WHERE ";
		$sql.= "site_id='".$this->site_id."' AND status=1";
		$this->clist = $this->db->get_all($sql,'id');
	}

	public function project_list()
	{
		$sql = "SELECT id,parent_id,identifier FROM ".$this->db->prefix."project WHERE ";
		$sql.= "site_id='".$this->site_id."' AND status=1";
		$this->plist = $this->db->get_all($sql,'id');
	}

	private function _url_rewrite_default($ctrl='index',$func='index',$ext='')
	{
		$url = $this->base_url;
		if(!in_array($ctrl,$this->protected_id)){
			$url .= $ctrl;
			if($func) $url .= "/".$func;
			$url .= ".html";
			if($ext){
				$url .="?".$ext;
			}
			return $url;
		}
		if(!$ctrl){
			return $url;
		}
		$url .= $this->phpfile;
		//判断ctrl在
		if(in_array($ctrl,$this->protected_id)){
			$url .= "?".$this->ctrl_id."=".$ctrl;
			if($func) $url .= "&".$this->func_id."=".$func;
			if($ext && $ext != "&"){
				if(substr($ext,0,1) == "&") $ext = substr($ext,1);
				$url .= "&".$ext;
			}
			return $url;
		}
		$url .= "?id=".$ctrl;
		if($func && $func != "&"){
			$url .= substr($func,0,1) == "&" ? $func : '&cate='.$func;
		}
		if($ext && $ext != "&"){
			if(substr($ext,0,1) == "&") $ext = substr($ext,1);
			$url .= "&".$ext;
		}
		return $url;
	}

}

?>