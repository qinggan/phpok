<?php
/**
 * 设计器中涉及到的Mode操作
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html 
 * @时间 2021年1月7日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class design_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function tplist($basedir='')
	{
		$syslist = array();
		$this->lib('file')->deep_ls($basedir,$syslist);
		if(count($syslist)<1){
			return false;
		}
		$tplist = array();
		$length = strlen($this->dir_root);
		foreach($syslist as $key=>$value){
			if(is_dir($value)){
				continue;
			}
			//预览模板跳过
			if(basename($value) == 'preview.html'){
				continue;
			}
			$ext = substr($value,-5);
			$ext = strtolower($ext);
			if($ext != '.html'){
				continue;
			}
			$chk = substr($value,0,-5);
			$tplfile = substr($value,$length,-5);
			$einfo = $chk.'.php';
			$config = array();
			if(file_exists($einfo)){
				include($einfo);
			}
			if(!$config['title']){
				$config['title'] = $tplfile;
			}
			$data = array('tplfile'=>$tplfile,"title"=>$config['title'],"note"=>$config['note']);
			if($config['img'] && file_exists($this->dir_root.$config['img'])){
				$data['img'] = $config['img'];
			}else{
				$img = $chk.'.jpg';
				if($img && file_exists($img)){
					$data['img'] = $tplfile.'.jpg';
				}else{
					$img = $chk.'.png';
					if($img && file_exists($img)){
						$data['img'] = $tplfile.'.png';
					}
				}
			}
			$tplist[$tplfile] = $data;
		}
		return $tplist;
	}

	public function tpl_info($id='')
	{
		if(!$id){
			return false;
		}
		$ext = substr($id,-5);
		$ext = strtolower($ext);
		if($ext == '.html'){
			$id = substr($id,0,-5);
		}
		$tplfile = $this->dir_root.$id.'.html';
		$einfo = $this->dir_root.$id.'.php';
		$config = array();
		if(file_exists($einfo)){
			include($einfo);
		}
		if(!$config['title']){
			$config['title'] = $tplfile;
		}
		$data = array('tplfile'=>$tplfile,"title"=>$config['title'],"note"=>$config['note']);
		if($config['img'] && file_exists($this->dir_root.$config['img'])){
			$data['img'] = $config['img'];
		}else{
			$img = $this->dir_root.$id.'.jpg';
			if($img && file_exists($img)){
				$data['img'] = $id.'.jpg';
			}else{
				$img = $this->dir_root.$id.'.png';
				if($img && file_exists($img)){
					$data['img'] = $id.'.png';
				}
			}
		}
		return $data;
	}
}
