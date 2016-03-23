<?php
/*****************************************************************************************
	文件： {phpok}/admin/sql_control.php
	备注： 数据库备份及恢复操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年3月19日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class sql_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("sql");
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		//读取全部数据库表
		$rslist = $this->model('sql')->tbl_all();
		$total_size = 0;
		if($rslist){
			foreach($rslist as $key=>$value){
				$length = $value['Avg_row_length'] + $value['Data_length'] + $value['Index_length'] + $value['Data_free'];
				$value['length'] = $this->lib('common')->num_format($length);
				$value['free'] = $value['Data_free'] ? $this->lib('common')->num_format($value['Data_free']) : 0;
				$total_size += $length;
				$rslist[$key] = $value;
			}
		}
		$this->assign("rslist",$rslist);
		$this->view("sql_index");
	}

	public function optimize_f()
	{
		if(!$this->popedom['optimize']){
			error(P_Lang('您没有权限执行此操作'),$this->url('sql'),'error');
		}
		$id = $this->get('id');
		if(!$id){
			error(P_Lang('未选定要操作的数据表'),$this->url('sql'),'error');
		}
		$idlist = explode(",",$id);
		foreach($idlist as $key=>$value){
			if(!preg_match("/^[a-z0-9A-Z\_\-]+$/u",$value)){
				continue;
			}
			$this->model('sql')->optimize($value);
		}
		error(P_Lang('数据表优化成功'),$this->url("sql"),'ok');
	}

	public function repair_f()
	{
		if(!$this->popedom['repair']){
			error(P_Lang('您没有权限执行此操作'),$this->url('sql'),'error');
		}
		$id = $this->get('id');
		if(!$id){
			error(P_Lang('未选定要操作的数据表'),$this->url('sql'),'error');
		}
		$idlist = explode(",",$id);
		foreach($idlist as $key=>$value){
			if(!preg_match("/^[a-z0-9A-Z\_\-]+$/u",$value)){
				continue;
			}
			$this->model('sql')->repair($value);
		}
		error(P_Lang('数据表信修复成功'),$this->url("sql"),'ok');
	}

	public function backup_f()
	{
		if(!$this->popedom['create']){
			error(P_Lang('您没有权限执行此操作'),$this->url('sql'),'error');
		}
		$id = $this->get('id');
		if(!$id || $id == "all"){
			$tbl_list = $this->model('sql')->tbl_all();
			$idlist = array();
			foreach($tbl_list AS $key=>$value){
				$idlist[] = $value["Name"];
			}
			$url = $this->url('sql','backup','id=all');
		}else{
			$url = $this->url("sql","backup","id=".rawurlencode($id));
			$idlist = explode(",",$id);
		}
		$backfilename = $this->get('backfilename');
		if(!$backfilename){
			$sql_prefix = $this->model('sql')->sql_prefix();
			$backfilename = "sql".$this->time;
			$url .= "&backfilename=".$backfilename;
			//更新数据表结构
			$html = "";
			foreach($idlist as $key=>$value){
				if(!preg_match("/^[a-z0-9A-Z\_\-]+$/u",$value)){
					continue;
				}
				$html .= "DROP TABLE IF EXISTS ".$value.";\n";
				$html .= $this->model('sql')->show_create_table($value);
				$html .= ";\n\n";
				if($value == $sql_prefix.'adm'){
					$rslist = $this->model('sql')->getsql($sql_prefix."adm",0,"all");
					if($rslist){
						foreach($rslist AS $k=>$v){
							$html .= "INSERT INTO ".$sql_prefix."admin VALUES('".implode("','",$v)."');\n";
						}
					}
				}
			}
			$this->lib('file')->vi($html,$this->dir_root.'data/'.$backfilename.".php");//存储数据
			$this->lib('file')->vi("-- PHPOK4 Full 数据备份\n\n",$this->dir_root.'data/'.$backfilename."_tmpdata.php");
			error(P_Lang('表结构备份成功，正在执行下一步'),$url,'ok');
		}
		$url .= "&backfilename=".$backfilename;
		$startid = $this->get("startid","int");
		$dataid = $this->get("dataid",'int');
		if(($startid + 1)> count($idlist) && file_exists($this->dir_root.'data/'.$backfilename.'_tmpdata.php')){
			$newfile = $this->dir_root.'data/'.$backfilename.'_'.$dataid.'.php';
			$this->lib('file')->mv($this->dir_root.'data/'.$backfilename.'_tmpdata.php',$newfile);
			error(P_Lang('数据备份成功'),$this->url('sql','backlist'),'ok');
		}
		$pageid = $this->get("pageid",'int');
		$table = $idlist[$startid];//指定表
		//判断如果是管理员表，则跳到下一步
		if($table == $sql_prefix."adm" || $table == $sql_prefix."session"){
			$url .= "&startid=".($startid+1)."&pageid=".$pageid."&dataid=".$dataid;
			error(P_Lang('数据表{table}已备份完成！正在进行下一步操作，请稍候！',array('table'=>' <span class="red">'.$table.'</span> ')),$url);
		}
		$psize = 100;
		$total = $this->model('sql')->table_count($table);
		if($total<1){
			$url .= "&startid=".($startid+1)."&pageid=".$pageid."&dataid=".$dataid;
			error(P_Lang('数据表{table}已备份完成！正在进行下一步操作，请稍候！',array('table'=>' <span class="red">'.$table.'</span> ')),$url);
		}
		if($psize >= $total){
			$rslist = $this->model('sql')->getsql($table,0,'all');
			if(!$rslist){
				$rslist = array();
			}
			$msg = "\n-- table : ".$table." , backup time ".date("Y-m-d H:i:s",$this->time)."\n";
			$msg.= "INSERT INTO ".$table." VALUES";
			$i=0;
			foreach($rslist as $key=>$value){
				$tmp_value = array();
				foreach($value AS $k=>$v){
					$v = $this->model('sql')->escape($v);
					$tmp_value[$k] = $v;
				}
				if($i){
					$msg .= ",\n";
				}
				$msg .= "('".implode("','",$tmp_value)."')";
				$i++;
			}
			$msg .= ";\n";
			$new_startid = $startid + 1;
			$pageid = 0;
		}else{
			$msg = '';
			$pageid = $this->get('pageid','int');
			if($pageid<1){
				$pageid = 1;
			}
			if($pageid<2){
				$msg .= "\n-- table : ".$table." , backup time ".date("Y-m-d H:i:s",$this->time)."\n";
			}
			$offset = ($pageid-1) * $psize;
			if($offset < $total){
				$rslist = $this->model('sql')->getsql($table,$offset,$psize);
				if($rslist){
					$msg.= "INSERT INTO ".$table." VALUES";
					$i=0;
					foreach($rslist AS $key=>$value){
						$tmp_value = array();
						foreach($value AS $k=>$v){
							$v = $this->model('sql')->escape($v);
							$tmp_value[$k] = $v;
						}
						if($i){
							$msg .= ",\n";
						}
						$msg .= "('".implode("','",$tmp_value)."')";
						$i++;
					}
					$msg .= ";\n";
					$new_startid = $startid;
					$pageid = $pageid + 1;
				}else{
					$new_startid = $startid + 1;
					$pageid = 0;
				}
			}else{
				$new_startid = $startid + 1;
				$pageid = 0;
			}
		}
		$url .= "&startid=".$new_startid."&pageid=".$pageid;
		$fsize = 0;
		if(!file_exists($this->dir_root.'data/'.$backfilename.'_tmpdata.php')){
			$tmpinfo = "\n-- Create time:".date("Y-m-d H:i:s",$this->time)."\n";
			$this->lib('file')->vi($tmpinfo,$this->dir_root.'data/'.$backfilename.'_tmpdata.php','file');
		}
		$this->lib('file')->vi(addslashes($msg),$this->dir_root.'data/'.$backfilename.'_tmpdata.php','','ab');
		$fsize = filesize($this->dir_root.'data/'.$backfilename.'_tmpdata.php');
		$update_dataid = false;
		if($fsize >= 2097152 || !$idlist[$new_startid]){
			$update_dataid = true;
			$newfile = $this->dir_root.'data/'.$backfilename.'_'.intval($dataid).'.php';
			$this->lib('file')->mv($this->dir_root.'data/'.$backfilename.'_tmpdata.php',$newfile);
		}
		if($update_dataid){
			$url .= "&dataid=".(intval($dataid)+1);
		}
		if(!$idlist[$new_startid]){
			error(P_Lang('数据备份成功'),$this->url('sql','backlist'),'ok');
		}
		error(P_Lang('正在备份数据，当前第{pageid}个文件，正在备{table}相关数据',array('pageid'=>' <span class="red">'.($dataid+1).'</span> ','table'=>' <span class="red">'.$idlist[$startid].'</span> ')),$url,'ok');
	}

	public function backlist_f()
	{
		if(!$this->popedom['list']){
			error(P_Lang('您没有权限执行此操作'),$this->url('sql'),'error');
		}
		$filelist = $this->lib('file')->ls($this->dir_root.'data/');
		if(!$filelist){
			error(P_Lang('空数据，请检查目录：{root}data/',array('root'=>$this->dir_root)),$this->url("sql"));
		}
		$tmplist = array();
		$i=0;
		foreach($filelist AS $key=>$value){
			$bv = basename($value);
			if(substr($bv,0,3) == "sql" && strlen($bv) == 17 && substr($bv,-4) == '.php'){
				$tmplist[$i] = array('filename'=>$bv,'time'=>date("Y-m-d H:i:s",substr($bv,3,10)),'size'=>filesize($value),'id'=>substr($bv,3,10));
				$i++;
			}
			if(!file_exists($value) || substr($bv,0,3) != 'sql' || strpos($bv,'_') === false || substr($bv,-4) != '.php'){
				unset($filelist[$key]);
			}
		}
		if(!$tmplist){
			error(P_Lang('没有相备份数据'),$this->url('sql'));
		}
		foreach($tmplist as $key=>$value){
			foreach($filelist as $k=>$v){
				$tmp = basename($v);
				if(substr($tmp,0,13) == 'sql'.$value['id']){
					$value['size'] += filesize($v);
				}
			}
			$tmplist[$key] = $value;
		}
		foreach($tmplist as $key=>$value){
			$value['size_str'] = $this->lib('common')->num_format($value['size']);
			$tmplist[$key] = $value;
		}
		$this->tpl->assign("rslist",$tmplist);
		$this->view("sql_list");
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			error(P_Lang('您没有权限执行此操作'),$this->url('sql'),'error');
		}
		$id = $this->get('id');
		if(!$id){
			error(P_Lang('没有指定备份文件'),$this->url('sql','backlist'),'error');
		}
		$filelist = $this->lib('file')->ls($this->dir_root.'data/');
		if(!$filelist){
			error(P_Lang('空数据，请检查目录：{root}data/',array('root'=>$this->dir_root)),$this->url("sql"));
		}
		$idlen = strlen($id);
		foreach($filelist AS $key=>$value){
			$bv = basename($value);
			if(substr($bv,0,13) == 'sql'.$id){
				$this->lib('file')->rm($value);
			}
		}
		error(P_Lang('备份文件删除成功'),$this->url('sql','backlist'),'ok');
	}

	public function recover_f()
	{
		if(!$this->popedom['recover']){
			error(P_Lang('您没有权限执行此操作'),$this->url('sql'),'error');
		}
		$id = $this->get('id');
		if(!$id){
			error(P_Lang('没有指定备份文件'),$this->url('sql','backlist'),'error');
		}
		$backfile = $this->dir_root.'data/sql'.$id.'.php';
		if(!file_exists($backfile)){
			error(P_Lang('备份文件不存在'),$this->url('sql','backlist'),'error');
		}
		$session = $_SESSION;
		$msg = $this->lib('file')->cat($backfile);
		$this->format_sql($msg);
		//判断管理员是否存在
		$admin_rs = $this->model('admin')->get_one($session['admin_id'],'id');
		if(!$admin_rs || $admin_rs['account'] != $session['admin_account']){
			//写入当前登录的管理员信息
			if(!$admin_rs){
				$this->model('sql')->update_adm($session['admin_rs'],$session['admin_id']);
			}else{
				$this->model('sql')->update_adm($session['admin_rs']);
			}
		}
		//更新相应的SESSION信息，防止被退出
		$_SESSION = $session;
		error(P_Lang('表结构数据修复成功，正在修复内容数据，请稍候！'),$this->url('sql','recover_data','id='.$id."&startid=0"),'ok');
	}

	public function recover_data_f()
	{
		if(!$this->popedom['recover']){
			error(P_Lang('您没有权限执行此操作'),$this->url('sql'),'error');
		}
		$id = $this->get('id');
		if(!$id){
			error(P_Lang('没有指定备份文件'),$this->url('sql','backlist'),'error');
		}
		$startid = $this->get('startid','int');
		$backfile = $this->dir_root.'data/sql'.$id.'_'.$startid.'.php';
		if(!file_exists($backfile)){
			error(P_Lang('数据恢复完成'),$this->url('sql','backlist'),'ok');
		}
		$msg = $this->lib('file')->cat($backfile);
		$this->format_sql($msg);
		$new_startid = $startid + 1;
		$newfile = $this->dir_root.'data/sql'.$id.'_'.$new_startid.'.php';
		if(!file_exists($newfile)){
			error(P_Lang('数据恢复完成'),$this->url('sql','backlist'),'ok');
		}
		error(P_Lang("正在恢复数据，正在恢复第{pageid}个文件，请稍候…",array('pageid'=>' <span class="red">'.($startid+1).'</span>')),$this->url('sql','recover_data','id='.$id.'&startid='.$new_startid),'ok');
	}

	private function format_sql($sql)
	{
		$sql = str_replace("\r","\n",$sql);
		$list = explode(";\n",trim($sql));
		foreach($list as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			$vlist = explode("\n",trim($value));
			$tmpsql = '';
			foreach($vlist as $k=>$v){
				if(!$v || !trim($v)){
					continue;
				}
				$v = trim($v);
				if(substr($v,0,1) != '#' && substr($v,0,2) != '--'){
					$tmpsql .= $v;
				}
			}
			if($tmpsql){
				$this->model('sql')->query($tmpsql);
			}
		}
	}
}
?>