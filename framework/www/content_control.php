<?php
/**
 * 内容信息
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2017年11月17日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class content_control extends phpok_control
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

	/**
	 * 内容信息，可传递参数：主题ID，分类标识符及项目标识符
	 */
	public function index_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$pid = $this->get('pid');
		if($pid){
			if(is_numeric($pid)){
				$project = $this->model('project')->get_one($pid,false);
			}else{
				$project = $this->model('project')->simple_project_from_identifier($pid);
			}
			if($project && $project['module']){
				if(!$this->model('popedom')->check($project['id'],$this->user_groupid,'read')){
					$this->error(P_Lang('您没有阅读此文章权限'));
				}
				$module = $this->model('module')->get_one($project['module']);
				if($module && $module['mtype']){
					$arc = $this->model('list')->single_one($id,$module['id']);
					if(!$arc){
						$this->error_404(P_Lang('没有找到内容'));
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
					$this->assign('rs',$arc);
					$this->assign('page_rs',$page_rs);
					$tplfile = array();
					if($arc['tpl']){
						$tplfile[0] = $arc['tpl'];
					}
					if($project['tpl_content']){
						$tplfile[7] = $project['tpl_content'];
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
						$this->error(P_Lang('未配置相应的模板'));
					}
					$tplfile = $this->get('tplfile');
					if($tplfile && $this->tpl->check_exists($tplfile)){
						$tpl = $tplfile;
					}
					$this->view($tpl);
				}
			}
		}
		$me = $this->get('me','int');
		$rs = $this->model('content')->get_one($id,($me ? false : true));
		if(!$rs){
			$this->error_404();
		}
		if(!$rs['status'] && $me){
			if(!$this->session->val('user_id') || !$rs['user_id'] || $rs['user_id'] != $this->session->val('user_id')){
				$this->error_404();
			}
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
		}
		if($rs['user_id']){
			$rs['user'] = $this->model('user')->get_one($rs['user_id']);
		}
		$rs['tag'] = $this->model('tag')->tag_list($rs['id'],'list');
		$rs = $this->content_format($rs);
		//如果未绑定网址
		if(!$rs['url']){
			$url_id = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
			$tmpext = 'project='.$project['identifier'];
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
		
		if($rs['tpl']){
			$tplfile[0] = $rs['tpl'];
		}
		if($project['tpl_content']){
			$tplfile[7] = $project['tpl_content'];
		}
		if($rs['cate_id'] && $project['cate']){
			$cate_root_rs = $this->call->phpok('_cate',array('pid'=>$project['id'],'cateid'=>$project['cate']));
			if(!$cate_root_rs || !$cate_root_rs['status']){
				$this->error(P_Lang('根分类信息不存在或未启用'),$this->url,5);
			}
			$this->assign('cate_root',$cate_root_rs);
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
				if($cate_parent_rs['tpl_content']){
					$tplfile[5] = $cate_parent_rs['tpl_content'];
				}
			}
			$this->assign("cate_rs",$cate_rs);
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
		$tplfile = $this->get('tplfile');
		if($tplfile && $this->tpl->check_exists($tplfile)){
			$tpl = $tplfile;
		}
		if(!$tpl){
			$this->error(P_Lang('未配置相应的模板'));
		}
		$this->model('list')->add_hits($rs["id"]);
		$rs['hits'] = $this->model('list')->get_hits($rs['id']);
		//针对点击事件的
		if($project['quick-comment-status']){
			$clicklist = $this->model('click')->get_all($rs['id'],'list',$this->session->val('user_id'));
			if($clicklist && isset($clicklist[$rs['id']])){
				$rs['click_list'] = $clicklist[$rs['id']];
			}
		}
		$this->data('arc',$rs);
		$this->node('PHPOK_arc');
		$rs = $this->data('arc');
		$this->assign("rs",$rs);
		//判断这个主题是否支持评论及评论验证码
		if($project['comment_status']){
			$vcode = $this->model('site')->vcode($project['id'],'comment');
			$this->assign('is_vcode',$vcode);
		}
		//是否增加积分
		if($this->session->val('user_id')){
			$this->model('wealth')->add_integral($rs['id'],$this->session->val('user_id'),'content',P_Lang('阅读#{id}',array('id'=>$rs['id'])));
		}
		$this->phpok_seo();
		$tplfile = $this->get('tplfile');
		if($tplfile && $this->tpl->check_exists($tplfile)){
			$tpl = $tplfile;
		}
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
					$rs['_'.$value['identifier']] = $rs[$value['identifier']]['list'];
					$rs[$value['identifier']] = $rs[$value['identifier']]['content'];
				}
				if($value['ext'] && $rs['tag']){
					$ext = unserialize($value['ext']);
					if($ext['inc_tag']){
						$rs[$value['identifier']] = $this->model('tag')->tag_format($rs['tag'],$rs[$value['identifier']]);
					}
				}
			}
		}
		return $rs;
	}

	public function comment_f()
	{
		$id = $this->get('id','int');
		$parent_id = $this->get('parent_id','int');
		if(!$id && !$parent_id){
			$this->error(P_Lang('参数不完整'));
		}
		if($parent_id){
			$comment = $this->model("reply")->get_one($parent_id);
			if(!$comment){
				$this->error(P_Lang('评论不存在'));
			}
			if(!$comment['status']){
				$this->error(P_Lang('评论未审核，不能查看'));
			}
			$id = $comment['tid'];

			//---统计点击情况
			$clicklist = $this->model('click')->get_all(array($comment['id']),'reply',$this->session->val('user_id'),$this->session->sessid());
			if($clicklist){
				$comment['click_list'] = $clicklist[$comment['id']];
			}
			$this->assign('comment',$comment);
		}
		//针对文章的权限
		$rs = $this->model('content')->get_one($id);
		if(!$rs || !$rs['status']){
			$this->error_404('内容未发布');
		}
		if(!$rs['project_id']){
			$this->error_404(P_Lang('未绑定项目'));
		}
		if(!$rs['module_id']){
			$this->error_404(P_Lang('未绑定相应的模块'));
		}
		$project = phpok("_project",array('pid'=>$rs['project_id']));
		if(!$project || !$project['status']){
			$this->error_404(P_Lang('项目不存在或未启用'));
		}
		if(!$project['comment_status']){
			$this->error(P_Lang('项目未开启评论功能，不支持查看'));
		}
		if(!$this->model('popedom')->check($project['id'],$this->user_groupid,'read')){
			$this->error_404(P_Lang('您没有阅读此文章权限'));
		}
		$vcode = $this->model('site')->vcode($project['id'],'comment');
		$this->assign('is_vcode',$vcode);
		//如果未绑定网址
		if(!isset($rs['url'])){
			$url_id = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
			$tmpext = 'project='.$project['identifier'];
			if($project['cate'] && $rs['cate_id']){
				$tmpext.= '&cateid='.$rs['cate_id'];
			}
			$rs['url'] = $this->url($url_id,'',$tmpext,'www');
		}
		$this->assign('page_rs',$project);
		$this->assign('rs',$rs);
		//读评论列表
		$pageid = $this->get('pageid','int');
		if(!$pageid){
			$pageid = 1;
		}
		$page_url = $this->url('content','comment','id='.$id);
		
		$psize = $project['psize'] ? $project['psize'] : $this->config['psize'];
		$ext = array();
		$ext['tid'] = $id;
		if($parent_id){
			$page_url .= '&parent_id='.$parent_id;
			$ext['parent_id'] = $parent_id;
			$this->assign('parent_id',$parent_id);
		}
		$vouch = $this->get('vouch','int');
		if($vouch){
			$page_url .= "&vouch=".$vouch;
			$ext['vouch'] = $vouch;
			$this->assign('vouch',$vouch);
		}
		$sublist = $this->get('sublist','int');
		if($sublist){
			$page_url .= "&sublist=".$sublist;
			$ext['sublist'] = $sublist;
			$this->assign('sublist',$sublist);
		}
		$ext['pageid'] = $pageid;
		$ext['psize'] = $psize;
		$data = phpok("_comment",$ext);
		if(!$data){
			$this->error_404(P_Lang('数据异常，请检查'));
		}
		if($data['rslist']){
			$this->assign('rslist',$data['rslist']);
		}
		$this->assign('total',$data['total']);
		$this->assign("pageid",$pageid);
		$this->assign("psize",$psize);
		$this->assign("pageurl",$page_url);
		if($comment && $comment['title']){
			$this->assign('title',$comment['title']);
		}else{
			$this->assign('title',$rs['title']);
		}
		$this->view('comment');
	}

	public function wholist_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定主题ID'));
		}
		$tid = $this->get('tid','int');
		if(!$tid){
			$this->error(P_Lang('未指定评论ID'));
		}
		$type = $this->get('type');
		$chk = array('list','reply');
		if(!isset($type) || !in_array($type,$chk)){
			$type = 'reply';
		}
		$uid = $this->get('uid','int');
		$this->assign('id',$id);
		$this->assign('uid',$uid);
		$this->assign('tid',$tid);
		$rs = phpok("_arc","title_id=".$id);
		if(!$rs){
			$this->error(P_Lang('内容不存在'));
		}
		$this->assign('rs',$rs);
		$comment = $this->model('reply')->get_one($tid);
		$total = $this->model('click')->users_total($tid,$type);
		if(!isset($total)){
			$this->error(P_Lang('没有评论信息'));
		}
		$total_user = $this->model('click')->users_total($tid,$type,true);
		if(!isset($total_user)){
			$this->error(P_Lang('没有会员评论'));
		}
		$this->assign('total',$total_user);
		$this->assign('total_guest',intval($total-$total_user));
		$pageurl = $this->url('content','wholist','id='.$id.'&uid='.$uid.'&tid='.$tid.'&type='.$type);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid - 1) * $psize;
		$rslist = $this->model('click')->users($tid,$type,$offset,$psize);
		if(!$rslist){
			$this->error(P_Lang('没有会员列表信息'));
		}
		$this->assign('rslist',$rslist);
		$this->assign('pageurl',$pageurl);
		$this->view('comment-wholist');
	}
}