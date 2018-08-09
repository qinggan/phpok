<?php
/**
 * 升级 ext 表到 fields 
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年05月18日
**/

$sql = "SELECT max(id) FROM ".$this->db->prefix."fields";
$m1 = $this->db->count($sql);
$this->db->debug(true);
$sql = "SELECT max(id) FROM ".$this->db->prefix."ext";
$m2 = $this->db->count($sql);
if($m1 < $m2){
	$sql = "ALTER TABLE ".$this->db->prefix."fields auto_increment=".$m2;
	$this->db->query($sql);
}

$sql = "SELECT * FROM ".$this->db->prefix."ext ORDER BY id ASC";
$rslist = $this->db->get_all($sql);
if($rslist){
	foreach($rslist as $key=>$value){
		//检查是否已经存在
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE identifier='".$value['identifier']."' AND ftype='".$value['module']."'";
		$chk = $this->db->get_one($sql);
		if($chk){
			echo $value['id'].' - '.$value['identifier'].' - '.$value['module'].' - is exists<br />';
			continue;
		}
		$data = $value;
		unset($data['id'],$data['module']);
		$data['ftype'] = $value['module'];
		$data['ext'] = $value['ext'] ? unserialize($value['ext']) : '';
		if($data['ext']){
			$data['ext'] = serialize($data['ext']);
		}
		$insert_id = $this->db->insert($data,'fields');
		if($insert_id){
			$sql = "UPDATE ".$this->db->prefix."extc SET id='".$insert_id."' WHERE id='".$value['id']."'";
			$this->db->query($sql);
			echo $value['id'].' - '.$value['identifier'].' - '.$value['module'].' - is ok<br />';
		}
	}
}
