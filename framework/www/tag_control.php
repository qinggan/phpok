<?php
/**
 * Tag标签读取
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年10月31日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'tag';
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('未指定标签'));
		}
		$tag_config = $this->model('tag')->config();
		if(!$tag_config){
			$tag_config = array();
		}
		$rs = $this->model('tag')->get_one($title,'identifier',$this->site['id']);
		if(!$rs){
			$rs = $this->model('tag')->get_one($title,'id',$this->site['id']);
		}
		if(!$rs){
			if(!$this->lib('trans')->is_utf8($title)){
				$title = $this->lib('trans')->charset($title,'GBK','UTF-8');
			}
			if(!$title){
				$this->error(P_Lang('标签异常，请联系管理员'));
				exit;
			}
			$rs = $this->model('tag')->get_one($title,'title',$this->site['id']);
		}
		if(!$rs){
			$this->error(P_Lang('标签信息不存在'));
		}
		//如果用户自定义了模板，则使用自定义模板
		if($rs['tpl'] && $this->tpl->check_exists($rs['tpl'])){
			$tplfile = $rs['tpl'];
		}
		$this->model('tag')->add_hits($rs['id']);
		if($rs['url']){
			//判断是否外链，仅限外部链接才会跳转，内部链接不跳转
			if(strpos($rs['url'],'http://') !== false || strpos($rs['url'],'https://') !== false){
				$host = parse_url($rs['url'],PHP_URL_HOST);
				$domain = $this->lib('server')->domain($this->config['get_domain_method']);
				if(!$host || !$domain){
					$this->error(P_Lang('数据异常'));
				}
				$host = strtolower($host);
				$domain = strtolower($domain);
				if($host != $domain){
					$this->_location($rs['url']);
				}
			}
			if(strpos($rs['url'],'tag/') !== false || strpos($rs['url'],'=tag') !== false){
				$this->error(P_Lang('自定义链接不允许使用tag标签链'));
			}
			$this->_location($rs['url']);
		}
		//读取列表
		$total = $this->model('tag')->tag_total($rs['id']);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		
		if(!$tag_config['psize']){
			$tag_config['psize'] = $this->config['psize'] ? $this->config['psize'] : 30;
		}
		$psize = $tag_config['psize'];
		$offset = ($pageid-1) * $psize;
		$idlist = $this->model('tag')->id_list($rs['id'],$offset,$psize);
		if(!$idlist){
			$this->view($tplfile);
			exit;
		}
		$rslist = false;
		foreach($idlist as $key=>$value){
			if(substr($value['id'],0,1) == 'p'){
				$tmp = substr($value['id'],1);
				$tmp = $this->call->phpok('_project',array('pid'=>$tmp));
				if($tmp){
					$rslist[] = $tmp;
				}
			}elseif(substr($value['id'],0,1) == 'c'){
				$tmp = substr($value['id'],1);
				$cate_rs = $this->model('cate')->get_one($tmp);
				if($cate_rs['parent_id']){
					$root_cate_id = $cate_rs['parent_id'];
					$this->model('cate')->get_root_id($root_cate_id,$cate_rs['parent_id']);
				}else{
					$root_cate_id = $cate_rs['id'];
				}
				$project_info = $this->model('project')->get_one_condition("cate='".$root_cate_id."' AND status=1");
				$tmp = $this->call->phpok('_cate',array('pid'=>$project_info['id'],'cateid'=>$tmp));
				if($tmp){
					$rslist[] = $tmp;
				}
			}else{
				$tmp = $this->call->phpok('_arc',array('title_id'=>$value['id']));
				if($tmp){
					$rslist[] = $tmp;
				}
			}
		}
		$this->assign("rslist",$rslist);
		$pageurl = $this->url('tag','','title='.rawurlencode($title));
		$this->assign("pageurl",$pageurl);
		$this->assign("total",$total);
		$this->assign("pageid",$pageid);
		$this->assign("psize",$psize);
		$this->assign("keywords",$rs['title']);
		//读取节点扩展
		$nodelist = $this->model('tag')->node_list($rs['id'],true);
		if($nodelist){
			foreach($nodelist as $key=>$value){
				if($value['ids']){
					$tmplist = explode(',',$value['ids']);
					$value['clist'] = $value['plist'] = $value['tlist'] = array();
					foreach($tmplist as $k=>$v){
						if(substr($v,0,1) == 'p'){
							$value['plist'][] = substr($v,1);
						}
						if(substr($v,0,1) == 'c'){
							$value['clist'][] = substr($v,1);
						}
						if(is_numeric($v)){
							$value['tlist'][] = $v;
						}
					}
					$nodelist[$key] = $value;
				}
				$rs[$value['identifier']] = $value;
			}
			$this->assign('nodelist',$nodelist);
		}
		$this->assign("rs",$rs);
		$this->phpok_seo();
		$this->view($tplfile);
	}
}