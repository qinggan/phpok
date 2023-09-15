<?php
/**
 * 通过Api获取文章列表，返回数组结果信
 * @file framework/api/project_control.php
 * @author phpok.com <admin@phpok.com>
 * @version 4.5.0
 * @date 2016年01月27日
 */
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class project_control extends phpok_control
{
	private $user_groupid;
	private $user_group;
	private $token_info;
	private $rlist;
	public function __construct()
	{
		parent::control();
		$this->config('is_ajax',true);
		if($this->session->val('user_gid')){
			$group_rs = $this->model('usergroup')->get_one($this->session->val('user_gid'));
		}else{
			$group_rs = $this->model('usergroup')->get_default(true);
		}
		if(!$group_rs){
			$this->error(P_Lang('用户组获取异常，请检查'));
		}
		$this->user_groupid = $group_rs['id'];
		$this->user_group = $group_rs;
	}

	//栏目
	public function index_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指ID'));
		}
		$tmp = $this->model('id')->id($id,$this->site['id'],true);
		if(!$tmp || $tmp['type'] != 'project'){
			$this->error(P_Lang('项目不存在'));
		}
		$pid = $tmp['id'];
		if(!$this->model('popedom')->check($pid,$this->user_groupid,'read')){
			$this->error(P_Lang('您没有阅读权限，请联系网站管理员'));
		}
		$project = $this->call->phpok('_project',array('pid'=>$pid));
		if(!$project || !$project['status']){
			$this->error(P_Lang('项目不存在或未启用'));
		}
		if($project["module"] && !$project['is_api']){
			$this->error(P_Lang('此项目接口不可用'));
		}
		$this->rlist['page_rs'] = $project;
		if($project['parent_id']){
			$parent_rs = $this->call->phpok('_project',array('pid'=>$project['parent_id']));
			if(!$parent_rs || !$parent_rs['status']){
				$this->error(P_Lang('父级项目不存在或未启用'));
			}
			$this->rlist['parent_rs'] = $parent_rs;
		}
		if($project["module"]){
			$this->load_module($project,$parent_rs);
		}
		$this->success($this->rlist);
	}

	//项目支持模型
	private function load_module($rs,$parent_rs='')
	{
		$m_rs = $this->model('module')->get_one($rs["module"]);
		if(!$m_rs || $m_rs["status"] != 1){
			$this->error(P_Lang('模块不存在或未启用'));
		}
		$this->rlist['m_rs'] = $m_rs;
		$cate_root = $rs["cate"];
		$cateid = 0;
		if($rs["cate"]){
			$cate = $this->get("cate");
			$cateid = $this->get('cateid');
			if($cate || $cateid){
				$array = array('pid'=>$rs['id']);
				if($cate){
					$array['cate'] = $cate;
				}
				if($cateid){
					$array['cateid'] = $cateid;
				}
				$cate_rs = $this->call->phpok('_cate',$array);
				if($cate_rs && $cate_rs['id'] != $rs['cate']){
					$cateid = $cate_rs['id'];
					$this->rlist['cate_rs'] = $cate_rs;
				}
			}
		}
		$keywords = $this->get("keywords");
		$ext = $this->get("ext");
		$tag = $this->get("tag");
		$uid = $this->get('uid','int');
		$attr = $this->get('attr');
		$is_usercp = $this->get('is_usercp','int');
		//价格，支持价格区间
		$price = $this->get('price','float');
		$sort = $this->get('sort');
		if($sort && !preg_match("/^[a-zA-Z][a-z0-9A-Z\_\-,\s\.]*[a-zA-Z]$/u",$sort)){
			$this->error(P_Lang('参数格式不正确'));
		}
		//读取列表信息
		$psize = $rs["psize_api"] ? $rs['psize_api'] : ($rs['psize'] ? $rs['psize'] : $this->config['psize']);
		$pageurl = $this->url($rs['identifier']);
		if($cate_root){
			if($cateid && $cateid != $cate_root){
				$cate_rs = $this->call->phpok('_cate',array('pid'=>$rs['id'],'cateid'=>$cateid));
				if(!$cate_rs || !$cate_rs['status']){
					$this->error(P_Lang('分类已停用，请联系管理员'));
				}
				$this->rlist['cate_rs'] = $cate_rs;
				if($cate_rs['psize_api']){
					$psize = $cate_rs['psize_api'];
				}
				$pageurl = $this->url($rs['identifier'],$cate_rs['identifier']);
				if($cate_rs['parent_id'] && $cate_rs['parent_id'] != $cate_root){
					$cate_parent_rs = $this->call->phpok('_cate',array('pid'=>$rs['id'],'cateid'=>$cate_rs['parent_id']));
					if(!$cate_parent_rs || !$cate_parent_rs['status']){
						$this->error(P_Lang('父级分类已停用，请联系管理员'));
					}
					$this->rlist['cate_parent_rs'] = $cate_parent_rs;
				}
			}
			$cate_root_rs = $this->call->phpok('_cate',array('pid'=>$rs['id'],'cateid'=>$cate_root));
			if(!$cate_root_rs || !$cate_root_rs['status']){
				$this->error(P_Lang('项目所绑定的根分类已停用，请联系管理员'));
			}
			$this->rlist['cate_root'] = $cate_root_rs;
			unset($cate_root_rs);
		}
		$dt = array('pid'=>$rs['id']);
		if($cateid){
			$dt['cateid'] = $cateid;
		}
		//读取列表信息
		$condition = "l.project_id=".$rs["id"]." AND l.module_id=".$rs["module"];
		if($tag || $keywords || $ext) $pageurl .= $this->site["url_type"] == "rewrite" ? "?" : "&";
		if($tag){
			$dt['tag'] = $tag;
			$pageurl .= "tag=".rawurlencode($tag)."&";
			$this->rlist['tag'] = $tag;
		}
		if($keywords){
			$dt['keywords'] = $keywords;
			$pageurl .= "keywords=".rawurlencode($keywords)."&";
			$this->rlist['keywords'] = $keywords;
			unset($keywords);
		}
		if($ext && is_array($ext)){
			foreach($ext as $key=>$value){
				if($key && $value){
					$dt['e_'.$key] = $value;
					$pageurl .= "ext[".$key."]=".rawurlencode($value)."&";
				}
			}
			$this->rlist['ext'] = $ext;
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
				$this->rlist['price'] = $price;
			}
		}
		if($uid){
			$pageurl .= "&uid=".$uid;
			$dt['user_id'] = $uid;
			if($uid == $this->session->val('user_id') && $is_usercp){
				$dt['is_usercp'] = true;
			}
		}
		//自定义排序
		if($sort){
			$dt['orderby'] = $sort;
			$pageurl .= '&sort='.rawurlencode($sort);
			$this->rlist['sort'] = $sort;
		}
		if(substr($pageurl,-1) == "&" || substr($pageurl,-1) == "?"){
			$pageurl = substr($pageurl,0,-1);
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$offset = ($pageid-1) * $psize;
		$dt['offset'] = $offset;
		$dt['psize'] = $psize;
		$dt['is_list'] = 1;
		if($attr){
			$dt['attr'] = $attr;
		}
		$fields = $this->get('fields');
		if(!$fields){
			$fields = '*';
		}
		$dt['fields'] = $fields;
		$this->plugin("system_api_arclist",$rs,$m_rs);
		$dt_ext = $this->data('dt');
		if($dt_ext){
			if($dt_ext['sqlext']){
				$dt['sqlext'] = $dt['sqlext'] ? $dt['sqlext'].' AND '.$dt_ext['sqlext'] : $dt_ext['sqlext'];
				unset($dt_ext['sqlext']);
			}
			$dt = array_merge($dt,$dt_ext);
		}
		$info = $this->call->phpok('_arclist',$dt);
		unset($dt);
		if(!$info['rslist']){
			$tip = $offset>0 ? P_Lang('已是最后一条数据') : P_Lang('没有数据');
			$this->error($tip);
		}
		$this->rlist['pageid'] = $pageid;
		$this->rlist['psize'] = $psize;
		$this->rlist['pageurl'] = $pageurl;
		$this->rlist['total'] = $info['total'];
		$this->rlist['rslist'] = $info['rslist'];
		//加载筛选器
		if($rs['filter_status']){
			$tmpdata = array('page_rs'=>$rs,'cate_rs'=>$cate_rs,'parent_rs'=>$parent_rs,'cate_parent_rs'=>$cate_parent_rs,'rslist'=>$info['rslist'],'total'=>$info['total'],'url'=>$pageurl,'dt'=>$dt,'ext'=>$ext);
			$tmpdata['cate_root'] = $cate_root_rs;
			$tmpdata['module'] = $m_rs;
			$tmpdata['price'] = $price;
			$tmpdata['mlist'] = $this->model('module')->fields_all($rs['module']);
			$filter = $this->model('filter')->start($tmpdata);
			$this->rlist['filter'] = $filter;
		}
	}
}