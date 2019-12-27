<?php
/**
 * 订单自动确认完成
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月11日
**/

$sql = "SELECT id,user_id FROM ".$this->db->prefix."order WHERE status IN('shipping','paid') AND (addtime+confirm_time)<".$this->time;
$rslist = $this->db->get_all($sql);
if($rslist){
	foreach($rslist as $key=>$value){
		//更新订单状态
		$this->model('order')->update_order_status($value['id'],'received');
		//更新订单日志
		$log = array('order_id'=>$value['id'],'addtime'=>$this->time);
		$log['user_id'] = $value['user_id'];
		$log['who'] = 'system';
		$log['note'] = P_Lang('系统签收');
		$this->model('order')->log_save($log);
	}
}