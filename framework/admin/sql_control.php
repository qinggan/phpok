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
	public $popedom;
	function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("sql");
		$this->assign("popedom",$this->popedom);
	}

	function index_f()
	{
		if(!$this->popedom["list"])
		{
			error("你没有查看权限",$this->url('index'),'error');
		}
		//读取全部数据库表
		$rslist = $this->model('sql')->tbl_all();
		$this->assign("rslist",$rslist);
		$this->view("sql_index");
	}

	function optimize_f()
	{
		sys_popedom("phpoksql:set","tpl");
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			error("没有指定要优化的数据表！",site_url("phpoksql"));
		}
		$idlist = sys_id_list($id);
		foreach($idlist AS $key=>$value)
		{
			$this->sql_m->optimize($value);
		}
		error("指定数据表信息已优化完成！",site_url("phpoksql"));
	}

	function repair_f()
	{
		sys_popedom("phpoksql:set","tpl");
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			error("没有指定要修复的数据表！",site_url("phpoksql"));
		}
		$idlist = sys_id_list($id);
		foreach($idlist AS $key=>$value)
		{
			$this->sql_m->repair($value);
		}
		error("指定数据表信息已修复完成！",site_url("phpoksql"));
	}

	function backup_f()
	{
		sys_popedom("phpoksql:set","tpl");
		$id = $this->trans_lib->safe("id");
		if(!$id || $id == "all")
		{
			$tbl_list = $this->sql_m->get_all();
			$idlist = array();
			foreach($tbl_list AS $key=>$value)
			{
				$idlist[] = $value["Name"];
			}
			$tourl = site_url("phpoksql,backup")."id=all";
		}
		else
		{
			$idlist = sys_id_list($id);
			$tourl = site_url("phpoksql,backup")."id=".rawurlencode($id);
		}
		//备份的文件名，如果为空，则表示未创建表结构
		$backfilename = $this->trans_lib->safe("backfilename");
		if(!$backfilename)
		{
			$sql_prefix = $this->sql_m->sql_prefix();//数据表前缀
			//创建备份文件名称
			$backfilename = "sql_".date("YmdHis",$this->system_time)."_".$_SESSION["admin_id"];
			//生成表名，获取表结构
			$html = "";
			foreach($idlist AS $key=>$value)
			{
				//禁止session表恢复操作
				if($value != $sql_prefix."session")
				{
					$html .= "DROP TABLE IF EXISTS ".$value.";\n";
					$html .= $this->sql_m->show_create_table($value);
					$html .= ";\n\n";
				}
			}
			//判断是否包含 管理员表，如果包含，则同时更新管理员数据
			if(in_array($sql_prefix."admin",$idlist))
			{
				$rslist = $this->sql_m->getsql($sql_prefix."admin",0,"all");
				if($rslist)
				{
					foreach($rslist AS $key=>$value)
					{
						$html .= "INSERT INTO ".$sql_prefix."admin VALUES('".implode("','",$value)."');\n";
					}
				}
			}
			$this->file_lib->vi($html,ROOT_DATA.$backfilename.".php");//存储数据
			$tourl .= "&backfilename=".rawurlencode($backfilename);//已创建表
			//初始化临昨表数据
			$this->file_lib->vi("#PHPOK Full 数据备份\n\n",ROOT_DATA.$backfilename."_tmpdata.php");
			error("表结构信息备份完毕，请稍候，正在执行下一步！",$tourl);
		}
		$tourl .= "&backfilename=".rawurlencode($backfilename);
		$startid = $this->trans_lib->int("startid");
		//判断startid是否存在
		if(($startid + 1)> count($idlist))
		{
			error("数据备份完毕！系统将返回已备份列表中",site_url('phpoksql,baklist'));
		}
		$table = $idlist[$startid];//指定表
		//判断如果是管理员表，则跳到下一步
		if($table == $sql_prefix."admin")
		{
			$pageid = $this->trans_lib->int("pageid");
			$dataid = $this->trans_lib->int("dataid");
			$tourl .= "&startid=".($startid+1)."&pageid=".$pageid."&dataid=".$dataid;
			error("数据表 ".$table." 已备份完成！正在进行下一步操作，请稍候！",$tourl);
		}
		//如果是session表，自动进入下一步操作
		if($table == $sql_prefix."session")
		{
			$pageid = $this->trans_lib->int("pageid");
			$dataid = $this->trans_lib->int("dataid");
			$tourl .= "&startid=".($startid+1)."&pageid=".$pageid."&dataid=".$dataid;
			error("数据表 ".$table." 无需备份数据！正在进行下一步操作，请稍候！",$tourl);
		}
		//每次只备份1000条数据
		$msg = "";
		$oldmsg = "";
		if(file_exists(ROOT_DATA.$backfilename."_tmpdata.php"))
		{
			$oldmsg = $this->file_lib->cat(ROOT_DATA.$backfilename."_tmpdata.php");
		}
		$psize = 100;//每次查询最多读取次数
		$total = $this->sql_m->table_count($table);//取得当前表的总记录数
		if($psize >= $total)
		{
			$rslist = $this->sql_m->getsql($table,0,"all");
			if($rslist)
			{
				$msg .= "\n#table : ".$table." , backup time ".date("Y-m-d H:i:s",$this->system_time)."\n";
				foreach($rslist AS $key=>$value)
				{
					$tmp_value = array();
					foreach($value AS $k=>$v)
					{
						$v = $this->sql_m->escape_string($v);
						$tmp_value[$k] = $v;
					}
					$msg .= "INSERT INTO ".$table." VALUES('".implode("','",$tmp_value)."');\n";
				}
			}
			$new_startid = $startid + 1;
			$pageid = 0;
		}
		else
		{
			$pageid = $this->trans_lib->int("pageid");
			if($pageid<1) $pageid = 1;
			if($pageid<2)
			{
				$msg .= "\n#table : ".$table." , backup time ".date("Y-m-d H:i:s",$this->system_time)."\n";
			}
			$offset = ($pageid-1)*$psize;
			if($offset < $total)
			{
				$rslist = $this->sql_m->getsql($table,$offset,$psize);
				if($rslist)
				{
					foreach($rslist AS $key=>$value)
					{
						$tmp_value = array();
						foreach($value AS $k=>$v)
						{
							$v = $this->sql_m->escape_string($v);
							$tmp_value[$k] = $v;
						}
						$msg .= "INSERT INTO ".$table." VALUES('".implode("','",$tmp_value)."');\n";
					}
					$new_startid = $startid;
					$pageid = $pageid + 1;
				}
				else
				{
					$new_startid = $startid + 1;
					$pageid = 0;
				}
			}
			else
			{
				$new_startid = $startid + 1;
				$pageid = 0;
			}
		}
		$tourl .= "&startid=".$new_startid."&pageid=".$pageid;
		//存储数据的文件Id
		$dataid = $this->trans_lib->int("dataid");
		//计算数据长度
		//$msg = $oldmsg.$msg;
		//$msg = $oldmsg . addslashes($msg);
		$msg = addslashes($oldmsg . $msg);
		if(strlen($msg)>=(1024*1000))
		{
			//如果文件存在，则自动加一
			if(file_exists(ROOT_DATA.$backfilename."_data_".$dataid.".php"))
			{
				$dataid++;
			}
			$this->file_lib->vi($msg,ROOT_DATA.$backfilename."_data_".$dataid.".php");//存储数据
			unset($msg,$oldmsg);
			//再重新创建临时文件
			$new_dataid = $dataid+1;
			//判断是否已经结束了
			if($idlist[$new_startid])
			{
				$this->file_lib->vi("#PHPOK Full 数据备份\n\n",ROOT_DATA.$backfilename."_tmpdata.php");
				error("正在备份数据，当前第 ".($dataid+1)." 个文件！",$tourl."&dataid=".$new_dataid);
			}
			else
			{
				$this->file_lib->rm(ROOT_DATA.$backfilename."_tmpdata.php");
				error("数据表备份操作成功，请稍候，正在进入下一步！",site_url("phpoksql,baklist"));
			}
		}
		else
		{
			if(!$idlist[$new_startid])
			{
				if(file_exists(ROOT_DATA.$backfilename."_data_".$dataid.".php"))
				{
					$dataid++;
				}
				$this->file_lib->vi($msg,ROOT_DATA.$backfilename."_data_".$dataid.".php");//存储数据
				$this->file_lib->rm(ROOT_DATA.$backfilename."_tmpdata.php");//删除临时文件
				error("数据表备份操作成功，请稍候，正在进入下一步！",site_url("phpoksql,baklist"));
			}
			else
			{
				//如果数据没有超过系统限制，则
				$this->file_lib->vi($msg,ROOT_DATA.$backfilename."_tmpdata.php");
				$new_dataid = $dataid;
				error("正在备份数据，当前第 ".($dataid+1)." 个文件！",$tourl."&dataid=".$new_dataid);
			}
		}
	}

	function baklist_f()
	{
		sys_popedom("phpoksql:list","tpl");
		$ifact = sys_popedom("phpoksql:set");
		$this->tpl->assign("set_popedom",$ifact);//执行操作
		$this->load_model("admin");
		$this->admin_m->psize = 999;//设置管理员999个
		$admin_tmplist = $this->admin_m->get_list(0);
		$adminlist = array();
		foreach($admin_tmplist AS $key=>$value)
		{
			$adminlist[$value["id"]] = $value["name"];
		}
		unset($admin_tmplist);
		$filelist = $this->file_lib->ls(ROOT_DATA);
		if(!$filelist)
		{
			error("没有取得相应数据！",site_url("phpoksql"));
		}
		$tmplist = array();
		$i=0;
		foreach($filelist AS $key=>$value)
		{
			$bv = basename($value);
			if(substr($bv,0,4) == "sql_")
			{
				$tmp = explode("_",substr($bv,0,-4));
				$tmplist[$i] = array();
				$tmplist[$i]["filename"] = $value;
				$tmplist[$i]["basename"] = substr($bv,0,-4);
				$tmplist[$i]["tmptime"] = $tmp[1];
				$tmplist[$i]["postdate"] = substr($tmp[1],0,4)."-".substr($tmp[1],4,2)."-".substr($tmp[1],6,2)." ".substr($tmp[1],8,2).":".substr($tmp[1],10,2).":".substr($tmp[1],12,2);
				$tmplist[$i]["admin"] = $adminlist[$tmp[2]];
				$tmplist[$i]["type"] = $tmp[3] ? "data" : "sql";
				if($tmp[3] == "tmpdata")
				{
					$tmplist[$i]["type"] = "tmpdata";
				}
				$i++;
			}
		}
		if(!$tmplist || count($tmplist)<1)
		{
			error("没有检测到备份文件！",site_url("phpoksql"));
		}
		$yclist = $rslist = array();
		foreach($tmplist AS $key=>$value)
		{
			if($value["type"] == "sql")
			{
				$filesize = filesize($value["filename"]);
				foreach($tmplist AS $k=>$v)
				{
					if($v["type"] == "data" && $v["tmptime"] == $value["tmptime"] && $v["admin"] == $value["admin"])
					{
						$filesize += filesize($v["filename"]);
					}
				}
				$value["psize"] = $filesize;
				$rslist[] = $value;
			}
			elseif($value["type"] == "tmpdata")
			{
				$value["psize"] = filesize($value["filename"]);
				$yclist[] = $value;
			}
		}
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("sql_list.html");
	}

	function del_f()
	{
		sys_popedom("phpoksql:set","tpl");
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			error("没有指定备份文件！",site_url("phpoksql,baklist"));
		}
		$filelist = $this->file_lib->ls(ROOT_DATA);
		if(!$filelist)
		{
			error("没有取得相应数据！",site_url("phpoksql,baklist"));
		}
		$idlen = strlen($id);
		foreach($filelist AS $key=>$value)
		{
			$bv = basename($value);
			if(substr($bv,0,$idlen) == $id)
			{
				$this->file_lib->rm($value);
			}
		}
		error("备份文件 ".$id." 删除操作成功！",site_url("phpoksql,baklist"));
	}

	function recover_f()
	{
		sys_popedom("phpoksql:set","tpl");
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			error("没有指定备份文件！",site_url("phpoksql,baklist"));
		}
		if(!file_exists(ROOT_DATA.$id.".php"))
		{
			error("备份文件丢失，请检查！",site_url("phpoksql,baklist"));
		}
		//恢复表结构数据
		$msg = $this->file_lib->cat(ROOT_DATA.$id.".php");
		$this->format_sql($msg);
		error("表结构数据已经修复，正在恢复数据！请稍候！",site_url("phpoksql,recover_data")."id=".rawurlencode($id)."&startid=0");
	}

	function recover_session_f()
	{
		sys_popedom("phpoksql:set","tpl");
		$this->sql_m->recover_session();
		error("SESSION表已还原！",site_url("phpoksql,baklist"));
	}

	function recover_data_f()
	{
		sys_popedom("phpoksql:set","tpl");
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			error("没有指定备份文件！",site_url("phpoksql,baklist"));
		}
		$filelist = $this->file_lib->ls(ROOT_DATA);
		if(!$filelist)
		{
			error("没有取得相应数据！",site_url("phpoksql,baklist"));
		}
		$idlen = strlen($id."_data_");
		$rslist = array();
		foreach($filelist AS $key=>$value)
		{
			$bv = basename($value);
			if(substr($bv,0,$idlen) == $id."_data_")
			{
				$rslist[] = $value;
			}
		}
		if(!$rslist || count($rslist)<1)
		{
			error("数据文件丢失，请检查！",site_url("phpoksql,baklist"));
		}
		$startid = $this->trans_lib->int("startid");
		if(!$rslist[$startid])
		{
			error("数据信息已恢复完成！建议您清空缓存后退出再重新登录！",site_url("phpoksql,baklist"));
		}
		$file = $rslist[$startid];
		//恢复表结构数据
		$msg = $this->file_lib->cat($file);
		$this->format_sql($msg);
		$new_startid = $startid + 1;
		if(!$rslist[$new_startid])
		{
			error("数据信息已恢复完成！建议您清空缓存后退出再重新登录！",site_url("phpoksql,baklist"));
		}
		error("正在恢复数据，请稍候！",site_url("phpoksql,recover_data")."id=".rawurlencode($id)."&startid=".$new_startid);
	}

	function format_sql($sql)
	{
		$sql = str_replace("\r","\n",$sql);
		$ret = array();
		$num = 0;
		foreach(explode(";\n", trim($sql)) as $query)
		{
			$queries = explode("\n", trim($query));
			foreach($queries as $query)
			{
				$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
			}
			$num++;
		}
		unset($sql);

		foreach($ret as $query)
		{
			$query = trim($query);
			if($query)
			{
				if(substr($query, 0, 12) == 'CREATE TABLE')
				{
					$this->sql_m->query_create($query);
				}
				else
				{
					$this->sql_m->query($query);
				}
			}
		}
	}
}
?>