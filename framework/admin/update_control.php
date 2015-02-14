<?php
/***********************************************************
	Note	: phpok4升级引挈控制器
	Version : 4.x
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年2月17日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class update_control extends phpok_control
{
	private $popedom;
	
	function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("update");
		$this->assign("popedom",$this->popedom);
	}

	//进入升级界面
	function index_f()
	{
		if(!$this->popedom['list'])
		{
			error('您没有查看权限',$this->url('index'),'error');
		}
		//判断您的空间是否支持FTP
		$update = array('online'=>true,'zip'=>true);
		$setfile = $this->dir_root.'data/update.php';
		$uconfig = array();
		if(is_file($setfile))
		{
			require_once($setfile);
		}
		if(!$uconfig['status'])
		{
			$update['online'] = false;
			$update['zip'] = false;
		}
		else
		{
			if(!$uconfig['server'])
			{
				$update['online'] = false;
			}
		}
		$this->assign('update',$update);
		$this->view('update_index');
	}

	//配置升级包
	function set_f()
	{
		$setfile = $this->dir_root.'data/update.php';
		$uconfig = array();
		if(is_file($setfile))
		{
			require_once($setfile);
		}
		$this->assign("rs",$uconfig);
		$this->view('update_set');
	}

	//存储配置
	function save_f()
	{
		$setfile = $this->dir_root.'data/update.php';
		$uconfig = array();
		if(is_file($setfile))
		{
			require_once($setfile);
		}
		$uconfig['status'] = $this->get('status','int');
		$uconfig['server'] = $this->get('server');
		$uconfig['date'] = $this->get('date','int');
		//写入数据
		$this->lib('file')->vi($uconfig,$this->dir_root.'data/update.php','uconfig');
		error('升级环境配置成功！',$this->url('update'),'ok');
	}

	//在线升级
	function main_f()
	{
		if(!$this->popedom['update'])
		{
			error('您没有在线升级权限',$this->url('update'),'error');
		}
		//取得最新版本包
		$info = $this->service(4);
		$rs = $this->lib('json')->decode($info);
		if($rs['status'] != 'ok')
		{
			error('没有找到升级包信息',$this->url('update'),'error');
		}
		if(!$rs['content'] || count($rs['content']) < 1)
		{
			error('没有符合您要求的升级包',$this->url('update'),'error');
		}
		if(is_file($this->dir_root.'data/update.php'))
		{
			include($this->dir_root.'data/update.php');
			$this->assign('uconfig',$uconfig);
		}
		$rslist = array();
		foreach($rs['content'] as $key=>$value)
		{
			$id = $value['phpok-id'];
			$version = substr($value['phpok-id'],0,1).'.'.substr($value['phpok-id'],1,1).'.'.substr($value['phpok-id'],2);
			$time = date("Y-m-d H:i:s",$value['phpok-time']);
			$size = $value['phpok-size'];
			if($size < 1024)
			{
				$size = '1KB';
			}
			else
			{
				if($size>1024 && $size < 1024 * 1024)
				{
					$size = round($size/1024,2)."KB";
				}
				else
				{
					$size = round($size/1048576,2)."MB";
				}
			}
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
	function unzip_f()
	{
		$zipfile = $this->get('zipfile','int');
		if(!$zipfile)
		{
			error('未指定附件文件',$this->url('update','zip'),'error');
		}
		$rs = $this->model('res')->get_one($zipfile);
		if(!$rs)
		{
			error('附件不存在',$this->url('update','zip'),'error');
		}
		$this->lib('phpzip')->unzip($rs['filename'],$this->dir_root.'data/update/');
		//执行升级程序
		$info = $this->update_load();
		if(!$info || (is_array($info) && $info['status'] == 'error'))
		{
			error($info['content'],$this->url('update'),'error');
		}
		error('升级成功！',$this->url('update'),'ok');
	}

	function update_f()
	{
		$list = array();
		$this->lib('file')->deep_ls($this->dir_root.'data/update/',$list);
		echo "<pre>".print_r($list,true)."</pre>";
	
	}

	//升级文件
	function update_load($verinfo='')
	{
		$list = array();
		$this->lib('file')->deep_ls($this->dir_root.'data/update/',$list);
		if(!$list || count($list) < 1)
		{
			return array('status'=>'error','content'=>'没有升级文件内容');
		}
		$strlen = strlen($this->dir_root."data/update/");
		//执行新的程序
		$delfile = false;
		$sqlfile = array();
		$cfile = array();
		foreach($list AS $key=>$value)
		{
			$tmp = substr($value,$strlen);
			//获取最新的版本信息
			if($tmp == 'version.txt')
			{
				$verinfo = file_get_contents($value);
				continue;
			}
			//获取要删除的文件
			if($tmp == 'delete.txt')
			{
				$delfile = $value;
				continue;
			}
			//获取要更新的SQL
			if(substr($tmp,-3) == 'sql')
			{
				$sqlfile[] = $value;
				continue;
			}
			//获取配置文件
			if(substr($tmp,0,17) == 'framework/config/')
			{
				$cfile[] = $value;
				continue;
			}
			//迁移文件
			if(substr($tmp,0,10) == 'framework/')
			{
				$tmp1 = substr($tmp,10);
				if(is_file($value))
				{
					$this->lib('file')->mv($value,$this->dir_phpok.$tmp1);
					continue;
				}
				if(is_dir($value) && !is_dir($this->dir_phpok.$tmp1))
				{
					$this->lib('file')->make($this->dir_phpok.$tmp1,'folder');
					continue;
				}
			}
			//移动其他文件
			if(is_file($value))
			{
				$this->lib('file')->mv($value,$this->dir_root.$tmp);
				continue;
			}
			if(is_dir($value) && !is_dir($this->dir_phpok.$tmp))
			{
				$this->lib('file')->make($this->dir_root.$tmp,'folder');
				continue;
			}
		}
		//现在执行删除
		if($delfile)
		{
			$dlist = file($delfile);
			if(!$dlist)
			{
				$dlist = array();
			}
			foreach($dlist AS $key=>$value)
			{
				if(!$value && !trim($value))
				{
					continue;
				}
				$value = trim($value);
				if($value && is_file($this->dir_root.$value))
				{
					$this->lib('file')->rm($this->dir_root.$value);
					continue;
				}
				//删除目录
				if($value && is_dir($this->dir_root.$value))
				{
					$this->lib('file')->rm($this->dir_root.$value,'folder');
					continue;
				}
			}
		}
		//执行SQL
		foreach($sqlfile AS $key=>$value)
		{
			if(!$value || !is_file($value))
			{
				continue;
			}
			$info = file_get_contents($value);
			if($info)
			{
				$this->sql_run($info);
			}
		}
		//更新配置文件
		foreach($cfile AS $key=>$value)
		{
			$base = basename($value);
			if(is_file($this->dir_phpok.'config/'.$base))
			{
				//更新配置文件信息
				$config = array();
				include_once($value);
				include_once($this->dir_phpok.'config/'.$base);
				$html = '/***********************************************************'."\n";
				$html.= "\t".'文件：{phpok}/config/'.$base."\n";
				$html.= "\t".'备注：配置文件'."\n";
				$html.= "\t".'版本：4.x'."\n";
				$html.= "\t".'网站：www.phpok.com'."\n";
				$html.= "\t".'作者：qinggan <qinggan@188.com>'."\n";
				$html.= "\t".'更新：'.date("Y-m-d H:i",$this->time)."\n";
				$html.= '***********************************************************/'."\n";
				$html.= 'if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}'."\n";
				$this->lib('file')->vi($config,$this->dir_phpok.'config/'.$base,'config','wb',$html);
			}
			else
			{
				$this->lib('file')->mv($value,$this->dir_phpok.'config/'.$base);
			}
		}
		//删除升级文件
		$this->lib('file')->rm($this->dir_root.'data/update/');
		$list = $this->lib('file')->ls($this->dir_root.'data/update/');
		if($list && count($list)>0)
		{
			foreach($list as $key=>$value)
			{
				$this->lib('file')->rm($value,'folder');
			}
		}
		//更新升级文件
		$this->success_version($verinfo);
		return array('status'=>'ok','content'=>'升级成功');
	}

	//更新成功后，修改记录
	function success_version($version='')
	{
		if(!$version)
		{
			return false;
		}
		//写入到最新版本
		$html = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$html.= '<phpok>'."\n";
		$html.= "\t".'<version>'.trim($version).'</version>'."\n";
		$html.= "\t".'<time>'.date("Y-m-d H:i:s",$this->time).'</time>'."\n";
		$html.= '</phpok>';
		file_put_contents($this->dir_root.'data/update.xml',$html);
		if(is_writeable($this->dir_root.'version.php'))
		{
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
		return true;
	}

	

	//文件升级
	function file_f()
	{
		$file = $this->get('file','int');
		if(!$file) $this->json('升级失败，未指定文件');
		$urlext = 'file='.rawurlencode($file);
		$rs = $this->service(5,$urlext);
		$rs = $this->lib('json')->decode($rs);
		if($rs['status'] != 'ok')
		{
			$this->json($rs['content']);
		}
		//如果不存在内容，那么将创建空白文件
		if(!$rs['content'])
		{
			$this->json('升级失败，升级包内容为空');
		}
		//有内容时进行管理
		//覆盖文件
		$info = base64_decode($rs['content']);
		file_put_contents($this->dir_root.'data/tmp.zip',$info);
		//执行解压
		$this->lib('phpzip')->unzip($this->dir_root.'data/tmp.zip',$this->dir_root.'data/update/');
		$this->lib('file')->rm($this->dir_root.'data/tmp.zip'); //删除临时文件
		//执行升级程序
		$verinfo = substr($file,0,1).".".substr($file,1,1).".".substr($file,2);
		$info = $this->update_load($verinfo);
		if(!$info || (is_array($info) && $info['status'] == 'error'))
		{
			if(!$info['content']) $info['content'] = '升级失败';
			$this->json($info['content']);
		}
		$this->json('ok',true);
	}

	function sql_run($sql='')
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

		foreach($ret as $query)
		{
			$query = trim($query);
			if(!$query) continue;
			if(substr($query, 0, 12) == 'CREATE TABLE') $query = $this->create_table($query);
			$this->db->query($query);
		}
		return true;
	}

	function create_table($sql)
	{
		if(!$sql) return false;
		return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql)." ENGINE=MyISAM DEFAULT CHARSET=utf8";
	}

	function ftype($filename)
	{
		$tmp = strtolower(basename($filename));
		$tmplist = explode('.',$tmp);
		$type = count($tmplist)>1 ? $tmplist[count($tmplist)-1] : 'unknow';
		return $type;		
	}
	
	function filelist_f()
	{
		$json = $this->service(4);
		$rs = $this->lib('json')->decode($json);
		if($rs['status'] != 'ok')
		{
			$this->json($rs['content']);
		}
		if($rs['status'] == 'ok' && !is_array($rs['content']))
		{
			$this->json($rs['content']);
		}
		foreach($rs['content'] AS $key=>$value)
		{
			$type = $value[$key.'type'];
			$size = $value[$key.'size'];
			if($size < 1024)
			{
				$size = '1KB';
			}
			else
			{
				if($size>1024 && $size < 1024 * 1024)
				{
					$size = round($size/1024,2)."KB";
				}
				else
				{
					$size = round($size/1048576,2)."MB";
				}
			}
			$value = array('id'=>$key,'time'=>date("Y-m-d H:i:s",$value[$key.'time']));
			$value['version'] = substr($key,0,1).".".substr($key,1,1).".".substr($key,2);
			$value['type'] = $type;
			$value['size'] = $size;
			$list[] = $value;
		}
		$this->json($list,true);
	}

	//更新成功后，修改记录
	function success_f()
	{
		$info = $this->service(3);
		$rs = $this->lib('json')->decode($info);
		if($rs['status'] != 'ok')
		{
			$this->json($rs['content']);
		}
		$this->success_version($rs['content']);
		$this->json('程序更新成功',true);
	}

	//手工检测升级
	function check_f()
	{
		exit($this->service(1));
	}

	//自动检测升级
	function auto_f()
	{
		$check = false;
		$time = 0;
		if(is_file($this->dir_root.'data/update.time'))
		{
			$time = file_get_contents($this->dir_root.'data/update.time');
		}
		if($time < $this->time && ($this->time - $this->config['update']['time'] * 86400) > $time)
		{
			$check = true;
		}
		if($check)
		{
			//更新检测时间
			file_put_contents($this->dir_root.'data/update.time',$this->time);
			exit($this->service(1));
		}
		$this->json('跳过检测');
	}

	function service($type=0,$urlext='')
	{
		if(!is_file($this->dir_root.'data/update.php'))
		{
			return $this->json('未配置升级服务器',false,false);
		}
		$uconfig = array();
		include($this->dir_root.'data/update.php');
		if(!$uconfig['status'])
		{
			return $this->json('在线升级功能未启用',false,false);
		}
		if(!$uconfig['server'])
		{
			return $this->json('未配置升级服务器',false,false);
		}
		if(is_file($this->dir_root.'data/update.xml'))
		{
			$info = $this->lib('xml')->read($this->dir_root.'data/update.xml');
			$info = $info['phpok'];
			$info['time'] = $info['time'] ? strtotime($info['time']) : 0;
		}
		else
		{
			$info['version'] = $this->version;
			$info['time'] = 0;
		}
		$url = $uconfig['server'];
		$url .= 'index.php?version='.rawurlencode($info['version']).'&time='.$this->time.'&type='.$type;
		if($urlext)
		{
			$url.="&".$urlext;
		}
		$info = $this->lib('html')->get_content($url);
		if(!$info)
		{
			return $this->json('检测异常，请登录官网查询补丁更新',false,false);
		}
		$rs = $this->lib('xml')->read($info,false);
		$rs = $rs['info'];
		if(!$rs['status'])
		{
			return $this->json($rs['content'],false,false,false);
		}
		return $this->json($rs['content'],true,false,false);
	}
}
?>