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
		$this->config('is_ajax',true);
		$this->model('popedom')->siteid($this->site['id']);
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
		if(!$this->site['api_code']){
			$this->error(P_Lang('未启用API接口'));
		}
		$this->model('apisafe')->code($this->site['api_code']);
		if(!$this->model('apisafe')->check()){
			$errInfo = $this->model('apisafe')->error_info();
			if(!$errInfo){
				$errInfo = P_Lang('未通过安全接口拼接');
			}
			$this->error($errInfo);
		}
		$data_info = array();
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$me = $this->get('me','int');
		$rs = $this->model('content')->get_one($id,($me ? false : true));
		if(!$rs){
			$this->error(P_Lang('文章内容不存在'));
		}
		if($me && $rs['user_id'] != $this->session->val('user_id')){
			$this->error(P_Lang('您没有权限'));
		}
		if(!$rs['project_id']){
			$this->error(P_Lang('未绑定项目'));
		}
		if(!$rs['module_id']){
			$this->error(P_Lang('未绑定相应的模块'));
		}
		$project = $this->call->phpok('_project',array('pid'=>$rs['project_id']));
		if(!$project || !$project['status']){
			$this->error(P_Lang('项目不存在或未启用'));
		}
		if(!$project['is_api'] && !$project['is_front']){
			$this->error(P_Lang('未启用API或前台可访问'));
		}
		if(!$this->model('popedom')->check($project['id'],$this->user_groupid,'read')){
			$this->error(P_Lang('您没有阅读此文章权限'));
		}
		if($project['parent_id']){
			$parent_rs = $this->call->phpok("_project",array('pid'=>$project['parent_id']));
			if(!$parent_rs || !$parent_rs['status']){
				$this->error(P_Lang('父级项目未启用'));
			}
			$data_info['parent_rs'] = $parent_rs;
		}
		$rs['tag'] = $this->model('tag')->tag_list($rs['id'],'list');
		$rs = $this->content_format($rs,$data_info);
		$taglist = array('tag'=>$rs['tag'],'list'=>array('title'=>$rs['title']));
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
		$data_info['page_rs'] = $project;
		
		if($rs['cate_id'] && $project['cate']){
			$cate_root_rs = $this->call->phpok('_cate',array('pid'=>$project['id'],'cateid'=>$project['cate']));
			if(!$cate_root_rs || !$cate_root_rs['status']){
				$this->error(P_Lang('根分类信息不存在或未启用'),$this->url,5);
			}
			$data_info['cate_root_rs'] = $cate_root_rs;
			$cate_rs = $this->call->phpok('_cate',array("pid"=>$project['id'],'cateid'=>$rs['cate_id']));
			if(!$cate_rs || !$cate_rs['status']){
				$this->error(P_Lang('分类信息不存在或未启用'),$this->url,5);
			}
			if($cate_rs['parent_id']){
				$cate_parent_rs = $this->call->phpok('_cate',array('pid'=>$project['id'],'cateid'=>$cate_rs['parent_id']));
				if(!$cate_parent_rs || !$cate_root_rs['status']){
					$this->error(P_Lang('父级分类信息不存在或未启用'),$this->url,5);
				}
				$data_info['cate_parent_rs'] = $cate_parent_rs;
			}
			$data_info['cate_rs'] = $cate_rs;
		}
		$this->model('list')->add_hits($rs["id"]);

		$this->data('arc',$rs);
		$this->node('PHPOK_arc');
		$rs = $this->data('arc');
		$data_info['rs'] = $rs;
		$data_info['seo'] = $this->site["seo"];
		//判断这个主题是否支持评论及评论验证码
		if($project['comment_status']){
			$vcode = $this->model('site')->vcode($project['id'],'comment');
			$data_info['is_vcode'] = $vcode;
		}
		//是否增加积分
		if($this->session->val('user_id')){
			$this->model('wealth')->add_integral($rs['id'],$this->session->val('user_id'),'content',P_Lang('阅读#{id}',array('id'=>$rs['id'])));
		}
		$this->success($data_info);
	}

	private function content_format($rs,&$data_info)
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
		$data_info['pageid'] = $pageid;
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
				/*if($value['ext'] && $rs['tag']){
					$ext = unserialize($value['ext']);
					if($ext['inc_tag']){
						$taglist['list'][$value['identifier']] = $rs[$value['identifier']];
						$rs[$value['identifier']] = $this->model('tag')->tag_format($rs['tag'],$rs[$value['identifier']]);
					}
				}*/
			}
		}
		return $rs;
	}
}