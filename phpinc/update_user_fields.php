<?php
/**
 * 升级 qinggan_user_fields 表到 fields 
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年05月18日
**/

$sql = "SELECT * FROM ".$this->db->prefix."user_fields ORDER BY id ASC";
$rslist = $this->db->get_all($sql);
if($rslist){
	foreach($rslist as $key=>$value){
		//检查是否已经存在
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE identifier='".$value['identifier']."' AND ftype='user'";
		$chk = $this->db->get_one($sql);
		if($chk){
			echo $value['id'].' - '.$value['identifier'].' - user - is exists<br />';
			continue;
		}
		$data = $value;
		unset($data['id'],$data['is_edit']);
		$data['is_front'] = $value['is_edit'];
		$data['ftype'] = 'user';
		$data['ext'] = $value['ext'] ? unserialize($value['ext']) : '';
		if($data['ext']){
			$data['ext'] = serialize($data['ext']);
		}
		$insert_id = $this->db->insert($data,'fields');
		if($insert_id){
			echo $value['id'].' - '.$value['identifier'].' - user - is ok<br />';
		}
	}
}
