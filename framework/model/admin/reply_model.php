<?php
/***********************************************************
	备注：回复管理
	版本：5.0.0
	官网：www.phpok.com
	作者：qinggan <qinggan@188.com>
	更新：2016年02月14日
***********************************************************/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class reply_model extends reply_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 获取指定主题下的评论数统计，返回统计数组，为空返回false
	 * @param mixed $ids 主题ID，字符串或数组
	 * @date 2016年02月14日
	 */
	public function comment_stat($ids)
	{
		if(!$ids){
			return false;
		}
		if(is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "SELECT count(tid) as total,tid FROM ".$this->db->prefix."reply WHERE tid IN(".$ids.") GROUP BY tid";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['tid']] = array('total'=>$value['total'],'uncheck'=>0);
		}
		$sql = "SELECT count(tid) as total,tid FROM ".$this->db->prefix."reply WHERE tid IN(".$ids.") AND status=0 GROUP BY tid";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return $rslist;
		}
		foreach($tmplist as $key=>$value){
			$rslist[$value['tid']]['uncheck'] = $value['total'];
		}
		return $rslist;
	}

}

?>