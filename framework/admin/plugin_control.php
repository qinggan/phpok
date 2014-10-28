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
		if(!$this->popedom["list"]) error($this->lang['global'][9001],$this->url('index'),'error');
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
		if(!$this->popedom["config"]) error($this->lang['global'][9004],$this->url('url'),'error');
		$id = $this->get("id");
		if(!$id)
		{
			error($this->lang[$this->app_id][1002],$this->url("plugin"));
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
		if(!$this->popedom["config"]) error($this->lang['global'][9004],$this->url('plugin'),'error');
		$id = $this->get("id");
		if(!$id)
		{
			error($this->lang[$this->app_id][1002],$this->url("plugin"));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs)
		{
			error($this->lang[$this->app_id][1007],$this->url('plugin'),'error');
		}
		$title = $this->get('title');
		if(!$title)
		{
			error($this->lang[$this->app_id][1005],$this->url('plugin'),'error');
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
		$tips = $this->lang[$this->app_id][102];
		$tips = $this->lang_format($tips,array('title'=>$rs['title']));
		error($tips,$this->url("plugin"),'ok');
	}

	//安装插件
	function install_f()
	{
		if(!$this->popedom["install"]) error($this->lang[$this->app_id][1004],$this->url('plugin'),'error');
		$id = $this->get("id");
		if(!$id)
		{
			error($this->lang[$this->app_id][1002],$this->url("plugin"));
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
		if(!$this->popedom["install"]) error($this->lang[$this->app_id][1004],$this->url('plugin'),'error');
		$id = $this->get("id");
		if(!$id)
		{
			error($this->lang[$this->app_id][1002],$this->url("plugin"));
		}
		$title = $this->get('title');
		if(!$title)
		{
			//插件名称不能为空
			error($this->lang[$this->app_id][1005],$this->url('plugin'),'error');
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
			error($this->lang[$this->app_id][1006],$this->url('plugin'),'error');
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
		error("插件：<span class='red'>".$title."</span> 安装成功！",$this->url("plugin"));
	}

	//卸载插件
	function uninstall_f()
	{
		if(!$this->popedom["uninstall"]) json_exit("你没有卸载插件权限");
		$id = $this->get("id");
		if(!$id) $this->json(1002);
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs) $this->json(1001);
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
		$this->json(101,true);
	}

	//状态执行
	function status_f()
	{
		if(!$this->popedom["status"]) $this->json(1003);
		$id = $this->get("id");
		if(!$id) $this->json(1002);
		$rs = $this->model('plugin')->get_one($id);
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
		if(!$id) $this->json(1002);
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs) json_exit(1001);
		if($rs['param']) $rs['param'] = unserialize($rs['param']);
		if(!is_file($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php')) $this->json(1008);
		include_once($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php');
		$name = $this->app_id.'_'.$id;
		$cls = new $name();
		$methods = get_class_methods($cls);
		$exec = $this->get("exec");
		if(!$exec) $exec = 'index';
		if(!$methods || !in_array($exec,$methods)) $this->json(1009);
		$this->assign('plugin_rs',$rs);
		$cls->$exec($rs);
	}

	function ajax_f()
	{
		$this->exec_f();
	}
}
?>