<?php
/**
 * 检查是否购买了，才启用评论
 * @package phpok
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月15日
**/
$is_comment = false;
if($rs && $session['user_id'] && $page_rs['comment_status']){
	//检测订单中是否有此人购买的产品
	$sql  = "SELECT p.id FROM ".$app->db->prefix."order_product p LEFT JOIN ".$app->db->prefix."order o ON(p.order_id=o.id) WHERE p.tid='".$rs['id']."' ";
	$sql .= "AND o.user_id='".$session['user_id']."' AND o.status IN('end','shipping','received','stop') LIMIT 1";
	$chk = $app->db->get_one($sql);
	if($chk && $chk['id']){
		$is_comment = true;
	}
}