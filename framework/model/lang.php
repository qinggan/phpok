<?php
/**
 * 语言包管理
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年12月04日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class lang_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	public function get_list()
	{
		$langlist = array("cn"=>"简体中文");
		if(is_file($this->dir_data."xml/langs.xml")){
			$langlist = $this->lib('xml')->read($this->dir_data.'xml/langs.xml');
		}
		return $langlist;
	}

	public function get_one($id)
	{
		$list = $this->get_list();
		if(!$list){
			return false;
		}
		if($list[$id]){
			$name = $list[$id];
			return array('id'=>$id,'title'=>$name);
		}
		return false;
	}

	public function save($data)
	{
		$list = $this->get_list();
		$list[$data['id']] = $data['title'];
		$this->lib('xml')->save($list,$this->dir_data.'xml/langs.xml');
		return true;
	}

	public function delete($id='')
	{
		$list = $this->get_list();
		if($id && $list[$id]){
			unset($list[$id]);
		}
		$this->lib('xml')->save($list,$this->dir_data.'xml/langs.xml');
		return true;
	}
}