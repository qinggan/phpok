<?php
/*****************************************************************************************
	文件： {phpok}/model/www/url_model.php
	备注： 伪静态网址生成及解析
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月03日 20时46分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class url_model extends url_model_base
{
	private $urltype = 'default';
	private $protected_id = array('js','ajax','inp');
	private $rule_list = false;
	private $rule = false;
	private $clist = false;
	private $ilist = false;
	private $plist = false;
	private $kids = array('{project_root}','{project}','{cate_root}','{cate}','{identifier}','{pageid}');
	private $type_ids = false;
	private $rule_id = false;
	
	public function __construct()
	{
		parent::__construct();
	}

	public function set_type($type='default')
	{
		if(!in_array($type,array('default','rewrite')))
		{
			$type = 'default';
		}
		$this->urltype = $type;
	}

	public function type_ids($info)
	{
		$this->type_ids = $info;
	}

	//保护字段
	public function protected_ctrl($info)
	{
		if(!$info)
		{
			$info = array("js",'ajax','inp');
		}
		if(is_string($info))
		{
			$info = explode(",",$info);
		}
		$this->protected_id = $info;
	}

	public function url($ctrl,$func,$ext)
	{
		if($this->urltype == 'default')
		{
			return $this->url_default($ctrl,$func,$ext);
		}
		return $this->url_rewrite($ctrl,$func,$ext);
	}

	public function url_default($ctrl='index',$func='index',$ext='')
	{
		if(in_array($ctrl,$this->protected_id))
		{
			return $this->url_ctrl($ctrl,$func,$ext);
		}
		$url = $this->base_url.$this->app_file."?id=".$ctrl;
		if($func && preg_match("/^[a-z0-9A-Z\_\-]+$/u",$func))
		{
			$url .= substr($func,0,1) == "&" ? $func : '&cate='.$func;
		}
		if($ext && $ext != "&")
		{
			if(substr($ext,0,1) == "&")
			{
				$ext = substr($ext,1);
			}
			$url .= "&".$ext;
		}
		return $url;
	}

	public function url_rewrite($ctrl='index',$func='index',$ext='')
	{
		$this->rule_id = in_array($ctrl,$this->type_ids) ? $ctrl : (is_numeric($ctrl) ? 'content' : 'project');
		if($this->rule_list && $this->rule_list[$this->rule_id])
		{
			$this->set_rule($this->rule_list[$this->rule_id]['urltype']);
			return $this->_url_rule($ctrl,$func,$ext);
		}
		return $this->_url_rewrite_default($ctrl,$func,$ext);
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
		if(is_numeric($ctrl))
		{
			if(!$this->ilist[$ctrl])
			{
				$sql = "SELECT id,project_id,cate_id,identifier FROM ".$this->db->prefix."list WHERE id='".$ctrl."'";
				$rs = $this->db->get_one($sql);
			}
			else
			{
				$rs = $this->ilist[$ctrl];
			}
		}
		else
		{
			$rs = false;
			foreach($this->ilist as $key=>$value)
			{
				if($value['identifier'] == $ctrl)
				{
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

	public function id_list()
	{
		$sql = "SELECT id,project_id,cate_id,identifier FROM ".$this->db->prefix."list WHERE ";
		$sql.= "site_id='".$this->site_id."' AND status=1 AND identifier!=''";
		$this->ilist = $this->db->get_all($sql,"id");
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
		if(!in_array($ctrl,$this->protected_id))
		{
			$url .= $ctrl;
			if($func) $url .= "/".$func;
			$url .= ".html";
			if($ext)
			{
				$url .="?".$ext;
			}
			return $url;
		}
		if(!$ctrl) return $url;
		$url .= $this->phpfile;
		//判断ctrl在
		if(in_array($ctrl,$this->protected_id))
		{
			$url .= "?".$this->ctrl_id."=".$ctrl;
			if($func) $url .= "&".$this->func_id."=".$func;
			if($ext && $ext != "&")
			{
				if(substr($ext,0,1) == "&") $ext = substr($ext,1);
				$url .= "&".$ext;
			}
			return $url;
		}
		$url .= "?id=".$ctrl;
		if($func && $func != "&")
		{
			$url .= substr($func,0,1) == "&" ? $func : '&cate='.$func;
		}
		if($ext && $ext != "&")
		{
			if(substr($ext,0,1) == "&") $ext = substr($ext,1);
			$url .= "&".$ext;
		}
		return $url;
	}
}
?>