<?php
/***********************************************************
	Filename: {phpok}/model/tag.php
	Note	: TAG管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-04-16 07:53
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	function tag_list($id,$site_id=0)
	{
		if(!$site_id)
		{
			$site_id = $this->site_id;
		}
		$sql = "SELECT t.title,t.url,t.target FROM ".$this->db->prefix."tag_stat s ";
		$sql.= "JOIN ".$this->db->prefix."tag t ON(s.tag_id=t.id AND t.site_id='".$site_id."') ";
		$sql.= "WHERE s.title_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			unset($sql);
			return false;
		}
		foreach($rslist as $key=>$value)
		{
			$value['target'] = $value['target'] ? '_blank' : '_self';
			$url = $this->url('tag','','title='.rawurlencode($value['title']));
			$alt = $value['alt'] ? $value['alt'] : $value['title'];
			$rslist[$key]['html'] = '<a href="'.$url.'" title="'.$alt.'" target="'.$value['target'].'" class="tag">'.$value['title'].'</a>';
			$rslist[$key]['target'] = $value['target'];
			$rslist[$key]['url'] = $url;
			$rslist[$key]['alt'] = $alt;
		}
		return $rslist;
	}
}
?>