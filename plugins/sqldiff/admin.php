<?php
/*****************************************************************************************
	文件： plugins/sqldiff/admin.php
	备注： 数据库比较工具
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年3月13日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_sqldiff extends phpok_plugin
{
	function __construct()
	{
		parent::plugin();
	}

	//管理
	function manage()
	{
		$plugin = $this->plugin_info();
		$this->assign('plugin',$plugin);
		//读取当前程序支持数据库引挈
		$list = $this->lib('file')->ls($this->dir_phpok.'engine/db');
		if(!$list)
		{
			error('未分配数据库引挈！',$this->url('index'),'error');
		}
		$rslist = array();
		foreach($list AS $key=>$value)
		{
			$value = basename($value);
			$value = str_replace('.php','',$value);
			$rslist[] = $value;
		}
		$this->assign('rslist',$rslist);
		echo $this->plugin_tpl('sqldiff_index.html');
	}

	//比较结果
	function rs()
	{
		$plugin = $this->plugin_info();
		$onlyerror = $this->get('onlyerror','checkbox');
		$onlymain = $this->get("onlymain",'checkbox');
		$dbconfig = array();
		$dbconfig['host'] =$this->get('host1');
		$dbconfig['port'] =$this->get('port1');
		$dbconfig['user'] =$this->get('user1');
		$dbconfig['pass'] =$this->get('pass1');
		$dbconfig['data'] =$this->get('data1');
		$this->assign('data1',$dbconfig['data']);
		$errurl = $this->url('plugin','exec','id='.$plugin['id'].'&exec=manage');
		if(!$dbconfig['host'] || !$dbconfig['port'] || !$dbconfig['user'] || !$dbconfig['data']){
			error('主数据库配置不完整',$errurl,'error');
		}
		//连接主数据库
		$engine = $this->get('engine1');
		$engine = 'mysql';
		$efile = $this->dir_phpok.'engine/db/'.$engine.'.php';
		if(!file_exists($efile)){
			error('主数据库引挈：'.$engine.' 不存在！',$errurl,'error');
		}
		include_once($efile);
		$dbname = "db_".$engine;
		$db1 = new $dbname($dbconfig);
		$db1->connect();
		if(!$db1->conn()){
			error('主数据库配置不正确，请检查',$errurl,'error');
		}

		//连接副数据库
		$dbconfig = array();
		$dbconfig['host'] =$this->get('host2');
		$dbconfig['port'] =$this->get('port2');
		$dbconfig['user'] =$this->get('user2');
		$dbconfig['pass'] =$this->get('pass2');
		$dbconfig['data'] =$this->get('data2');
		$this->assign('data2',$dbconfig['data']);
		if(!$dbconfig['host'] || !$dbconfig['port'] || !$dbconfig['user'] || !$dbconfig['data'])
		{
			error('副数据库配置不完整',$errurl,'error');
		}
		//连接主数据库
		$engine = $this->get('engine2');
		$engine = 'mysql';
		$efile = $this->dir_phpok.'engine/db/'.$engine.'.php';
		if(!is_file($efile))
		{
			error('副数据库引挈：'.$engine.' 不存在！',$errurl,'error');
		}
		include_once($efile);
		$dbname = "db_".$engine;
		$db2 = new $dbname($dbconfig);
		$db2->connect();
		if(!$db2->conn())
		{
			error('副数据库配置不正确，请检查',$errurl,'error');
		}

		//读取表1的数据表信息
		$list = $db1->list_tables();
		if(!$list)
		{
			error('主数据库中没有相应的数据表',$errurl,'error');
		}

		//读取表2的数据
		$list2 = $db2->list_tables();
		if(!$list2)
		{
			error('副数据库中没有相应的数据表',$errurl,'error');
		}
		//合并数组
		$mergelist = array_merge($list,$list2);
		$tlist = array_unique($mergelist);
		if($onlymain)
		{
			foreach($tlist AS $key=>$value)
			{
				$tmp = explode("_",$value);
				$count = count($tmp);
				$last = $tmp[($count-1)];
				if(intval($last)>0)
				{
					unset($tlist[$key]);
				}
			}
		}
		sort($tlist);
		$rslist = array();
		foreach($tlist AS $key=>$value)
		{
			$flist = $flist2 = array();
			if(in_array($value,$list))
			{
				$flist = $db1->list_fields_more($value);
			}
			if(in_array($value,$list2))
			{
				$flist2 = $db2->list_fields_more($value);
			}
			//合并字段组
			if($flist && $flist2)
			{
				$tmp1 = array_keys($flist);
				$tmp2 = array_keys($flist2);
				$tmp = array_unique(array_merge($tmp1,$tmp2));
				sort($tmp);
				foreach($tmp AS $k=>$v)
				{
					if($flist[$v] && $flist2[$v])
					{
						if($onlyerror)
						{
							if($flist[$v] != $flist2[$v])
							{
								$rslist[$value]['error'][$v] = true;
								$rslist[$value]['one'][$v] = $flist[$v];
								$rslist[$value]['two'][$v] = $flist2[$v];
							}
						}
						else
						{
							$rslist[$value]['error'][$v] = $flist[$v] != $flist2[$v] ? true : false;
							$rslist[$value]['one'][$v] = $flist[$v];
							$rslist[$value]['two'][$v] = $flist2[$v];
						}
					}
					else
					{
						$rslist[$value]['error'][$v] = true;
						if($flist[$v])
						{
							$rslist[$value]['one'][$v] = $flist[$v];
							$rslist[$value]['two'][$v] = array('error'=>'没有这个字段');
						}
						if($flist2[$k])
						{
							$rslist[$value]['one'][$v] = array('error'=>'没有这个字段');
							$rslist[$value]['two'][$v] = $flist2[$v];
						}
					}
				}
			}
			else
			{
				$rslist[$value]['error'] = '表不存在';
				if($flist)
				{
					$rslist[$value]['one'] = $flist;
				}
				if($flist2)
				{
					$rslist[$value]['two'] = $flist2;
				}
			}
		}
		$this->assign('rslist',$rslist);
		echo $this->plugin_tpl('sqldiff_rs.html');
	}

	//更新状态后执行信息
	function ap_system_status_after()
	{
		$rs = $this->plugin_info();
		$menu_rs = $this->model('sysmenu')->get_one($rs['param']['sysmenu_id']);
		$this->model('plugin')->update_status($rs['id'],$menu_rs['status']);
	}

	//删除菜单后
	function ap_system_delete_after()
	{
		$rs = $this->plugin_info();
		$id = $this->get('id','int');
		if($id && $rs['param']['sysmenu_id'] == $id)
		{
			$this->model('plugin')->delete($rs['id']);
		}
	}
}
?>