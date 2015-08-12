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
	public function __construct()
	{
		parent::control();
		$this->obj = $this->_object();
		if(file_exists($this->dir_phpok.'www/global.func.php')){
			include_once($this->dir_phpok.'www/global.func.php');
		}
	}

	//显示管理界面
	public function index_f()
	{
		$site = $this->model('site')->get_one($_SESSION['admin_site_id']);
		if($site['url_type'] != 'html'){
			error(P_Lang('未启用静态页功能，此项不能使用'));
		}
		if(!$this->obj->status || $this->obj->status != 'ok'){
			error($this->obj->error,'','error');
		}
		$tplinfo = $this->obj->tplinfo;
		$tlist = $this->obj->tlist;
		/*$tlist = $this->model('html')->list_tpl($this->dir_root.'tpl/'.$tplinfo['folder'].'/',$tplinfo['ext']);
		if(!$tlist){
			error(P_Lang("没有模板文件，请检查"),'','error');
		}*/
		$types = array('index'=>P_Lang('封面'),'list'=>P_Lang('列表'),"content"=>P_Lang('内容'),'page'=>P_Lang('项目单页'));
		$this->assign('types',$types);
		$rslist = $this->model('project')->get_all_project($_SESSION["admin_site_id"]);
		if(!$rslist){
			$this->view('html_index');
			exit;
		}
		foreach($rslist as $key=>$value){
			//如果使用首页标识，则跳过
			if($value['identifier'] == 'index'){
				unset($rslist[$key]);
				continue;
			}
			if($value['module']){
				$tpl_content = $value['tpl_content'] ? $value['tpl_content'] : $value['identifier'].'_content';
				$tpl_list = $value['tpl_list'] ? $value['tpl_list'] : $value['identifier'].'_list';
				$tpl_index = $value['tpl_index'] ? $value['tpl_index'] : $value['identifier'].'_index';
				if(!in_array($tpl_content,$tlist) && !in_array($tpl_list,$tlist) && !in_array($tpl_index,$tlist)){
					if($value['parent_id']){
						$parent_rs = $this->model('project')->get_one($value['parent_id'],false);
						$tpl_content = $parent_rs['tpl_content'] ? $parent_rs['tpl_content'] : $parent_rs['identifier'].'_content';
						$tpl_list = $parent_rs['tpl_list'] ? $parent_rs['tpl_list'] : $parent_rs['identifier'].'_list';
						$tpl_index = $parent_rs['tpl_index'] ? $parent_rs['tpl_index'] : $parent_rs['identifier'].'_index';
						if(!in_array($tpl_content,$tlist) && !in_array($tpl_list,$tlist) && !in_array($tpl_index,$tlist)){
							unset($rslist[$key]);
							continue;
						}
					}else{
						unset($rslist[$key]);
						continue;
					}
				}
				$_note = array();
				$_type = array();
				if(in_array($tpl_content,$tlist)){
					$_note[] = P_Lang('详细');
					$_type[] = 'content';
				}
				if(in_array($tpl_list,$tlist)){
					$_note[] = P_Lang('列表');
					$_type[] = 'list';
				}
				if(in_array($tpl_index,$tlist)){
					$_note[] = P_Lang('封面');
					$_type[] = 'index';
				}
				$value['_note'] = implode(' + ',$_note);
				$value['_type'] = $_type;
			}else{
				$tpl = $value['tpl_index'] ? $value['tpl_index'] : ($value['tpl_list'] ? $value['tpl_list'] : $value['tpl_content']);
				if(!$tpl){
					$tpl = $value['identifier'].'_page';
					if(!in_array($tpl,$tlist) && $value['parent_id']){
						$parent_rs = $this->model('project')->get_one($value['parent_id'],false);
						$tpl = $parent_rs['tpl_index'] ? $parent_rs['tpl_index'] : ($parent_rs['tpl_list'] ? $parent_rs['tpl_list'] : $parent_rs['tpl_content']);
						if(!$tpl){
							$tpl = $parent_rs['identifier'].'_page';
						}
					}
				}
				if(!in_array($tpl,$tlist)){
					unset($rslist[$key]);
					continue;
				}
				$value['_note'] = P_Lang('项目（独立单页）');
				$value['_type'] = array("page");
			}
			$rslist[$key] = $value;
		}
		$this->assign('rslist',$rslist);
		$this->view('html_index');
	}

	//创建静态页
	function create_f()
	{
		$cache_id = $this->tmp_cache_id();
		$file = $this->dir_root.'data/cache/'.$cache_id.'.php';
		if(!file_exists($file)){
			$this->json(P_Lang('未检测到生成的缓存文件'));
		}
		$startid = $this->get('startid','int');
		include_once($file);
		if(!$list[$startid]){
			$this->json(P_Lang('没有找到相关参数'));
		}
		$info = $list[$startid];
		$site = $this->model('site')->get_one($_SESSION['admin_site_id']);
		if($info['type'] == 'homepage'){
			$rs = $this->html_index($site);
			if($rs['status'] != 'ok'){
				$this->json($info['content']);
			}
			unset($rs['status']);
			$nextid = $startid + 1;
			if($list[$nextid]){
				$rs['startid'] = $nextid;
			}
			$this->json($rs,true);
		}elseif($info['type'] == 'page'){
			$page_rs = $this->model('project')->get_one($info['id'],false);
			if(!$page_rs || !$page_rs['status']){
				$this->json(P_Lang('栏目不存在或未启用-列表'));
			}
			$url = 'http://'.$site['domain'].$site['dir'].'index.php?siteId='.$_SESSION['admin_site_id'].'&_html=1&id='.$page_rs['identifier'];
			$content = $this->lib('html')->get_content($url);
			$content = $this->_html($content,$site);
			if(!$content){
				$this->json(P_Lang('获取内容失败'));
			}
			$file_dir = $this->dir_root;
			$url = 'http://'.$site['domain'].$site['dir'];
			if($site['html_root_dir'] != '/'){
				$file_dir .= $site['html_root_dir'];
				$url .= $site['html_root_dir'];
			}
			$url .= $page_rs['identifier'].'.html';
			$this->lib('file')->vim($content,$file_dir.$page_rs['identifier'].'.html');
			$tip = '更新<b style="color:darkblue;">'.$page_rs['title'].'</b>完成，<a href="'.$url.'" target="_blank" class="red">点这里访问</a>！';
			$data = array('info'=>$tip);
			$nextid = $startid + 1;
			if($list[$nextid]){
				$data['startid'] = $nextid;
			}
			$this->json($data,true);
		}elseif($info['type'] == 'content'){
			$rs = $this->model('list')->call_one($info['id']);
			if(!$rs || !$rs['status']){
				$this->json(P_Lang('内容不存在或未启用'));
			}
			$tmpid = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
			$url = 'http://'.$site['domain'].$site['dir'].'index.php?siteId='.$_SESSION['admin_site_id'].'&_html=1&id='.$tmpid;
			$content = $this->lib('html')->get_content($url);
			$content = $this->_html($content,$site);
			if(!$content){
				$this->json(P_Lang('获取内容失败'));
			}
			$file_dir = $this->dir_root;
			$url = 'http://'.$site['domain'].$site['dir'];
			if($site['html_root_dir'] != '/'){
				$file_dir .= $site['html_root_dir'];
				$url .= $site['html_root_dir'];
			}
			if($rs['identifier']){
				$url .= $rs['identifier'].'.html';
				$file = $rs['identifier'].'.html';
			}else{
				if($site['html_content_type'] && $site['html_content_type'] != 'empty'){
					$file_dir .= date($site['html_content_type'],$rs['dateline']);
					$url .= date($site['html_content_type'],$rs['dateline']);
				}
				$url .= $rs['id'].'.html';
				$file = $rs['id'].'.html';
			}
			$this->lib('file')->make($file_dir);
			$this->lib('file')->vim($content,$file_dir.$file);
			$tip = P_Lang('更新').'<b style="color:darkblue;">'.$rs['title'].'</b>'.P_Lang('完成，').'<a href="'.$url.'" target="_blank" class="red">'.P_Lang('点这里访问').'</a>';
			$data = array('info'=>$tip);
			$nextid = $startid + 1;
			if($list[$nextid]){
				$data['startid'] = $nextid;
			}
			$this->json($data,true);
		}elseif($info['type'] == 'index'){
			$page_rs = $this->model('project')->get_one($info['id'],false);
			if(!$page_rs || !$page_rs['status']){
				$this->json(P_Lang('栏目不存在或未启用-列表'));
			}
			$url = 'http://'.$site['domain'].$site['dir'].'index.php?siteId='.$_SESSION['admin_site_id'].'&_html=1&id='.$page_rs['identifier'];
			$content = $this->lib('html')->get_content($url);
			$content = $this->_html($content,$site);
			if(!$content){
				$this->json(P_Lang('获取内容失败'));
			}
			$file_dir = $this->dir_root;
			$url = 'http://'.$site['domain'].$site['dir'];
			if($site['html_root_dir'] != '/'){
				$file_dir .= $site['html_root_dir'];
				$url .= $site['html_root_dir'];
			}
			$url .= $page_rs['identifier'].'.html';
			$this->lib('file')->vim($content,$file_dir.$page_rs['identifier'].'.html');
			$tip = P_Lang('更新').'<b style="color:darkblue;">'.$page_rs['title'].'</b>'.P_Lang('完成，').'<a href="'.$url.'" target="_blank" class="red">'.P_Lang('点这里访问').'</a>';
			$data = array('info'=>$tip);
			$nextid = $startid + 1;
			if($list[$nextid]){
				$data['startid'] = $nextid;
			}
			$this->json($data,true);
		}elseif($info['type'] == 'list'){
			$page_rs = $this->model('project')->get_one($info['id'],false);
			if(!$page_rs || !$page_rs['status']){
				$this->json(P_Lang('栏目不存在或未启用-列表'));
			}
			$url = 'http://'.$site['domain'].$site['dir'].'index.php?siteId='.$_SESSION['admin_site_id'].'&_html=1&id='.$page_rs['identifier'];
			$psize = $page_rs['psize'];
			if($info['cateid']){
				$cate_rs = $this->model('cate')->cate_info($info['cateid'],false);
				$url .="&cate=".$cate_rs['identifier'];
				if($cate_rs['psize']){
					$psize = $cate_rs['psize'];
				}
			}
			if($info['pageid']){
				$url .= "&pageid=".$info['pageid'];
			}
			$total = $this->model('html')->get_subject_total($page_rs['id'],$page_rs['module'],$page_rs['site_id'],$info['idstring']);
			if(!$total){
				$this->json(P_Lang('栏目下没有信息，不支持生成静态页'));
			}
			$content = $this->lib('html')->get_content($url);
			$content = $this->_html($content,$site);
			if(!$content){
				$this->json(P_Lang('获取内容失败'));
			}
			$file_dir = $this->dir_root;
			$url = 'http://'.$site['domain'].$site['dir'];
			if($site['html_root_dir'] != '/')
			{
				$file_dir .= $site['html_root_dir'];
				$url .= $site['html_root_dir'];
			}
			
			if($cate_rs){
				$file_dir .= $page_rs['identifier'].'/';
				$this->lib('file')->make($file_dir);
				$file = $file_dir.$cate_rs['identifier'];
				$url .= $page_rs['identifier'].'/'.$cate_rs['identifier'];
			}else{
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
			if($cate_rs){
				$title .= ' - '.$cate_rs['title'];
			}
			$title .= P_Lang(' - 列表');
			$tip = P_Lang('更新').'<b style="color:#6633FF;">'.$title.'</b>'.P_Lang('完成，').'<a href="'.$url.'" target="_blank" class="red">'.P_Lang('点这里访问').'</a>';
			$data = array('info'=>$tip);
			$nextid = $startid + 1;
			if($list[$nextid]){
				$data['startid'] = $nextid;
			}
			$this->json($data,true);
		}
		$data = array('info'=>P_Lang('更新结束'));
		$this->json($data,true);
	}

	function html_index($site)
	{
		if(!$this->obj->status || $this->obj->status != 'ok'){
			return array('status'=>'error','content'=>$this->obj->error);
		}
		$url = 'http://'.$site['domain'].$site['dir'].'index.php?siteId='.$_SESSION['admin_site_id'].'&_html=1';
		$content = $this->lib('html')->get_content($url);
		$content = $this->_html($content,$site);
		if(!$content){
			return array('status'=>'error','content'=>P_Lang('获取内容失败'));
		}
		$site_dir = $this->dir_root;
		$url = 'http://'.$site['domain'].$site['dir'];
		if($site['html_root_dir'] != '/'){
			$site_dir .= $site['html_root_dir'];
			$url .= $site['html_root_dir'];
		}
		$url .= 'index.html';
		$this->lib('file')->vim($content,$site_dir.'index.html');
		$tip = P_Lang('首页更新完成，').'<a href="'.$url.'" target="_blank" class="red">'.P_Lang('点这里访问站点首页').'</a>';
		return array('status'=>'ok','info'=>$tip);
	}

	//获取要更新统计数
	function count_f()
	{
		$ids = $this->get('ids');
		if(!$ids)
		{
			$this->json(P_Lang('未指定要生成页面的类型'));
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
			$this->json(P_Lang('要生成的网页数量少于1，请检查'));
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

	private function _tmp_addslashes($url){
		return str_replace('/','\/',addslashes($url));
	}

	//生成的页面进行静态化格式化
	private function _html($content,$site=array())
	{
		if(!$content || !$site || !$site['domain']){
			return false;
		}
		$list = array();
		$url = 'http://'.$site['domain'].$site['dir'].$this->config['www_file'];
		preg_match_all("/[\"|'](".$this->_tmp_addslashes($url).")(\?*.*)[\"|']/isU",$content,$array);
		if($array && $array[2] && $array[1]){
			foreach($array[2] as $key=>$value){
				if($value && trim($value)){
					$list[] = $array[1][$key].$value;
				}
			}
		}
		$list = array_unique($list);
		sort($list);
		$list = $this->_sort($list);
		$rslist = array();
		//保留字
		$html_root_dir = 'http://'.$site['domain'].$site['dir'];
		if($site['html_root_dir'] && $site['html_root_dir'] != '/'){
			$html_root_dir .= $site['html_root_dir'];
		}
		foreach($list as $key=>$value){
			$value = preg_replace("/[\?|&]\_noCache=[0-9\.]+/is",'',$value);
			//项目+分类+分页
			$pcntl = "/[".$this->_tmp_addslashes($url)."|".addslashes($this->config['www_file'])."]+\?";
			$pcntl.= "id=([a-zA-Z\_\-0-9]+)\&";
			$pcntl.= "cate=([a-zA-Z\_\-0-9]+)\&";
			$pcntl.= "[".$this->config['pageid']."]+=([0-9]+)/is";
			$tmp = preg_replace($pcntl,$html_root_dir."\\1/\\2-\\3.html",$value);
			if($tmp && $tmp != $value){
				$content = str_replace($value,$tmp,$content);
				continue;
			}
			//项目+分页
			$pcntl = "/[".$this->_tmp_addslashes($url)."|".addslashes($this->config['www_file'])."]+\?";
			$pcntl.= "id=([a-zA-Z\_\-0-9]+)\&";
			$pcntl.= "[".$this->config['pageid']."]+=([0-9]+)/is";
			$tmp = preg_replace($pcntl,$html_root_dir."\\1-\\2.html",$value);
			if($tmp && $tmp != $value){
				$content = str_replace($value,$tmp,$content);
				continue;
			}
			//项目+分类
			$pcntl = "/[".$this->_tmp_addslashes($url)."|".addslashes($this->config['www_file'])."]+\?";
			$pcntl.= "id=([a-zA-Z\_\-0-9]+)\&";
			$pcntl.= "cate=([a-zA-Z\_\-0-9]+)/is";
			$tmp = preg_replace($pcntl,$html_root_dir."\\1/\\2.html",$value);
			if($tmp && $tmp != $value){
				$content = str_replace($value,$tmp,$content);
				continue;
			}
			//更新项目
			$pcntl = "/[".$this->_tmp_addslashes($url)."|".addslashes($this->config['www_file'])."]+\?";
			$pcntl.= "id=([a-zA-Z\_\-]+[a-zA-Z\_\-0-9]+)/is";
			$tmp = preg_replace($pcntl,$html_root_dir."\\1.html",$value);
			if($tmp && $tmp != $value){
				$content = str_replace($value,$tmp,$content);
				continue;
			}
			$pcntl = "/[".$this->_tmp_addslashes($url)."|".addslashes($this->config['www_file'])."]+\?";
			$pcntl.= "id=([0-9]+)/is";
			//更新主题
			$id = preg_replace($pcntl,"\\1",$value);
			if($id && intval($id)){
				$folder = '';
				if($site['html_content_type'] && $site['html_content_type'] != 'empty'){
					$folder = $this->model('html')->subject_folder($id,$site['html_content_type']);
				}
				$tmp = $folder ? $html_root_dir.$folder.$id.'.html' : $id.'.html';
				if($tmp && $tmp != $value){
					$content = str_replace($value,$tmp,$content);
					continue;
				}
			}
		}
		$content = preg_replace("/[\?|&]\_noCache=[0-9\.]+/is",'',$content);
		return $content;
	}

	//冒泡排序
	private function _sort($array)
	{
		$count = count($array);
		if($count <= 0){
			return false;
		}
		for($i=0; $i<$count; $i++){
			for($k=$count-1; $k>$i; $k--){
				if(strlen($array[$k]) > strlen($array[$k-1])){
					$tmp = $array[$k];
					if($tmp){
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
		if(!$tplinfo){
			$obj->status = 'error';
			$obj->error = P_Lang('未配置风格模板，请先到网站信息配置站点风格');
			return $obj;
		}
		//判断模板文件是否存在，不存在的模板将同时隐藏相应的项目信息
		if(!file_exists($this->dir_root.'tpl/'.$tplinfo['folder'])){
			$obj->status = 'error';
			$obj->error = P_Lang('模板风格目录不存在，请检查');
			return $obj;
		}
		$tlist = $this->model('html')->list_tpl($this->dir_root.'tpl/'.$tplinfo['folder'].'/',$tplinfo['ext']);
		if(!$tlist){
			$obj->status = 'error';
			$obj->error = P_Lang('没有模板文件，请检查');
			return $obj;
		}
		$obj->tplinfo = $tplinfo;
		$obj->tlist = $tlist;
		return $obj;
	}
}

?>