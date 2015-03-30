<?php
/*****************************************************************************************
	文件： phpinc/all.php
	备注： 调用全部主题信息，指定属性
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月24日 16时32分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
function phpok_all($attr='',$psize=10)
{
	$sql = "SELECT * FROM ".$GLOBALS['app']->db->prefix."list WHERE status=1 AND hidden=0 ";
	if($attr){
		$sql .= " AND attr LIKE '%".$attr."%' ";
	}
	$sql .= " ORDER BY sort ASC,id DESC LIMIT ".intval($psize);
	$tmplist = $GLOBALS['app']->db->get_all($sql);
	if(!$tmplist){
		return false;
	}
	$rslist = array();
	foreach($tmplist as $key=>$value){
		$value['url'] = $value['identifier'] ? $GLOBALS['app']->url($value['identifier']) : $GLOBALS['app']->url($value['id']);
		$rslist[$value['id']] = $value;
	}
	return $rslist;
}
?>