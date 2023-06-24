<?php
/**
 * 调用中心类
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 4.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2017年04月02日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class phpok_call extends _init_auto
{
	private $mlist;
	private $_cache = array();
	public function __construct()
	{
		parent::__construct();
		$this->mlist = get_class_methods($this);
		$this->model('project')->site_id($this->site['id']);
		$this->lib('form')->appid('www');
	}

	private function load_phpoklist($id,$siteid=0)
	{
		$this->model('call')->site_id($siteid);
		if($this->_cache && $this->_cache[$id]){
			return $this->_cache[$id];
		}
		$tmplist = $this->model('call')->all($siteid);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$this->_cache['datacall-'.$value['identifier']] = $value;
			}
		}
		if($this->_cache && $this->_cache[$id]){
			return $this->_cache[$id];
		}
		return false;
	}

	private function _phpok_sys_func()
	{
		$array = array();
		$array[] = 'arc';
		$array[] = 'arclist';
		$array[] = 'cate';
		$array[] = 'catelist';
		$array[] = 'cate_id';
		$array[] = 'comment';
		$array[] = 'condition';
		$array[] = 'fields';
		$array[] = 'keywords';
		$array[] = 'menu';
		$array[] = 'options';
		$array[] = 'parent';
		$array[] = 'project';
		$array[] = 'res';
		$array[] = 'reslist';
		$array[] = 'sql';
		$array[] = 'subcate';
		$array[] = 'sublist';
		$array[] = 'subtitle';
		$array[] = 'taglist';
		$array[] = 'total';
		$array[] = 'user';
		$array[] = 'userlist';
		$array[] = 'module'; //模块基本信息
		return array_unique($array);
	}

	//执行数据调用
	public function phpok($id,$rs="")
	{
		if(!$id){
			return false;
		}
		//格式化参数
		if($rs && is_string($rs)){
			parse_str($rs,$rs);
		}
		//扩展参数
		if(!$rs){
			$rs = array('site'=>$this->site['id']);
		}
		if($this->is_mobile){
			$rs['_mobile'] = true;
		}
		if(!isset($rs['site']) || (isset($rs['site']) && !$rs['site'])){
			$rs['site'] = $this->site['id'];
		}
		if($rs['site']){
			$rs['site'] = intval($rs['site']);
		}
		if($rs['site'] != $this->site['id']){
			$siteinfo = $this->model('site')->get_one($rs['site']);
			$baseurl = '//'.$siteinfo['domain'].$siteinfo['dir'];
		}else{
			$siteinfo = $this->site;
			$baseurl = $this->url;
		}
		if(!$siteinfo){
			return false;
		}
		if($rs['cate_id'] && !isset($rs['cateid'])){
			$rs['cateid'] = $rs['cate_id'];
		}
		//定义 _baseurl
		$rs['_baseurl'] = $baseurl;
		$this->model('url')->base_url($baseurl);
		if(substr($id,0,1) != '_'){
			$call_rs = $this->load_phpoklist('datacall-'.$id,$rs['site']);
			if(!$call_rs){
				return false;
			}
			if($rs && is_array($rs)){
				$call_rs = array_merge($call_rs,$rs);
			}
		}else{
			$list = $this->_phpok_sys_func();
			$id = substr($id,1);
			if($id == "arclist"){
				if(isset($rs['is_list']) && (!$rs['is_list'] || $rs['is_list'] === 'false')){
					$rs['is_list'] = false;
				}else{
					$rs['is_list'] = true;
				}
			}
			if(!$id || !in_array($id,$list)){
				return false;
			}
			$call_rs = array_merge($rs,array('type_id'=>$id));
		}
		$func = '_'.$call_rs['type_id'];
		if(!in_array($func,$this->mlist)){
			return false;
		}
		//禁用缓存获取数据
		if(isset($call_rs['cache']) && ($call_rs['cache'] == 'false' || !$call_rs['cache'])){
			return $this->$func($call_rs);
		}
		$cache_id = $this->cache->id($call_rs);
		$info = $this->cache->get($cache_id);
		if($info){
			return $info;
		}
		$tmps = array('list','user','cate','project','reply','tag');
		$db_keys = array();
		foreach($tmps as $key=>$value){
			$db_keys[] = $this->db->prefix.$value;
		}
		$this->cache->key_list($cache_id,$db_keys);
		//指定数据库里的缓存
		$this->db->cache_set($cache_id); //初始化缓存ID
		return $this->$func($call_rs,$cache_id);
	}

	//生成查询条件
	private function _condition($rs)
	{
		if($rs['project'] && is_array($rs['project'])){
			$project = $rs['project'];
			unset($rs['project']);
		}else{
			$projecg = $this->phpok('_project',array('pid'=>$rs['pid'],'site'=>$rs['site']));
			if(!$project || !$project['status'] || !$project['module']){
				return false;
			}
		}
		if(!$project){
			return false;
		}
		if($rs['module_fields'] && is_array($rs['module_fields'])){
			$flist = $rs['module_fields'];
			unset($rs['module_fields']);
		}else{
			$flist = $this->model('module')->fields_all($project['module']);
		}
		return $this->_arc_condition($rs,$flist,$project);
	}
	//自定义SQL
	private function _sql($rs,$cache_id='')
	{
		$rs['sqlinfo'] = str_replace(array('&#39;','&quot;','&apos;','&#34;'),array("'",'"',"'",'"'),$rs['sqlinfo']);
		if($rs['is_list'] && $rs['is_list'] != 'false'){
			$rslist = $this->db->get_all($rs['sqlinfo']);
		}else{
			$rslist = $this->db->get_one($rs['sqlinfo']);
		}
		if($cache_id && $rslist){
			$this->cache->save($cache_id,$rslist);
		}
		return $rslist;
	}

	//读取文章列表
	private function _arclist($rs,$cache_id='')
	{
		if(!$rs['pid'] && !$rs['phpok']){
			return false;
		}
		if(!$rs['pid']){
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site'],true);
			if(!$tmp || $tmp['type'] != 'project'){
				unset($tmp,$rs);
				return false;
			}
			$rs['pid'] = $tmp['id'];
			unset($tmp);
		}
		if(!$rs['pid']){
			unset($rs);
			return false;
		}
		$project = $this->phpok("_project",array('pid'=>$rs['pid'],'site'=>$rs['site']));
		if(!$project || !$project['status'] || !$project['module']){
			return false;
		}
		$module = $this->phpok('_module',array('id'=>$project['module']));
		if(!$module || $module['status'] != 1){
			return false;
		}
		//如果使用了独立模块
		if($module['mtype']){
			return $this->_arclist_single($rs,$cache_id,$project,$module);
		}
		$array = array('project'=>$project);
		$this->model('list')->is_biz($project['is_biz']);
		if($project['cate']){
			$this->model('list')->multiple_cate($project['cate_multiple']);
		}else{
			$this->model('list')->multiple_cate(0);
		}
		if($project['cate'] || $rs['cateid']){
			$cateid = $rs['cateid'] ? $rs['cateid'] : $project['cate'];
			$cate = $this->_cate(array("pid"=>$rs['pid'],"cateid"=>$cateid,'site'=>$rs['site']));
			if($cate){
				$array['cate'] = $cate;
				$rs['cateid'] = $cateid;
			}
		}
		$flist = $this->model('module')->fields_all($project['module']);
		$nlist = array();
		if($project['cate'] && $project['cate_multiple']){
			$list_fields = $this->model('fields')->list_fields();
			$field = 'DISTINCT l.id';
			foreach($list_fields as $key=>$value){
				if($value == 'id'){
					continue;
				}
				$field .= ",l.".$value;
			}
		}else{
			$field = "l.*";
		}
		if($rs['fields'] && $rs['fields'] != '*'){
			$tmp = explode(",",$rs['fields']);
			if($flist){
				foreach($flist as $key=>$value){
					if(in_array($value['identifier'],$tmp)){
						$field .= ",ext.".$value['identifier'];
						$nlist[$value['identifier']] = $value;
					}
				}
			}
		}else{
			if($flist){
				foreach($flist as $key=>$value){
					$field .= ",ext.".$value['identifier'];
					$nlist[$value['identifier']] = $value;
				}
			}
		}
		if($project['is_biz']){
			$field.= ",b.price,b.currency_id,b.weight,b.volume,b.unit,b.is_virtual,b.qty";
		}
		$condition = $this->_arc_condition($rs,$flist,$project);
		$array['total'] = $this->model('list')->arc_count($project['module'],$condition);
		if(!$array['total']){
			if($cache_id){
				$this->cache->save($cache_id,$array);
			}
			return $array;
		}
		$orderby = $rs['orderby'] ? $rs['orderby'] : $project['orderby'];
		if(!$rs['is_list']){
			$rs['psize'] = 1;
		}
		$offset = $rs['offset'] ? intval($rs['offset']) : 0;
		$psize = $rs['is_list'] ? intval($rs['psize']) : 1;
		$rslist = $this->model('list')->arc_all($project,$condition,$field,$offset,$psize,$orderby);
		if(!$rslist){
			if($cache_id){
				$this->cache->save($cache_id,$array);
			}
			return $array;
		}
		$ids = array();
		foreach($rslist as $key=>$value){
			$ids[] = $value['id'];
			foreach($nlist as $k=>$v){
				$myval = $this->lib('form')->show($v,$value[$k]);
				if($v['form_type'] == 'url' && $value[$k]){
					$tmp = unserialize($value[$k]);
					$link = $this->site['url_type'] == 'rewrite' ? $tmp['rewrite'] : $tmp['default'];
					if(!$link){
						$link = $tmp['default'];
					}
					if(!$value['url'] && $k != 'url' && $link){
						$value['url'] = $link;
					}
				}
				$value[$k] = $myval;
			}
			if(!$value['url']){
				$tmpid = $value['identifier'] ? $value['identifier'] : $value['id'];
				if($this->site['url_type'] == 'rewrite'){
					$tmpext = '&project='.$project['identifier'];
					if($project['cate']){
						$tmpext.= '&cateid='.$value['cate_id'];
					}
					$value['url'] = $this->url($tmpid,'',$tmpext,'www');
				}else{
					$value['url'] = $this->url($tmpid,'','','www');
				}
			}
			//针对标题，生成一个带HTML格式的样式
			if(!isset($value['title_format'])){
				$html = '<a href="'.$value['url'].'" title="'.$value['title'].'"';
				if($value['style']){
					$html .= ' style="'.$value['style'].'"';
				}
				if($value['target']){
					$html .= ' target="'.$value['target'].'"';
				}
				$html .= '>'.$value['title'].'</a>';
				$value['title_format'] = $html;
			}
			$rslist[$key] = $value;
		}
		//格式化标签组件
		$tag_all = $this->model('tag')->list_all($ids,$rs['site']);
		if($tag_all){
			foreach($rslist as $key=>$value){
				$value['tag'] = $tag_all[$value['id']] ? $tag_all[$value['id']] : array();
				$rslist[$key] = $value;
			}
		}
		//显示快评
		if($project['quick-comment-status']){
			$clicklist = $this->model('click')->get_all($ids,'list',$this->session->val('user_id'));
			if($clicklist){
				foreach($rslist as $key=>$value){
					if(!isset($clicklist[$value['id']])){
						continue;
					}
					$value['click_list'] = $clicklist[$value['id']];
					$rslist[$key] = $value;
				}
			}
		}
		if($rs['in_sub'] && strtolower($rs['in_sub']) != 'false'){
			$list = array();
			foreach($rslist as $key=>$value){
				if(!$value['parent_id']){
					$value['sonlist'] = array();
					foreach($rslist as $k=>$v){
						if($v['parent_id'] == $value['id']){
							$value['sonlist'][$v['id']] = $v;
						}
					}
					$list[] = $value;
				}
			}
			$rslist = $list;
			unset($list);
		}
		$this->data('rslist',$rslist);
		$this->data('pid',$array['project']['id']);
		$this->node('PHPOK_arclist');
		$rslist = $this->data('rslist');
		if(!$rs['is_list']){
			$array['rs'] = current($rslist);
		}else{
			$tmplist = array();
			foreach($rslist as $key=>$value){
				$tmplist[] = $value;
			}
			$array['rslist'] = $tmplist;
			$array['ids'] = $ids;
			unset($tmplist);
		}
		unset($rslist,$project);
		if($cache_id){
			//将缓存的ID和索引挂起来
			$this->cache->key_list($cache_id,$this->db->prefix.'list');
			$this->cache->save($cache_id,$array);
		}
		return $array;
	}

	private function _arclist_single($rs,$cache_id='',$project='',$module='')
	{
		if(!$project){
			$project = $this->phpok("_project",array('pid'=>$rs['pid'],'site'=>$rs['site']));
			if(!$project || !$project['status'] || !$project['module']){
				return false;
			}
		}
		if(!$module){
			$module = $this->model('module')->get_one($project['module']);
			if(!$module || $module['status'] != 1){
				return false;
			}
		}
		$array = array('project'=>$project);
		$flist = $this->model('module')->fields_all($project['module']);
		if(!$flist){
			$flist = array();
		}
		$nlist = array();
		$field = "id,project_id,site_id,cate_id,status,hidden,dateline,sort,hits";
		if($rs['fields'] && $rs['fields'] != '*'){
			$tmp = explode(",",$rs['fields']);
			foreach($flist as $key=>$value){
				if(in_array($value['identifier'],$tmp)){
					$field .= ",".$value['identifier'];
					$nlist[$value['identifier']] = $value;
				}
			}
		}else{
			foreach($flist as $key=>$value){
				$field .= ",".$value['identifier'];
				$nlist[$value['identifier']] = $value;
			}
		}
		$condition = $this->_arc_condition_single($rs,$flist);
		$array['total'] = $this->model('list')->single_count($project['module'],$condition);
		if(!$array['total']){
			if($cache_id){
				$this->cache->save($cache_id,$array);
			}
			return $array;
		}
		$orderby = $rs['orderby'] ? $rs['orderby'] : $project['orderby'];
		if(!$rs['is_list']){
			$rs['psize'] = 1;
		}
		$offset = $rs['offset'] ? intval($rs['offset']) : 0;
		$psize = $rs['is_list'] ? intval($rs['psize']) : 1;
		$rslist = $this->model('list')->single_list($project['module'],$condition,$offset,$psize,$orderby,$field);
		if(!$rslist){
			if($cache_id){
				$this->cache->save($cache_id,$array);
			}
			return $array;
		}
		foreach($rslist as $key=>$value){
			foreach($nlist as $k=>$v){
				$myval = $this->lib('form')->show($v,$value[$k]);
				$value[$k] = $myval;
			}
			$rslist[$key] = $value;
		}
		if(!$rs['is_list']){
			$array['rs'] = current($rslist);
		}else{
			$tmplist = array();
			foreach($rslist as $key=>$value){
				$tmplist[] = $value;
			}
			$array['rslist'] = $tmplist;
			unset($tmplist);
		}
		unset($rslist,$project);
		if($cache_id){
			$this->cache->key_list($cache_id,$this->db->prefix.$module['id']);
			$this->cache->save($cache_id,$array);
		}
		return $array;
	}

	private function _arc_condition_single($rs,$fields='')
	{
		$condition = "site_id='".intval($rs['site'])."' ";
		if($rs['pid']){
			$condition .= "AND project_id=".intval($rs['pid'])." ";
		}
		if(!$rs['is_usercp']){
			$condition .= " AND hidden=0 ";
		}
		if($rs['cateid']){
			$condition .= " AND cate_id='".$rs['cateid']."' ";
		}
		if(!isset($rs['not_status']) || $rs['not_status'] != 2){
			$condition .= " AND status=1 ";
		}
		if($rs['notin']){
			$tmp = explode(",",$rs['notin']);
			foreach($tmp as $key=>$value){
				if(!$value || !trim($value) || !intval($value)){
					unset($tmp[$key]);
					continue;
				}
			}
			$condition .= " AND id NOT IN(".(implode(",",$tmp)).") ";
		}
		if($rs['idin']){
			$tmp = explode(",",$rs['idin']);
			foreach($tmp as $key=>$value){
				if(!$value || !trim($value) || !intval($value)){
					unset($tmp[$key]);
					continue;
				}
			}
			$condition .= " AND id IN(".(implode(",",$tmp)).") ";
		}
		if($rs['keywords'] && $fields && is_array($fields)){
			$keywords = str_replace(" ","%",$rs['keywords']);
			$k_condition = array();
			foreach($fields as $k=>$v){
				if($v['search'] && ($v['search'] == 1 || $v['search'] == 2)){
					if($v['search'] == 1){
						if(!in_array($v['field_type'],array('int','float'))){
							$k_condition[] = " ".$v['identifier']."='".$keywords."' ";
						}
						//$k_condition[] = " ".$v['identifier']."='".$keywords."' ";
					}else{
						$k_condition[] = " ".$v['identifier']." LIKE '%".$keywords."%' ";
					}
				}
			}
			$condition .= " AND (".implode(" OR ",$k_condition).") ";
			unset($k_condition,$keywords);
		}
		if($rs['fields_need']){
			$list = explode(",",$rs['fields_need']);
			$tmp_int = array('int','float','smallint','mediumint','bigint','tinyint','decimal','double');
			foreach($list as $key=>$value){
				$tmp = explode(".",$value);
				$f = $tmp[1] ? $tmp[1] : $tmp[0];
				if(in_array($fields[$f]['field_type'],$tmp_int)){
					$condition .= " AND ".$value." != 0";
				}else{
					$condition .= " AND ".$value." != ''";
				}
			}
			unset($list);
		}
		if($rs['sqlext']){
			$condition .= " AND ".$rs['sqlext'];
		}
		if($rs['ext'] && is_array($rs['ext'])){
			foreach($rs['ext'] as $key=>$value){
				$key = stripslashes($key);
				$key = str_replace(array("'",'"'),'',$key);
				$condition .= " AND ".$key."='".$value."' ";
			}
		}
		//判断是否有扩展字段
		if($fields && is_array($fields)){
			foreach($fields as $key=>$value){
				$tmpid = 'e_'.$value['identifier'];
				if(!$rs[$tmpid] || !$value['search']){
					continue;
				}
				if($value['search'] == 2){
					if($value['form_type'] == 'title' && !is_numeric($rs[$tmpid])){
						$tmp = $this->model('id')->id($rs[$tmpid],$rs['site'],true);
						if($tmp && $tmp['type'] == 'content'){
							$condition .= " AND ".$value['identifier']." LIKE '%".$tmp['id']."%' ";
						}
					}else{
						$condition .= " AND ".$value['identifier']." LIKE '%".$rs[$tmpid]."%' ";
					}
				}elseif($value['search'] == 3){
					$separator = $value['search_separator'] ? $value['search_separator'] : ',';
					$tmp = explode($separator,$rs[$tmpid]);
					if($tmp[0]){
						$condition .= " AND ".$value['identifier']." >='".$tmp[0]."' ";
					}
					if($tmp[1]){
						$condition .= " AND ".$value['identifier']." <='".$tmp[1]."' ";
					}
				}else{
					if($value['form_type'] == 'title' && !is_numeric($rs[$tmpid])){
						$tmp = $this->model('id')->id($rs[$tmpid],$rs['site'],true);
						if($tmp && $tmp['type'] == 'content'){
							$condition .= " AND ".$value['identifier']."='".$tmp['id']."' ";
						}
					}else{
						$condition .= " AND ".$value['identifier']."='".$rs[$tmpid]."' ";
					}
				}
			}
		}
		return $condition;
	}

	private function _arc_condition($rs,$fields='',$project='')
	{
		$condition  = " l.site_id='".$rs['site']."' ";
		if(!$rs['is_usercp']){
			$condition .= "AND l.hidden=0 ";
		}
		if($rs['pid']){
			$condition .= " AND l.project_id=".intval($rs['pid'])." ";
		}
		if($rs['not_status'] == 2){
			//当 not_status 值为 2 时，忽略所有状态
		}elseif($rs['not_status'] == 1 || $rs['not_status'] == true){
			if($this->session->val('user_id')){
				$condition .= " AND (l.status=1 OR (l.user_id=".intval($this->session->val('user_id'))." AND l.status=0)) ";
			}else{
				$condition .= " AND l.status=1 ";
			}
		}else{
			$condition .= " AND l.status=1 ";
		}
		if($rs['notin']){
			$tmp = explode(",",$rs['notin']);
			foreach($tmp as $key=>$value){
				if(!$value || !trim($value) || !intval($value)){
					unset($tmp[$key]);
					continue;
				}
			}
			$condition .= " AND l.id NOT IN(".(implode(",",$tmp)).") ";
		}
		if(!$rs['in_sub'] || $rs['in_sub'] == 'false'){
			$condition .= " AND l.parent_id=0 ";
		}
		if($rs['cate']){
			$tmp = $this->model('id')->id($rs['cate'],$rs['site'],true);
			if($tmp['type'] == 'cate') $rs['cateid'] = $tmp['id'];
		}
		$rs['cateid'] = $this->_ids($rs['cateid']);
		if($rs['cateid']){
			if(strpos($rs['cateid'],',') !== false){
				if($project['cate_multiple']){
					$condition .= " AND lc.cate_id IN(".$rs['cateid'].") ";
				}else{
					$condition .= " AND l.cate_id IN(".$rs['cateid'].") ";
				}
			}else{
				$cate_all = $this->model('cate')->cate_all($rs['site']);
				$array = array($rs['cateid']);
				$this->model('cate')->cate_ids($array,$rs['cateid'],$cate_all);
				if($project && $project['cate']){
					if($project['cate_multiple']){
						$condition .= " AND lc.cate_id IN(".implode(",",$array).") ";
					}else{
						$condition .= " AND l.cate_id IN(".implode(",",$array).") ";
					}
				}else{
					$condition .= " AND (lc.cate_id IN(".implode(",",$array).") OR l.cate_id IN(".implode(",",$array).")) ";
				}
			}
			unset($cate_all,$array);
		}
		//绑定某个用户
		if($rs['user_id']){
			$rs['user_id'] = $this->_ids($rs['user_id']);
			$condition .= strpos($rs['user_id'],",") === false ? " AND l.user_id='".intval($rs['user_id'])."'" : " AND l.user_id IN(".$rs['user_id'].")";
		}
		if($rs['attr']){
			$condition.= " AND l.attr LIKE '%".$rs['attr']."%' ";
		}
		if($rs['idin']){
            $rs['idin'] = $this->_ids($rs['idin']);
            if($rs['idin']){
	            $condition .= " AND l.id IN(".(implode(",",$tmp)).") ";
            }
		}
		if($rs['tag']){
			$list = explode(",",$rs['tag']);
			$tag_condition = array();
			foreach($list as $key=>$value){
				if($value && trim($value)){
					$tag_condition [] = " l.tag LIKE '%".trim($value)."%'";
				}
			}
			if($tag_condition){
				$condition .= " AND (".implode(" OR ",$tag_condition).")";
			}
			unset($tag_condition,$list);
		}
		if($rs['keywords'] && trim($rs['keywords'])){
			$rs['keywords'] = trim($rs['keywords']);
			$kwlist = explode(" ",$rs['keywords']);
			$search_fields = array('l.seo_title','l.seo_keywords','l.seo_desc','l.title','l.tag');
			if($fields && is_array($fields)){
				foreach($fields as $k=>$v){
					if(!$v['search']){
						continue;
					}
					$search_fields[] = 'ext.'.$v['identifier'];
				}
			}
			$k_condition = array();
			$search_field = 'CONCAT('.implode(",",$search_fields).')';
			foreach($kwlist as $key=>$value){
				$value = trim($value);
				if($value == ''){
					continue;
				}
				$k_condition [] = " ".$search_field." LIKE '%".$value."%'";
			}
			unset($kwlist);
			if($k_condition && count($k_condition)>0){
				$condition .= "AND ".implode(" AND ",$k_condition)." ";
				unset($k_condition);
			}
		}
		if($rs['fields_need']){
			$list = explode(",",$rs['fields_need']);
			$tmp_int = array('int','float','smallint','mediumint','bigint','tinyint','decimal','double');
			foreach($list as $key=>$value){
				$tmp = explode(".",$value);
				$f = $tmp[1] ? $tmp[1] : $tmp[0];
				if(in_array($fields[$f]['field_type'],$tmp_int)){
					$condition .= " AND ".$value." != 0";
				}else{
					$condition .= " AND ".$value." != ''";
				}
			}
			unset($list);
		}
		if($rs['sqlext']){
			$condition .= " AND ".$rs['sqlext'];
		}
		if($rs['ext'] && is_array($rs['ext'])){
			foreach($rs['ext'] as $key=>$value){
				$key = stripslashes($key);
				$key = str_replace(array("'",'"'),'',$key);
				$condition .= " AND ext.".$key."='".$value."' ";
			}
		}
		//判断是否有扩展字段
		if($fields && is_array($fields)){
			foreach($fields as $key=>$value){
				$tmpid = 'e_'.$value['identifier'];
				if(!$rs[$tmpid] || !$value['search']){
					continue;
				}
				if($value['search'] == 2){
					if($value['form_type'] == 'title' && !is_numeric($rs[$tmpid])){
						$tmp = $this->model('id')->id($rs[$tmpid],$this->site_id,true);
						if($tmp && $tmp['type'] == 'content'){
							$condition .= " AND ext.".$value['identifier']." LIKE '%".$tmp['id']."%' ";
						}
					}else{
						$condition .= " AND ext.".$value['identifier']." LIKE '%".$rs[$tmpid]."%' ";
					}
				}elseif($value['search'] == 3){
					$separator = $value['search_separator'] ? $value['search_separator'] : ',';
					$tmp = explode($separator,$rs[$tmpid]);
					if($tmp[0]){
						$condition .= " AND ext.".$value['identifier']." >='".$tmp[0]."' ";
					}
					if($tmp[1]){
						$condition .= " AND ext.".$value['identifier']." <='".$tmp[1]."' ";
					}
				}else{
					if($value['form_type'] == 'title' && !is_numeric($rs[$tmpid])){
						$tmp = $this->model('id')->id($rs[$tmpid],$this->site_id,true);
						if($tmp && $tmp['type'] == 'content'){
							$condition .= " AND ext.".$value['identifier']."='".$tmp['id']."' ";
						}
					}else{
						$condition .= " AND ext.".$value['identifier']."='".$rs[$tmpid]."' ";
					}
				}
			}
		}
		unset($rs);
		return $condition;
	}

	//格式化扩展数据
	private function _format_ext_all($rslist)
	{
		$res = '';
		foreach($rslist as $key=>$value){
			if($value['form_type'] == 'url' && $value['content']){
				$value['content'] = unserialize($value['content']);
				$url = $this->site['url_type'] == 'rewrite' ? $value['content']['rewrite'] : $value['content']['default'];
				if(!$url) $url = $value['content']['default'];
				$value['content'] = $url;
				//绑定扩展自定义url
				if(!$rslist['url']) $rslist['url'] = array('form_type'=>'text','content'=>$url);
			}elseif($value['form_type'] == 'upload' && $value['content']){
				$tmp = explode(',',$value['content']);
				foreach($tmp as $k=>$v){
					$v = intval($v);
					if($v) $res[] = $v;
				}
			}elseif($value['form_type'] == 'editor' && $value['content']){
				if($value['ext']){
					$value['ext'] = unserialize($value['ext']);
				}
				$value['content'] = str_replace('[:page:]','',$value['content']);
				$value['content'] = $this->lib('ubb')->to_html($value['content'],false);
			}
			$rslist[$key] = $value;
		}
		//格式化内容数据，并合并附件数据
		$flist = "";
		foreach($rslist as $key=>$value){
			$flist[$key] = $value;
			$rslist[$key] = $value['content'];
		}
		if($res && is_array($res)){
			$res = $this->_res_info2($res);
		}
		$rslist = $this->_format($rslist,$flist,$res);
		unset($flist,$res);
		return $rslist;
	}


	private function _subtitle($rs)
	{
		if(!$rs['tid'] && !$rs['phpok']){
			unset($rs);
			return false;
		}
		if(!$rs['tid']){
			$rs['tid'] = $rs['phpok'];
		}
		$idlist = $this->model('list')->subtitle_ids($rs['tid']);
		if(!$idlist){
			return false;
		}
		$rslist = array();
		foreach($idlist as $key=>$value){
			$tmp = $rs;
			unset($tmp['phpok'],$tmp['id']);
			$tmp['title_id'] = $value;
			$rslist[$key] = $this->_arc($tmp);
		}
		return $rslist;
	}

	private function _total($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']){
			unset($rs);
			return false;
		}
		if(!$rs['pid']){
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site'],true);
			if(!$tmp || $tmp['type'] != 'project'){
				unset($tmp,$rs);
				return false;
			}
			$rs['pid'] = $tmp['id'];
			unset($tmp);
		}
		if(!$rs['pid']){
			unset($rs);
			return false;
		}
		$project = $this->phpok('_project',array('pid'=>$rs['pid'],'site'=>$rs['site']));
		if(!$project || !$project['status'] || !$project['module']){
			return false;
		}
		$flist = $this->model('module')->fields_all($project['module']);
		$condition = $this->_arc_condition($rs,$flist,$project);
		return $this->model('list')->arc_count($project['module'],$condition);
	}

	//读取单篇文章
	private function _arc($param,$cache_id='')
	{
		$tmpid = isset($param['phpok']) ? $param['phpok'] : (isset($param['title_id']) ? $param['title_id'] : $param['id']);
		if(!$tmpid){
			return false;
		}
		if(isset($param['pid']) && isset($param['is_single']) && ($param['is_single'] == 1 || $param['is_single'] == 'true' || $param['is_single'] == true)){
			$project = $this->model('project')->get_one($param['pid'],false);
			if($project && $project['module']){
				$module = $this->model('module')->get_one($project['module']);
				if($module && $module['mtype']){
					$arc = $this->model('list')->single_one($tmpid,$module['id']);
					if(!$arc){
						return false;
					}
					$flist = $this->model('module')->fields_all($module['id']);
					if($flist){
						foreach($flist as $key=>$value){
							$arc[$value['identifier']] = $this->lib('form')->show($value,$arc[$value['identifier']]);
						}
					}
					$this->data('arc',$arc);
					$this->node('PHPOK_arc');
					$arc = $this->data('arc');
					return $arc;
				}
			}
		}
		$need_status = isset($param['not_status']) ? false : true;
		$arc = $this->model('content')->get_one($tmpid,$need_status,$param['site']);
		if(!$arc){
			return false;
		}
		if($arc['user_id']){
			$arc['user'] = $this->model('user')->get_one($arc['user_id']);
		}
		$project = $this->model('project')->project_one($arc['site_id'],$arc['project_id']);
		if(!$project){
			return false;
		}
		if(!$arc['module_id']){
			$url_id = $arc['identifier'] ? $arc['identifier'] : $arc['id'];
			$arc['url'] = $this->url($url_id,'','project='.$project['identifier'],'www');
			$this->data('arc',$arc);
			$this->node('PHPOK_arc');
			$arc = $this->data('arc');
			return $arc;
		}
		$cate = array();
		if($arc['cate_id']){
			$cate = $this->call->phpok('_cate',array('pid'=>$arc['project_id'],'cateid'=>$arc['cate_id']));
		}
		//针对点击事件的
		if($project['quick-comment-status']){
			$clicklist = $this->model('click')->get_all($arc['id'],'list',$this->session->val('user_id'));
			if($clicklist && !isset($clicklist[$arc['id']])){
				$arc['click_list'] = $clicklist[$arc['id']];
			}
		}
		//读取这个主题可能涉及到的Tag
		$arc['tag'] = $this->model('tag')->tag_list($arc['id'],'list');
		$flist = $this->model('module')->fields_all($arc['module_id']);
		if(!$flist){
			$url_id = $arc['identifier'] ? $arc['identifier'] : $arc['id'];
			$arc['url'] = $this->url($url_id,'','project='.$project['identifier'],'www');
			$this->data('arc',$arc);
			$this->node('PHPOK_arc');
			$arc = $this->data('arc');
			return $arc;
		}
		$taglist = array('tag'=>$arc['tag'],'list'=>array('title'=>$arc['title']));
		//格式化扩展内容
		foreach($flist as $key=>$value){
			//指定分类
			if($value['form_type'] == 'editor'){
				$value['pageid'] = isset($param['pageid']) ? intval($param['pageid']) : 0;
			}
			$arc[$value['identifier']] = $this->lib('form')->show($value,$arc[$value['identifier']]);
			if($value['form_type'] == 'url' && $arc[$value['identifier']] && $value['identifier'] != 'url'){
				if(!$arc['url']){
					$arc['url'] = $arc[$value['identifier']];
				}
			}
			//针对编辑器内容的格式化
			if($value['form_type'] == 'editor'){
				if(is_array($arc[$value['identifier']])){
					$arc[$value['identifier'].'_pagelist'] = $arc[$value['identifier']]['pagelist'];
					$arc['_'.$value['identifier']] = $arc[$value['identifier']]['list'];
					$arc[$value['identifier']] = $arc[$value['identifier']]['content'];
				}
				if($value['ext']){
					$ext = unserialize($value['ext']);
					if($ext['inc_tag'] && $arc['tag']){
						$taglist['list'][$value['identifier']] = $arc[$value['identifier']];
						$arc[$value['identifier']] = $this->model('tag')->tag_format($arc['tag'],$arc[$value['identifier']]);
					}
				}
			}
		}
		//如果未绑定网址
		if(!isset($arc['url'])){
			$url_id = $arc['identifier'] ? $arc['identifier'] : $arc['id'];
			$tmpext = '';
			if($cate){
				$tmpext = "cate=".$cate['identifier']."&cateid=".$cate['id'];
			}
			$arc['url'] = $this->url($url_id,'',$tmpext,'www');
		}
		//读取分类树
		$arc['_catelist'] = $this->model('cate')->ext_catelist($arc['id']);
		if(!$arc['_catelist']){
			$this->data('arc',$arc);
			$this->node('PHPOK_arc');
			$arc = $this->data('arc');
			return $arc;
		}
		$cate['url'] = $this->url($project['identifier'],$cate['identifier'],'','www');
		$arc['cate'] = $cate;
		$tmplist = array();
		foreach($arc['_catelist'] as $key=>$value){
			$tmplist[] = 'cate-'.$value['id'];
		}
		$tmplist = array_unique($tmplist);
		$tmplist = $this->model('ext')->get_all($tmplist,true);
		if(!$tmplist){
			foreach($arc['_catelist'] as $key=>$value){
				$arc['_catelist'][$key]['url'] = $this->url($project['identifier'],$value['identifier'],'','www');
			}
			$this->data('arc',$arc);
			$this->node('PHPOK_arc');
			$arc = $this->data('arc');
			return $arc;
		}
		//执行
		foreach($arc['_catelist'] as $k=>$v){
			$cate_tmp = isset($tmplist['cate-'.$v['id']]) ? $tmplist['cate-'.$v['id']] : false;
			if(!$cate_tmp){
				$arc['_catelist'][$k]['url'] = $this->url($project['identifier'],$v['identifier'],'','www');
				continue;
			}
			$tmp = array_merge($cate_tmp,$v);
			if(!$tmp['url']){
				$tmp['url'] = $this->url($project['identifier'],$value['identifier'],'','www');
			}
			$arc['_catelist'][$k] = $tmp;
		}
		$this->data('arc',$arc);
		$this->node('PHPOK_arc');
		$arc = $this->data('arc');
		return $arc;
	}

	//取得项目信息
	private function _project($rs,$cache_id='')
	{
		if(!$rs['pid'] && !$rs['phpok']){
			return false;
		}
		if(!$rs['pid']){
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site'],true);
			if(!$tmp || $tmp['type'] != 'project'){
				unset($tmp,$rs);
				return false;
			}
			$rs['pid'] = $tmp['id'];
			unset($tmp);
		}
		if(!$rs['pid']){
			return false;
		}
		$project = $this->model('project')->get_one($rs['pid'],false);
		if(!$project || !$project['status']){
			unset($rs);
			return false;
		}
		$project_tmp = $this->model('ext')->ext_all('project-'.$rs['pid'],true);
		$project['tag'] = $this->model('tag')->tag_list($project['id'],'project',$project['site_id']);
		$taglist = array('tag'=>$project['tag'],'list'=>array('title'=>$project['title']));
		//格式化扩展内容
		if($project_tmp){
			foreach($project_tmp as $key=>$value){
				$project_ext[$value['identifier']] = $this->lib('form')->show($value);
				if($value['form_type'] == 'url' && !$project['url'] && $value['content']){
					$project['url'] = $project_ext[$value['identifier']];
				}
				//针对编辑器内容的格式化
				if($value['form_type'] == 'editor'){
					if(is_array($project_ext[$value['identifier']])){
						$project_ext[$value['identifier'].'_pagelist'] = $project_ext[$value['identifier']]['pagelist'];
						$project_ext['_'.$value['identifier']] = $project_ext[$value['identifier']]['list'];
						$project_ext[$value['identifier']] = $project_ext[$value['identifier']]['content'];
					}
					if($value['ext']){
						$ext = unserialize($value['ext']);
						if($ext['inc_tag'] && $project['tag']){
							$taglist['list'][$value['identifier']] = $project_ext[$value['identifier']];
							$project_ext[$value['identifier']] = $this->model('tag')->tag_format($project['tag'],$project_ext[$value['identifier']]);
						}
					}
				}
			}
			$project = array_merge($project_ext,$project);
			unset($project_ext,$project_tmp);
		}
		unset($rs);
		if(!isset($project['url'])){
			$project['url'] = $this->url($project['identifier'],'','','www');
		}
		if($project['tag']){
			$project['tag'] = $this->model('tag')->tag_filter($taglist,$project['id'],'project');
		}
		//针对项目标题，生成一个带HTML格式的样式
		if(!isset($project['title_format'])){
			$html = '<a href="'.$project['url'].'" title="'.$project['title'].'"';
			if($project['style']){
				$html .= ' style="'.$project['style'].'"';
			}
			if(isset($project['target'])){
				$html .= ' target="'.$project['target'].'"';
			}
			$html .= '>'.$project['title'].'</a>';
			$project['title_format'] = $html;
		}
		if($cache_id){
			$this->cache->key_list($cache_id,$this->db->prefix.'project');
			$this->cache->save($cache_id,$project);
		}
		return $project;
	}

	private function _cate_total(&$count,$rslist=array(),$totals=array(),$parent_id=0)
	{
		if($rslist && is_array($rslist)){
			foreach($rslist as $key=>$value){
				if($value['parent_id'] == $parent_id && $totals[$value['id']]){
					$count += $totals[$value['id']];
					$this->_cate_total($count,$rslist,$totals,$value['id']);
				}
			}
		}
	}

	//读取分类树
	private function _catelist($rs,$cache_id='')
	{
		if(!$rs['pid'] && !$rs['phpok']){
			return false;
		}
		if(!$rs['pid']){
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site'],true);
			if(!$tmp || $tmp['type'] != 'project'){
				unset($tmp,$rs);
				return false;
			}
			$rs['pid'] = $tmp['id'];
			unset($tmp);
		}
		if(!$rs['pid']){
			return false;
		}
		$project_rs = $this->phpok('_project',array('pid'=>$rs['pid'],'site'=>$rs['site']));
		if(!$project_rs || !$project_rs['status'] || !$project_rs['cate']){
			return false;
		}
		if($rs['cate']){
			$tmp = $this->model('id')->id($rs['cate'],$rs['site'],true);
			if($tmp && $tmp['type'] == 'cate'){
				$rs['cateid'] = $tmp['id'];
			}
		}
		if(!$rs['cateid']){
			$rs['cateid'] = $project_rs['cate'];
		}
		$orderby = '';
		if($rs['orderby']){
			$orderby = $rs['orderby'];
		}
		$cate_all = $this->model('cate')->cate_all($rs['site'],true,$orderby);
		if(!$cate_all){
			return false;
		}
		$total = $this->model('list')->cate_total($project_rs['id']);
		if(!$total){
			$total = array();
		}
		foreach($cate_all as $key=>$value){
			$tmp = $total[$value['id']] ? $total[$value['id']] : 0;
			$this->_cate_total($tmp,$cate_all,$total,$value['id']);
			$value['total'] = $tmp;
			$cate_all[$key] = $value;
		}
		$list = array();
		$this->model('data')->cate_sublist($list,$project_rs['cate'],$cate_all,$project_rs['identifier']);
		if(!$list || !is_array($list) || count($list)<1){
			return false;
		}
		//格式化分类
		$array = array('all'=>$list,'project'=>$project_rs);
		$array['cate'] = $this->_cate(array('pid'=>$project_rs['id'],'cateid'=>$rs['cateid'],"site"=>$rs['site']));
		$array['cate']['total'] = $cate_all[$rs['cateid']]['total'];
		//读子分类
		$i = -1;
		$start = $rs['offset'] ? intval($rs['offset']) : 0;
		$psize = $rs['psize'] ? intval($rs['psize']) : 0;
		foreach($list as $key=>$value){
			if($value['parent_id'] == $rs['cateid']){
				$i++;
				if($i >= $start){
					if($psize){
						if($i<($psize+$start)){
							$array['sublist'][$value['id']] = $value;
						}
					}else{
						$array['sublist'][$value['id']] = $value;
					}
				}
			}
		}
		//取得分类树
		$tree = array();
		$i = -1;
		foreach($list as $key=>$value){
			if($value['parent_id'] == $rs['cateid']){
				$i++;
				if($i >= $start){
					if($psize){
						if($i<($psize+$start)){
							$tree[$value['id']] = $value;
							$this->model('data')->_tree($tree[$value['id']]['sublist'],$list,$value['id'],$start,$psize);
						}
					}else{
						$tree[$value['id']] = $value;
						$this->model('data')->_tree($tree[$value['id']]['sublist'],$list,$value['id'],$start,$psize);
					}
				}
			}
		}
		$array['tree'] = $tree;
		if($cache_id){
			$this->cache->key_list($cache_id,$this->db->prefix.'cate');
			$this->cache->save($cache_id,$array);
		}
		return $array;
	}

	//读取当前分类信息
	private function _cate($rs,$cache_id='')
	{
		if(!$rs['cateid'] && !$rs['phpok'] && !$rs['cate']){
			return false;
		}
		if(!$rs['cateid']){
			$identifier = $rs['cate'] ? $rs['cate'] : $rs['phpok'];
			$tmp = $this->model('id')->id($identifier,$rs['site'],true);
			if(!$tmp || $tmp['type'] != 'cate'){
				unset($tmp,$rs);
				return false;
			}
			$rs['cateid'] = $tmp['id'];
			unset($tmp,$identifier);
		}
		$cate = $this->model('cate')->get_one($rs['cateid']);
		if(!$cate){
			unset($rs);
			return false;
		}
		if($cate['tag']){
			$cate['tag'] = $this->model('tag')->tag_html($cate['tag']);
		}
		if($cate['url']){
			if($cache_id){
				$this->cache->save($cache_id,$cate);
			}
			return $cate;
		}
		if(!$rs['pid'] && $rs['phpok']){
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site'],true);
			if(!$tmp || $tmp['type'] != 'project'){
				unset($tmp,$rs);
				return false;
			}
			$rs['pid'] = $tmp['id'];
			unset($tmp);
		}
		if(!$rs['pid']){
			return $cate;
		}
		$project = $this->model('project')->project_one($rs['site'],$rs['pid']);
		$cate['url'] = $this->url($project['identifier'],$cate['identifier'],'','www');
		if($cache_id){
			$this->cache->key_list($cache_id,$this->db->prefix.'cate');
			$this->cache->save($cache_id,$cate);
		}
		return $cate;
	}

	private function _cate_id($rs)
	{
		return $this->_cate($rs);
	}

	//取得项目扩展字段
	private function _fields($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']){
			return false;
		}
		if(!$rs['pid']){
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site'],true);
			if(!$tmp || $tmp['type'] != 'project'){
				return false;
			}
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']){
			return false;
		}
		$project_rs = $this->model('project')->project_one($rs['site'],$rs['pid']);
		if(!$project_rs || !$project_rs['module'] || !$project_rs['status']){
			return false;
		}
		$array = array();
		if($rs['in_title']){
			$tmp_id = $rs['prefix'].'title';
			$tmp_title = $project_rs['alias_title'] ? $project_rs['alias_title'] : '主题';
			$array['title'] = array('id'=>0,"module_id"=>$project_rs['module'],'title'=>$tmp_title,'identifier'=>$tmp_id,'field_type'=>'varchar','form_type'=>'text','format'=>'safe','taxis'=>1,'width'=>'300','content'=>$rs['info']['title']);
		}
		$flist = $this->model('module')->fields_all($project_rs['module']);
		if($flist){
			foreach($flist as $key=>$value){
				if($value['ext']){
					$value['ext'] = unserialize($value['ext']);
				}
				if(!$value['is_front']){
					continue;
				}
				//禁止游客上传时
				if($value['form_type'] == 'upload' && !$this->site['upload_guest'] && !$_SESSION['user_id']){
					continue;
				}
				//禁止用户上传时
				if($value['form_type'] == 'upload' && !$this->site['upload_user'] && $_SESSION['user_id']){
					continue;
				}
				if($rs['prefix']){
					$value["identifier"] = $rs['prefix'].$value['identifier'];
				}
				if($rs['info'][$value['identifier']]){
					$value['content'] = $rs['info'][$value['identifier']];
				}
				$array[$key] = $value;
			}
		}
		if(!$array){
			return false;
		}
		//判断是否格式化
		if($rs['fields_format']){
			foreach($array as $key=>$value){
				if($value['ext']){
					$ext = is_string($value['ext']) ? unserialize($value['ext']) : $value['ext'];
					$value = array_merge($ext,$value);
				}
				$array[$key] = $this->lib('form')->format($value);
			}
		}
		return $array;
	}

	/**
	 * 获取模块信息，仅限内部使用
	**/
	private function _module($rs,$cache_id='')
	{
		if(!$rs['id']){
			return false;
		}
		$module = $this->model('module')->get_one($rs['id']);
		if(!$module){
			return false;
		}
		if($cache_id){
	 		$this->cache->key_list($cache_id,$this->db->prefix.'module');
			$this->cache->save($cache_id,$module);
		}
 		return $module;
	}

	//取得上一级项目
	private function _parent($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']){
			return false;
		}
		if(!$rs['pid']){
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site'],true);
			if(!$tmp || $tmp['type'] != 'project'){
				return false;
			}
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']){
			return false;
		}
		$project = $this->phpok('_project',$rs);
		if(!$project || !$project['status']){
			return false;
		}
		if(!$project['parent_id']){
			return $project;
		}
		return $this->_parent(array('pid'=>$project['parent_id']));
	}

	//读取当前项目下的子项目，支持多级
	private function _sublist($rs,$cache_id='')
	{
		if(!$rs['pid'] && !$rs['phpok']){
			return false;
		}
		if(!$rs['pid']){
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site'],true);
			if(!$tmp || $tmp['type'] != 'project'){
				return false;
			}
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']){
			return false;
		}
		$rslist = $this->model('project')->get_all($rs['site'],$rs['pid'],'p.status=1 AND p.hidden=0','id');
		if(!$rslist){
			return false;
		}
		$list = array();
		foreach($rslist as $key=>$value){
			$ext_rs = $this->model('ext')->ext_all('project-'.$value['id'],true);
			if($ext_rs){
				$project_ext = array();
				foreach($ext_rs as $k=>$v){
					$project_ext[$v['identifier']] = $this->lib('form')->show($v);
					if($v['form_type'] == 'url' && $v['content']){
						$v['content'] = unserialize($v['content']);
						$value['url'] = $v['content']['default'];
						if($this->site['url_type'] == 'rewrite' && $v['content']['rewrite']){
							$value['url'] = $v['content']['rewrite'];
						}
					}
				}
				if($project_ext){
					$value = array_merge($project_ext,$value);
				}
			}
			$list[$value['id']] = $value;
		}
		unset($rslist);
		foreach($list as $key=>$value){
			if(!$value['url']){
				$value['url'] = $this->url($value['identifier'],'','','www');
			}
			$list[$key] = $value;
 		}
 		if($cache_id){
	 		$this->cache->key_list($cache_id,$this->db->prefix.'project');
			$this->cache->save($cache_id,$list);
		}
 		return $list;
	}

	//读取当前分类下的子分类
	private function _subcate($rs,$cache_id='')
	{
		if(!$rs['cateid'] && !$rs['phpok'] && !$rs['cate'] && !$rs['pid']){
			return false;
		}
		$project = array();
		if($rs['pid']){
			$project_rs = $this->phpok('_project',array('pid'=>$rs['pid'],'site'=>$rs['site']));
			if($project_rs && $project_rs['status'] && $project_rs['cate']){
				$project = $project_rs;
				if(!$rs['cateid'] && !$rs['cate'] && !$rs['phpok']){
					$rs['cateid'] = $project['cate'];
				}
			}
		}
		if(!$rs['cateid'] && !$rs['phpok'] && !$rs['cate']){
			return false;
		}
		if(!$rs['cateid']){
			$identifier = $rs['cate'] ? $rs['cate'] : $rs['phpok'];
			$tmp = $this->model('id')->id($identifier,$rs['site'],true);
			if(!$tmp || $tmp['type'] != 'cate'){
				return false;
			}
			$rs['cateid'] = $tmp['id'];
			unset($tmp,$identifier);
		}
		$orderby = '';
		if($rs['orderby']){
			$orderby = $rs['orderby'];
		}
		$offset = $rs['offset'] ? intval($rs['offset']) : 0;
		$psize = $rs['psize'] ? intval($rs['psize']) : 0;
		$condition = "site_id='".$rs['site']."' AND status=1 AND parent_id='".$rs['cateid']."'";
		$cate_all = $this->model('cate')->cate_list($condition,$offset,$psize,$orderby);
		if(!$cate_all){
			return false;
		}
		$list = array();
		foreach($cate_all as $k=>$v){
			if(!$v['url'] && $project){
				$v['url'] = $this->url($project['identifier'],$v['identifier']);
			}
			$list[$k] = $v;
		}
		if($cache_id){
			$this->cache->key_list($cache_id,$this->db->prefix.'cate');
			$this->cache->save($cache_id,$list);
		}
		return $list;
	}

	//读取附件信息
	private function _res($rs)
	{
		if(!$rs['fileid'] && !$rs['phpok']){
			return false;
		}
		if(!$rs['fileid']) $rs['fileid'] = $rs['phpok'];
		return $this->model('res')->get_one(intval($rs['fileid']),true);
	}

	//读取附件列表
	private function _reslist($rs)
	{
		if(!$rs['fileids'] && !$rs['phpok']){
			return false;
		}
		if(!$rs['fileids']) $rs['fileids'] = $rs['phpok'];
		if(is_array($rs['fileids'])){
			$rs['fileids'] = implode(",",$rs['fileids']);
		}
		$list = explode(",",$rs['fileids']);
		$ids = array();
		foreach($list as $key=>$value){
			if($value && intval($value)){
				$ids[] = intval($value);
			}
		}
		$condition = " id IN(".implode(",",$ids).") ";
		return $this->model('res')->get_list($condition,0,999,true);
	}

	/**
	 * 获取用户数据
	 * @参数 $rs 数组，数组参数分别是：
	 *       phpok：用户 field 对应的字段值，默认为用户ID
	 *       user_id：等同于 phpok
	 *       field：要查询的字段ID，默认是id，支持mobile,email
	 *       ext：是否显示扩展，默认为true，当参数字符为false时，不查询扩展数据
	 *       weight：是否显示财富，默认为true，当参数字符为false时，不查询财富信息
	 * @返回 false 或 数组
	**/
	private function _user($rs)
	{
		if(!$rs['phpok'] && !$rs['user_id']){
			return false;
		}
		$user_id = $rs['user_id'] ? $rs['user_id'] : $rs['phpok'];
		$field = $rs['field'] ? $rs['field'] : 'id';
		$showext = true;
		if(isset($rs['ext']) && (!$rs['ext'] || ($rs['ext'] && $rs['ext'] == 'false'))){
			$showext = false;
		}
		$wealth = true;
		if(isset($rs['wealth']) && (!$rs['wealth'] || ($rs['wealth'] && $rs['wealth'] == 'false'))){
			$wealth = false;
		}
		return $this->model('user')->get_one($user_id,$field,$showext,$wealth);
	}

	/**
	 * 用户列表
	 * @参数 $rs 数组，数组参数分别是：
	 *       phpok：用户 field 对应的字段值，默认为用户ID
	 *       status：未设置时，默认为true，已设置参数，为false时表示未审核用户数据也读取
	 *       group_id：用户组ID
	 *       sqlext：SQL扩展查询，用户主表使用字段u，扩展表用ext
	 * @返回 多维数组
	**/
	private function _userlist($rs,$cache_id='')
	{
		$condition = 'u.status=1';
		if(isset($rs['status']) && (!$rs['status'] || ($rs['status'] && $rs['status'] == 'false'))){
			$condition = '1=1';
		}
		if(isset($rs['is_avatar'])){
			if(!$rs['is_avatar'] || ($rs['is_avatar'] && $rs['is_avatar'] == 'false')){
				$condition .= " AND u.avatar=''";
			}else{
				$condition .= " AND u.avatar !='' ";
			}
		}
		if($rs['group_id']){
			$condition .= " AND u.group_id='".$rs['group_id']."'";
		}
		if($rs['sqlext']){
			$condition .= " AND ".$rs['sqlext'];
		}
		$psize = ($rs['psize'] && intval($rs['psize'])) ? $rs['psize'] : 20;
		$offset = $rs['pageid'] ? (($rs['pageid'] - 1)* $psize) : 0;
		$data = array('total'=>0);
		$data['total'] = $this->model('user')->get_count($condition);
		if($data['total']>0){
			$data['rslist'] = $this->model('user')->get_list($condition,$offset,$psize);
		}
		if($cache_id){
			$this->cache->key_list($cache_id,$this->db->prefix.'user');
			$this->cache->save($cache_id,$data);
		}
		return $data;
	}

	private function _taglist($rs)
	{
		$orderby = 'id DESC';
		if($rs['orderby'] == 'hot'){
			$orderby = 'hits DESC,id DESC';
		}
		if($rs['orderby'] == 'cold'){
			$orderby = 'hits ASC,id DESC';
		}
		$site_id = $rs['site'] ? $rs['site'] : $this->site['id'];
		$condition = array();
		$condition[] = "site_id='".$site_id."'";
		if($rs['target']){
			$condition[] = $rs['target'] == 1 ? 'target=1' : 'target=0';
		}
		if($rs['alt']){
			$tmp = explode(",",$rs['alt']);
			$tmplist = array();
			foreach($tmp as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$tmplist[] = " alt LIKE '%".trim($value)."%'";
			}
			if(count($tmplist)>1){
				$condition[] = '('.implode(' OR ',$tmplist).')';
			}else{
				reset($tmplist);
				$condition[] = current($tmplist);
			}
		}
		if($rs['is_alt']){
			$condition[] = "alt != ''";
		}
		if($rs['is_global']){
			$condition[] = $rs['is_global'] ? "is_global=1" : "is_global=0";
		}
		if($rs['title'] || $rs['phpok'] || $rs['tag']){
			$title = $rs['phpok'] ? $rs['phpok'] : '';
			if($rs['title']){
				$title = $title ? $title.','.$rs['title'] : $rs['title'];
			}
			if($rs['tag']){
				$title = $title ? $title.','.$rs['tag'] : $rs['tag'];
			}
			$tmp = explode(",",$title);
			$tmplist = array();
			foreach($tmp as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				if(strpos($value,'%') !== false){
					$tmplist[] = " title LIKE '".trim($value)."'";
				}else{
					$tmplist[] = " title='".trim($value)."'";
				}
			}
			if(count($tmplist)>1){
				$condition[] = '('.implode(' OR ',$tmplist).')';
			}else{
				reset($tmplist);
				$condition[] = current($tmplist);
			}
		}
		if($rs['url']){
			$condition[] = "url LIKE '".$rs['url']."%'";
		}
		if($rs['is_url']){
			$condition[] = "url!=''";
		}
		$sqlcondition = implode(" AND ",$condition);
		$offset = $rs['offset'] ? intval($rs['offset']) : 0;
		$psize = $rs['psize'] ? intval($rs['psize']) : 30;
		if(!$psize){
			$psize = 30;
		}
		$taglist = $this->model('tag')->get_all($sqlcondition,$offset,$psize,$orderby);
		if(!$taglist){
			return false;
		}
		return $this->model('tag')->tag_array_html($taglist);
	}

	private function _comment($rs)
	{
		if(!$rs['phpok'] && !$rs['tid']){
			return false;
		}
		$id = $rs['tid'] ? $rs['tid'] : $rs['phpok'];
		$info = $this->model('reply')->get_title_info($id);
		if(!$info || !$info['comment_status']){
			return false;
		}
		$psize = $rs['psize'] ? $rs['psize'] : ($info['psize'] ? $info['psize'] : $this->config['psize']);
		if(!$psize){
			$psize = 30;
		}
		$parent_id = isset($rs['parent_id']) ? intval($rs['parent_id']) : 0;
		$pageid = ($rs['pageid'] && intval($rs['pageid'])) ? intval($rs['pageid']) : 1;
		$offset = ($pageid-1) * $psize;
		$condition = " r.tid=".intval($id)." AND r.parent_id=".$parent_id." ";
		$data = array('avatar'=>'images/avatar.gif','uid'=>0,'user'=>P_Lang('游客'));
		$data['total'] = 0;
		$data['pageid'] = $pageid;
		$data['psize'] = $psize;
		$condition_ext = " r.admin_id=0 AND (r.status=1 OR (r.status=0 AND r.session_id='".$this->session->sessid()."'))";
		if($this->session->val('user_id')){
			$condition_ext = " r.admin_id=0 AND (r.status=1 OR (r.status=0 AND r.uid=".$this->session->val('user_id')."))";
			$user = $this->model('user')->get_one($this->session->val('user_id'));
			if($user){
				if($user['avatar']){
					$data['avatar'] = $user['avatar'];
				}
				$data['user'] = $user['user'];
				$data['uid'] = $user['id'];
			}
		}
		if($rs['vouch'] && ($rs['vouch'] == 'true' || $rs['vouch'] == '1')){
			$condition .= " AND r.vouch=1 ";
		}
		$total = $this->model('reply')->get_total($condition.' AND '.$condition_ext);
		if(!$total){
			return $data;
		}
		$data['total'] = $total;
		if($rs['orderby'] && strtolower($rs['orderby']) == 'desc'){
			$orderby = 'r.addtime DESC,r.id DESC';
		}else{
			$orderby = 'r.addtime ASC,r.id ASC';
		}
		$rslist = $this->model('reply')->get_list($condition.' AND '.$condition_ext,$offset,$psize,$orderby);
		if(!$rslist){
			return $data;
		}
		$ids = array_keys($rslist);
		//输出子评论数
		$sublist = array();
		if($rs['sublist']){
			$sub_psize = is_numeric($rs['sublist']) ? intval($rs['sublist']) : 2;
			if(!$sub_psize){
				$sub_psize = 2;
			}
			$sublist = $this->model('reply')->group_parent_list($ids,$condition_ext,0,$sub_psize);
			foreach($rslist as $key=>$value){
				$value['sublist'] = array();
				if(isset($sublist[$value['id']])){
					$value['sublist'] = $sublist[$value['id']];
				}
				$rslist[$key] = $value;
			}
		}
		//输出子评论数量
		$sub_total = $this->model('reply')->group_parent_total($ids,$condition_ext,false);
		if(isset($sub_total) || isset($sublist)){
			foreach($rslist as $key=>$value){
				$value['reply_total'] = 0;
				$value['sublist'] = array();
				if(isset($sublist[$value['id']])){
					$value['sublist'] = $sublist[$value['id']];
					foreach($sublist[$value['id']] as $k=>$v){
						$ids[] = $v['id'];
					}
				}
				if(isset($sub_total[$value['id']])){
					$value['reply_total'] = $sub_total[$value['id']];
				}
				$rslist[$key] = $value;
			}
		}

		//输出评论的点赞信息
		$clicklist = $this->model('click')->get_all($ids,'reply',$this->session->val('user_id'),$this->session->sessid());
		if($clicklist){
			foreach($rslist as $key=>$value){
				if(!isset($clicklist[$value['id']])){
					continue;
				}
				$value['click_list'] = $clicklist[$value['id']];
				if($value['sublist']){
					foreach($value['sublist'] as $k=>$v){
						if(!isset($clicklist[$v['id']])){
							continue;
						}
						$v['click_list'] = $clicklist[$v['id']];
						$value['sublist'][$k] = $v;
					}
				}
				$rslist[$key] = $value;
			}
		}
		$data['rslist'] = $rslist;
		return $data;
	}

	private function _menu($rs)
	{
		if(!$rs['phpok'] && !$rs['id'] && !$rs['menu']){
			return false;
		}
		$groupId = $rs['phpok'] ? $rs['phpok'] : $rs['id'];
		if($rs['menu']){
			$groupId = $rs['menu'];
		}
		$is_userid = $this->session->val('user_id') ? true : false;
		if(!$is_userid && isset($rs['is_userid'])){
			$is_userid = true;
		}
		$this->model('menu')->site_id($rs['site']);
		$rslist = $this->model('menu')->treelist($groupId,$is_userid);
		return $rslist;
	}

	private function _keywords($rs)
	{
		$site_id = $rs['site'] ? $rs['site'] : $this->site_id;
		$condition = " site_id='".$site_id."' ";
		if($rs['sign'] && $rs['sign'] != 'false'){
			$condition .= " AND sign=1 ";
		}else{
			if($rs['keywords_sign']){
				$condition .= " AND sign=1 ";
			}
		}
		$orderby = '';
		//读数据库的设置
		if($rs['keywords_type'] == 'hot'){
			$orderby = 'hits DESC,id DESC';
		}
		if($rs['keywords_type'] == 'cold'){
			$orderby = 'hits ASC,id DESC';
		}
		if($rs['keywords_type'] == 'new'){
			$orderby = 'dateline DESC,id DESC';
		}
		if($rs['keywords_type'] == 'old'){
			$orderby = 'dateline ASC,id ASC';
		}
		//读用户传参过来的设置
		if($rs['type'] == 'hot'){
			$orderby = 'hits DESC,id DESC';
		}
		if($rs['type'] == 'cold'){
			$orderby = 'hits ASC,id DESC';
		}
		if($rs['type'] == 'new'){
			$orderby = 'dateline DESC,id DESC';
		}
		if($rs['type'] == 'old'){
			$orderby = 'dateline ASC,id ASC';
		}
		$psize = $rs['psize'] ? $rs['psize'] : 999;
		$rslist = $this->model('search')->keywords($condition,$psize,$orderby);
		if(!$rslist){
			return false;
		}
		$list = array();
		foreach($rslist as $key=>$value){
			if(!$value['title']){
				continue;
			}
			$tmp = array('title'=>$value['title']);
			$tmp['hits'] = $value['hits'];
			$tmp['url'] = $this->url('search','','keywords='.rawurlencode($value['title']));
			$list[] = $tmp;
		}
		return $list;
	}

	public function _options($rs)
	{
		if(!$rs['phpok'] && !$rs['id'] && !$rs['option_id']){
			return false;
		}
		$group_id = $rs['phpok'] ? $rs['phpok'] : $rs['id'];
		if($rs['option_id']){
			$group_id = $rs['option_id'];
		}
		$condition = "group_id='".$group_id."'";
		$parent_id = intval($rs['parent_id']);
		if($parent_id){
			$condition .= " AND parent_id=".$parent_id." ";
		}else{
			$condition .= " AND parent_id=0 ";
		}
		$offset = $rs['offset'] ? intval($rs['offset']) : 0;
		$psize = $rs['psize'] ? intval($rs['psize']) : 999;
		$rslist = $this->model('opt')->opt_list($condition,$offset,$psize);
		if(!$rslist){
			return false;
		}
		return $rslist;
	}
}