<?php
/**
 * 应用于复杂的设计，仅适用于后台
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2021年1月2日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class design_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function cssjs()
	{
		
	}

	public function phpok_config()
	{
		$this->view($this->dir_phpok.'form/html/design_admin.html','abs-file');
	}

	public function phpok_format($rs,$appid="admin")
	{
		$this->addjs('static/bootstrap/admin/admin.js');
		$this->addcss('static/bootstrap/grid.css');
		$this->addcss('static/bootstrap/admin/admin.css'); //后台设计器效果
		if(!$rs['height']){
			$rs['height'] = 150;
		}
		$this->assign('_rs',$rs);
		return $this->fetch($this->dir_phpok."form/html/design_admin_tpl.html",'abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		return $this->get($rs['identifier'],'html_js');
	}

	public function phpok_show($rs,$appid="admin")
	{
		if($appid == 'admin'){
			return false;
		}
		$content = $rs['content'];
		//去除按钮-1
		$content = preg_replace('/<input[^>]+?>/is','',$content);
		//去除按钮-2
		$content = preg_replace('/<button[^>]+>.*?<\/button>/is','',$content);
		//去除外框的按钮组
		$content = preg_replace('/<div[^>]*layui-btn-group[^>]+>.*?<\/div>/is','',$content);
		//去除外框的toolbar
		$content = preg_replace('/<div[^>]*toolbar[^>]+>.*?<\/div>/is','',$content);
		//去除iframe保护框
		$content = preg_replace('/<div[^>]*data-iframe=\"layer\"[^>]+>.*?<\/div>/is','',$content);
		//设置是否限制外框限制
		preg_match_all('/<div([^>]+)pre\-width=\"fixed\"([^>]*)>/isU',$content,$matches);
		if($matches && $matches[0] && $matches[1]){
			foreach($matches[1] as $key=>$value){
				preg_match_all('/pre\-id=\"([a-zA-Z0-9\-\_\s]+)\"/isU',$value,$tmp);
				$tmpid = $tmp[1][0];
				$content = str_replace('<!-- layer '.$tmpid.' -->','<div class="container">',$content);
				$content = str_replace('<!-- /layer '.$tmpid.' -->','</div>',$content);
			}
		}

		//通过正则获取内容
		preg_match_all('/<div[^>]*?pre\-vtype=\"calldata\"[^>]*?>/is',$content,$matches);
		if($matches && $matches[0]){
			foreach($matches[0] as $key=>$value){
				$id = 'k'.md5($value);
				$tmp = $this->_str2array($value);
				if(!$tmp || !$tmp['code']){
					continue;
				}
				$rs = $this->model('design')->get_one($tmp['code'],'code');
				$content_id = $tmp['id'];
				if($rs){
					if($tmp['ext'] && is_array($tmp['ext'])){
						foreach($tmp['ext'] as $k=>$v){
							$rs['ext'][$k] = $v;
						}
					}
					$tplfile = $this->dir_data.'design/'.$rs['code'].'.html';
					if(!file_exists($tplfile)){
						continue;
					}
					$info = $this->lib('file')->cat($tplfile);
					$calldata = '';
					if($rs['ext'] && $rs['ext']['calldata']){
						$calldata = $rs['ext']['calldata'];
					}
					if(!$calldata){
						$calldata = $tmp['code'];
					}
					if($tmp['calldata']){
						$calldata = $tmp['calldata'];
					}
				}else{
					$tplfile = $tmp['tplfile'];
					$ext = substr($tplfile,-5);
					$ext = strtolower($ext);
					if($ext != '.html'){
						$tplfile .= '.html';
					}
					if(!file_exists($this->dir_root.$tplfile)){
						continue;
					}
					$info = $this->lib('file')->cat($this->dir_root.$tplfile);
					$calldata = $tmp['code'];
				}
				//变量替换
				if($tmp['replace'] && is_array($tmp['replace'])){
					foreach($tmp['replace'] as $key=>$value){
						$info = str_replace($value['old'],$value['new'],$info);
					}
				}
				$info = str_replace('$info','$'.$id,$info);//更换变量
				$list = phpok($calldata,$tmp['param']);
				if($list){
					$this->assign($id,$list);
					$mycontent = $this->tpl->fetch($info,'content');
					$content = preg_replace('/<!--\s*content\-'.$content_id.'\s*-->.*?<!--\s*\/content\-'.$content_id.'\s*-->/is',$mycontent,$content);
				}
				//将内容替换
			}
		}	
		$content = str_replace('pre-type="content"','data-type="content"',$content);
		//去除pre-****=*** data-ext-***=***属性
		$content = preg_replace('/pre\-[a-zA-Z0-9]+=\"[^\"]*\"/is','',$content);
		$content = preg_replace('/data\-ext\-[a-zA-Z0-9]+=\"[^\"]*\"/is','',$content);
		//去除无值的动画
		$content = preg_replace('/data\-wow\-[a-zA-Z]+?=\"[0]*\"/is','',$content);
		$content = preg_replace('/wow\-[a-zA-Z]+?=\"[0]*\"/is','',$content);
		//去除多余空格，只保留一个空格
		$content = preg_replace("/(\x20{2,})/"," ",$content);
		$content = str_replace('" >','">',$content);
		//去除备注
		$content = preg_replace('/<!--\s+[\/]*layer\s+[a-zA-Z0-9]+\s+-->/isU','',$content);
		return $content;
	}

	private function _str2array($str)
	{
		preg_match_all('/pre\-([a-zA-Z0-9]+?)=\"([^\"]*?)\"/is',$str,$matches);
		if(!$matches || !$matches[1] || !$matches[2]){
			return false;
		}
		$rs = array();
		foreach($matches[1] as $key=>$value){
			$rs[$value] = $matches[2][$key];
		}
		//增加data-ext-的扩展信息
		preg_match_all('/data\-ext\-([a-zA-Z0-9]+?)=\"([^\"]*?)\"/is',$str,$matches);
		foreach($matches[1] as $key=>$value){
			$rs[$value] = $matches[2][$key];
		}
		
		preg_match_all('/pre\-ext\-([a-zA-Z0-9]+?)=\"([^\"]*?)\"/is',$str,$matches);
		$ext = array();
		foreach($matches[1] as $key=>$value){
			$ext[$value] = $matches[2][$key];
		}
		$rs['ext'] = $ext;
		if($rs['param']){
			$dt = array();
			$dt['ext'] = array();
			$old = array('&amp;lt;','&amp;gt;','&amp;quot;','&amp;apos;','&lt;','&gt;','&quot;','&apos;');
			$new = array('<','>','"',"'",'<','>','"',"'");
			$param = str_replace($old,$new,$rs['param']);
			$list = explode("\n",$param);
			foreach($list as $key=>$value){
				$tmp = explode("=",$value);
				if($tmp[0] && $tmp[1] != ''){
					$length = strlen($tmp[0]);
					$e = substr($value,($length+1));
					if(strpos($tmp[0],'ext[') === false){
						$dt[$tmp[0]] = $e;
					}else{
						$tmp_id = str_replace(array('ext[',']'),'',$tmp[0]);
						$dt['ext'][$tmp_id] = $e;
					}
				}
			}
			if(count($dt['ext'])<1){
				unset($dt['ext']);
			}
			$rs['param'] = $dt;
		}
		//变量替换
		if($rs['replace']){
			$dt = array();
			$old = array('&amp;lt;','&amp;gt;','&amp;quot;','&amp;apos;','&lt;','&gt;','&quot;','&apos;');
			$new = array('<','>','"',"'",'<','>','"',"'");
			$param = str_replace($old,$new,$rs['replace']);
			$list = explode("\n",$param);
			foreach($list as $key=>$value){
				$tmp = explode("=",$value);
				if(!$tmp[0] || !$tmp[1]){
					continue;
				}
				$tmp[0] = trim($tmp[0]);
				$tmp[1] = trim($tmp[1]);
				if($tmp[0] && $tmp[1]){
					$dt[] = array('old'=>$tmp[0],'new'=>$tmp[1]);
				}
			}
			$rs['replace'] = $dt;
		}
		return $rs;
	}
}