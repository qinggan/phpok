<?php
/***********************************************************
	Filename: plugins/pscws/admin.php
	Note	: 后台分词使用
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月7日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_pscws extends phpok_plugin
{
	public $path;
	public $dede_cls;
	function __construct()
	{
		parent::plugin();
		$this->path = str_replace("\\","/",dirname(__FILE__))."/";
		include_once($this->path."pscws4.class.php");
	}

	//更新节点操作
	function ap_list_ok_after($array)
	{
		if(!$array || !is_array($array)) return false;
		$id = $array['id'];
		$p_rs = $array['project'];
		$rs = $this->model('list')->get_one($id);
		if(!$rs) return false;
		if($rs['tag'] && $rs['seo_desc'] && $rs['seo_keywords']) return false;
		$cws = new PSCWS4('gbk');
		$cws->set_dict($this->path.'etc/dict.xdb');
		$cws->set_rule($this->path.'etc/rules.ini');
		$cws->set_ignore(true);
		$cws->set_multi(false);
		$tag = $rs['tag'];
 		if(!$rs["seo_desc"] || !$rs["seo_keywords"] || !$rs["tag"])
 		{
	 		$ext_list = $this->model('module')->fields_all($rs["module_id"]);
	 		$content = "";
	 		if($ext_list)
	 		{
		 		foreach($ext_list AS $key=>$value)
		 		{
			 		if($value["field_type"] == "longtext")
			 		{
				 		$content .= $rs[$value["identifier"]];
			 		}
		 		}
	 		}
	 		if(!$content) $content = $rs["title"];
	 		$content = phpok_cut($content,20480);
	 		$update = array();
	 		if(!$rs["seo_keywords"] || !$rs["tag"])
	 		{
		 		$content_gbk = $this->charset($content,'UTF-8','GBK');
		 		$cws->send_text($content_gbk);
		 		$words = $cws->get_tops(10, 'n,v');
		 		if(!$words) $words = array();
		 		$lst = array();
		 		foreach($words AS $key=>$value)
		 		{
			 		$lst[] = $this->charset($value['word'],'GBK','UTF-8');
 				}
 				$words = implode(' ',$lst);
		 		if($words)
		 		{
			 		if(!$rs["seo_keywords"]) $update["seo_keywords"] = str_replace(" ",",",$words);
			 		if(!$rs["tag"])
			 		{
				 		$update["tag"] = $words;
				 		$tag = $update["tag"];
			 		}
		 		}
	 		}
	 		if(!$rs["seo_desc"])
	 		{
		 		$update["seo_desc"] = str_replace(strstr($content,'。'),'',$content);
		 		$update["seo_desc"] = phpok_cut($update['seo_desc'],240,'…');
	 		}
	 		$this->list_model->save($update,$id);
 		}
 		if(!$tag) return false;
 		//当存在Tag时，更新Tag操作
 		$this->model('tag')->update_tag($id,$tag);
 		return true;
	}
}
?>