<?php
/**
 * 地铁线路获取
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
$gid = 24; //分组ID
$cid = 19;
$id = $this->get('id');
if(!$id){
	$alist = $this->lib('curl')->get_json('http://map.baidu.com/?qt=subwayscity&t=123457788');
	$rslist = $alist['subways_city']['cities'];
	echo tpl_head();
	foreach($rslist as $key=>$value){
		echo '<div><a href="index.php?c=index&f=phpinc&phpfile=railway&id='.$value['code'].'&title='.rawurlencode($value['cn_name']).'" target="_blank">'.$value['cn_name'].'</a></div>';
		echo "\n";
	}
	exit;
}
$title = $this->get('title');
$toid = $this->get('toid');
if(!$toid && $title){
	//检查
	$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE group_id='".$gid."' AND title='".$title."'";
	$tmp = $this->db->get_one($sql);
	if(!$tmp){
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE group_id='".$cid."' AND title='".$title."'";
		$chk = $this->db->get_one($sql);
		if(!$chk){
			$this->error("未指定目标ID");
		}
		if($chk['parent_id']){
			$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE group_id='".$cid."' AND id='".$chk['parent_id']."'";
			$parent = $this->db->get_one($sql);
			if(!$parent){
				$this->error('未指定目标ID');
			}
			$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE group_id='".$gid."' AND title='".$parent['title']."'";
			$g_parent = $this->db->get_one($sql);
			if($g_parent){
				$parent_id = $g_parent['id'];
			}else{
				//写入数据
				$sql = "SELECT max(taxis) FROM ".$this->db->prefix."opt WHERE group_id='".$gid."' AND parent_id=0";
				$taxis = $this->db->count($sql);
				$tmpdata = array('group_id'=>$gid,'parent_id'=>0,'title'=>$parent['title'],'val'=>$parent['title']);
				$tmpdata['taxis'] = $taxis ? $taxis+5 : 5;
				$parent_id = $this->db->insert($tmpdata,'opt');
			}
			//写入子项
			$sql = "SELECT max(taxis) FROM ".$this->db->prefix."opt WHERE group_id='".$gid."' AND parent_id=".$parent_id;
			$taxis = $this->db->count($sql);
			$tmpdata = array('group_id'=>$gid,'parent_id'=>$parent_id,'title'=>$title,'val'=>$title);
			$tmpdata['taxis'] = $taxis ? $taxis+5 : 5;
			$toid = $this->db->insert($tmpdata,'opt');
		}else{
			$sql = "SELECT max(taxis) FROM ".$this->db->prefix."opt WHERE group_id='".$gid."' AND parent_id=0";
			$taxis = $this->db->count($sql);
			$tmpdata = array('group_id'=>$gid,'parent_id'=>0,'title'=>$title,'val'=>$title);
			$tmpdata['taxis'] = $taxis ? $taxis+5 : 5;
			$toid = $this->db->insert($tmpdata,'opt');
		}
	}else{
		$toid = $tmp['id'];
	}
}
if(!$toid){
	$this->error("未指定目标ID");
}
$tmplist = $this->lib("curl")->get_json('http://map.baidu.com/?qt=bsi&c='.$id.'&t=123457788');
$rslist = $tmplist['content'];
if(!$rslist || count($rslist)<1){
	$this->error("没有找到信息");
}
$bs = 5;
$list = $mylist = $checks = array();
foreach($rslist as $key=>$value){
	$tmptitle = $value['line_name'];
	preg_match_all('/(.+)\(([^\)]+)\-(.+)\)/isU',$tmptitle,$matches);
	$stops = array();
	$chkstops = array();
	foreach($value['stops'] as $k=>$v){
		$stops[] = $v['name'];
		$chkstops[] = $v['name'];
	}
	$tmpname = $matches[1][0];
	$chks = array($matches[1][0],$matches[2][0],$matches[3][0]);
	sort($chks);
	sort($chkstops);
	$code_a = 'a-'.md5(implode(",",$chks).'-'.implode(",",$chkstops));
	$code_b = 'b-'.md5(implode(',',$chks));
	$code_c = 'c-'.md5($tmpname);
	if(!isset($mylist[$tmpname])){
		$mylist[$tmpname] = array();
	}
	if($list[$code_a]){
		continue;
	}
	if($list[$code_b]){
		$tmp = array('title'=>$tmptitle,'from'=>$matches[2][0],'to'=>$matches[3][0],'stops'=>$stops,'keyid'=>$key);
		$mylist[$tmpname][] = $tmp;
		continue;
	}
	$tmp = array('title'=>$tmpname,'from'=>$matches[2][0],'to'=>$matches[3][0],'stops'=>$stops,'keyid'=>$key);
	$list[$code_a] = $list[$code_b] = $list[$code_c] = $tmp;
	$mylist[$tmpname][] = $tmp;
}
$rslist = array();
foreach($mylist as $key=>$value){
	$count = count($value);
	if($count>1){
		foreach($value as $k=>$v){
			$tmp = array('title'=>$v['title'].'/'.$v['from'].'/'.$v['to']);
			$tmp['stops'] = $v['stops'];
			$rslist[$v['keyid']] = $tmp;
		}
	}else{
		foreach($value as $k=>$v){
			$tmp = array('title'=>$v['title']);
			$tmp['stops'] = $v['stops'];
			$rslist[$v['keyid']] = $tmp;
		}
	}
}
ksort($rslist);
$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE parent_id='".$toid."'";
$chk = $this->db->get_one($sql);
if($chk){
	$this->error('数据已存在，不要重复更新');
}
$i=0;
foreach($rslist as $key=>$value){
	if(!$value['title'] || !is_array($value['stops'])){
		continue;
	}
	$data = array('group_id'=>$gid,'parent_id'=>$toid,'title'=>$value['title'],'val'=>$value['title'],'taxis'=>($i+1)*$bs);
	$insert_id = $this->db->insert($data,'opt');
	$sql = "INSERT INTO ".$this->db->prefix."opt(group_id,parent_id,title,val,taxis) VALUES";
	$tlist = array();
	foreach($value['stops'] as $k=>$v){
		$tlist[] = "('".$gid."','".$insert_id."','".$v."','".$v."','".($k+1)*$bs."')";
	}
	$sql .= implode(",",$tlist);
	$this->db->query($sql);
	$i++;
}
$this->success($title.'地铁线更新成功');
