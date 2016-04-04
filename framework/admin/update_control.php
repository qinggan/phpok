<?php
/***********************************************************
	Note	: phpok4升级引挈控制器
	Version : 4.x
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2015年06月11日 14时33分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class update_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("update");
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$update = array('online'=>true,'zip'=>true);
		$setfile = $this->dir_root.'data/update.php';
		$uconfig = array();
		if(is_file($setfile)){
			include($setfile);
		}
		if(!$uconfig['status']){
			$update['online'] = false;
			$update['zip'] = false;
		}else{
			if(!$uconfig['server']){
				$update['online'] = false;
			}
		}
		$this->assign('update',$update);
		$this->view('update_index');
	}

	public function set_f()
	{
		$setfile = $this->dir_root.'data/update.php';
		$uconfig = array();
		if(is_file($setfile)){
			include($setfile);
		}
		$this->assign("rs",$uconfig);
		$this->view('update_set');
	}

	//存储配置
	public function save_f()
	{
		$setfile = $this->dir_root.'data/update.php';
		$uconfig = array();
		if(is_file($setfile)){
			include($setfile);
		}
		$uconfig['status'] = $this->get('status','int');
		$uconfig['server'] = $this->get('server');
		$uconfig['date'] = $this->get('date','int');
		$this->lib('file')->vi($uconfig,$this->dir_root.'data/update.php','uconfig');
		error(P_Lang('升级环境配置成功'),$this->url('update'),'ok');
	}

	//在线升级
	function main_f()
	{
		if(!$this->popedom['update']){
			error(P_Lang('您没有权限执行此操作'),$this->url('update'),'error');
		}
		$info = $this->service(4);
		$rs = $this->lib('json')->decode($info);
		if($rs['status'] != 'ok'){
			error(P_Lang('没有找到升级包信息'),$this->url('update'),'error');
		}
		if(!$rs['content'] || count($rs['content']) < 1)	{
			error(P_Lang('没有符合您要求的升级包'),$this->url('update'),'error');
		}
		if(is_file($this->dir_root.'data/update.php')){
			include($this->dir_root.'data/update.php');
			$this->assign('uconfig',$uconfig);
		}
		$rslist = array();
		foreach($rs['content'] as $key=>$value){
			$id = $value['phpok-id'];
			$version = substr($value['phpok-id'],0,1).'.'.substr($value['phpok-id'],1,1).'.'.substr($value['phpok-id'],2);
			$time = date("Y-m-d H:i:s",$value['phpok-time']);
			$size = $this->lib('common')->num_format($value['phpok-size']);
			$rslist[] = array('id'=>$id,'version'=>$version,'time'=>$time,'size'=>$size,'type'=>'zip');
		}
		$this->assign('rslist',$rslist);
		$this->view('update');
	}

	//zip升级
	function zip_f()
	{
		$array = array("identifier"=>'zipfile',"form_type"=>'upload');
		$array['upload_type'] = 'update';
		$this->lib('form')->cssjs($array);
		$upload = $this->lib('form')->format($array);
		$this->assign('upload_html',$upload);
		$this->view('update_zip');
	}

	//解压zip
	public function unzip_f()
	{
		$zipfile = $this->get('zipfile','int');
		if(!$zipfile){
			error(P_Lang('未指定附件文件'),$this->url('update','zip'),'error');
		}
		$rs = $this->model('res')->get_one($zipfile);
		if(!$rs){
			error(P_Lang('附件不存在'),$this->url('update','zip'),'error');
		}
		$this->lib('phpzip')->unzip($rs['filename'],'data/update/');
		//执行升级程序
		$info = $this->update_load();
		if(!$info || (is_array($info) && $info['status'] == 'error')){
			error($info['content'],$this->url('update'),'error');
		}
		error(P_Lang('升级成功'),$this->url('update'),'ok');
	}

	//升级文件
	private function update_load($verinfo='')
	{
		$list = array();
		$this->lib('file')->deep_ls($this->dir_root.'data/update/',$list);
		if(!$list || count($list) < 1){
			return array('status'=>'error','content'=>P_Lang('没有升级文件内容'));
		}
		$strlen = strlen($this->dir_root."data/update/");
		$delfile = false;
		$sqlfile = array();
		$cfile = array();
		foreach($list AS $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			$tmp = substr($value,$strlen);
			if($tmp == 'version.txt'){
				$verinfo = trim(file_get_contents($value));
				continue;
			}
			if($tmp == 'delete.txt'){
				$delfile = $value;
				continue;
			}
			if($tmp == 'run.php'){
				continue;
			}
			if(substr($tmp,-3) == 'sql' && $tmp != 'table.sql'){
				$sqlfile[] = $value;
				continue;
			}
			if(substr($tmp,0,17) == 'framework/config/'){
				$cfile[] = $value;
				continue;
			}
			if(substr($tmp,0,10) == 'framework/'){
				$tmp1 = substr($tmp,10);
				if(is_file($value)){
					$this->lib('file')->mv($value,$this->dir_phpok.$tmp1);
					continue;
				}
				if(is_dir($value) && !is_dir($this->dir_phpok.$tmp1)){
					$this->lib('file')->make($this->dir_phpok.$tmp1,'folder');
					continue;
				}
			}
			if(is_file($value) && $tmp != 'table.sql'){
				$this->lib('file')->mv($value,$this->dir_root.$tmp);
				continue;
			}
			if(is_dir($value) && !is_dir($this->dir_root.$tmp)){
				$this->lib('file')->make($this->dir_root.$tmp,'folder');
				continue;
			}
		}
		//现在执行删除
		if($delfile){
			$dlist = file($delfile);
			if(!$dlist){
				$dlist = array();
			}
			foreach($dlist AS $key=>$value){
				if(!$value && !trim($value)){
					continue;
				}
				$value = trim($value);
				if($value && is_file($this->dir_root.$value)){
					$this->lib('file')->rm($this->dir_root.$value);
					continue;
				}
				if($value && is_dir($this->dir_root.$value)){
					$this->lib('file')->rm($this->dir_root.$value,'folder');
					continue;
				}
			}
		}
		//执行table.sql操作
		$this->update_table();
		//执行新的扩展
		foreach($sqlfile AS $key=>$value){
			if(!$value || !is_file($value)){
				continue;
			}
			$info = trim(file_get_contents($value));
			if($this->db->prefix != 'qinggan_'){
				$info = str_replace('qinggan_',$this->db->prefix,$info);
			}
			if($info){
				$this->sql_run($info);
			}
		}
		//更新配置文件
		foreach($cfile AS $key=>$value){
			$base = basename($value);
			$this->lib('file')->mv($value,$this->dir_phpok.'config/'.$base);
		}
		//运行PHP文件，以实现高级的PHP更新操作
		if(file_exists($this->dir_root."data/update/run.php")){
			include($this->dir_root.'data/update/run.php');
		}
		$this->lib('file')->rm($this->dir_root.'data/update/');
		$list = $this->lib('file')->ls($this->dir_root.'data/update/');
		if($list && count($list)>0)
		{
			foreach($list as $key=>$value){
				$this->lib('file')->rm($value,'folder');
			}
		}
		//更新升级文件
		$this->success_version($verinfo);
		return array('status'=>'ok','content'=>P_Lang('升级成功'));
	}

	private function update_table()
	{
		if(!file_exists($this->dir_root.'data/update/table.sql')){
			return false;
		}
		//创建新表临时
		$prefix = 'tmp_'.$this->db->prefix;
		$sqlcontent = file_get_contents($this->dir_root.'data/update/table.sql');
		$sqlcontent = str_replace('qinggan_',$prefix,$sqlcontent);
		$this->sql_run($sqlcontent);
		//比较新表结果
		$list = $this->db->list_tables();
		$tblist = array();
		$nlength = strlen($prefix);
		$olength = strlen($this->db->prefix);
		foreach($list as $key=>$value){
			//跳过扩展表
			$continue_1 = substr($value,0,strlen($prefix.'list_'));
			$continue_2 = substr($value,0,strlen($this->db->prefix.'list_'));
			if($continue_1== $prefix.'list_' ||  $continue_2 == $this->db->prefix."list_"){
				continue;
			}
			if(substr($value,0,$nlength) == $prefix){
				$tblid = substr($value,$nlength);
				$tblist[$tblid]['new'] = $value;
			}
			if(substr($value,0,$olength) == $this->db->prefix){
				$tblid = substr($value,strlen($this->db->prefix));
				$tblist[$tblid]['old'] = $value;
			}
		}
		foreach($tblist as $key=>$value){
			if(!$value['new']){
				continue;
			}
			if(!$value['old']){
				$sql = "SHOW CREATE TABLE ".$value['new'];
				$rs = $this->db->get_one($sql);
				if(!$rs['Create Table']){
					continue;
				}
				$rs['Create Table'] = str_replace($prefix,$this->db->prefix,$rs['Create Table']);
				$this->db->query($rs['Create Table']);
				continue;
			}
			//比较新表
			$nlist = $this->db->list_fields_more($value['new']);
			$olist = $this->db->list_fields_more($value['old']);
			foreach($nlist as $k=>$v){
				if($olist[$k] && $olist[$k]['type'] == $v['type']){
					continue;
				}
				if(!$olist[$k]){
					$sql = "ALTER TABLE ".$value['old']." ADD `".$k."` ".$v['type']." ";
				}else{
					$sql = "ALTER TABLE `".$value['old']."` CHANGE `".$k."` `".$k."` ".$v['type']." ";
				}
				if($v['null'] == 'NO'){
					$sql .= " NOT NULL ";
					if($v['default'] != ''){
						$sql .= " DEFAULT ".$v['default']." ";
					}
				}else{
					$sql .= " DEFAULT ".($v['default'] != '' ? $v['default'] : ' NULL ')." ";
				}
				if($value['extra']){
					$sql .= " ".$v['extra']." ";
				}
				if($v['comment']){
					$sql .= " COMMENT '".$v['comment']."'";
				}
				$this->db->query($sql);
			}
			unset($nlist,$olist);
		}
		//删除临时表操作
		foreach($list as $key=>$value){
			if(substr($value,0,$nlength) == $prefix){
				$sql = "DROP TABLE ".$value;
				$this->db->query($sql);
			}
		}
		unset($list,$tbllist);
		return true;
	}

	//更新成功后，修改记录
	private function success_version($version='')
	{
		if(!$version){
			return false;
		}
		//写入到最新版本
		$html = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$html.= '<phpok>'."\n";
		$html.= "\t".'<version>'.trim($version).'</version>'."\n";
		$html.= "\t".'<time>'.date("Y-m-d H:i:s",$this->time).'</time>'."\n";
		$html.= '</phpok>';
		file_put_contents($this->dir_root.'data/update.xml',$html);
		if(is_writeable($this->dir_root.'version.php')){
			$html = '<?php'."\n";
			$html.= '/***********************************************************'."\n";
			$html.= "\t".'文件：version.php'."\n";
			$html.= "\t".'备注：PHPOK版本'."\n";
			$html.= "\t".'版本：4.x'."\n";
			$html.= "\t".'网站：www.phpok.com'."\n";
			$html.= "\t".'作者：qinggan <qinggan@188.com>'."\n";
			$html.= "\t".'更新：'.date("Y-m-d H:i",$this->time)."\n";
			$html.= '***********************************************************/'."\n";
			$html.= 'if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}'."\n";
			$html.= 'define("VERSION","'.trim($version).'");'."\n";
			$html.= '?>';
			file_put_contents($this->dir_root.'version.php',$html);
		}
		$this->lib('file')->rm($this->dir_root.'data/tpl_admin/');
		$this->lib('file')->rm($this->dir_root.'data/tpl_www/');
		$this->lib('file')->rm($this->dir_root.'data/cache/');
		return true;
	}

	//文件升级
	public function file_f()
	{
		$file = $this->get('file','int');
		if(!$file){
			$this->json(P_Lang('升级失败，未指定文件'));
		}
		$urlext = 'file='.rawurlencode($file);
		$rs = $this->service(5,$urlext);
		$rs = $this->lib('json')->decode($rs);
		if($rs['status'] != 'ok'){
			$this->json($rs['content']);
		}
		if(!$rs['content']){
			$this->json(P_Lang('升级失败，升级包内容为空'));
		}
		$info = base64_decode($rs['content']);
		file_put_contents($this->dir_root.'data/tmp.zip',$info);
		$this->lib('phpzip')->unzip($this->dir_root.'data/tmp.zip','data/update/');
		$this->lib('file')->rm($this->dir_root.'data/tmp.zip');
		$verinfo = substr($file,0,1).".".substr($file,1,1).".".substr($file,2);
		$info = $this->update_load($verinfo);
		if(!$info || (is_array($info) && $info['status'] == 'error')){
			if(!$info['content']) $info['content'] = '升级失败';
			$this->json($info['content']);
		}
		$this->json('ok',true);
	}

	private function sql_run($sql='')
	{
		$sql = str_replace("\r","\n",$sql);
		$ret = array();
		$num = 0;
		foreach(explode(";\n", trim($sql)) as $query){
			$queries = explode("\n", trim($query));
			foreach($queries as $query){
				$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
			}
			$num++;
		}
		foreach($ret as $query){
			$query = trim($query);
			if($query){
				$this->db->query($query);
			}
		}
		return true;
	}

	public function success_f()
	{
		$info = $this->service(3);
		$rs = $this->lib('json')->decode($info);
		if($rs['status'] != 'ok')
		{
			$this->json($rs['content']);
		}
		$this->success_version($rs['content']);
		$this->json(P_Lang('程序更新成功'),true);
	}

	public function check_f()
	{
		exit($this->service(1));
	}

	public function auto_f()
	{
		$check = false;
		$time = 0;
		if(is_file($this->dir_root.'data/update.time')){
			$time = file_get_contents($this->dir_root.'data/update.time');
		}
		if($time < $this->time && ($this->time - $this->config['update']['time'] * 86400) > $time){
			$check = true;
		}
		if($check){
			//更新检测时间
			file_put_contents($this->dir_root.'data/update.time',$this->time);
			exit($this->service(1));
		}
		$this->json(P_Lang('跳过检测'));
	}

	private function service($type=0,$urlext='')
	{
		if(!is_file($this->dir_root.'data/update.php')){
			return $this->json(P_Lang('未配置升级服务器'),false,false);
		}
		$uconfig = array();
		include($this->dir_root.'data/update.php');
		if(!$uconfig['status']){
			return $this->json(P_Lang('在线升级功能未启用'),false,false);
		}
		if(!$uconfig['server']){
			return $this->json(P_Lang('未配置升级服务器'),false,false);
		}
		if(is_file($this->dir_root.'data/update.xml')){
			$info = $this->lib('xml')->read($this->dir_root.'data/update.xml',true);
			$info['time'] = $info['time'] ? strtotime($info['time']) : 0;
		}else{
			$info['version'] = $this->version;
			$info['time'] = 0;
		}
		$url = $uconfig['server'];
		$url .= 'index.php?version='.rawurlencode(trim($info['version'])).'&time='.$this->time.'&type='.$type;
		if($urlext){
			$url.="&".$urlext;
		}
		$this->lib('html')->setting('timeout',900);
		$info = $this->lib('html')->get_content($url);
		if(!$info){
			return $this->json(P_Lang('检测异常，请登录官网查询补丁更新'),false,false);
		}
		$rs = $this->lib('xml')->read($info,false);
		if(!$rs['status']){
			return $this->json($rs['content'],false,false,false);
		}
		return $this->json($rs['content'],true,false,false);
	}
}
?>