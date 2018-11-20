<?php
/**
 * URL网址生成
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年10月01日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class url_model_base extends phpok_model
{
	protected $base_url = '';
	protected $ctrl_id = "c";
	protected $func_id = "f";
	protected $phpfile = 'index.php';
	protected $page_id = 'pageid';
	protected $nocache = '';
	private $protected_id = array('js','ajax','inp');
	private $url_appid = 'www';
	private $tmpdata = array();
	public $urltype = 'default';
	
	public function __construct()
	{
		parent::model();
		if($this->config['debug']){
			$this->nocache = '0.'.$this->time;
		}
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

	public function nocache($act=false)
	{
		if($act){
			$this->nocache = '0.'.$this->time;
		}
		return $this->nocache;
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
	public function protected_ctrl($info='')
	{
		if(!$info){
			return $this->protected_id;
		}
		if(is_string($info)){
			$info = explode(",",$info);
		}
		if($this->protected_id){
			$tmp = array_merge($this->protected_id,$info);
			$tmp = array_unique($tmp);
			$this->protected_id = $tmp;
		}else{
			$this->protected_id = $info;
		}
		return $this->protected_id;
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
		if(defined('PHPOK_SITE_ID')){
			$url .= "&siteId=".PHPOK_SITE_ID;
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
		if(defined('PHPOK_SITE_ID')){
			if(strpos($url,'?') === false){
				$url .= "?siteId=".PHPOK_SITE_ID;
			}else{
				$url .= "&siteId=".PHPOK_SITE_ID;
			}
		}
		return $url;
	}


	public function url_rewrite($ctrl='index',$func='index',$ext='')
	{
		$data = array();
		if($ext){
			$tmp = $ext;
			if(is_string($ext)){
				parse_str($ext,$tmp);
			}
			if($tmp && is_array($tmp)){
				foreach($tmp as $key=>$value){
					$data[$key] = $value;
				}
			}
		}
		$rule_id = false;
		if($ctrl == 'project' || $ctrl == 'content'){
			$rule_id = $ctrl;
			$data['ctrl'] = $ctrl;
			if($func && $func != 'index'){
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
				$rule_id = 'content';
				if($this->get_from_identifier($ctrl,'project')){
					$rule_id = 'project';
				}
				$data['ctrl'] = $rule_id;
				$data['id'] = $ctrl;
				if($func && $func != 'index'){
					$data['cate'] = $func;
				}
			}
		}
		if(!$rule_id){
			return $this->url_default($ctrl,$func,$ext);
		}
		if($data['cateid'] && !$data['cate']){
			$tmp = $this->get_from_id($data['cateid'],'cate');
			$data['cate'] = $tmp['identifier'];
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
			if($value['var'] && is_array($value['var'])){
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
		$extlist = array();
		$forbid = array('ctrl','func','cate','cateid','cate_id','cid','module','mid','project');
		foreach($data as $key=>$value){
			if(strpos($url,'['.$key.']') !== false){
				$url = str_replace('['.$key.']',rawurlencode($value),$url);
			}else{
				if(!in_array($key,$forbid)){
					$extlist[$key] = $value;
				}
			}
		}
		if($this->base_url){
			$url = $this->base_url.$url;
		}
		if($extlist && count($extlist)>0){
			$tmp = http_build_query($extlist);
			$url .= "?".$tmp;
		}
		if(defined('PHPOK_SITE_ID')){
			if(strpos($url,'?') === false){
				$url .= "?siteId=".PHPOK_SITE_ID;
			}else{
				$url .= "&siteId=".PHPOK_SITE_ID;
			}
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
		$project_rs = $this->get_from_identifier($ctrl,'project');
		if($project_rs){
			$array['project'] = $project_rs['identifier'];
			if($project_rs['parent_id']){
				$tmp = $this->get_from_id($project_rs['parent_id'],'project');
				$array['project_root'] = $tmp['identifier'];
			}
			if($project_rs['cate']){
				$tmp = $this->get_from_id($project_rs['cate'],'cate');
				if($tmp){
					$array['cate_root'] = $tmp['identifier'];
				}
			}
			if($func){
				$array['cate'] = $func;
				if(is_numeric($func)){
					$tmp = $this->get_from_id($func,'cate');
					if($tmp){
						$array['cate'] = $tmp['identifier'];
					}
				}
			}
		}
		if($ext && is_string($ext)){
			$list = array();
			parse_str($ext,$list);
			$ext = $list;
		}
		if($ext && is_array($ext)){
			if($ext[$this->page_id]){
				$array['pageid'] = $ext[$this->page_id];
				unset($ext[$this->page_id]);
			}
			$ext = http_build_query($ext);
		}
		$url = $this->rule;
		foreach($array as $key=>$value){
			$url = str_replace('{'.$key.'}',$value,$url);
		}
		$url = preg_replace("/(\/{2,})/","/",$url);
		if(substr($url,0,1) == '/'){
			$url = substr($url,1);
		}
		if(substr($url,-1) == '/'){
			$url = substr($url,0,-1);
		}
		$url = $this->base_url.$url;
		return $url;
	}

	private function _url_content($ctrl,$func='',$ext='')
	{
		$array = array('project_root'=>'','project'=>'','cate_root'=>'','cate'=>'','identifier'=>'','pageid'=>'');
		$rs = is_numeric($ctrl) ? $this->get_from_id($ctrl,'list') : $this->get_from_identifier($ctrl,'list');
		if(!$rs){
			return false;
		}
		$project_rs = $this->get_from_id($rs['project_id'],'project');
		if(!$project_rs){
			return false;
		}
		$array['identifier'] = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
		$array['project'] = $project_rs['identifier'];
		if($project_rs['parent_id']){
			$parent_rs = $this->get_from_id($project_rs['parent_id'],'project');
			if($parent_rs){
				$array['project_root'] = $parent_rs['identifier'];
			}
		}
		if($project_rs['cate']){
			$tmp = $this->get_from_id($project_rs['cate'],'cate');
			if($tmp){
				$array['cate_root'] = $tmp['identifier'];
			}
		}
		if($rs['cate_id']){
			$tmp = $this->get_from_id($rs['cate_id'],'cate');
			if($tmp){
				$array['cate'] = $tmp['identifier'];
			}
		}
		$url = $this->rule;
		foreach($array as $key=>$value){
			$url = str_replace('{'.$key.'}',$value,$url);
		}
		$url = preg_replace("/(\/{2,})/","/",$url);
		if(substr($url,0,1) == '/'){
			$url = substr($url,1);
		}
		if(substr($url,-1) == '/'){
			$url = substr($url,0,-1);
		}
		$url = $this->base_url.$url;
		return $url;
	}

	public function rules($rslist)
	{
		$this->rule_list = $rslist;
	}

	/**
	 * 取得标识
	 * @参数 $id 主键ID
	 * @参数 $type 类型，仅支持：list，cate，project
	**/
	public function get_from_id($id,$type='list')
	{
		return $this->_get_url_data($id,$type,'id');
	}

	/**
	 * 取得ID
	 * @参数 $identifier 标识
	 * @参数 $type 类型，仅支持：list，cate，project
	**/
	public function get_from_identifier($identifier,$type='list')
	{
		return $this->_get_url_data($identifier,$type,'identifier');
	}


	private function _get_url_data($id,$type='list',$pri='id')
	{
		$sql = '';
		if($type == 'list'){
			$sql = "SELECT id,parent_id,project_id,cate_id,identifier FROM ".$this->db->prefix."list WHERE site_id='".$this->site_id."' AND status=1 AND ".$pri."='".$id."'";
		}
		if($type == 'cate'){
			$sql = "SELECT id,parent_id,identifier FROM ".$this->db->prefix."cate WHERE site_id='".$this->site_id."' AND status=1 AND ".$pri."='".$id."'";
		}
		if($type == 'project'){
			$sql = "SELECT id,parent_id,identifier FROM ".$this->db->prefix."project WHERE site_id='".$this->site_id."' AND status=1 AND ".$pri."='".$id."'";
		}
		if(!$sql){
			return false;
		}
		$cache_id = $this->cache->id($sql);
		if($this->tmpdata && $this->tmpdata[$cache_id]){
			return $this->tmpdata[$cache_id];
		}
		$info = $this->db->get_one($sql);
		if(!$info){
			return false;
		}
		$this->tmpdata[$cache_id] = $info;
		return $info;
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