<?php
/***********************************************************
	Filename: {phpok}/www/content_control.php
	Note	: 内容信息
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-27 11:24
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class content_control extends phpok_control
{
	private $user_groupid;
	function __construct()
	{
		parent::control();
		$this->model('popedom')->siteid($this->site['id']);
		$groupid = $this->model('usergroup')->group_id($_SESSION['user_id']);
		if(!$groupid){
			error(P_Lang('无法获取前端用户组信息'),'','error');
		}
		$this->user_groupid = $groupid;
	}

	//内容首页
	/**
	 * 内容信息，可传递参数：主题ID，分类标识符及项目标识符
	 * @date 2016年02月05日
	 */
	public function index_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'),"","error");
		}
		$rs = $this->model('content')->get_one($id,true);
		if(!$rs){
			$this->error(P_Lang('内容不存在'),$this->url,5);
		}
		if(!$rs['project_id']){
			$this->error(P_Lang('未绑定项目'),$this->url,5);
		}
		if(!$rs['module_id']){
			$this->error(P_Lang('未绑定相应的模块'));
		}
		$project = $this->call->phpok('_project',array('pid'=>$rs['project_id']));
		if(!$project || !$project['status']){
			$this->error(P_Lang('项目不存在或未启用'));
		}
		if(!$this->model('popedom')->check($project['id'],$this->user_groupid,'read')){
			$this->error(P_Lang('您没有阅读此文章权限'));
		}
		$tplfile = array();
		if($project['parent_id']){
			$parent_rs = $this->call->phpok("_project",array('pid'=>$project['parent_id']));
			if(!$parent_rs || !$parent_rs['status']){
				$this->error(P_Lang('父级项目未启用'));
			}
			$this->assign("parent_rs",$parent_rs);
			if($parent_rs['tpl_content']){
				$tplfile[8] = $parent_rs['tpl_content'];
			}
			$this->phpok_seo($parent_rs);
		}
		$rs['tag'] = $this->model('tag')->tag_list($rs['id'],'list');
		$rs = $this->content_format($rs);
		$taglist = array('tag'=>$rs['tag'],'list'=>array('title'=>$rs['title']));
		//if($rs['tag'] && $taglist){
		//	$rs['tag'] = $this->model('tag')->tag_filter($taglist,$rs['id'],'list');
		//}
		//如果未绑定网址
		if(!$rs['url']){
			$url_id = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
			$tmpext = '&project='.$project['identifier'];
			if($project['cate'] && $rs['cate_id']){
				$tmpext.= '&cateid='.$rs['cate_id'];
			}
			$rs['url'] = $this->url($url_id,'',$tmpext,'www');
		}
		//读取分类树
		$rs['_catelist'] = $this->model('cate')->ext_catelist($rs['id']);
		if($rs['_catelist']){
			foreach($rs['_catelist'] as $k=>$v){
				$rs['_catelist'][$k]['url'] = $this->url($project['identifier'],$v['identifier'],'','www');
			}
		}
		$this->assign('page_rs',$project);
		$this->phpok_seo($project);
		
		if($rs['tpl']){
			$tplfile[0] = $rs['tpl'];
		}
		if($project['tpl_content']){
			$tplfile[7] = $project['tpl_content'];
		}
		if($rs['cate_id']){
			$cate_root_rs = $this->call->phpok('_cate',array('pid'=>$project['id'],'cateid'=>$project['cate']));
			if(!$cate_root_rs || !$cate_root_rs['status']){
				$this->error(P_Lang('根分类信息不存在或未启用'),$this->url,5);
			}
			$this->assign('cate_root_rs',$cate_root_rs);
			$this->phpok_seo($cate_root_rs);
			if($cate_root_rs['tpl_content']){
				$tplfile[6] = $cate_root_rs['tpl_content'];
			}
			//分类信息
			$cate_rs = $this->call->phpok('_cate',array("pid"=>$project['id'],'cateid'=>$rs['cate_id']));
			if(!$cate_rs || !$cate_rs['status']){
				$this->error(P_Lang('分类信息不存在或未启用'),$this->url,5);
			}
			if($cate_rs['parent_id']){
				$cate_parent_rs = $this->call->phpok('_cate',array('pid'=>$project['id'],'cateid'=>$cate_rs['parent_id']));
				if(!$cate_parent_rs || !$cate_root_rs['status']){
					$this->error(P_Lang('父级分类信息不存在或未启用'),$this->url,5);
				}
				$this->assign('cate_parent_rs',$cate_parent_rs);
				$this->phpok_seo($cate_parent_rs);
				if($cate_parent_rs['tpl_content']){
					$tplfile[5] = $cate_parent_rs['tpl_content'];
				}
			}
			$this->assign("cate_rs",$cate_rs);
			$this->phpok_seo($cate_rs);
			if($cate_rs['tpl_content']){
				$tplfile[4] = $cate_rs['tpl_content'];
			}
		}
		$tplfile[9] = $project['identifier'].'_content';
		ksort($tplfile);
		$tpl = '';
		foreach($tplfile as $key=>$value){
			if($this->tpl->check_exists($value)){
				$tpl = $value;
				break;
			}
		}
		if(!$tpl){
			$this->eror(P_Lang('未配置相应的模板'));
		}
		$this->model('list')->add_hits($rs["id"]);
		$rs['hits'] = $this->model('list')->get_hits($rs['id']);
		$this->phpok_seo($rs);
		$this->assign("rs",$rs);
		$this->view($tpl);
	}

	private function content_format($rs)
	{
		$flist = $this->model('module')->fields_all($rs['module_id']);
		if(!$flist){
			return $rs;
		}
		$page = $this->config['pageid'] ? $this->config['pageid'] : 'pageid';
		$pageid = $this->get($page,'int');
		if(!$pageid){
			$pageid = 1;
		}
		$this->assign('pageid',$pageid);
		foreach($flist as $key=>$value){
			if($value['form_type'] == 'editor'){
				$value['pageid'] = $pageid;
			}
			$rs[$value['identifier']] = $this->lib('form')->show($value,$rs[$value['identifier']]);
			if($value['form_type'] == 'url' && $rs[$value['identifier']] && $value['identifier'] != 'url' && !$rs['url']){
				$rs['url'] = $rs[$value['identifier']];
			}
			if($value['form_type'] == 'editor'){
				if(is_array($rs[$value['identifier']])){
					$rs[$value['identifier'].'_pagelist'] = $rs[$value['identifier']]['pagelist'];
					$rs[$value['identifier']] = $rs[$value['identifier']]['content'];
				}
				if($value['ext'] && $rs['tag']){
					$ext = unserialize($value['ext']);
					if($ext['inc_tag']){
						$taglist['list'][$value['identifier']] = $rs[$value['identifier']];
						$rs[$value['identifier']] = $this->model('tag')->tag_format($rs['tag'],$rs[$value['identifier']]);
					}
				}
			}
		}
		return $rs;
	}
}
?>