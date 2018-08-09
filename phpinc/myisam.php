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

$rslist = $this->db->list_tables();
if($rslist){
	foreach($rslist as $key=>$value){
		$sql = "ALTER TABLE ".$value." ENGINE=MYISAM";
		$this->db->query($sql);
		echo "UPDATE table Engine: MYISAM OK<br />";
	}
}
