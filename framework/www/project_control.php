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
		$this->phpok_seo($project);
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
		$cateid = 0;
		if($rs["cate"]){
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
			$this->view($rs["tpl_index"]);
		}
		//读取列表信息
		$tplfile = $rs["tpl_list"];
		$psize = $rs["psize"] ? $rs['psize'] : $this->config['psize'];
		$pageurl = $this->url($rs['identifier']);
		if($cate_root){
			if($cateid && $cateid != $cate_root){
				$cate_rs = $this->call->phpok('_cate',array('pid'=>$rs['id'],'cateid'=>$cateid));
				if(!$cate_rs){
					$this->error_404();
				}
				if(!$cate_rs['status']){
					$this->error(P_Lang('分类已停用，请联系管理员'));
				}
				$this->assign('cate_rs',$cate_rs);
				$this->phpok_seo($cate_rs);
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
			unset($cate_root_rs);
		}
		$dt = array('pid'=>$rs['id']);
		if($cateid){
			$dt['cateid'] = $cateid;
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
			foreach($ext AS $key=>$value){
				if($key && $value){
					$dt['e_'.$key] = $value;
					$pageurl .= "ext[".$key."]=".rawurlencode($value)."&";
				}
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
		unset($rslist,$total,$pageurl,$psize,$pageid,$rs,$parent_rs);
		$this->view($tplfile);
	}
}