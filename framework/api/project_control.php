<?php
/**
 * 通过Api获取文章列表，返回数组结果信
 * @file framework/api/project_control.php
 * @author phpok.com <admin@phpok.com>
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @version 4.5.0
 * @date 2016年01月27日
 */
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class project_control extends phpok_control
{
	private $user_groupid;
	private $token_info;
	private $rlist;
	public function __construct()
	{
		parent::control();
		$token = $this->get('token');
		if(!$token){
			$this->json(P_Lang('未指定Token信息'));
		}
		$this->token_info = $this->lib('token')->decode($token);
		$groupid = $this->model('usergroup')->group_id($this->token_info['user_id']);
		if(!$groupid){
			$this->json(P_Lang('无法获取前端用户组信息'));
		}
		$this->user_groupid = $groupid;
	}

	//栏目
	public function index_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->json(P_Lang('未指ID'));
		}
		$tmp = $this->model('id')->id($id,$this->site['id'],true);
		if(!$tmp || $tmp['type'] != 'project'){
			$this->json(P_Lang('项目不存在'));
		}
		$pid = $tmp['id'];
		if(!$this->model('popedom')->check($pid,$this->user_groupid,'read')){
			$this->json(P_Lang('您没有阅读权限，请联系网站管理员'));
		}
		$project = $this->call->phpok('_project',array('pid'=>$pid));
		if(!$project || !$project['status']){
			$this->json(P_Lang('项目不存在或未启用'));
		}
		$this->rlist['page_rs'] = $project;
		if($project['parent_id']){
			$parent_rs = $this->call->phpok('_project',array('pid'=>$project['parent_id']));
			if(!$parent_rs || !$parent_rs['status']){
				$this->json(P_Lang('父级项目不存在或未启用'));
			}
			$this->rlist['parent_rs'] = $parent_rs;
		}
		if($project["module"]){
			$this->load_module($project,$parent_rs);
		}
		$this->json($this->rlist,true);
	}

	//项目支持模型
	private function load_module($rs,$parent_rs='')
	{
		$m_rs = $this->model('module')->get_one($rs["module"]);
		if(!$m_rs || $m_rs["status"] != 1){
			$this->json(P_Lang('模块不存在或未启用'));
		}
		$this->rlist['m_rs'] = $m_rs;
		$cate_root = $rs["cate"];
		$cateid = 0;
		if($rs["cate"]){
			$cate = $this->get("cate");
			if($cate){
				$cate_rs = $this->call->phpok('_cate',array('pid'=>$rs['id'],'cate'=>$cate));
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
		//价格，支持价格区间
		$price = $this->get('price','float');
		$sort = $this->get('sort');
		//判断该项目是否启用封面
		if($rs["tpl_index"] && !$cateid && !$keywords && !$ext && !$tag && !$uid && !$attr && !$price && !$sort){
			$this->json($this->rlist,true);
		}
		//读取列表信息
		$tplfile = $rs["tpl_list"];
		$psize = $rs["psize"] ? $rs['psize'] : $this->config['psize'];
		$pageurl = $this->url($rs['identifier']);
		if($cate_root){
			if($cateid && $cateid != $cate_root){
				$cate_rs = $this->call->phpok('_cate',array('pid'=>$rs['id'],'cateid'=>$cateid));
				if(!$cate_rs || !$cate_rs['status']){
					$this->json(P_Lang('分类已停用，请联系管理员'));
				}
				$this->rlist['cate_rs'] = $cate_rs;
				if($cate_rs['psize']){
					$psize = $cate_rs['psize'];
				}
				$pageurl = $this->url($rs['identifier'],$cate_rs['identifier']);
				if($cate_rs['parent_id'] && $cate_rs['parent_id'] != $cate_root){
					$cate_parent_rs = $this->call->phpok('_cate',array('pid'=>$rs['id'],'cateid'=>$cate_rs['parent_id']));
					if(!$cate_parent_rs || !$cate_parent_rs['status']){
						$this->json(P_Lang('父级分类已停用，请联系管理员'));
					}
					$this->rlist['cate_parent_rs'] = $cate_parent_rs;
				}
			}
			$cate_root_rs = $this->call->phpok('_cate',array('pid'=>$rs['id'],'cateid'=>$cate_root));
			if(!$cate_root_rs || !$cate_root_rs['status']){
				$this->json(P_Lang('项目所绑定的根分类已停用，请联系管理员'));
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
			foreach($ext AS $key=>$value){
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
			$fields = 'id';
		}
		$dt['fields'] = $fields;
		$info = $this->call->phpok('_arclist',$dt);
		unset($dt);
		if(!$info['rslist']){
			$this->json(P_Lang('已是最后一条数据'));
		}
		$rslist = array();
		$funclist = $this->get('_func');
		if($funclist){
			foreach($funclist as $key=>$value){
				$funclist[$key] = explode(",",$value);
			}
			foreach($info['rslist'] as $key=>$value){
				foreach($value as $k=>$v){
					if($funclist[$k] && $v && $funclist[$k][0]== 'cut'){
						if($k == 'title'){
							$value['_title'] = phpok_cut($v,$funclist[$k][1],($funclist[$k][2] ? '…' : ''));
						}else{
							$value[$k] = phpok_cut($v,$funclist[$k][1],($funclist[$k][2] ? '…' : ''));
						}
						
					}
					if($k == 'dateline' && $v && $funclist[$k][0] == 'date'){
						$value['_dateline'] = time_format($v);
					}
				}
				$rslist[$key] = $value;
			}
		}
		$this->rlist['pageid'] = $pageid;
		$this->rlist['psize'] = $psize;
		$this->rlist['pageurl'] = $pageurl;
		$this->rlist['total'] = $total;
		$this->rlist['rslist'] = $rslist;
		unset($rslist,$total,$pageurl,$psize,$pageid,$rs,$parent_rs);
	}
}
?>