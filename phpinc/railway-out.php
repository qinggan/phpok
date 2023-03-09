<?php
/**
 * 地铁线路数据导出
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2022年3月5日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
error_reporting(0);
$gid = 23; //分组ID
function _export($rslist,$parent_id,$file,$space='')
{
	global $app;
	foreach($rslist as $key=>$value){
		if($parent_id == $value['parent_id']){
			if(!$parent_id){
				echo $value['title']."\r\n";
			}
			$title = $value['title'] ? $value['title'] : ($value['val'] ? $value['val'] : '0');
			$val = $value['val'] ? $value['val'] : '0';
			$taxis = $value['taxis'] ? $value['taxis'] : 255;
			$data  = $space.'<info>'."\n";
			$data .= $space."\t".'<title><![CDATA['.$value['title'].']]></title>'."\n";
			$data .= $space."\t".'<val><![CDATA['.$value['val'].']]></val>'."\n";
			$data .= $space."\t".'<taxis><![CDATA['.$value['taxis'].']]></taxis>'."\n";
			$app->lib('file')->vi($data,$file,'','ab');
			$chk = _chkparent($rslist,$value['id']);
			if($chk){
				unset($rslist[$key]);//注销当前父级信息
				$app->lib('file')->vi($space."\t".'<sublist>'."\n",$file,'','ab');
				_export($rslist,$value['id'],$file,$space."\t\t");//
				$app->lib('file')->vi($space."\t".'</sublist>'."\n",$file,'','ab');
			}
			$app->lib('file')->vi($space.'</info>'."\n",$file,'','ab');
		}
	}
}
function _chkparent($rslist,$pid){
	$p = false;
	foreach($rslist as $key=>$value){
		if($value['parent_id'] == $pid){
			$p = true;
			break;
		}
	}
	return $p;
}

$rs = $this->model('opt')->group_one($gid);
if(!$rs){
	$this->error(P_Lang('组信息不存在'));
}
$rslist = $this->model('opt')->opt_all("group_id=".$gid);
if(!$rslist){
	$this->error(P_Lang('没有选项内容数据'));
}
$tmpfile = '_data/opt_'.$gid.'.xml';
$data  = '<root>'."\n";
if(!$pid && $sub){
	$data .= "\t".'<title><![CDATA['.$rs['title'].']]></title>'."\n";
}
$data .= "\t".'<data>'."\n";
$this->lib('file')->vi($data,$tmpfile,'','wb');
_export($rslist,0,$tmpfile,"\t\t");
$data  = "\t".'</data>'."\n";
$data .= '</root>';
$this->lib('file')->vi($data,$tmpfile,'','ab');
echo "OK";
exit;