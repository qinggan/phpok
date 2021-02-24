<?php
/**
 * 执行URL
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年9月21日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
$condition = "l.status=1 AND l.project_id=1 AND l.dateline<=".$this->time;
$sql  = " SELECT e.*,l.title,l.dateline FROM ".$this->db->prefix."list l ";
$sql .= " LEFT JOIN ".$this->db->prefix."list_1 e ON(l.id=e.id) WHERE ".$condition;
$rslist = $this->db->get_all($sql);
if(!$rslist){
	return false;
}
foreach($rslist as $key=>$value){
	if($value['ip']){
		$this->lib('curl')->host_ip($value['ip']);
	}
	$this->lib('curl')->get_content($value['linkurl']);
	//执行这条语句
	$sql = "UPDATE ".$this->db->prefix."list SET dateline='".($this->time+$value['ptime'])."' WHERE id='".$value['id']."'";
	$this->db->query($sql);
}
return true;