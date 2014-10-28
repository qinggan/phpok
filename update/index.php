<?php
/***********************************************************
	Note	: 升级包管理，注意，这里仅支持的参数是很少的
	Version : 4.x
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年2月17日
***********************************************************/
error_reporting(E_ALL ^ E_NOTICE);
define("ROOT",str_replace("\\","/",dirname(__FILE__))."/");

//PHPOK升级类
class phpok_update
{
	public $version;
	public $lastest;
	public $id = 0;
	public $dir_root;
	//初始化信息
	function __construct()
	{
		$this->id = $this->get('type','int');
		$this->version = $this->get('version');
		if(!$this->version)
		{
			$this->encode('版本参数不能为空');
		}
		$this->dir_root = defined('ROOT') ? ROOT : str_replace("\\","/",dirname(__FILE__))."/";
		if(!is_file($this->dir_root.'data/version.txt'))
		{
			$this->encode('升级服务器配置不完整，未配置服务器最新版本');
		}
		$this->lastest = file_get_contents($this->dir_root.'data/version.txt');
	}

	//执行动作
	function action()
	{
		$array = array(0=>'xml',1=>'check',2=>'file',3=>'version',4=>'ziplist',5=>'zipdown');
		$name = $array[$this->id];
		if($name)
		{
			$this->$name();
		}
		else
		{
			$this->encode('动作不存在，请检查');
		}
	}

	//返回最新版本
	function version()
	{
		$this->encode($this->lastest,true);
	}

	//
	function ziplist()
	{
		$vlist = explode(".",$this->version);
		$last = explode('.',$this->lastest);
		$rslist = array();
		$old = $vlist[0].$vlist[1].str_pad($vlist[2],2,'0',STR_PAD_LEFT);
		$new = $last[0].$last[1].str_pad($last[2],2,'0',STR_PAD_LEFT);
		if($old>=$new)
		{
			$this->encode('您当前是最新版本，不需要再升级');
		}
		$rslist = array();
		for($i=$old;$i<=$new;$i++)
		{
			$file = 'zip/'.$i.'.zip';
			if(is_file($this->dir_root.$file))
			{
				$rslist[$i] = array('id'=>$i,'time'=>filemtime($file),'size'=>filesize($file),'type'=>'zip');
			}
		}
		if(!$rslist || count($rslist)<1)
		{
			$this->encode('没有找到升级包');
		}
		$this->encode($rslist,true);
	}

	public function xml()
	{
		$action = $this->check(false);
		if(!$action) $this->encode('版本检测不通过，不能取得相应的XML文件');
		
		//读取升级包文件
		$list = array();
		$this->ls($list,'',$this->dir_root.'data/'.$this->lastest);
		$delete = '';
		foreach($list AS $key=>$value)
		{
			if($value == '/delete.txt')
			{
				$delete = file($this->dir_root.'data/'.$this->lastest.'/delete.txt');
				unset($list[$key]);
			}
		}
		$rs = $list ? array('change'=>$list) : array();
		if($delete)
		{
			$rs['delete'] = $delete;
		}
		$this->encode($rs,true);
	}

	public function file()
	{
		//将升级包文件单独下载
		$file = $this->get('file');
		if(!$file) $this->encode('未指定要下载的文件');
		$file = str_replace('..','',$file);
		if(!$file) $this->encode('未指定要下载的文件');
		if(substr($file,0,1) == '/') $file = substr($file,1);
		if(is_dir($this->dir_root.'data/'.$this->lastest.'/'.$file))
		{
			$this->encode('dir',true);
		}
		else
		{
			if(!is_file($this->dir_root.'data/'.$this->lastest.'/'.$file))
			{
				$this->encode('文件不存在');
			}
			$content = file_get_contents($this->dir_root.'data/'.$this->lastest.'/'.$file);
			if($content) $content = base64_encode($content);
			$this->encode($content,true);
		}
	}

	public function zipdown()
	{
		$file = $this->get('file');
		$file = intval($file);
		if(!$file) $this->encode('未指定要下载的文件');
		if(!is_file($this->dir_root.'zip/'.$file.'.zip'))
		{
			$this->encode('文件不存在');
		}
		$content = file_get_contents($this->dir_root.'zip/'.$file.'.zip');
		if($content) $content = base64_encode($content);
		$this->encode($content,true);
	}

	//版本检测
	public function check($isxml=true)
	{
		$vlist = explode('.',$this->version);
		$slist = explode('.',$this->lastest);
		if($vlist[0] == $slist[0] && $vlist[1] == $slist[1] && $vlist[2] == $slist[2])
		{
			if(!$isxml) return false;
			$this->encode('您当前是最新版本，不需要再升级');
		}
		//返回要升级的内容
		if(!is_dir(ROOT.'data/'.$this->lastest))
		{
			if(!$isxml) return false;
			$this->encode('升级包还未配置好，请耐心等候');
		}
		if(!$isxml) return true;
		$this->encode('有升级包，请点这里升级',true);
	}

	public function get($id,$type='safe')
	{
		$msg = isset($_GET[$id]) ? $_GET[$id] : '';
		if($msg == '') return false;
		if($type == 'safe')
		{
			$msg = str_replace(array("\\","'",'"',"<",">"," "),array("&#92;","&#39;","&quot;","&lt;","&gt;","&nbsp;"),$msg);
			return $msg;
		}
		elseif($type == 'int' || $type == 'intval')
		{
			return intval($msg);
		}
		elseif($type == 'float' || $type == 'floatval')
		{
			return floatval($msg);
		}
		elseif($type == 'version')
		{
			$msg = preg_match("/^[0-9][0-9\.]*$/u",$msg) ? $msg : false;
			return $msg;
		}
		return false;
	}

	//返回结果集
	public function encode($info,$status=false)
	{
		header("Content-type: text/xml");
		$html = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$html.= '<info>'."\n";
		$html.= "\t".'<status>'.($status ? 1 : 0).'</status>'."\n";
		if(is_array($info))
		{
			$html.= "\t".'<content>'."\n";
			foreach($info AS $key=>$value)
			{
				$html .= "\t\t".'<phpok-'.$key.'>'."\n";
				if(is_array($value))
				{
					foreach($value AS $k=>$v)
					{
						$html .= "\t\t\t<phpok-".$k.">".$v."</phpok-".$k.">\n";
					}
				}
				else
				{
					if(is_file(ROOT.$value))
					{
						$ftime = filectime(ROOT.$value);
						$html .= "\t\t\t<phpok-".$ftime.">".$value."</phpok-".$ftime.">\n";
					}
				}
				$html .= "\t\t".'</phpok-'.$key.'>'."\n";
			}
			$html.= "\t".'</content>'."\n";
		}
		else
		{
			$html.= "\t<content>".$info."</content>\n";
		}
		$html.= '</info>'."\n";
		exit($html);
	}

	public function ls(&$list,$dir='',$root_dir='')
	{
		$tmplist = $this->_dir_list($dir,$root_dir);
		if($tmplist)
		{
			foreach($tmplist AS $key=>$value)
			{
				if(is_dir($root_dir.$value))
				{
					$list[] = $value;
					$this->ls($list,$value,$root_dir);
				}
				else
				{
					$list[] = $value;
				}
			}
		}
	}

	private function _dir_list($dir='',$root_dir='')
	{
		$handle = opendir($root_dir.$dir);
		$array = array();
		while(false !== ($myfile = readdir($handle)))
		{
			if($myfile != "." && $myfile != "..") $array[] = $dir.'/'.$myfile;
		}
		closedir($handle);
		return $array;
	}
}

//实例化升级引挈
$update = new phpok_update();
$update->action();