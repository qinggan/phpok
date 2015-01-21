<?php
/***********************************************************
	Filename: {phpok}/admin/plugin_control.php
	Note	: 插件中心
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-08 10:04
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class plugin_control extends phpok_control
{
	var $popedom;
	function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("plugin");
		$this->assign("popedom",$this->popedom);
	}

	//取得插件列表
	function index_f()
	{
		if(!$this->popedom["list"])
		{
			error(P_Lang('无权限，请联系超级管理员开放权限'),'','error');
		}
		$rslist = $this->model('plugin')->get_all();
		$this->assign("rslist",$rslist);
		$dlist = $this->model('plugin')->dir_list();
		if($dlist)
		{
			$not_install = "";
			foreach($dlist AS $key=>$value)
			{
				if(!$rslist[$value] || !$rslist) $not_install[$value] = $this->model('plugin')->get_xml($value);
			}
			$this->assign('not_install',$not_install);
		}
		$this->view("plugin_index");
	}

	//配置件插件信息
	function config_f()
	{
		if(!$this->popedom["config"])
		{
			error(P_Lang('无权限，请联系超级管理员开放权限'),'','error');
		}
		$id = $this->get("id");
		if(!$id)
		{
			error(P_Lang('未指定ID'),$this->url('plugin'),'error');
		}
		$this->assign("id",$id);
		$rs = $this->model('plugin')->get_one($id);
		if($rs['param'])
		{
			$rs['param'] = unserialize($rs['param']);
		}
		$this->assign("rs",$rs);
		if(is_file($this->dir_root.'plugins/'.$id.'/setting.php'))
		{
			include_once($this->dir_root.'plugins/'.$id.'/setting.php');
			$name = 'setting_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('index',$methods))
			{
				$plugin_html = $cls->index();
				$this->assign('plugin_html',$plugin_html);
			}
		}
		$this->view("plugin_config");
	}

	//存储配置的插件信息
	function save_f()
	{
		if(!$this->popedom["config"])
		{
			error(P_Lang('无权限，请联系超级管理员开放权限'),'','error');
		}
		$id = $this->get("id");
		if(!$id)
		{
			error(P_Lang('未指定ID'),$this->url('plugin'),'error');
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs)
		{
			error(P_Lang('数据不存在，请检查'),$this->url('plugin'),'error');
		}
		$title = $this->get('title');
		if(!$title)
		{
			error(P_Lang('插件名称不能为空'),$this->url('plugin','config','id='.$id),'error');
		}
		$note = $this->get('note');
		$taxis = $this->get("taxis",'int');
		$author = $this->get('author');
		$version = $this->get('version');
		$array = array('title'=>$title,'note'=>$note,'taxis'=>$taxis,'author'=>$author,'version'=>$version);
		$this->model('plugin')->update_plugin($array,$id);
		if(is_file($this->dir_root.'plugins/'.$id.'/setting.php'))
		{
			include_once($this->dir_root.'plugins/'.$id.'/setting.php');
			$name = 'setting_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('save',$methods))
			{
				$cls->save();
			}
		}
		error(P_Lang('插件{title}配置成功',array('title'=>' <span class="red">'.$rs['title'].'</span> ')),$this->url("plugin"),'ok');
	}

	//安装插件
	function install_f()
	{
		if(!$this->popedom["install"])
		{
			error(P_Lang('无权限，请联系超级管理员开放权限'),'','error');
		}
		$id = $this->get("id");
		if(!$id)
		{
			error(P_Lang('未指定ID'),$this->url('plugin'),'error');
		}
		$this->assign("id",$id);
		$rs = $this->model('plugin')->get_xml($id);
		$this->assign("rs",$rs);
		//加载安装手续
		//加载include 属性
		if(is_file($rs['path'].'install.php'))
		{
			include_once($rs['path'].'install.php');
			$name = 'install_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array("index",$methods))
			{
				$info = $cls->index();
				$this->assign("plugin_html",$info);
			}
		}
		$this->view("plugin_install");
	}

	//存储安装插件中的信息
	function install_save_f()
	{
		if(!$this->popedom["install"])
		{
			error(P_Lang('无权限，请联系超级管理员开放权限'),'','error');
		}
		$id = $this->get("id");
		if(!$id)
		{
			error(P_Lang('未指定ID'),$this->url('plugin'),'error');
		}
		$title = $this->get('title');
		if(!$title)
		{
			error(P_Lang('插件名称不能为空'),$this->url('plugin','config','id='.$id),'error');
		}
		$note = $this->get("note");
		$taxis = $this->get('taxis','int');
		$author = $this->get('author');
		$version = $this->get('version');
		$array = array('id'=>$id,'title'=>$title,'note'=>$note,'status'=>0,'author'=>$author,'taxis'=>$taxis,'version'=>$version);
		//存储安装数据
		$id = $this->model('plugin')->install_save($array);
		if(!$id)
		{
			error(P_Lang('插件安装失败，请检查'),$this->url('plugin','install','id='.$id),'error');
		}
		//判断是否有
		$xmlrs = $this->model('plugin')->get_xml($id);
		if(is_file($xmlrs['path'].'install.php'))
		{
			include_once($xmlrs['path'].'install.php');
			$name = 'install_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('save',$methods))
			{
				$cls->save();
			}
		}
		error(P_Lang('插件{title}安装成功',array('title'=>' <span class="red">'.$title.'</span> ')),$this->url("plugin"),'ok');
	}

	//卸载插件
	function uninstall_f()
	{
		if(!$this->popedom["install"])
		{
			$this->json(P_Lang('无权限，请联系超级管理员开放权限'));
		}
		$id = $this->get("id");
		if(!$id)
		{
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs)
		{
			$this->json(P_Lang('数据不存在，请检查'));
		}
		if(is_file($this->dir_root.'plugins/'.$id.'/uninstall.php'))
		{
			include_once($this->dir_root.'plugins/'.$id.'/uninstall.php');
			$name = 'uninstall_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if(in_array("index",$methods))
			{
				$cls->index();
			}
		}
		$this->model('plugin')->delete($id);
		$this->json(P_Lang('插件卸载成功'),true);
	}

	//状态执行
	function status_f()
	{
		if(!$this->popedom["install"])
		{
			$this->json(P_Lang('无权限，请联系超级管理员开放权限'));
		}
		$id = $this->get("id");
		if(!$id)
		{
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs)
		{
			$this->json(P_Lang('数据不存在，请检查'));
		}
		$status = $rs["status"] ? 0 : 1;
		$this->model('plugin')->update_status($id,$status);
		//执行插件运行
		if(is_file($this->dir_root.'plugins/'.$id.'/setting.php'))
		{
			include_once($this->dir_root.'plugins/'.$id.'/setting.php');
			$name = 'setting_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('status',$methods))
			{
				$cls->status();
			}
		}
		$this->json($status,true,true,false);
	}

	//执行JS
	function exec_f()
	{
		$id = $this->get("id");
		if(!$id)
		{
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs)
		{
			$this->json(P_Lang('数据不存在，请检查'));
		}
		if($rs['param']) $rs['param'] = unserialize($rs['param']);
		if(!is_file($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php'))
		{
			$this->json(P_Lang('插件应用文件{appid}不存在',array('appid'=>' <span class="red">'.$this->app_id.'.php</span> ')));
		}
		include_once($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php');
		$name = $this->app_id.'_'.$id;
		$cls = new $name();
		$methods = get_class_methods($cls);
		$exec = $this->get("exec");
		if(!$exec) $exec = 'index';
		if(!$methods || !in_array($exec,$methods))
		{
			$this->json(P_Lang('方法{method}不存在',array('appid'=>' <span class="red">'.$exec.'.php</span> ')));
		}
		$this->assign('plugin_rs',$rs);
		$cls->$exec($rs);
	}

	function ajax_f()
	{
		$this->exec_f();
	}
}
?>