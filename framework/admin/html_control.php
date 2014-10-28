<?php
/*****************************************************************************************
	文件： {phpok}/admin/html_control.php
	备注： 静态页批量生成处理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年7月30日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class html_control extends phpok_control
{
	private $obj;
	function __construct()
	{
		parent::control();
		$this->obj = $this->_object();
		if(file_exists($this->dir_phpok.'www/global.func.php'))
		{
			include_once($this->dir_phpok.'www/global.func.php');
		}
	}

	//显示管理界面
	function index_f()
	{
		$site = $this->model('site')->get_one($_SESSION['admin_site_id']);
		if($site['url_type'] != 'html')
		{
			error("未启用静态页功能，此项不能使用");
		}
		if(!$this->obj->status || $this->obj->status != 'ok')
		{
			error(P_Lang($this->obj->error),'','error');
		}
		$tplinfo = $this->obj->tplinfo;
		$tlist = $this->model('html')->list_tpl($this->dir_root.'tpl/'.$tplinfo['folder'].'/',$tplinfo['ext']);
		if(!$tlist)
		{
			error(P_Lang("没有模板文件，请检查"),'','error');
		}

		$rslist = $this->model('project')->get_all_project($_SESSION["admin_site_id"]);
		if($rslist)
		{
			foreach($rslist as $key=>$value)
			{
				//如果使用首页标识，则跳过
				if($value['identifier'] == 'index')
				{
					unset($rslist[$key]);
					continue;
				}
				if($value['module'])
				{
					$tpl_content = $value['tpl_content'] ? $value['tpl_content'] : $value['identifier'].'_content';
					$tpl_list = $value['tpl_list'] ? $value['tpl_list'] : $value['identifier'].'_list';
					$tpl_index = $value['tpl_index'] ? $value['tpl_index'] : $value['identifier'].'_index';
					if(!in_array($tpl_content,$tlist) && !in_array($tpl_list,$tlist) && !in_array($tpl_index,$tlist))
					{
						if($value['parent_id'])
						{
							$parent_rs = $this->model('project')->get_one($value['parent_id'],false);
							$tpl_content = $parent_rs['tpl_content'] ? $parent_rs['tpl_content'] : $parent_rs['identifier'].'_content';
							$tpl_list = $parent_rs['tpl_list'] ? $parent_rs['tpl_list'] : $parent_rs['identifier'].'_list';
							$tpl_index = $parent_rs['tpl_index'] ? $parent_rs['tpl_index'] : $parent_rs['identifier'].'_index';
							if(!in_array($tpl_content,$tlist) && !in_array($tpl_list,$tlist) && !in_array($tpl_index,$tlist))
							{
								unset($rslist[$key]);
								continue;
							}
						}
						else
						{
							unset($rslist[$key]);
							continue;
						}
						
					}
					$_note = array();
					$_type = array();
					if(in_array($tpl_content,$tlist))
					{
						$_note[] = '详细';
						$_type[] = 'content';
					}
					if(in_array($tpl_list,$tlist))
					{
						$_note[] = '列表';
						$_type[] = 'list';
					}
					if(in_array($tpl_index,$tlist))
					{
						$_note[] = '封面';
						$_type[] = 'index';
					}
					$value['_note'] = implode(' + ',$_note);
					$value['_type'] = $_type;
				}
				else
				{
					$tpl = $value['tpl_index'] ? $value['tpl_index'] : ($value['tpl_list'] ? $value['tpl_list'] : $value['tpl_content']);
					if(!$tpl)
					{
						$tpl = $value['identifier'].'_page';
						if(!in_array($tpl,$tlist) && $value['parent_id'])
						{
							$parent_rs = $this->model('project')->get_one($value['parent_id'],false);
							$tpl = $parent_rs['tpl_index'] ? $parent_rs['tpl_index'] : ($parent_rs['tpl_list'] ? $parent_rs['tpl_list'] : $parent_rs['tpl_content']);
							if(!$tpl)
							{
								$tpl = $parent_rs['identifier'].'_page';
							}
						}
					}
					if(!in_array($tpl,$tlist))
					{
						unset($rslist[$key]);
						continue;
					}
					$value['_note'] = '项目（独立单页）';
					$value['_type'] = array("page");
				}
				$rslist[$key] = $value;
			}
			$this->assign('rslist',$rslist);
		}
		$types = array('index'=>'封面','list'=>"列表","content"=>'内容','page'=>"项目单页");
		$this->assign('types',$types);
		$this->view('html_index');
	}

	//创建静态页
	function create_f()
	{
		$cache_id = $this->tmp_cache_id();
		$file = $this->dir_root.'data/cache/'.$cache_id.'.php';
		if(!file_exists($file))
		{
			$this->json('未检测到生成的缓存文件');
		}
		$startid = $this->get('startid','int');
		include_once($file);
		if(!$list[$startid])
		{
			$this->json('没有找到相关参数');
		}
		$info = $list[$startid];
		//获取站点信息，及更新站点默认SEO
		$site = $this->model('site')->get_one($_SESSION['admin_site_id']);
		$ext_list = $this->model('site')->site_config($site["id"]);
		if($ext_list)
		{
			$site = array_merge($ext_list,$site);
		}
		$this->obj->tpl->assign('config',$site);
		$this->obj->tpl->assign('sys',$this->config);
		$this->_seo($site,$site);
		//更新首页
		if($info['type'] == 'homepage')
		{
			$rs = $this->html_index($site);
			//如果首页更新不成功，禁止进入下一步
			if($rs['status'] != 'ok')
			{
				$this->json($info['content']);
			}
			unset($rs['status']);
			$nextid = $startid + 1;
			if($list[$nextid])
			{
				$rs['startid'] = $nextid;
			}
			$this->json($rs,true);
		}
		if($info['type'] == 'page')
		{
			$page_rs = $this->obj->call->phpok('_project',array('pid'=>$info['id'],'project_ext'=>true));
			if(!$page_rs || !$page_rs['status'])
			{
				$this->json(P_Lang('栏目不存在或未启用'));
			}
			$this->obj->tpl->assign('page_rs',$page_rs);
			$this->_seo($page_rs,$site);
			if($page_rs['parent_id'])
			{
				$parent_rs = $this->obj->call->phpok('_project','pid='.$page_rs['parent_id'].'&project_ext=1');
				if(!$parent_rs || !$parent_rs['status'])
				{
					$this->json(P_Lang('父级项目未启用'));
				}
				$this->obj->tpl->assign("parent_rs",$parent_rs);
			}
			$tpl = $page_rs["tpl_index"] ? $page_rs["tpl_index"] : ($page_rs["tpl_list"] ? $page_rs["tpl_list"] : $page_rs["tpl_content"]);
			if(!$tpl && $page_rs["parent_id"] && $parent_rs)
			{
				$tpl = $parent_rs["tpl_index"] ? $parent_rs["tpl_index"] : ($parent_rs["tpl_list"] ? $parent_rs["tpl_list"] : $parent_rs["tpl_content"]);
				if(!$tpl)
				{
					$tpl = $parent_rs["identifier"]."_page";
					if(!$this->obj->tpl->check_exists($tpl))
					{
						$tpl = '';
					}
				}
			}
			if(!$tpl)
			{
				$tpl = $page_rs["identifier"]."_page";
			}
			if(!$this->obj->tpl->check_exists($tpl))
			{
				$this->json(P_Lang('未配置相应的模板'));
			}
			$content = $this->obj->tpl->fetch($tpl);
			$content = $this->_html($content,$site);
			if(!$content)
			{
				$this->json(P_Lang('获取内容失败'));
			}
			$file_dir = $this->dir_root;
			$url = 'http://'.$site['domain'].$site['dir'];
			if($site['html_root_dir'] != '/')
			{
				$file_dir .= $site['html_root_dir'];
				$url .= $site['html_root_dir'];
			}
			$url .= $page_rs['identifier'].'.html';
			$this->lib('file')->vim($content,$file_dir.$page_rs['identifier'].'.html');
			$tip = '更新<b style="color:darkblue;">'.$page_rs['title'].'</b>完成，<a href="'.$url.'" target="_blank" class="red">点这里访问</a>！';
			$data = array('info'=>$tip);
			$nextid = $startid + 1;
			if($list[$nextid])
			{
				$data['startid'] = $nextid;
			}
			$this->json($data,true);
		}
		if($info['type'] == 'content')
		{
			$dt = array('site_id'=>$_SESSION['admin_site_id']);
			$dt['id'] = $info['id'];
			$rs = $this->call->phpok('_arc',$dt);
			$page_rs = $this->obj->call->phpok('_project',array('pid'=>$rs['project_id']['id'],'project_ext'=>true));
			if(!$page_rs || !$page_rs['status'])
			{
				$this->json(P_Lang('栏目不存在或未启用'));
			}
			$this->obj->tpl->assign('page_rs',$page_rs);
			$this->_seo($page_rs,$site);
			if($page_rs['parent_id'])
			{
				$parent_rs = $this->obj->call->phpok('_project','pid='.$page_rs['parent_id'].'&project_ext=1');
				if(!$parent_rs || !$parent_rs['status'])
				{
					$this->json(P_Lang('父级项目未启用'));
				}
				$this->obj->tpl->assign("parent_rs",$parent_rs);
			}
			if($rs['cate_id'])
			{
				$cate_rs = $rs['cate_id'];
				$this->obj->tpl->assign("cate_rs",$cate_rs);
				//父级分类
				if($cate_rs['parent_id'] && $cate_rs['parent_id'] != $page_rs['cate'])
				{
					$dt = array('site_id'=>$rs['site_id'],'pid'=>$page_rs['id'],'cateid'=>$rs['parent_id'],'cate_ext'=>1);
					$cate_parent_rs = $this->call->phpok("_cate",$dt);
					$this->obj->tpl->assign("cate_parent_rs",$cate_parent_rs);
				}
			}
			//获取模板配置
			$tpl = $rs['tpl'];
			if(!$tpl && $cate_rs['tpl_content']) $tpl = $cate_rs['tpl_content'];
			if(!$tpl && $cate_parent_rs['tpl_content']) $tpl = $cate_parent_rs['tpl_content'];
			if(!$tpl && $page_rs['tpl_content']) $tpl = $page_rs['tpl_content'];
			if(!$tpl && $parent_rs['tpl_content']) $tpl = $parent_rs['tpl_content'];
			if(!$tpl) $tpl = $page_rs['identifier'].'_content';
			if(!$this->obj->tpl->check_exists($tpl))
			{
				$this->json('未配置内容模板');
			}
			$this->_seo($rs,$site);
			$rs['project_id'] = $project_rs['id'];
			if($rs['cate_id'] && $cate_rs)
			{
				$rs['cate_id'] = $cate_rs['id'];
			}
			$this->obj->tpl->assign("rs",$rs);
			$content = $this->obj->tpl->fetch($tpl);
			$content = $this->_html($content,$site);
			if(!$content)
			{
				$this->json(P_Lang('获取内容失败'));
			}
			$file_dir = $this->dir_root;
			$url = 'http://'.$site['domain'].$site['dir'];
			if($site['html_root_dir'] != '/')
			{
				$file_dir .= $site['html_root_dir'];
				$url .= $site['html_root_dir'];
			}
			if($rs['identifier'])
			{
				$url .= $rs['identifier'].'.html';
				$file = $rs['identifier'].'.html';
			}
			else
			{
				if($site['html_content_type'] && $site['html_content_type'] != 'empty')
				{
					$file_dir .= date($site['html_content_type'],$rs['dateline']);
					$url .= date($site['html_content_type'],$rs['dateline']);
				}
				$url .= $rs['id'].'.html';
				$file = $rs['id'].'.html';
			}
			//$url .= $page_rs['identifier'].'.html';
			//$dir = $this->dir_root.'html/'.date("Ym/d/",$rs['dateline']);
			$this->lib('file')->make($file_dir);
			$this->lib('file')->vim($content,$file_dir.$file);
			//$url = $url;
			$tip = '更新<b style="color:darkblue;">'.$rs['title'].'</b>完成，<a href="'.$url.'" target="_blank" class="red">点这里访问</a>！';
			$data = array('info'=>$tip);
			$nextid = $startid + 1;
			if($list[$nextid])
			{
				$data['startid'] = $nextid;
			}
			$this->json($data,true);
		}
		if($info['type'] == 'index')
		{
			$page_rs = $this->obj->call->phpok('_project',array('pid'=>$info['id'],'project_ext'=>true));
			if(!$page_rs || !$page_rs['status'] || !$page_rs['tpl_index'])
			{
				$this->json(P_Lang('栏目不存在或未启用或未指定封面模板'));
			}
			$this->obj->tpl->assign('page_rs',$page_rs);
			$this->_seo($page_rs,$site);
			if($page_rs['parent_id'])
			{
				$parent_rs = $this->obj->call->phpok('_project','pid='.$page_rs['parent_id'].'&project_ext=1');
				if(!$parent_rs || !$parent_rs['status'])
				{
					$this->json(P_Lang('父级项目未启用'));
				}
				$this->obj->tpl->assign("parent_rs",$parent_rs);
			}
			$tpl = $page_rs["tpl_index"];
			if(!$this->obj->tpl->check_exists($tpl))
			{
				$this->json(P_Lang('未配置相应的模板'));
			}
			$content = $this->obj->tpl->fetch($tpl);
			$content = $this->_html($content,$site);
			if(!$content)
			{
				$this->json(P_Lang('获取内容失败'));
			}
			$file_dir = $this->dir_root;
			$url = 'http://'.$site['domain'].$site['dir'];
			if($site['html_root_dir'] != '/')
			{
				$file_dir .= $site['html_root_dir'];
				$url .= $site['html_root_dir'];
			}
			$url .= $page_rs['identifier'].'.html';
			$this->lib('file')->vim($content,$file_dir.$page_rs['identifier'].'.html');
			$tip = '更新<b style="color:darkblue;">'.$page_rs['title'].'</b>完成，<a href="'.$url.'" target="_blank" class="red">点这里访问</a>！';
			$data = array('info'=>$tip);
			$nextid = $startid + 1;
			if($list[$nextid])
			{
				$data['startid'] = $nextid;
			}
			$this->json($data,true);
		}
		if($info['type'] == 'list')
		{
			$page_rs = $this->obj->call->phpok('_project',array('pid'=>$info['id'],'project_ext'=>true));
			if(!$page_rs || !$page_rs['status'])
			{
				$this->json(P_Lang('栏目不存在或未启用-列表'));
			}
			$this->obj->tpl->assign('page_rs',$page_rs);
			$this->_seo($page_rs,$site);
			$tpl = $page_rs['tpl_list'];
			if($page_rs['parent_id'])
			{
				$parent_rs = $this->obj->call->phpok('_project','pid='.$page_rs['parent_id'].'&project_ext=1');
				if(!$parent_rs || !$parent_rs['status'])
				{
					$this->json(P_Lang('父级项目未启用'));
				}
				$this->obj->tpl->assign("parent_rs",$parent_rs);
				if(!$tpl) $tpl = $parent_rs['tpl_list'];
			}
			$psize = $page_rs['psize'];
			if($info['cateid'])
			{
				$dt = array('site_id'=>$page_rs['site_id'],'pid'=>$page_rs['id'],'cateid'=>$info['cateid'],'cate_ext'=>1);
				$cate_rs = $this->call->phpok('_cate',$dt);
				//如果分类有自定义模板
				if($cate_rs['tpl_list']) $tpl = $cate_rs['tpl_list'];
				//
				if($cate_rs['parent_id'])
				{
					$dt = array('site_id'=>$page_rs['site_id'],'pid'=>$page_rs['id'],'cateid'=>$cate_rs['parent_id'],'cate_ext'=>1);
					$cate_parent_rs = $this->call->phpok("_cate",$dt);
					$this->obj->tpl->assign("cate_parent_rs",$cate_parent_rs);
				}
				$this->obj->tpl->assign("cate_rs",$cate_rs);
				if($cate_rs['psize'])
				{
					$psize = $cate_rs['psize'];
				}
			}
			$offset = $info['pageid'] ? ($info['pageid'] - 1) : 0;
			$pageurl = $this->config['www_file']."?id=".$page_rs['identifier'];
			if($info['cateid'] && $cate_rs)
			{
				$pageurl .= "&cate=".$cate_rs['identifier'];
			}
			/*if($info['pageid']>1)
			{
				$pageurl .= "&".$this->config['pageid']."=".$info['pageid'];
			}*/
			$this->obj->tpl->assign('pageurl',$pageurl);
			$this->obj->tpl->assign('psize',$psize);
			$this->obj->tpl->assign('pageid',$info['pageid']);
			//读取主题数
			$total = $this->model('html')->get_subject_total($page_rs['id'],$page_rs['module'],$page_rs['site_id'],$info['idstring']);
			if(!$total)
			{
				$this->json('栏目下没有信息为空，不支持生成静态页');
			}
			$this->obj->tpl->assign('total',$total);
			if(!$tpl) $tpl = $page_rs['identifier'].'_list';
			if(!$this->obj->tpl->check_exists($tpl) && $parent_rs)
			{
				$tpl = $parent_rs['identifier'].'_list';
				if(!$this->obj->tpl->check_exists($tpl))
				{
					$this->json('没有找到模板：'.$tpl.'，请检查！');
				}
			}
			$dt = array('pid'=>$page_rs['id'],'offset'=>$offset,'psize'=>$psize,'in_text'=>1,'is_list'=>1);
			if($cate_rs)
			{
				$dt['cateid'] = $cate_rs['id'];
			}
			$rslist = $this->call->phpok('_arclist',$dt);
			$this->obj->tpl->assign("rslist",$rslist);
			$content = $this->obj->tpl->fetch($tpl);
			$content = $this->_html($content,$site);
			if(!$content)
			{
				$this->json(P_Lang('获取内容失败'));
			}
			$file_dir = $this->dir_root;
			$url = 'http://'.$site['domain'].$site['dir'];
			if($site['html_root_dir'] != '/')
			{
				$file_dir .= $site['html_root_dir'];
				$url .= $site['html_root_dir'];
			}
			
			if($cate_rs)
			{
				$file_dir .= $page_rs['identifier'].'/';
				$this->lib('file')->make($file_dir);
				$file = $file_dir.$cate_rs['identifier'];
				$url .= $page_rs['identifier'].'/'.$cate_rs['identifier'];
			}
			else
			{
				$file = $file_dir.$page_rs['identifier'];
				$url .= $page_rs['identifier'];
			}
			if($info['pageid']>1)
			{
				$file .= '-'.$info['pageid'];
				$url .= '-'.$info['pageid'];
			}
			$file .= '.html';
			$url .= '.html';
			$this->lib('file')->vim($content,$file);
			$title = $page_rs['title'];
			if($cate_rs) $title .= ' - '.$cate_rs['title'];
			$title .= ' - 列表';
			$tip = '更新<b style="color:#6633FF;">'.$title.'</b>完成，<a href="'.$url.'" target="_blank" class="red">点这里访问</a>！';
			$data = array('info'=>$tip);
			$nextid = $startid + 1;
			if($list[$nextid])
			{
				$data['startid'] = $nextid;
			}
			$this->json($data,true);
		}
		$data = array('info'=>'更新结束！');
		$this->json($data,true);
	}

	function html_index($site)
	{
		if(!$this->obj->status || $this->obj->status != 'ok')
		{
			return array('status'=>'error','content'=>$this->obj->error);
		}
		
		//判断首页是否有绑定index项目
		$page_rs = $this->model('project')->identifier_one('index',$_SESSION['admin_site_id'],true);
		if($page_rs)
		{
			$this->obj->tpl->assign('page_rs',$page_rs);
			$this->_seo($page_rs,$site);
		}
		//获取内容
		$content = $this->obj->tpl->fetch("index",'file',true);
		//内容正则替换
		$content = $this->_html($content,$site);
		if(!$content)
		{
			return array('status'=>'error','content'=>'获取内容失败');
		}
		//设置生成的路径
		
		$site_dir = $this->dir_root;
		$url = 'http://'.$site['domain'].$site['dir'];
		if($site['html_root_dir'] != '/')
		{
			$site_dir .= $site['html_root_dir'];
			$url .= $site['html_root_dir'];
		}
		$url .= 'index.html';
		$this->lib('file')->vim($content,$site_dir.'index.html');
		$tip = '首页更新完成，<a href="'.$url.'" target="_blank" class="red">点这里访问站点首页</a>！';
		return array('status'=>'ok','info'=>$tip);
	}

	//获取要更新统计数
	function count_f()
	{
		$ids = $this->get('ids');
		if(!$ids)
		{
			$this->json('未指定要生成页面的类型');
		}
		$this->lib('file')->rm($this->dir_root."data/cache/"); //删除缓存
		$site = $this->model('site')->get_one($_SESSION['admin_site_id']);
		$ext_list = $this->model('site')->site_config($site["id"]);
		if($ext_list)
		{
			$site = array_merge($ext_list,$site);
		}
		$list = explode(",",$ids);
		$tlist = array();
		$array = array('index','list','content','page');
		foreach($list as $key=>$value)
		{
			if($value == 'index')
			{
				$tlist[] = array('id'=>'index','type'=>'homepage');
				continue;
			}
			//参数不符合要求跳过
			$value = intval($value);
			if(!$value) continue;
			//未指定生成的类型，跳过
			$types = $this->get("types_".$value);
			if(!$types) continue;
			//项目不存在，跳过
			$project = $this->model('html')->project($value);
			if(!$project) continue;
			$types = explode(",",$types);
			foreach($types as $k=>$v)
			{
				//存在项目信息，但同时也绑定模块，跳过
				if($v == 'page')
				{
					if($project['module']) continue;
					$tlist[] = array('id'=>$value,'type'=>'page');
				}
				if($v == 'content' && $project['module'])
				{
					$tmplist = $this->model('html')->title_list($project['site_id'],$project['id'],$project['module']);
					if(!$tmplist) continue;
					foreach($tmplist as $kk=>$vv)
					{
						$tlist[] = array('id'=>$vv['id'],'type'=>'content');
					}
				}
				if($v == 'list' && $project['module'])
				{
					$tmplist = $this->_getlist($project);
					if(!$tmplist) continue;
					foreach($tmplist as $kk=>$vv)
					{
						$tlist[] = $vv;
					}
				}
				if($v == 'index' && $project['module'])
				{
					$tlist[] = array('id'=>$value,'type'=>'index');
				}
			}
		}
		$total = count($tlist);
		if($total<1)
		{
			$this->json('要生成的网页数量少于1，请检查');
		}
		$cache_id = $this->tmp_cache_id();
		$this->lib('file')->vi($tlist,$this->dir_root.'data/cache/'.$cache_id.'.php','list');
		$this->lib('file')->rm($this->dir_root."data/tpl_html/"); //删除编译后的缓存
		$this->json($total,true);
	}

	function tmp_cache_id()
	{
		return md5($_SESSION['admin_id'].'_'.$this->session->sessid());
	}

	private function _getlist($page_rs)
	{
		$list = array();
		if(!$page_rs['tpl_index'])
		{
			$total = $this->model('html')->get_subject_total($page_rs['id'],$page_rs['module'],$page_rs['site_id']);
			if($total<1)
			{
				return false;
			}
			$psize = $page_rs['psize'] ? $page_rs['psize'] : 30;
			$tmp = intval($total/$psize);
			if($total%$psize != '')
			{
				$tmp++;
			}
			for($i=0;$i<$tmp;$i++)
			{
				$list[] = array('id'=>$page_rs['id'],"type"=>'list','pageid'=>($i+1));
			}
		}
		if(!$page_rs['cate'])
		{
			return $list;
		}
		//取得绑定项目的分类
		$catelist = $this->model('html')->get_catelist($page_rs['site_id'],$page_rs['cate']);
		if(!$catelist)
		{
			return $list;
		}
		foreach($catelist as $key=>$value)
		{
			//读主题数
			$sublist = $this->model('html')->get_catelist($page_rs['site_id'],$value['id']);
			if($sublist)
			{
				$idlist = array_keys($sublist);
				$idlist[] = $value['id'];
			}
			else
			{
				$idlist[] = $value['id'];
			}
			$idstring = implode(",",$idlist);
			$subject_total = $this->model('html')->get_subject_total($page_rs['id'],$page_rs['module'],$page_rs['site_id'],$idstring);
			if(!$subject_total)
			{
				continue;
			}
			$psize = $value['psize'] ? $value['psize'] : ($page_rs['psize'] ? $page_rs['psize'] : '30');
			$tmp = intval($subject_total/$psize);
			if($subject_total%$psize != '')
			{
				$tmp++;
			}
			for($i=0;$i<$tmp;$i++)
			{
				$total++;
				$list[] = array('id'=>$page_rs['id'],"type"=>'list','pageid'=>($i+1),"idstring"=>$idstring,"cateid"=>$value['id']);
			}
		}
		return $list;
	}

	//生成的页面进行静态化格式化
	private function _html($content,$site=array())
	{
		if(!$content || !$site || !$site['domain'])
		{
			return false;
		}
		$list = array();
		$url = $site['domain'].$site['dir'].$this->config['www_file'];
		preg_match_all("/[\"|'](".addslashes($this->config['www_file'])."|".addslashes($this->config['admin_file']).")(\?*.*)[\"|']/isU",$content,$array);
		if($array && $array[2] && $array[1])
		{
			foreach($array[2] as $key=>$value)
			{
				if($value && trim($value))
				{
					$list[] = $array[1][$key].$value;
				}
			}
		}
		$list = array_unique($list);
		sort($list);
		$list = $this->_sort($list);
		$rslist = array();
		//保留字
		$chk = array('cart','search','login','logout','download','user','usercp');
		$html_root_dir = ($site['html_root_dir'] && $site['html_root_dir'] != '/') ? $site['html_root_dir'] : '';
		foreach($list as $key=>$value)
		{
			$value = preg_replace("/[\?|&]\_noCache=[0-9\.]+/is",'',$value);
			//项目+分类+分页
			$pcntl = "/[".addslashes($this->config['www_file'])."|".addslashes($this->config['admin_file'])."]+\?";
			$pcntl.= "([id|".$this->config['ctrl_id']."]+)=([a-zA-Z\_\-0-9\%]+)\&";
			$pcntl.= "([cate|".$this->config['func_id']."]+)=([a-zA-Z\_\-0-9\%]+)\&";
			$pcntl.= "[".$this->config['pageid']."]+=([0-9]+)/is";
			$tmp = preg_replace($pcntl,$html_root_dir."\\2/\\4-\\5.html",$value);
			if($tmp && $tmp != $value)
			{
				$content = str_replace($value,$tmp,$content);
				continue;
			}
			//项目+分页
			$pcntl = "/[".addslashes($this->config['www_file'])."|".addslashes($this->config['admin_file'])."]+\?";
			$pcntl.= "([id|".$this->config['ctrl_id']."]+)=([a-zA-Z\_\-0-9\%]+)\&";
			$pcntl.= "[".$this->config['pageid']."]+=([0-9]+)/is";
			$tmp = preg_replace($pcntl,$html_root_dir."\\2-\\3.html",$value);
			if($tmp && $tmp != $value)
			{
				$content = str_replace($value,$tmp,$content);
				continue;
			}
			//项目+分类
			$pcntl = "/[".addslashes($this->config['www_file'])."|".addslashes($this->config['admin_file'])."]+\?";
			$pcntl.= "([id|".$this->config['ctrl_id']."]+)=([a-zA-Z\_\-0-9\%]+)\&";
			$pcntl.= "([cate|".$this->config['func_id']."]+)=([a-zA-Z\_\-0-9\%]+)/is";
			$tmp = preg_replace($pcntl,$html_root_dir."\\2/\\4.html",$value);
			if($tmp && $tmp != $value)
			{
				$content = str_replace($value,$tmp,$content);
				continue;
			}
		
			//更新项目
			$pcntl = "/[".addslashes($this->config['www_file'])."|".addslashes($this->config['admin_file'])."]+\?";
			$pcntl.= "([id|".$this->config['ctrl_id']."]+)=([a-zA-Z\_\-]+[a-zA-Z\_\-0-9\%]+)/is";
			$tmp = preg_replace($pcntl,$html_root_dir."\\2.html",$value);
			if($tmp && $tmp != $value)
			{
				if(strpos($tmp,'&') !== false)
				{
					$t1 = strstr($tmp,'&');
					$t2 = str_replace($t1,'',$tmp);
					$t2 = substr(basename($t2),0,-5);
					if(in_array($t2,$chk))
					{
						$tmp = $this->config['www_file'].'?'.$this->config['ctrl_id'].'='.$t2.$t1;
					}
					else
					{
						$tmp = $this->config['www_file'].'?id='.$t2.$t1;
					}
				}
				else
				{
					$t2 = substr(basename($tmp),0,-5);
					if(in_array($t2,$chk))
					{
						$tmp = $this->config['www_file'].'?'.$this->config['ctrl_id'].'='.$t2;
					}
				}
				$content = str_replace($value,$tmp,$content);
				continue;
			}
			$pcntl = "/[".addslashes($this->config['www_file'])."|".addslashes($this->config['admin_file'])."]+\?";
			$pcntl.= "([id|".$this->config['ctrl_id']."]+)=([0-9]+)/is";
			//更新主题
			$id = preg_replace($pcntl,"\\2",$value);
			if($id)
			{
				$folder = '';
				if($site['html_content_type'] && $site['html_content_type'] != 'empty')
				{
					$folder = $this->model('html')->subject_folder($id,$site['html_content_type']);
				}
				$tmp = $folder ? $html_root_dir.$folder.$id.'.html' : $id.'.html';
			}
			if($tmp && $tmp != $value)
			{
				$content = str_replace($value,$tmp,$content);
			}
		}
		$content = preg_replace("/[\?|&]\_noCache=[0-9\.]+/is",'',$content);
		return $content;
	}

	//冒泡排序
	private function _sort($array)
	{
		$count = count($array);
		if($count <= 0)
		{
			return false;
		}
		for($i=0; $i<$count; $i++)
		{
			for($k=$count-1; $k>$i; $k--)
			{
				if(strlen($array[$k]) > strlen($array[$k-1]))
				{
					$tmp = $array[$k];
					if($tmp)
					{
						$array[$k] = $array[$k-1];
						$array[$k-1] = $tmp;
					}
				}
			}
		}
		return $array;
	}

	//更新类
	private function _object()
	{
		$obj = new stdClass();
		$obj->status = 'ok';
		$tplinfo = $this->model('html')->get_tpl($_SESSION['admin_site_id']);
		if(!$tplinfo)
		{
			$obj->status = 'error';
			$obj->error = '未配置风格模板，请先到网站信息配置站点风格';
			return $obj;
		}
		//判断模板文件是否存在，不存在的模板将同时隐藏相应的项目信息
		if(!file_exists($this->dir_root.'tpl/'.$tplinfo['folder']))
		{
			$obj->status = 'error';
			$obj->error = '模板风格目录不存在，请检查';
			return $obj;
		}
		$tlist = $this->model('html')->list_tpl($this->dir_root.'tpl/'.$tplinfo['folder'].'/',$tplinfo['ext']);
		if(!$tlist)
		{
			$obj->status = 'error';
			$obj->error = '没有模板文件，请检查';
			return $obj;
		}
		$tpl_rs = array();
		$tpl_rs["id"] = $tplinfo["id"];
		$tpl_rs["dir_tpl"] = $tplinfo["folder"] ? "tpl/".$tplinfo["folder"]."/" : "tpl/www/";
		$tpl_rs["dir_cache"] = $this->dir_root."data/tpl_html/";
		$tpl_rs["dir_php"] = $this->dir_root;
		$tpl_rs["dir_root"] = $this->dir_root;
		if($tplinfo["folder_change"])
		{
			$tpl_rs["path_change"] = $tplinfo["folder_change"];
		}
		$tpl_rs["refresh_auto"] = $tplinfo["refresh_auto"] ? true : false;
		$tpl_rs["refresh"] = $tplinfo["refresh"] ? true : false;
		$tpl_rs["tpl_ext"] = $tplinfo["ext"] ? $tplinfo["ext"] : "html";
		//echo "<pre>".print_r($tpl_rs,true)."</pre>";
		$obj->tpl = new phpok_tpl($tpl_rs);
		$obj->tplinfo = $tplinfo;
		$obj->tpl_rs = $tpl_rs;
		include_once($this->dir_phpok."phpok_call.php");
		$GLOBALS['app']->call = new phpok_call();
		$obj->call = $GLOBALS['app']->call;
		return $obj;
	}

	//更新SEO信息
	private function _seo($rs,$site)
	{
		if(!$rs || !is_array($rs)) return false;
		$seo = $site['seo'] ? $site['seo'] : array();
		foreach($rs AS $key=>$value)
		{
			if(substr($key,0,3) == "seo" && $value && is_string($value))
			{
				$subkey = substr($key,4);
				if($subkey == "kw" || $subkey == "keywords" || $subkey == "keyword")
				{
					$seo["keywords"] = $value;
				}
				elseif($subkey == "desc" || $subkey == "description")
				{
					$seo["description"] = $value;
				}
				elseif($subkey == "title")
				{
					$seo["title"] = $value;
				}
				else
				{
					$seo[$subkey] = $value;
				}
			}
		}
		$this->obj->tpl->assign("seo",$seo);
	}

}

?>