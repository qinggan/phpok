<?php
/**
 * 公共函数
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年06月04日
**/


/**
 * 返回主题被收藏的次数
 * @参数 $title_id 主题ID
 * @内容页模板代码 {func fav_count $rs.id} 
**/
function fav_count($title_id=0)
{
	return $GLOBALS['app']->model('fav')->title_fav_count($title_id);
}

/**
 * 检测主题是否已被收藏
 * @参数 $title_id 主题ID
 * @参数 $user_id 会员ID，留空直接通过 session 获取
 * @内容示例 {if fav_check($rs.id,$session.user_id)}
**/
function fav_check($title_id=0,$user_id=0)
{
	if(!$user_id){
		$user_id = $GLOBALS['app']->session->val('user_id');
	}
	return $GLOBALS['app']->model('fav')->chk($title_id,$user_id);
}