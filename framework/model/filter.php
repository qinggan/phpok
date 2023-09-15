<?php
/**
 * 筛选器
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年9月15日
 * @更新 2023年9月15日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class filter_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function start($data)
	{
		$filter = array();
		$urlist = $this->_url2list($data['url'],$data['page_rs'],$data['cate_rs']);
		if($data['page_rs'] && $data['page_rs']['cate'] && $data['page_rs']['filter_cate_status']){
			$this->filter_cate($filter,$data,$urlist);
		}
		$flist = $this->model('module')->fields_all($data['page_rs']['module']);
		if($flist){
			foreach($flist as $key=>$value){
				if(!$value['search'] || !$value['filter']){
					continue;
				}
				if($value['ext'] && is_string($value['ext'])){
					$value['ext'] = unserialize($value['ext']);
				}
				if($value['filter_content'] && trim($value['filter_content'])){
					$this->filter_content($filter,$data,$urlist,$value);
				}else{
					if($value['form_type'] == 'text'){
						$this->filter_text($filter,$data,$urlist,$value);
					}
					if($value['form_type'] == 'radio' || $value['form_type'] == 'checkbox' || $value['form_type'] == 'select' || $value['form_type'] == 'selectpage'){
						$this->filter_options($filter,$data,$urlist,$value);
					}
				}
			}
		}
		if($data['page_rs'] && $data['page_rs']['is_biz'] && $data['page_rs']['filter_price']){
			$this->filter_price($filter,$data,$urlist);
		}
		return $filter;
	}

	/**
	 * 价格筛选器
	**/
	private function filter_price(&$filter,$data,$urlist)
	{
		$highlight = true;
		$tmplist = array();
		if($data['page_rs']['filter_price_info']){
			$tmp = explode("\n",$data['page_rs']['filter_price_info']);
			$rslist = array();
			foreach($tmp as $key=>$value){
				$value = trim($value);
				if(!$value){
					continue;
				}
				$e = explode(":",$value);
				if(!$e[0]){
					continue;
				}
				$p = explode("-",$e[0]);
				$t = array('val'=>$e[0],"min"=>$p[0],'max'=>$p[1],"title"=>($e[1] ? $e[1] : $e[0]));
				$rslist[] = $t;
			}
			foreach($rslist as $key=>$value){
				$urldata = $urlist;
				$tmp = array();
				$tmp['title'] = $value['title'];
				if($value['val']){
					$urldata['price'] = array('min'=>$value['min'],'max'=>$value['max']);
				}
				
				$tmp['url'] = $this->_array2url($urldata);
				$tmp['identifier'] = 'price';
				$tmp['val'] = $value['val'];
				$tmp['urlext'] = "price[min]=".$value['min'].'&price[max]='.$value['max'];
				$tmp['highlight'] = false;
				if($data['price'] && $data['price']['min'] == $value['min'] && $data['price']['max'] == $value['max']){
					$tmp['highlight'] = true;
					$highlight = false;
				}
				$tmplist[] = $tmp;
			}
		}

		$title = $data['page_rs']['filter_price_title'] ? $data['page_rs']['filter_price_title'] : P_Lang('价格');
		$rootData = $urlist;
		if(isset($rootData['price'])){
			unset($rootData['price']);
		}
		$tmpdata = array('title'=>$title,'highlight'=>$highlight,'identifier'=>'price',"join"=>false,"user"=>true,'url'=>$this->_array2url($rootData),'list'=>$tmplist);
		$filter['price'] = $tmpdata;
	}

	/**
	 * 自定义内容筛选器
	**/
	private function filter_content(&$filter,$data,$urlist,$fields)
	{
		$tmp = explode("\n",$fields['filter_content']);
		$rslist = array();
		foreach($tmp as $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			$e = explode(":",$value);
			$t = array("val"=>$e[0],"title"=>($e[1] ? $e[1] : $e[0]));
			$rslist[] = $t;
		}
		$highlight = true;
		$tmplist = array();
		foreach($rslist as $key=>$value){
			$urldata = $urlist;
			$tmp = array();
			$tmp['title'] = $value['title'] ? $value['title'] : $value['val'];
			$urldata['ext'][$fields['identifier']] = $value['val'];
			$tmp['url'] = $this->_array2url($urldata);
			$tmp['identifier'] = 'ext['.$fields['identifier'].']';
			$tmp['val'] = $value['val'];
			$tmp['urlext'] = "ext[".$fields['identifier']."]=".rawurlencode($value['val']);
			$tmp['highlight'] = false;
			if($data['ext'] && $data['ext'][$fields['identifier']]){
				if($fields['filter'] == 2 && $fields['filter_join']){
					$m = explode($fields['filter_join'],$data['ext'][$fields['identifier']]);
					if(in_array($value['val'],$m)){
						$tmp['highlight'] = true;
						$highlight = false;
					}
				}else{
					if($data['ext'][$fields['identifier']] == $value['val']){
						$tmp['highlight'] = true;
						$highlight = false;
					}
				}
			}
			$tmplist[] = $tmp;
		}
		$rootData = $urlist;
		if(isset($rootData['ext']) && isset($rootData['ext'][$fields['identifier']])){
			unset($rootData['ext'][$fields['identifier']]);
		}
		$join = ($fields['filter_join'] && $fields['filter'] == 2) ? $fields['filter_join'] : false;
		$title = $fields['filter_title'] ? $fields['filter_title'] : $fields['title'];
		$tmpdata = array('title'=>$title,'highlight'=>$highlight,'identifier'=>$fields['identifier'],"join"=>$join,"user"=>false,'url'=>$this->_array2url($rootData),'list'=>$tmplist);
		$filter[$fields['identifier']] = $tmpdata;
	}

	/**
	 * 单选，多选，下拉筛选器
	**/
	private function filter_options(&$filter,$data,$urlist,$fields)
	{
		if(!$fields['ext']){
			return false;
		}
		$dt = $data['dt'];
		$opt_list = $fields['ext']["option_list"] ? explode(":",$fields['ext']["option_list"]) : array('default','');
		$rslist = opt_rslist($opt_list[0],$opt_list[1],$fields['ext']['ext_select']);
		if(!$rslist){
			return false;
		}
		// 进行数据过滤
		$rslist = $this->_options_clear($rslist,$data,$fields);
		if(!$rslist){
			return false;
		}
		$highlight = true;
		$tmplist = array();
		foreach($rslist as $key=>$value){
			$urldata = $urlist;
			$tmp = array();
			$tmp['title'] = $value['title'] ? $value['title'] : $value['val'];
			$urldata['ext'][$fields['identifier']] = $value['val'];
			$tmp['url'] = $this->_array2url($urldata);
			$tmp['identifier'] = 'ext['.$fields['identifier'].']';
			$tmp['val'] = $value['val'];
			$tmp['urlext'] = "ext[".$fields['identifier']."]=".rawurlencode($value['val']);
			$tmp['highlight'] = false;
			if($data['ext'] && $data['ext'][$fields['identifier']]){
				if($fields['filter'] == 2 && $fields['filter_join']){
					$m = explode($fields['filter_join'],$data['ext'][$fields['identifier']]);
					if(in_array($value['val'],$m)){
						$tmp['highlight'] = true;
						$highlight = false;
					}
				}else{
					if($data['ext'][$fields['identifier']] == $value['val']){
						$tmp['highlight'] = true;
						$highlight = false;
					}
				}
			}
			$tmplist[] = $tmp;
		}
		$rootData = $urlist;
		if(isset($rootData['ext']) && isset($rootData['ext'][$fields['identifier']])){
			unset($rootData['ext'][$fields['identifier']]);
		}
		$join = ($fields['filter_join'] && $fields['filter'] == 2) ? $fields['filter_join'] : false;
		$title = $fields['filter_title'] ? $fields['filter_title'] : $fields['title'];
		$tmpdata = array('title'=>$title,'highlight'=>$highlight,'identifier'=>$fields['identifier'],"join"=>$join,"user"=>false,'url'=>$this->_array2url($rootData),'list'=>$tmplist);
		$filter[$fields['identifier']] = $tmpdata;
	}

	/**
	 * 文本框筛选值
	**/
	private function filter_text(&$filter,$data,$urlist,$fields)
	{
		$tbl = $this->db->prefix;
		$dt = $data['dt'];
		$mlist = $data['mlist'];
		$keywords_list = array();
		if($dt['keywords']){
			$tmp = str_replace(array("\r","\n","\t","　"," ","&nbsp;","&amp;",'"',"'")," ",$dt['keywords']);
			$tmp = preg_replace("/(\x20{2,})/"," ",$tmp);# 去除多余空格，只保留一个空格
			$keywords_list = explode(" ",$tmp);
		}
		if($data['module'] && $data['module']['mtype']){
			$tbl .= $data['module']['id'];
			$sql  = "SELECT count(id) as total,".$fields['identifier']." as title FROM ".$tbl." WHERE project_id='".$data['page_rs']['id']."'";
			if($mlist && $keywords_list){
				$orlist = array();
				foreach($mlist as $key=>$value){
					if($value['search'] == 1){
						foreach($keywords_list as $k=>$v){
							$orlist[] = $value['identifier']."='".$v."'";
						}
					}
					if($value['search'] == 2){
						foreach($keywords_list as $k=>$v){
							$orlist[] = $value['identifier']." LIKE '%".$v."%'";
						}
					}
				}
				if($orlist && count($orlist)>0){
					$sql .= " AND (".implode(" OR ",$orlist).")";
				}
			}
			$sql .= " GROUP BY ".$fields['identifier'];
		}else{
			$tbl .= 'list_'.$data['module']['id'];
			$sql  = " SELECT count(l.id) as total,e.".$fields['identifier']." as title FROM ".$this->db->prefix."list l ";
			$sql .= " LEFT JOIN ".$tbl." e ON(l.id=e.id) ";
			$sql .= " WHERE l.project_id='".$data['page_rs']['id']."' AND l.site_id='".$data['page_rs']['site_id']."' AND l.status=1 AND l.hidden=0 ";
			$sql .= " AND e.".$fields['identifier']."!='' AND e.".$fields['identifier']." IS NOT NULL";
			if($mlist && $keywords_list){
				$orlist = array();
				foreach($mlist as $key=>$value){
					if($value['search'] == 1){
						foreach($keywords_list as $k=>$v){
							$orlist[] = 'e.'.$value['identifier']."='".$v."'";
						}
					}
					if($value['search'] == 2){
						foreach($keywords_list as $k=>$v){
							$orlist[] = 'e.'.$value['identifier']." LIKE '%".$v."%'";
						}
					}
				}
				if($orlist && count($orlist)>0){
					$sql .= " AND (".implode(" OR ",$orlist).")";
				}
			}
			$sql .= " GROUP BY e.".$fields['identifier'];
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$highlight = true;
		$tmplist = array();
		foreach($rslist as $key=>$value){
			$urldata = $urlist;
			$tmp = array();
			$tmp['title'] = $value['title'];
			$urldata['ext'][$fields['identifier']] = $value['title'];
			$tmp['url'] = $this->_array2url($urldata);
			$tmp['identifier'] = 'ext['.$fields['identifier'].']';
			$tmp['val'] = $value['title'];
			$tmp['urlext'] = "ext[".$fields['identifier']."]=".rawurlencode($value['title']);
			$tmp['highlight'] = false;
			$tmp['count'] = $value['total'];
			if($data['ext'] && $data['ext'][$fields['identifier']]){
				if($fields['filter'] == 2 && $fields['filter_join']){
					$m = explode($fields['filter_join'],$data['ext'][$fields['identifier']]);
					if(in_array($value['title'],$m)){
						$tmp['highlight'] = true;
						$highlight = false;
					}
				}else{
					if($data['ext'][$fields['identifier']] == $value['title']){
						$tmp['highlight'] = true;
						$highlight = false;
					}
				}
			}
			$tmplist[] = $tmp;
		}
		$rootData = $urlist;
		if(isset($rootData['ext']) && isset($rootData['ext'][$fields['identifier']])){
			unset($rootData['ext'][$fields['identifier']]);
		}
		$join = ($fields['filter_join'] && $fields['filter'] == 2) ? $fields['filter_join'] : false;
		$title = $fields['filter_title'] ? $fields['filter_title'] : $fields['title'];
		$tmpdata = array('title'=>$title,'identifier'=>$fields['identifier'],'highlight'=>$highlight,"join"=>$join,"user"=>false,'url'=>$this->_array2url($rootData),'list'=>$tmplist);
		$filter[$fields['identifier']] = $tmpdata;
	}

	/**
	 * 分类筛选器
	**/
	private function filter_cate(&$filter,$data,$urlist)
	{
		// 如查有关键字搜索，使用最终类目模式
		if($data['dt'] && $data['dt']['keywords']){
			$subcatelist = $this->_cate_clear($data);
			if(!$subcatelist){
				return false;
			}
			$highlight = true;
			$title = $data['page_rs']['filter_cate'] ? $data['page_rs']['filter_cate'] : $data['cate_root']['title'];
			$tmplist = array();
			foreach($subcatelist as $key=>$value){
				$urldata = $urlist;
				$tmp = array();
				$tmp['title'] = $value['title'];
				$urldata['cate'] = $value['identifier'];
				$tmp['url'] = $this->_array2url($urldata);
				$tmp['identifier'] = 'cate';
				$tmp['val'] = $value['identifier'];
				$tmp['urlext'] = "cate=".$value['identifier'];
				$tmp['highlight'] = false;
				if($data['cate_rs'] && $data['cate_rs']['id'] == $value['id']){
					$tmp['highlight'] = true;
					$highlight = false;
				}
				$tmplist[] = $tmp;
			}
			$rootData = $urlist;
			if(isset($rootData['cate'])){
				unset($rootData['cate']);
			}
			$tmpdata = array("title"=>$title,"highlight"=>$highlight,'identifier'=>'cate',"join"=>false,"user"=>false,"url"=>$this->_array2url($rootData),"list"=>$tmplist);
			$filter['catelist'] = $tmpdata;
			return true;
		}
		$subcatelist = phpok("_subcate","cateid=".$data['page_rs']['cate']);
		if(!$subcatelist){
			return false;
		}
		$highlight = true;
		$title = $data['page_rs']['filter_cate'] ? $data['page_rs']['filter_cate'] : $data['cate_root']['title'];
		$tmplist = array();
		foreach($subcatelist as $key=>$value){
			$urldata = $urlist;
			$tmp = array();
			$tmp['title'] = $value['title'];
			$urldata['cate'] = $value['identifier'];
			$tmp['url'] = $this->_array2url($urldata);
			$tmp['identifier'] = 'cate';
			$tmp['val'] = $value['identifier'];
			$tmp['urlext'] = "cate=".$value['identifier'];
			$tmp['highlight'] = false;
			if(($data['cate_rs'] && $data['cate_rs']['id'] == $value['id']) || ($data['cate_parent_rs'] && $data['cate_parent_rs']['id'] == $value['id'])){
				$tmp['highlight'] = true;
				$highlight = false;
			}
			$tmplist[] = $tmp;
		}
		$rootData = $urlist;
		if(isset($rootData['cate'])){
			unset($rootData['cate']);
		}
		$tmpdata = array("title"=>$title,"highlight"=>$highlight,'identifier'=>'cate',"join"=>false,"user"=>false,"url"=>$this->_array2url($rootData),"list"=>$tmplist);
		$filter['catelist'] = $tmpdata;
		if($data['cate_parent_rs']){
			$subcatelist = phpok("_subcate","cateid=".$data['cate_parent_rs']['id']);
			if(!$subcatelist){
				return false;
			}
			$highlight = true;
			$title = $data['cate_parent_rs']['title'];
			$tmplist = array();
			foreach($subcatelist as $key=>$value){
				$urldata = $urlist;
				$tmp = array();
				$tmp['title'] = $value['title'];
				$urldata['cate'] = $value['identifier'];
				$tmp['url'] = $this->_array2url($urldata);
				$tmp['identifier'] = 'cate';
				$tmp['val'] = $value['identifier'];
				$tmp['urlext'] = "cate=".$value['identifier'];
				$tmp['highlight'] = false;
				if($data['cate_rs'] && $data['cate_rs']['id'] == $value['id']){
					$tmp['highlight'] = true;
					$highlight = false;
				}
				$tmplist[] = $tmp;
			}
			$tmpdata = array("title"=>$title,"highlight"=>$highlight,'identifier'=>'cate',"join"=>false,"user"=>false,"url"=>$data['cate_parent_rs']['url'],"list"=>$tmplist);
			$filter['subcatelist'] = $tmpdata;
		}
		if($data['cate_rs']){
			$subcatelist = phpok("_subcate","cateid=".$data['cate_rs']['id']);
			if(!$subcatelist){
				return false;
			}
			$highlight = true;
			$title = $data['cate_rs']['title'];
			$tmplist = array();
			foreach($subcatelist as $key=>$value){
				$urldata = $urlist;
				$tmp = array();
				$tmp['title'] = $value['title'];
				$urldata['cate'] = $value['identifier'];
				$tmp['url'] = $this->_array2url($urldata);
				$tmp['identifier'] = 'cate';
				$tmp['val'] = $value['identifier'];
				$tmp['urlext'] = "cate=".$value['identifier'];
				$tmp['highlight'] = false;
				if($data['cate_rs'] && $data['cate_rs']['id'] == $value['id']){
					$tmp['highlight'] = true;
					$highlight = false;
				}
				$tmplist[] = $tmp;
			}
			$tmpdata = array("title"=>$title,"highlight"=>$highlight,'identifier'=>'cate',"join"=>false,"user"=>false,"url"=>$this->_array2url($urlist),"list"=>$tmplist);
			$filter['subcatelist'] = $tmpdata;
		}
	}

	/**
	 * 将网址格式化为数组
	**/
	private function _url2list($url,$page_rs=array(),$cate_rs=array())
	{
		$tmp = parse_url($url);
		$data = array();
		$data['id'] = $page_rs['identifier'];
		if($cate_rs){
			$data['cate'] = $cate_rs['identifier'];
		}
		if($tmp['query']){
			parse_str($tmp['query'], $output);
			foreach($output as $key=>$value){
				$data[$key] = $value;
			}
		}
		return $data;
	}

	/**
	 * 数组转成URL
	**/
	private function _array2url($urlist)
	{
		$urlext = array();
		foreach($urlist as $key=>$value){
			if($key == 'id' || $key == 'cate'){
				continue;
			}
			if(is_array($value)){
				foreach($value as $k=>$v){
					if($v != ''){
						$urlext[] = $key.'['.$k.']='.rawurlencode($v);
					}
				}
			}else{
				$urlext[] = $key.'='.rawurlencode($value);
			}
		}
		$tmp = implode('&',$urlext);
		return $this->url($urlist['id'],$urlist['cate'],$tmp);
	}

	/**
	 * 清除类目中无用数据
	**/
	private function _cate_clear($data)
	{
		$tbl = $this->db->prefix;
		$dt = $data['dt'];
		$mlist = $data['mlist'];
		$keywords_list = array();
		if($dt['keywords']){
			$tmp = str_replace(array("\r","\n","\t","　"," ","&nbsp;","&amp;",'"',"'")," ",$dt['keywords']);
			$tmp = preg_replace("/(\x20{2,})/"," ",$tmp);# 去除多余空格，只保留一个空格
			$keywords_list = explode(" ",$tmp);
		}
		if($data['module'] && $data['module']['mtype']){
			$tbl .= $data['module']['id'];
			$sql  = "SELECT count(id) as total,cate_id as val FROM ".$tbl." WHERE project_id='".$data['page_rs']['id']."'";
			if($mlist && $keywords_list){
				$orlist = array();
				foreach($mlist as $key=>$value){
					if($value['search'] == 1){
						foreach($keywords_list as $k=>$v){
							$orlist[] = $value['identifier']."='".$v."'";
						}
					}
					if($value['search'] == 2){
						foreach($keywords_list as $k=>$v){
							$orlist[] = $value['identifier']." LIKE '%".$v."%'";
						}
					}
				}
				if($orlist && count($orlist)>0){
					$sql .= " AND (".implode(" OR ",$orlist).")";
				}
			}
			$sql .= " GROUP BY cate_id";
		}else{
			$tbl .= 'list_'.$data['module']['id'];
			$sql  = " SELECT count(l.id) as total,l.cate_id as val FROM ".$this->db->prefix."list l ";
			$sql .= " LEFT JOIN ".$tbl." e ON(l.id=e.id) ";
			$sql .= " WHERE l.project_id='".$data['page_rs']['id']."' AND l.site_id='".$data['page_rs']['site_id']."' AND l.status=1 AND l.hidden=0 ";
			$sql .= " AND l.cate_id!=0";
			if($mlist && $keywords_list){
				$orlist = array();
				foreach($mlist as $key=>$value){
					if($value['search'] == 1){
						foreach($keywords_list as $k=>$v){
							$orlist[] = 'e.'.$value['identifier']."='".$v."'";
						}
					}
					if($value['search'] == 2){
						foreach($keywords_list as $k=>$v){
							$orlist[] = 'e.'.$value['identifier']." LIKE '%".$v."%'";
						}
					}
				}
				if($orlist && count($orlist)>0){
					$sql .= " AND (".implode(" OR ",$orlist).")";
				}
			}
			$sql .= " GROUP BY l.cate_id";
		}
		$newlist = $this->db->get_all($sql);
		if(!$newlist){
			return false;
		}
		$vals = array();
		foreach($newlist as $key=>$value){
			$vals[] = $value['val'];
		}
		$vals = array_unique($vals);
		$rslist = $this->model('cate')->list_ids($vals,$data['page_rs']['identifier']);
		if(!$rslist){
			return false;
		}
		return $rslist;
	}

	/**
	 * 清除下拉选项中不存在的值
	**/
	private function _options_clear($rslist,$data,$fields)
	{
		$tbl = $this->db->prefix;
		$dt = $data['dt'];
		$mlist = $data['mlist'];
		$keywords_list = array();
		if($dt['keywords']){
			$tmp = str_replace(array("\r","\n","\t","　"," ","&nbsp;","&amp;",'"',"'")," ",$dt['keywords']);
			$tmp = preg_replace("/(\x20{2,})/"," ",$tmp);# 去除多余空格，只保留一个空格
			$keywords_list = explode(" ",$tmp);
		}
		if($data['module'] && $data['module']['mtype']){
			$tbl .= $data['module']['id'];
			$sql  = "SELECT count(id) as total,".$fields['identifier']." as val FROM ".$tbl." WHERE project_id='".$data['page_rs']['id']."'";
			if($mlist && $keywords_list){
				$orlist = array();
				foreach($mlist as $key=>$value){
					if($value['search'] == 1){
						foreach($keywords_list as $k=>$v){
							$orlist[] = $value['identifier']."='".$v."'";
						}
					}
					if($value['search'] == 2){
						foreach($keywords_list as $k=>$v){
							$orlist[] = $value['identifier']." LIKE '%".$v."%'";
						}
					}
				}
				if($orlist && count($orlist)>0){
					$sql .= " AND (".implode(" OR ",$orlist).")";
				}
			}
			$sql .= " GROUP BY ".$fields['identifier'];
		}else{
			$tbl .= 'list_'.$data['module']['id'];
			$sql  = " SELECT count(l.id) as total,e.".$fields['identifier']." as val FROM ".$this->db->prefix."list l ";
			$sql .= " LEFT JOIN ".$tbl." e ON(l.id=e.id) ";
			$sql .= " WHERE l.project_id='".$data['page_rs']['id']."' AND l.site_id='".$data['page_rs']['site_id']."' AND l.status=1 AND l.hidden=0 ";
			$sql .= " AND e.".$fields['identifier']."!='' AND e.".$fields['identifier']." IS NOT NULL";
			if($mlist && $keywords_list){
				$orlist = array();
				foreach($mlist as $key=>$value){
					if($value['search'] == 1){
						foreach($keywords_list as $k=>$v){
							$orlist[] = 'e.'.$value['identifier']."='".$v."'";
						}
					}
					if($value['search'] == 2){
						foreach($keywords_list as $k=>$v){
							$orlist[] = 'e.'.$value['identifier']." LIKE '%".$v."%'";
						}
					}
				}
				if($orlist && count($orlist)>0){
					$sql .= " AND (".implode(" OR ",$orlist).")";
				}
			}
			$sql .= " GROUP BY e.".$fields['identifier'];
		}
		$newlist = $this->db->get_all($sql);
		if(!$newlist){
			return false;
		}
		$vals = array();
		foreach($newlist as $key=>$value){
			$vals[] = $value['val'];
		}
		$vals = array_unique($vals);
		foreach($rslist as $key=>$value){
			if(!in_array($value['val'],$vals)){
				unset($rslist[$key]);
			}
		}
		if(!$rslist){
			return false;
		}
		return $rslist;
	}
}