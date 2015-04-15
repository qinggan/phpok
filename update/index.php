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
	//public $lastest;
	public $id = 0;
	public $dir_root;
	public function __construct()
	{
		$this->id = $this->get('type','int');
		$this->version = $this->get('version');
		if(!$this->version){
			$this->encode('版本参数不能为空');
		}
		$this->version = trim($this->version);
	}

	//执行动作
	public function action()
	{
		$array = array(0=>'xml',1=>'check',2=>'file',3=>'version',4=>'ziplist',5=>'zipdown');
		$name = $array[$this->id];
		if($name){
			$this->$name();
		}else{
			$this->encode('动作不存在，请检查');
		}
	}

	//返回最新版本
	public function version($is_xml=true)
	{
		$vlist = explode(".",trim($this->version));
		$old = $vlist[0].$vlist[1].str_pad($vlist[2],3,'0',STR_PAD_LEFT);
		$rslist = array();
		$this->ls($rslist,'',$this->dir_root.'zip/');
		if(count($rslist)<1){
			$this->encode($this->version,true);
		}
		$list = false;
		$max = $old;
		foreach($rslist as $key=>$value){
			$value = trim(basename($value));
			if(!$value){
				continue;
			}
			$version = str_replace('.zip','',$value);
			if($version<=$max){
				continue;
			}
			if($version>$max){
				$max = $version;
			}
		}
		$version = substr($max,0,1).'.'.substr($max,1,1).'.'.str_pad(substr($max,2),3,'0',STR_PAD_LEFT);
		if(!$is_xml){
			return $version;
		}
		$this->encode($version,true);
	}

	public function ziplist()
	{
		$vlist = explode(".",$this->version);
		$old = $vlist[0].$vlist[1].str_pad($vlist[2],3,'0',STR_PAD_LEFT);
		$rslist = array();
		$this->ls($rslist,'',$this->dir_root.'zip/');
		if(count($rslist)<1){
			$this->encode('没有找到升级包');
		}
		$list = false;
		foreach($rslist as $key=>$value){
			$value = trim(basename($value));
			if(!$value){
				continue;
			}
			$version = str_replace('.zip','',$value);
			if($version<=$old){
				continue;
			}
			if(!$list){
				$list = array();
			}
			$list[$version] = array('id'=>$version,'time'=>filemtime($this->dir_root.'zip/'.$value),'size'=>filesize($this->dir_root.'zip/'.$value),'type'=>'zip');
		}
		if(!$list){
			$this->encode('没有找到升级包');
		}
		ksort($list);
		$this->encode($list,true);
	}

	public function xml()
	{
		$this->encode('服务器升级，仅支持ZIP升级');
	}

	public function file()
	{
		$this->encode('服务器升级，仅支持ZIP升级');
	}

	public function zipdown()
	{
		$file = $this->get('file');
		$file = intval($file);
		if(!$file){
			$this->encode('未指定要下载的文件');
		}
		if(!is_file($this->dir_root.'zip/'.$file.'.zip')){
			$this->encode('文件不存在');
		}
		$content = file_get_contents($this->dir_root.'zip/'.$file.'.zip');
		if($content){
			$content = base64_encode($content);
		}
		$this->encode($content,true);
	}

	//版本检测
	public function check($isxml=true)
	{
		$vlist = explode('.',$this->version);
		$lastest = $this->version(false);
		$slist = explode('.',$lastest);
		if($vlist[0] <= $slist[0] && $vlist[1] <= $slist[1] && $vlist[2] <= $slist[2]){
			if(!$isxml){
				return false;
			}
			$this->encode('您当前是最新版本，不需要再升级');
		}
		if(!$isxml){
			return true;
		}
		$this->encode('检测到升级包',true);
	}

	public function get($id,$type='safe')
	{
		$msg = isset($_GET[$id]) ? $_GET[$id] : '';
		if($msg == '') return false;
		if($type == 'safe'){
			$msg = str_replace(array("\\","'",'"',"<",">"," "),array("&#92;","&#39;","&quot;","&lt;","&gt;","&nbsp;"),$msg);
			return $msg;
		}elseif($type == 'int' || $type == 'intval'){
			return intval($msg);
		}elseif($type == 'float' || $type == 'floatval'){
			return floatval($msg);
		}elseif($type == 'version'){
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