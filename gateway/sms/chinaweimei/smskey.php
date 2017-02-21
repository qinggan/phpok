<?php
/**
 * 
 * @package phpok
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年11月17日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://cs.chinaweimei.com/wmsms/VerifySms.aspx";
$data = array(
	'action'=>'smskey'
);
$url .= "?";
foreach($data as $key=>$value){
	$url .= $key.'='.rawurlencode($value).'&';
}
$smsfile = $this->dir_root.'data/'.$rs['type'].'-'.$rs['code'].'-'.$rs['id'].'-smskey.php';
$update = $this->get('update');
if($update){
	
	$info = $this->lib('html')->get_content($url);
	if(!$info){
		return false;
	}
	$this->lib('file')->vi($info,$smsfile);
	return '更新成功';
}
$content = $this->lib('file')->cat($smsfile);
if(!$content){
	$content = $this->lib('html')->get_content($url);
	if($content){
		$this->lib('file')->vi($content,$smsfile);
	}
}
$this->assign('content',$content);
$this->view($this->dir_root.'gateway/'.$rs['type'].'/'.$rs['code'].'/smskey.html','abs-file');