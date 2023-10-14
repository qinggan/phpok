<?php
/**
 * 后台管理_用于管理多语言，支持批量翻译等操作
 * @作者 phpok.com <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年10月13日 18时20分
**/
namespace phpok\app\control\multi_language;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class admin_control extends \phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('multi_language');
		$this->assign("popedom",$this->popedom);
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('lang')->delete($id);
		$this->success();
	}

	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有权限操作'));
		}
		$langlist = $this->model('lang')->get_list();
		$this->assign("langlist",$langlist);
		$this->display("admin-index");
	}

	public function langs_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定语言ID'));
		}
		$rs = $this->model('lang')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('语言包信息不存在'));
		}
		$this->assign('id',$id);
		$this->assign('rs',$rs);
		$this->display("admin-langs");
	}

	/**
	 * 更新中文基础包
	**/
	public function refresh_f()
	{
		//更新PHP包
		$list = $this->file2content($this->dir_phpok.'admin/','.php');
		$tmplist = $this->php2data($list);
		//更新模板包
		$list = $this->file2content($this->dir_phpok.'view/','.html');
		$tmplist2 = $this->html2data($list);
		$tmplist = array_merge($tmplist,$tmplist2);
		$this->_save($tmplist,$this->dir_root.'langs/admin.xml');
		
		//更新API库
		$list = $this->file2content($this->dir_phpok.'api/','.php');
		$tmplist = $this->php2data($list);
		$this->_save($tmplist,$this->dir_root.'langs/api.xml');
		
		//更新www库
		$list = $this->file2content($this->dir_phpok.'www/','.php');
		$tmplist = $this->php2data($list);
		$this->_save($tmplist,$this->dir_root.'langs/www.xml');

		//更新JS库
		$list = $this->file2content($this->dir_phpok.'js/','.js');
		$tmplist = $this->js2data($list);
		$this->_save($tmplist,$this->dir_root.'langs/js.xml');
		
		//更新其他公共库
		$list = $this->file2content($this->dir_phpok.'engine/','.php');
		$tmp1 = $this->php2data($list);
		$list = $this->file2content($this->dir_phpok.'form/','.php');
		$tmp2 = $this->php2data($list);
		$list = $this->file2content($this->dir_phpok.'form/html/','.html');
		$tmp3 = $this->html2data($list);
		$list = $this->file2content($this->dir_phpok.'libs/','.php');
		$tmp4 = $this->php2data($list);
		$list = $this->file2content($this->dir_phpok.'model/','.php');
		$tmp5 = $this->php2data($list);
		$list = $this->file2content($this->dir_phpok.'model/admin/','.php');
		$tmp6 = $this->php2data($list);
		$list = $this->file2content($this->dir_phpok.'model/api/','.php');
		$tmp7 = $this->php2data($list);
		$list = $this->file2content($this->dir_phpok.'model/www/','.php');
		$tmp8 = $this->php2data($list);
		$list = $this->file2content($this->dir_phpok.'open/','.html');
		$tmp9 = $this->html2data($list);
		$tmplist = array_merge($tmp1,$tmp2,$tmp3,$tmp4,$tmp5,$tmp6,$tmp7,$tmp8,$tmp9);
		$this->_save($tmplist,$this->dir_root.'langs/global.xml');
		$this->success();
	}

	public function set_f()
	{
		if(!$this->popedom['modify'] && $this->popedom['add']){
			$this->error(P_Lang('您没有权限操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('未指定名称'));
		}
		$data = array('id'=>$id,'title'=>$title);
		$this->model('lang')->save($data);
		$this->success();
	}

	private function file2content($folder='',$type='.php')
	{
		$length = strlen($type);
		$type = strtolower($type);
		$list = $this->lib('file')->ls($folder);
		if(!$list){
			return array();
		}
		foreach($list as $key=>$value){
			$basename = basename($value);
			$ext = strtolower(substr($basename,-$length));
			if($ext != $type){
				continue;
			}
			$tmp = $this->lib('file')->cat($value);
			yield $tmp;
		}
	}

	private function html2data($list)
	{
		if(!$list){
			return array();
		}
		$tmplist = array();
		foreach($list as $key=>$value){
			preg_match_all("/\{lang([^\\)\(}]+)[\}|\|]/isU",$value,$matches);
			if(!$matches || !$matches[1]){
				continue;
			}
			$this->_val($tmplist,$matches[1]);
		}
		return $tmplist;
	}

	private function js2data($list)
	{
		if(!$list){
			return array();
		}
		$tmplist = array();
		foreach($list as $key=>$value){
			preg_match_all("/p_lang\([\"']{1}(.+)[\"']/isU",$value,$matches);
			if(!$matches || !$matches[1]){
				continue;
			}
			$this->_val($tmplist,$matches[1]);
		}
		return $tmplist;
	}

	private function php2data($list)
	{
		if(!$list){
			return array();
		}
		$tmplist = array();
		foreach($list as $key=>$value){
			preg_match_all("/P_Lang\([\"']{1}(.+)[\"']/isU",$value,$matches);
			if(!$matches || !$matches[1]){
				continue;
			}
			$this->_val($tmplist,$matches[1]);
		}
		return $tmplist;
	}

	private function _save($rslist,$file='')
	{
		$fopen = fopen($file,'wb');
		fwrite($fopen,'<?xml version="1.0" encoding="utf-8"?>'."\n");
		fwrite($fopen,'<root>'."\n");
		foreach($rslist as $key=>$value){
			fwrite($fopen,"\t".'<'.$key.'><![CDATA['.$value.']]></'.$key.'>'."\n");
		}
		fwrite($fopen,'</root>');
		fclose($fopen);
		return true;
	}

	private function _val(&$rslist,$data)
	{
		foreach($data as $key=>$value){
			if(strpos($value,'.$') !== false){
				continue;
			}
			$code = md5($value);
			//确保以字母开头
			$rslist['a'.$code] = $value;
		}
	}
}
