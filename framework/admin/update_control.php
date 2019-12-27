<?php
/**
 * PHPOK4升级引挈控制器
 * @package phpok\admin\update
 * @author qinggan <admin@phpok.com>
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @homepage http://www.phpok.com
 * @version 4.x
 * @license http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @update 2016年07月19日
**/

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
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$update = array('online'=>true,'zip'=>true);
		$setfile = $this->dir_data.'update.php';
		$uconfig = array();
		if(file_exists($setfile)){
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
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$setfile = $this->dir_data.'update.php';
		$uconfig = array();
		if(file_exists($setfile)){
			include($setfile);
		}
		$this->assign("rs",$uconfig);
		$this->view('update_set');
	}

	//存储配置
	public function save_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$setfile = $this->dir_data.'update.php';
		$uconfig = array();
		if(file_exists($setfile)){
			include($setfile);
		}
		$uconfig['status'] = $this->get('status','int');
		$uconfig['server'] = $this->get('server');
		$uconfig['date'] = $this->get('date','int');
		$uconfig['ip'] = $this->get('ip');
		if(!$uconfig['onlyid']){
			$uconfig['onlyid'] = $this->_onlyid();
		}
		$this->lib('file')->vi($uconfig,$this->dir_data.'update.php','uconfig');
		$this->success();
	}


	private function _onlyid()
	{
		$onlyid = $this->lib('server')->domain($this->config['get_domain_method']).'/phpok/';
		$onlyid.= $this->lib('common')->ip().'/phpok/';
		$onlyid.= $this->lib('server')->signature().'/phpok/';
		if(file_exists($this->dir_root.'config.php')){
			$onlyid.= filemtime($this->dir_root.'config.php');
		}
		if(file_exists($this->dir_config.'db.ini.php')){
			$onlyid.= filemtime($this->dir_config.'db.ini.php');
		}
		return md5($onlyid);
	}

	//在线升级
	public function main_f()
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
		if(file_exists($this->dir_data.'update.php')){
			include($this->dir_data.'update.php');
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
	public function zip_f()
	{
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
		$this->view('update_zip');
	}

	//解压zip
	public function unzip_f()
	{
		$zipfile = $this->get('zipfile');
		if(!$zipfile){
			$this->error(P_Lang('未指定附件文件'));
		}
		if(strpos($zipfile,'..') !== false){
			$this->error(P_Lang('不支持带..上级路径'));
		}
		if(!file_exists($this->dir_root.$zipfile)){
			$this->error(P_Lang('ZIP文件不存在'));
		}
		$this->lib('phpzip')->unzip($this->dir_root.$zipfile,$this->dir_data.'update/');
		$info = $this->update_load();
		if(!$info || (is_array($info) && $info['status'] == 'error')){
			$this->error($info['content']);
		}
		$this->success();
	}

	//升级文件
	private function update_load($verinfo='')
	{
		$list = array();
		$this->lib('file')->deep_ls($this->dir_data.'update/',$list);
		if(!$list || count($list) < 1){
			return array('status'=>'error','content'=>P_Lang('没有升级文件内容'));
		}
		$strlen = strlen($this->dir_data."update/");
		$delfile = false;
		$sqlfile = array();
		foreach($list as $key=>$value){
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
		foreach($sqlfile as $key=>$value){
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
		//运行PHP文件，以实现高级的PHP更新操作
		if(file_exists($this->dir_data."update/run.php")){
			include($this->dir_data.'update/run.php');
		}
		$this->lib('file')->rm($this->dir_data.'update/');
		$list = $this->lib('file')->ls($this->dir_data.'update/');
		if($list && count($list)>0){
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
		if(!file_exists($this->dir_data.'update/table.sql')){
			return false;
		}
		//创建新表临时
		$prefix = 'tmp_'.$this->db->prefix;
		$sqlcontent = file_get_contents($this->dir_data.'update/table.sql');
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
		file_put_contents($this->dir_data.'update.xml',$html);
		if(is_writeable($this->dir_root.'version.php') && file_exists($this->dir_data.'version.tpl')){
			$info = file_get_contents($this->dir_data.'version.tpl');
			$info = str_replace('{version}',trim($version),$info);
			$info = str_replace('{updatetime}',date("Y年m月d日 H时i分s秒",$this->time),$info);
			file_put_contents($this->dir_root.'version.php',$info);
		}
		$this->lib('file')->rm($this->dir_data.'tpl_admin/');
		$this->lib('file')->rm($this->dir_data.'tpl_www/');
		$this->lib('file')->rm($this->dir_cache);
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
		file_put_contents($this->dir_data.'tmp.zip',$info);
		$this->lib('phpzip')->unzip($this->dir_data.'tmp.zip',$this->dir_data.'update/');
		$this->lib('file')->rm($this->dir_data.'tmp.zip');
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
		if($rs['status'] != 'ok'){
			$this->json($rs['content']);
		}
		$this->success_version($rs['content']);
		$this->json(P_Lang('程序更新成功'),true);
	}

	public function check_f()
	{
		if(file_exists($this->dir_data.'update.php')){
			include($this->dir_data.'update.php');
			$time = 0;
			if(file_exists($this->dir_data.'update.time')){
				$time = $this->lib('file')->cat($this->dir_data.'update.time');
			}
			$check = false;
			if($uconfig['status'] && $time < $this->time && ($this->time - $uconfig['date'] * 86400) > $time){
				$check = true;
			}
			if($check){
				$this->lib('file')->vim($this->time,$this->dir_data.'update.time');
				$list['update_action'] = true;
			}
		}
		exit($this->service(1));
	}

	public function auto_f()
	{
		$check = false;
		$time = 0;
		if(file_exists($this->dir_data.'update.time')){
			$time = file_get_contents($this->dir_data.'update.time');
		}
		if($time < $this->time && ($this->time - $this->config['update']['time'] * 86400) > $time){
			$check = true;
		}
		if($check){
			//更新检测时间
			file_put_contents($this->dir_data.'update.time',$this->time);
			exit($this->service(1));
		}
		$this->json(P_Lang('跳过检测'));
	}

	private function service($type=0,$urlext='')
	{
		if(!file_exists($this->dir_data.'update.php')){
			return $this->json(P_Lang('未配置升级服务器'),false,false);
		}
		$uconfig = array();
		include($this->dir_data.'update.php');
		if(!$uconfig['status']){
			return $this->json(P_Lang('在线升级功能未启用'),false,false);
		}
		if(!$uconfig['server']){
			return $this->json(P_Lang('未配置升级服务器'),false,false);
		}
		if(file_exists($this->dir_data.'update.xml')){
			$info = $this->lib('xml')->read($this->dir_data.'update.xml',true);
			$info['time'] = $info['time'] ? strtotime($info['time']) : 0;
		}else{
			$info['version'] = $this->version;
			$info['time'] = 0;
		}
		$url = $uconfig['server'];
		if(substr($url,-1) != '/'){
			$url .= '/';
		}
		$url .= 'index.php?version='.rawurlencode(trim($info['version'])).'&time='.$this->time.'&type='.$type;
		if($urlext){
			$url.="&".$urlext;
		}
		if($type == 1 || $type == 4){
			$onlyid = $uconfig['onlyid'] ? $uconfig['onlyid'] : $this->_onlyid();
			$domain = $this->lib('server')->domain($this->config['get_domain_method']);
			$client_ip = $this->lib('common')->ip();
			$url .= "&domain=".rawurlencode($domain)."&ip=".rawurlencode($client_ip);
			$url .= "&onlyid=".$onlyid."&phpversion=".PHP_VERSION;
			if(function_exists('php_uname')){
				$url .= "&server=".rawurlencode(php_uname('s'));
			}
			$soft = $_SERVER['SERVER_SOFTWARE'];
			if($soft){
				$url .= "&soft=".rawurlencode($soft);
			}
			$mysqlversion = $this->db->version('server');
			if($mysqlversion){
				$url .= "&mysql=".$mysqlversion;
			}
		}
		$this->lib('html')->setting('timeout',900);
		if($uconfig['ip']){
			$this->lib('html')->ip($uconfig['ip']);
		}
		$info = $this->lib('html')->get_content($url);
		if(!$info){
			return $this->json(P_Lang('检测异常，请登录官网查询补丁更新'),false,false);
		}
		$rs = $this->lib('xml')->read($info,false);
		if(!$rs['status']){
			return $this->json($rs['content'],false,false,false);
		}
		$rs = array('status'=>'ok','content'=>$rs['content']);
		return $this->lib('json')->encode($rs);
	}
}