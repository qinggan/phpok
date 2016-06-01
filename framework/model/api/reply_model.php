<?php
/*****************************************************************************************
	文件： {phpok}/model/api/reply_model.php
	备注： 评论相关
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月14日 20时44分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class reply_model extends reply_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	function check_time($tid,$uid='',$sessid='')
	{
		if(!$uid && !$sessid){
			return false;
		}
		$sql = "SELECT addtime FROM ".$this->db->prefix."reply WHERE tid='".$tid."'";
		if($uid){
			$sql .= " AND uid='".$uid."'";
		}else{
			$sessid  .= " AND session_id='".$sessid."'";
		}
		$sql .= " ORDER BY addtime DESC LIMIT 1";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return true;
		}
		if(($rs['addtime'] + 30) > $this->time){
			return false;
		}
		return true;
	}
}

?>