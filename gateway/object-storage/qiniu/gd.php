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
	$tmp = $filename.'?imageView2';
	if($value['cut_type']){
		$tmp .= '/'.intval($value['cut_type']);
	}else{
		if(!$value['width'] && !$value['height']){
			$tmp .= '/0';
		}else{
			$tmp .= '/2';
		}
	}
	if($value['width']){
		$tmp .= '/w/'.intval($value['width']);
	}
	if($value['height']){
		$tmp .= '/h/'.intval($value['height']);
	}
	if($value['quality']){
		$tmp .= '/q/'.$value['quality'];
	}
	$tmp .= '/ignore-error/1';
	if($value['mark_picture'] && is_file($this->dir_root.$value['mark_picture'])){
		$tmp .= '|watermark/1/image/'.$this->lib('common')->urlsafe_b64encode($this->url.$value['mark_picture']);
		if($value['trans']){
			$tmp .= '/dissolve/'.$value['trans'];
		}
		//与PHPOK对应的，缩图位置
		$pos_list = array('top-left'=>'NorthWest','top-middle'=>'North','top-right'=>'NorthEast');
		$pos_list['middle-left'] = 'West';
		$pos_list['middle-middle'] = 'Center';
		$pos_list['middle-right'] = 'East';
		$pos_list['bottom-left'] = 'SouthWest';
		$pos_list['bottom-middle'] = 'South';
		$pos_list['bottom-right'] = 'SouthEast';
		if($value['mark_position'] && $pos_list[$value['mark_position']]){
			$tmp .= '/gravity/'.$pos_list[$value['mark_position']];
		}
	}
	$tmplist[$value['identifier']] = $tmp; 
}
return $tmplist;
