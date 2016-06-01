<?php
/*****************************************************************************************
	文件： {phpok}/model/www/id_model.php
	备注： ID中的获取
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年11月17日 00时24分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class id_model extends id_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_ctrl($identifier,$site_id=0)
	{
		$rslist = $this->id_all($site_id);
		if($rslist[$identifier]){
			return $rslist[$identifier]['type'];
		}
		return false;
	}

	public function id_all($site_id=0,$status=0)
	{
		$sql_1 = "SELECT concat('p',id) AS id,identifier FROM ".$this->db->prefix."project WHERE site_id='".$site_id."' AND status=1";
		$sql_2 = "SELECT concat('c',id) AS id,identifier FROM ".$this->db->prefix."cate WHERE site_id='".$site_id."' AND status=1";
		$sql_3 = "SELECT concat('t',id) AS id,identifier FROM ".$this->db->prefix."list WHERE site_id='".$site_id."' AND identifier!='' AND status=1";
		$sql = "(".$sql_1.") UNION (".$sql_2.") UNION (".$sql_3.")";
		$cache_id = $this->cache->id($sql);
		$tmplist = $this->cache->get($cache_id);
		if($tmplist){
			return $tmplist;
		}
		$this->db->cache_set($cache_id);
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		$tlist = array('t'=>'content','p'=>'project','c'=>'cate');
		foreach($tmplist as $key=>$value){
			$tmp = substr($value['id'],0,1);
			$id = substr($value['id'],1);
			$rslist[$value['identifier']] = array('id'=>$id,'type'=>$tlist[$tmp]);
		}
		$this->cache->save($cache_id,$rslist);
		return $rslist;
	}
	
}

?>