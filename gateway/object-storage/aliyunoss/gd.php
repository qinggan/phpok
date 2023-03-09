<?php
/**
 * 附件上传成功后，提交到服务端，增加登记
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月19日
**/

$filename = $this->get('filename');
if(!$filename){
	return false;
}
$gdlist = $this->model('gd')->get_all();
if(!$gdlist){
	return false;
}
$tmplist = array();
foreach($gdlist as $key=>$value){
	$tmplist[$value['identifier']] = $filename;
}
return $tmplist;
