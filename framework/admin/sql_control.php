<?php
/**
 * 数据库备份及恢复操作
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年12月02日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class sql_control extends phpok_control
{
	private $popedom;

	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("sql");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 数据库列表
	**/
	public function index_f()
	{
		if(!$this->popedom["list"] && !$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		//读取全部数据库表
		$rslist = $this->model('sql')->tbl_all();
		$total_size = 0;
		$strlen = strlen($this->db->prefix);
		if($rslist){
			foreach($rslist as $key=>$value){
				$length = $value['Avg_row_length'] + $value['Data_length'] + $value['Index_length'] + $value['Data_free'];
				$value['length'] = $this->lib('common')->num_format($length);
				$value['free'] = $value['Data_free'] ? $this->lib('common')->num_format($value['Data_free']) : 0;
				$total_size += $length;
				$value['delete'] = substr($value['Name'],0,$strlen) == $this->db->prefix ? false : true;
				$rslist[$key] = $value;
			}
		}
		$this->assign("rslist",$rslist);
		$this->view("sql_index");
	}

	/**
	 * 数据表优化
	 * @参数 id 要优化的数据表，不能为空
	**/
	public function optimize_f()
	{
		if(!$this->popedom['optimize']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未选定要操作的数据表'));
		}
		$idlist = explode(",",$id);
		foreach($idlist as $key=>$value){
			if(!preg_match("/^[a-z0-9A-Z\_\-]+$/u",$value)){
				continue;
			}
			$this->model('sql')->optimize($value);
		}
		$this->success();
	}

	/**
	 * 数据表修复
	 * @参数 id 要修复的数据表，不能为空
	**/
	public function repair_f()
	{
		if(!$this->popedom['repair']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未选定要操作的数据表'));
		}
		$idlist = explode(",",$id);
		foreach($idlist as $key=>$value){
			if(!preg_match("/^[a-z0-9A-Z\_\-]+$/u",$value)){
				continue;
			}
			$this->model('sql')->repair($value);
		}
		$this->success();
	}

	/**
	 * 备份数据表操作
	 * @参数 id 要备份的表，为空或all时表示备份全部
	 * @参数 backfilename 备份的文件名，为空表示刚开始备份，系统自动生成一个备份文件名，并同时将数据库里的表结构备份好
	 * @参数 startid 整数型 开始ID，为空表示从0开始，表示备份表的ID顺序
	 * @参数 dataid 备份到的目标ID，也是从0开始（为空即为0）
	 * @参数 pageid 页码ID，每次备份100条数据，当数据表中的数据超过100条时，pageid就起到作用了
	**/
	public function backup_f()
	{
		if(!$this->popedom['create']){
			$this->error(P_Lang('您没有权限执行此操作'),$this->url('sql'));
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
		$sql_prefix = $this->model('sql')->sql_prefix();
		if(!$backfilename){
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
							$html .= "INSERT INTO ".$sql_prefix."adm VALUES('".implode("','",$v)."');\n";
						}
					}
					$html .= "\n";
				}
			}
			$this->lib('file')->vi($html,$this->dir_data.$backfilename.".php");//存储数据
			$this->lib('file')->vi("-- PHPOK5 Full 数据备份\n\n",$this->dir_data.$backfilename."_tmpdata.php");
			$this->success(P_Lang('表结构备份成功，正在执行下一步'),$url,0.5);
		}
		$url .= "&backfilename=".$backfilename;
		$startid = $this->get("startid","int");
		$dataid = $this->get("dataid",'int');
		if(($startid + 1)> count($idlist) && file_exists($this->dir_data.$backfilename.'_tmpdata.php')){
			$newfile = $this->dir_data.$backfilename.'_'.$dataid.'.php';
			$this->lib('file')->mv($this->dir_data.$backfilename.'_tmpdata.php',$newfile);
			$this->success(P_Lang('数据备份成功'),$this->url('sql','backlist'));
		}
		$pageid = $this->get("pageid",'int');
		$table = $idlist[$startid];//指定表
		//判断如果是管理员表，则跳到下一步
		if($table == $sql_prefix."adm" || $table == $sql_prefix."session" || $table == $sql_prefix."log"){
			$url .= "&startid=".($startid+1)."&pageid=".$pageid."&dataid=".$dataid;
			$this->success(P_Lang('数据表{table}已备份完成！正在进行下一步操作，请稍候！',array('table'=>' <span class="red">'.$table.'</span> ')),$url,0.5);
		}
		$psize = 100;
		$total = $this->model('sql')->table_count($table);
		if($total<1){
			$url .= "&startid=".($startid+1)."&pageid=".$pageid."&dataid=".$dataid;
			$this->success(P_Lang('数据表{table}已备份完成！正在进行下一步操作，请稍候！',array('table'=>' <span class="red">'.$table.'</span> ')),$url,0.5);
		}
		if($psize >= $total){
			$rslist = $this->model('sql')->getsql($table,0,'all');
			if(!$rslist){
				$rslist = array();
			}
			$msg = "\n-- 表：".$table."，备份时间：".date("Y-m-d H:i:s",$this->time)."\n";
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
				$msg .= "\n-- 表：".$table."，备份时间：".date("Y-m-d H:i:s",$this->time)."\n";
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
		if(!file_exists($this->dir_data.$backfilename.'_tmpdata.php')){
			$tmpinfo = "\n-- Create time:".date("Y-m-d H:i:s",$this->time)."\n";
			$this->lib('file')->vi($tmpinfo,$this->dir_data.$backfilename.'_tmpdata.php','file');
		}
		$this->lib('file')->vi(addslashes($msg),$this->dir_data.$backfilename.'_tmpdata.php','','ab');
		$fsize = filesize($this->dir_data.$backfilename.'_tmpdata.php');
		$update_dataid = false;
		if($fsize >= 2097152 || !$idlist[$new_startid]){
			$update_dataid = true;
			$newfile = $this->dir_data.$backfilename.'_'.intval($dataid).'.php';
			$this->lib('file')->mv($this->dir_data.$backfilename.'_tmpdata.php',$newfile);
		}
		if($update_dataid){
			$url .= "&dataid=".(intval($dataid)+1);
		}
		if(!$idlist[$new_startid]){
			$this->success(P_Lang('数据备份成功'),$this->url('sql','backlist'));
		}
		$tmparray = array('pageid'=>' <span class="red">'.($dataid+1).'</span> ','table'=>' <span class="red">'.$idlist[$startid].'</span> ');
		$this->success(P_Lang('正在备份数据，当前第{pageid}个文件，正在备{table}相关数据',$tmparray),$url,0.5);
	}

	/**
	 * 备份列表，查看当前系统备份的数据表数据
	**/
	public function backlist_f()
	{
		if(!$this->popedom['list'] && !$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$filelist = $this->lib('file')->ls($this->dir_data);
		if(!$filelist){
			$this->error(P_Lang('空数据，请检查目录：_data/'));
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
			$this->error(P_Lang('没有相备份数据'));
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
		$this->assign("rslist",$tmplist);
		$this->view("sql_list");
	}

	/**
	 * 删除备份数据
	 * @参数 id 指定要删除的备份数据ID
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'),$this->url('sql'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('没有指定备份文件'),$this->url('sql','backlist'));
		}
		$filelist = $this->lib('file')->ls($this->dir_data);
		if(!$filelist){
			$this->error(P_Lang('空数据，请检查目录：_data/'),$this->url("sql"));
		}
		$idlen = strlen($id);
		foreach($filelist AS $key=>$value){
			$bv = basename($value);
			if(substr($bv,0,13) == 'sql'.$id){
				$this->lib('file')->rm($value);
			}
		}
		$this->success(P_Lang('备份文件删除成功'),$this->url('sql','backlist'));
	}

	/**
	 * 恢复数据备份
	 * @参数 id 要恢复的数据ID
	**/
	public function recover_f()
	{
		if(!$this->popedom['recover'] && !$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'),$this->url('sql'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('没有指定备份文件'),$this->url('sql','backlist'));
		}
		$backfile = $this->dir_data.'sql'.$id.'.php';
		if(!file_exists($backfile)){
			$this->error(P_Lang('备份文件不存在'),$this->url('sql','backlist'));
		}
		$session_id = $this->session->sessid();
		$session = $_SESSION;
		$msg = $this->lib('file')->cat($backfile);
		$this->format_sql($msg);
		//判断管理员是否存在
		$admin_rs = $this->model('admin')->get_one($session['admin_id'],'id');
		if(!$admin_rs || $admin_rs['account'] != $session['admin_account']){
			//写入当前登录的管理员信息
			if($admin_rs){
				$this->model('sql')->update_adm($session['admin_rs'],$session['admin_id']);
			}else{
				$insert_id = $this->model('sql')->update_adm($session['admin_rs']);
				$session['admin_id'] = $insert_id;
			}
		}
		//更新相应的SESSION信息，防止被退出
		$_SESSION = $session;
		$this->success(P_Lang('表结构数据恢复成功，正在恢复内容数据，请稍候…'),$this->url('sql','recover_data','id='.$id."&startid=0"));
	}

	/**
	 * 恢复备份文件中的其他数据
	 * @参数 id 要恢复的数据ID
	 * @参数 startid 开始ID，从0记起
	**/
	public function recover_data_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'),$this->url('sql'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('没有指定备份文件'),$this->url('sql','backlist'));
		}
		$startid = $this->get('startid','int');
		$backfile = $this->dir_data.'sql'.$id.'_'.$startid.'.php';
		if(!file_exists($backfile)){
			$this->success(P_Lang('数据恢复完成'),$this->url('sql','backlist'));
		}
		$msg = $this->lib('file')->cat($backfile);
		$this->format_sql($msg);
		$new_startid = $startid + 1;
		$newfile = $this->dir_data.'sql'.$id.'_'.$new_startid.'.php';
		if(!file_exists($newfile)){
			$this->success(P_Lang('数据恢复完成'),$this->url('sql','backlist'));
		}
		$tmparray = array('pageid'=>' <span class="red">'.($startid+1).'</span>');
		$this->success(P_Lang("正在恢复数据，正在恢复第{pageid}个文件，请稍候…",$tmparray),$this->url('sql','recover_data','id='.$id.'&startid='.$new_startid));
	}

	/**
	 * 格式化SQL语句
	 * @参数 $sql 要格式化的数据
	**/
	private function format_sql($sql)
	{
		$sql = str_replace("\r","\n",$sql);
		$list = explode(";\n",trim($sql));
		$update_admin = false;
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
				if(strpos($tmpsql,'INSERT INTO '.$this->db->prefix.'adm') !== false){
					$sql = "TRUNCATE TABLE `".$this->db->prefix."adm`";
					$this->db->query($sql);
					$update_admin = true;
				}
				$this->model('sql')->query($tmpsql);
			}
		}
		if($update_admin){
			$admin_rs = $this->model('admin')->get_one($this->session->val('admin_id'),'id');
			if(!$admin_rs || $admin_rs['account'] != $this->session->val('admin_account')){
				if($admin_rs){
					$this->model('sql')->update_adm($this->session->val('admin_rs'),$this->session->val('admin_id'));
				}else{
					$insert_id = $this->model('sql')->update_adm($this->session->val('admin_rs'));
					$this->session->assign('admin_id',$insert_id);
				}
			}
		}
		return true;
	}

	public function show_f()
	{
		$tbl = $this->get('table');
		if(!$tbl){
			$this->error(P_Lang('未指定表名'));
		}
		$rslist = $this->model('sql')->table_info($tbl);
		$this->assign('rslist',$rslist);
		$this->assign('tbl',$tbl);
		$this->view('sql_show');
	}

	public function table_delete_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$tbl = $this->get('tbl');
		if(!$tbl){
			$this->error(P_Lang('未指定表名'));
		}
		$length = strlen($this->db->prefix);
		if(substr($tbl,0,$length) == $this->db->prefix){
			$this->error(P_Lang('官网前缀的系统表不支持删除'));
		}
		$this->model('sql')->table_delete($tbl);
		$this->success();
	}

	public function replace_f()
	{
		$tbl = $this->get('table');
		if(!$tbl){
			$this->error(P_Lang('未指定表名'));
		}
		$field = $this->get('field');
		if(!$field){
			$this->error(P_Lang('未指定字段名'));
		}
		$val1 = $this->get('val1','html');
		if(!$val1){
			$this->error(P_Lang('未指定替换前的文本'));
		}
		$val2 = $this->get('val2');
		$sql = "UPDATE ".$tbl." SET ".$field."=REPLACE(".$field.",'".$val1."','".$val2."')";
		$this->db->query($sql);
		$this->success();
	}
}