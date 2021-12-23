<?php
/**
 * 项目信息，包括列表，自身项目
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年03月17日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class project_control extends phpok_control
{
	private $user_groupid;
	public function __construct()
	{
		parent::control();
		$groupid = $this->model('usergroup')->group_id($this->session->val('user_id'));
		if(!$groupid){
			$this->error(P_Lang('无法获取前端用户组信息'));
		}
		$this->user_groupid = $groupid;
	}

	//栏目
	public function index_f()
	{
		$id = $this->get("id","system");
		if(!$id){
			$this->error(P_Lang('未指ID'));
		}
		$tmp = $this->model('id')->id($id,$this->site['id'],true);
		if(!$tmp || $tmp['type'] != 'project'){
			$this->error_404();
		}
		$pid = $tmp['id'];
		if(!$this->model('popedom')->check($pid,$this->user_groupid,'read')){
			$this->error(P_Lang('您没有阅读权限，请联系网站管理员'));
		}
		$project = $this->call->phpok('_project',array('pid'=>$pid));
		$this->assign("page_rs",$project);
		if($project['parent_id']){
			$parent_rs = $this->call->phpok('_project',array('pid'=>$project['parent_id']));
			if(!$parent_rs || !$parent_rs['status']){
				$this->error(P_Lang('父级项目不存在或未启用'));
			}
			$this->assign("parent_rs",$parent_rs);
		}
		if($project["module"]){
			$this->load_module($project,$parent_rs);
		}
		$this->load_me($project,$parent_rs);
	}

	private function load_me($project,$parent_rs='')
	{
		$tpl = $project["tpl_index"] ? $project["tpl_index"] : ($project["tpl_list"] ? $project["tpl_list"] : $project["tpl_content"]);
		if(!$tpl && !$parent_rs){
			$tpl = $project["identifier"]."_page";
		}
		if(!$tpl && $project["parent_id"] && $parent_rs){
			$tpl = $parent_rs["tpl_index"] ? $parent_rs["tpl_index"] : ($parent_rs["tpl_list"] ? $parent_rs["tpl_list"] : $parent_rs["tpl_content"]);
			if(!$tpl){
				$tpl = $parent_rs["identifier"]."_page";
			}
		}
		if(!$tpl){
			$tpl = $project["identifier"]."_page";
		}
		if(!$this->tpl->check_exists($tpl)){
			$this->error(P_Lang('模板文件缺少'));
		}
		$this->phpok_seo();
		$this->view($tpl);
	}

	//项目支持模型
	private function load_module($rs,$parent_rs='')
	{
		$m_rs = $this->model('module')->get_one($rs["module"]);
		if(!$m_rs || $m_rs["status"] != 1){
			$this->error(P_Lang('模块不存在或未启用'));
		}
		$this->assign("m_rs",$m_rs);
		$cate_root = $rs["cate"];
		$cateid = $this->get('cateid');
		if($rs["cate"] && !$cateid){
			$cate = $this->get("cate");
			if($cate){
				$cate_rs = $this->call->phpok('_cate',array('pid'=>$rs['id'],'cate'=>$cate));
				if(!$cate_rs){
					$this->error_404();
				}
				if(!$cate_rs['status']){
					$this->error(P_Lang('分类已停用，请联系管理员'));
				}
				if($cate_rs['id'] != $cate_root){
					$cateid = $cate_rs['id'];
					$this->assign('cate_rs',$cate_rs);
				}
			}
		}
		if($rs['cate'] && !is_numeric($cateid)){
			$tmp = explode(",",$cateid);
			$tmp_ids = array();
			foreach($tmp as $key=>$value){
				$value = intval($value);
				if($value){
					$tmp_ids[] = $value;
				}
			}
			$cateid = implode(",",$tmp_ids);
		}
		$keywords = $this->get("keywords");
		$ext = $this->get("ext");
		$tag = $this->get("tag");
		$uid = $this->get('uid','int');
		$attr = $this->get('attr');
		//价格，支持价格区间
		$price = $this->get('price','float');
		$sort = $this->get('sort');
		if($sort && !preg_match("/^[a-zA-Z][a-z0-9A-Z\_\-,\s\.]*[a-zA-Z]$/u",$sort)){
			$this->error(P_Lang('参数格式不正确'));
		}
		//判断该项目是否启用封面
		if($rs["tpl_index"] && !$cateid && !$keywords && !$ext && !$tag && !$uid && !$attr && !$price && !$sort && $this->tpl->check_exists($rs['tpl_index'])){
			$this->phpok_seo();
			$this->view($rs["tpl_index"]);
		}
		//读取列表信息
		$tplfile = $rs["tpl_list"];
		$psize = $rs["psize"] ? $rs['psize'] : $this->config['psize'];
		$pageurl = $this->url($rs['identifier']);
		if($cate_root){
			if($cateid && is_numeric($cateid) && $cateid != $cate_root){
				$cate_rs = $this->call->phpok('_cate',array('pid'=>$rs['id'],'cateid'=>$cateid));
				if(!$cate_rs){
					$this->error_404();
				}
				if(!$cate_rs['status']){
					$this->error(P_Lang('分类已停用，请联系管理员'));
				}
				$this->assign('cate_rs',$cate_rs);
				if($cate_rs['tpl_list'] && $this->tpl->check_exists($cate_rs['tpl_list'])){
					$tplfile = $cate_rs['tpl_list'];
				}
				if($cate_rs['psize']){
					$psize = $cate_rs['psize'];
				}
				$pageurl = $this->url($rs['identifier'],$cate_rs['identifier']);
				if($cate_rs['parent_id'] && $cate_rs['parent_id'] != $cate_root){
					$cate_parent_rs = $this->call->phpok('_cate',array('pid'=>$rs['id'],'cateid'=>$cate_rs['parent_id']));
					if(!$cate_parent_rs || !$cate_parent_rs['status']){
						$this->error(P_Lang('父级分类已停用，请联系管理员'));
					}
					$this->assign('cate_parent_rs',$cate_parent_rs);
					if(!$tplfile && $cate_parent_rs['tpl_list'] && $this->tpl->check_exists($cate_parent_rs['tpl_list'])){
						$tplfile = $cate_parent_rs['tpl_list'];
					}
				}
			}
			$cate_root_rs = $this->call->phpok('_cate',array('pid'=>$rs['id'],'cateid'=>$cate_root));
			if(!$cate_root_rs || !$cate_root_rs['status']){
				$this->error(P_Lang('项目所绑定的根分类已停用，请联系管理员'));
			}
			$this->assign('cate_root',$cate_root_rs);
		}
		$dt = array('pid'=>$rs['id']);
		if($cateid){
			$dt['cateid'] = $cateid;
			$this->assign('cateid',$cateid);
		}
		//读取列表信息
		$condition = "l.project_id=".$rs["id"]." AND l.module_id=".$rs["module"];
		if($tag || $keywords || $ext || $sort || $attr || $price || $uid){
			$pageurl .= $this->site["url_type"] == "rewrite" ? "?" : "&";
		}
		if($tag){
			$dt['tag'] = $tag;
			$pageurl .= "tag=".rawurlencode($tag)."&";
			$this->assign("tag",$tag);
		}
		if($keywords){
			$dt['keywords'] = $keywords;
			$pageurl .= "keywords=".rawurlencode($keywords)."&";
			$this->assign("keywords",$keywords);
			unset($keywords);
		}
		if($ext && is_array($ext)){
			$flist = $this->model('module')->fields_all($rs['module'],'identifier');
			foreach($ext as $key=>$value){
				if(!$key || !$value){
					continue;
				}
				if($flist[$key] && $flist[$key]['filter'] == 2 && $flist[$key]['filter_join'] && strpos($value,$flist[$key]['filter_join']) !== false){
					$tmp = explode($flist[$key]['filter_join'],$value);
					$tmplist = array();
					foreach($tmp as $k=>$v){
						$tmplist[] = "ext.".$key." LIKE '%".$v."%'";
					}
					$condition = "(".implode(" OR ",$tmplist).") ";
					if($dt['sqlext']){
						$dt['sqlext'] .= " AND ".$condition;
					}else{
						$dt['sqlext'] = $condition;
					}
				}else{
					$dt['e_'.$key] = $value;
				}
				$pageurl .= "ext[".$key."]=".rawurlencode($value)."&";
			}
			$this->assign('ext',$ext);
		}
		//价格区间
		if($price){
			if(!is_array($price)){
				$price = array('min'=>$price);
			}
			$condition = '';
			if($price['min']){
				$condition .= "b.price>='".$price['min']."'";
				$pageurl .= '&price[min]='.rawurlencode($price['min']);
			}
			if($price['max']){
				if($condition){
					$condition .= " AND ";
				}
				$condition .= "b.price<='".$price['max']."'";
				$pageurl .= '&price[max]='.rawurlencode($price['max']);
			}
			if($condition){
				if($dt['sqlext']){
					$dt['sqlext'] .= " AND ".$condition;
				}else{
					$dt['sqlext'] = $condition;
				}
				$this->assign('price',$price);
			}
		}
		if($uid){
			$pageurl .= "&uid=".$uid;
			$dt['user_id'] = $uid;
		}
		//自定义排序
		if($sort){
			$dt['orderby'] = $sort;
			$pageurl .= 'sort='.rawurlencode($sort)."&";
			$this->assign('sort',$sort);
		}
		if(substr($pageurl,-1) == "&" || substr($pageurl,-1) == "?"){
			$pageurl = substr($pageurl,0,-1);
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$dt['offset'] = $offset;
		$dt['psize'] = $psize;
		$dt['is_list'] = 1;
		if($attr){
			$dt['attr'] = $attr;
		}
		if($rs['list_fields']){
			$dt['fields'] = $rs['list_fields'];
		}
		$this->plugin("system_www_arclist",$rs,$m_rs);
		$dt_ext = $this->tpl->val('dt');
		if($dt_ext){
			if($dt_ext['sqlext']){
				$dt['sqlext'] = $dt['sqlext'] ? $dt['sqlext'].' AND '.$dt_ext['sqlext'] : $dt_ext['sqlext'];
				unset($dt_ext['sqlext']);
			}
			$dt = array_merge($dt,$dt_ext);
		}		
		$info = $this->call->phpok('_arclist',$dt);
		$total_page = intval($info['total']/$dt['psize']);
		if($info['total']%$dt['psize']){
			$total_page++;
		}
		if($total_page && $total_page<$pageid){
			$this->error_404(P_Lang('内容信息不存在'));
		}
		$this->assign('dt',$dt);
		$this->assign("pageid",$pageid);
		$this->assign("psize",$dt['psize']);
		$this->assign("pageurl",$pageurl);
		$this->assign("total",$info['total']);
		$this->assign("rslist",$info['rslist']);
		if(!$tplfile && $parent_rs){
			$tplfile = $parent_rs['tpl_list'] ? $parent_rs['tpl_list'] : $rs['identifier'].'_list';
		}
		if(!$tplfile){
			$tplfile = $rs["identifier"]."_list";
		}
		if(!$this->tpl->check_exists($tplfile)){
			if($rs["tpl_content"] && $info['rslist']){
				reset($info['rslist']);
				$rs = current($info['rslist']);
				$url = $rs['identifier'] ? $this->url($rs['identifier']) : $this->url($rs['id']);
				$this->_location($url);
			}
			$this->error(P_Lang('未配置模板 {tplfile}，请配置相应的模板',array('tplfile'=>$tplfile)));
		}
		//加载筛选器
		$tmpdata = array('page_rs'=>$rs,'cate_rs'=>$cate_rs,'parent_rs'=>$parent_rs,'cate_parent_rs'=>$cate_parent_rs,'rslist'=>$rslist,'total'=>$total,'url'=>$pageurl,'dt'=>$dt,'ext'=>$ext);
		$tmpdata['cate_root'] = $cate_root_rs;
		$tmpdata['module'] = $m_rs;
		$tmpdata['price'] = $price;
		if($rs['filter_status']){
			$this->_filter($tmpdata);
		}
		unset($rslist,$total,$pageurl,$psize,$pageid,$rs,$parent_rs);
		$this->phpok_seo();
		$tpl = $this->get('tplfile');
		if($tpl && $this->tpl->check_exists($tpl)){
			$tplfile = $tpl;
		}
		$this->view($tplfile);
	}

	private function _filter($data)
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
					if($value['form_type'] == 'radio' || $value['form_type'] == 'checkbox' || $value['form_type'] == 'select'){
						$this->filter_options($filter,$data,$urlist,$value);
					}
				}
			}
		}
		if($data['page_rs'] && $data['page_rs']['is_biz'] && $data['page_rs']['filter_price']){
			$this->filter_price($filter,$data,$urlist);
		}
		$this->assign('filter',$filter);
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
		$opt_list = $fields['ext']["option_list"] ? explode(":",$fields['ext']["option_list"]) : array('default','');
		$rslist = opt_rslist($opt_list[0],$opt_list[1],$fields['ext']['ext_select']);
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
		if($data['module'] && $data['module']['mtype']){
			$tbl .= $data['module']['id'];
			$sql = "SELECT count(id) as total,".$fields['identifier']." as title FROM ".$tbl." WHERE project_id='".$data['page_rs']['id']."' GROUP BY ".$fields['identifier'];
		}else{
			$tbl .= 'list_'.$data['module']['id'];
			$sql  = " SELECT count(l.id) as total,e.".$fields['identifier']." as title FROM ".$this->db->prefix."list l ";
			$sql .= " LEFT JOIN ".$tbl." e ON(l.id=e.id) ";
			$sql .= " WHERE l.project_id='".$data['page_rs']['id']."' AND l.site_id='".$data['page_rs']['site_id']."' AND l.status=1 AND l.hidden=0 ";
			$sql .= " AND e.".$fields['identifier']."!='' AND e.".$fields['identifier']." IS NOT NULL";
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
}