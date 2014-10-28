<?php
/*****************************************************************************************
	文件： {phpok}/admin/tool_package_control.php
	备注： 工具箱之打包JS+CSS，此项为PHPOK自用
	版本： 4.x;
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年2月28日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tool_package_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	//设置要打包的目录
	function index_f()
	{
		$this->view("tool_package_index");
	}

	function filelist_f()
	{
		$folder = $this->get('folder');
		if(!$folder)
		{
			$this->json('未指定目录，不允许获取根目录信息');
		}
		$folder = str_replace(array("..","//"),array("","/"),$folder);
		if(!is_dir($this->dir_root.$folder))
		{
			$this->json("这不是一个目录，请填写准确的目录信息");
		}
		$flist = $this->lib('file')->ls($this->dir_root.$folder);
		if(!$flist)
		{
			$this->json('目录下没有相应的文件，请检查');
		}
		$info = "";
		foreach($flist AS $key=>$value)
		{
			$ext = strtolower(substr($value,-3));
			if($ext == 'css' || $ext == '.js')
			{
				$info .= basename($value)."\n";
			}
		}
		$this->json($info,true,true,false);
	}

	//合并文件
	function mini_f()
	{
		$dir = $this->dir_root;
		$folder = $this->get('folder');
		if($folder)
		{
			$folder = str_replace(array("..","//"),array("","/"),$folder);
			if($folder)
			{
				if(substr($folder,-1) != '/') $folder .= "/";
				$dir .= $folder;
			}
			
		}
		$content = $this->get('content');
		if(!$content)
		{
			error('未指定要合并的文件',$this->url('tool_package'),'error');
		}
		$name = $this->get('name');
		if(!$name)
		{
			error('未指定新的文件名',$this->url('tool_package'),'error');
		}
		$ext = substr($name,-3);
		if($ext != '.js' && $ext != 'css')
		{
			error('文件名不符合要求，必须是.css或是.js结尾',$this->url('tool_package'),'error');
		}
		$list = explode("\n",$content);
		$info = '';
		foreach($list AS $key=>$value)
		{
			if(!$value) continue;
			$value = trim($value);
			if(!$value) continue;
			$tmp = strtolower(substr($value,0,7));
			if($tmp != 'http://' && $tmp != 'https:/')
			{
				if(is_file($dir.$value))
				{
					$content = file_get_contents($dir.$value);
					$info .= $content;
					if($ext == '.js') $info .= ";";
					$info .= "\n";
				}
			}
			else
			{
				$content = $this->lib('html')->get_content($value);
				if($content)
				{
					$info .= $contentl.";\n";
				}
			}
		}
		$header  = "/*****************************************************************************************\n";
		$header .= "	文件： ".$name."\n";
		$header .= "	说明： 合并文件\n";
		$header .= "	版本： PHPOK".VERSION."\n";
		$header .= "	作者： phpok.com<admin@phpok.com>\n";
		$header .= "	更新： ".date("Y-m-d H:i",$this->time)."\n";
		$header .= "*****************************************************************************************/\n";
		$info = $header.$info;
		$filesize = strlen($info);
		ob_end_clean();
		header("Date: ".gmdate("D, d M Y H:i:s", $this->time)." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", $this->time)." GMT");
		header("Content-Encoding: none");
		header("Content-Disposition: attachment; filename=".rawurlencode($name));
		header("Content-Length: ".$filesize);
		header("Accept-Ranges: bytes");
		echo $info;
		flush();
		ob_flush();
	}
}

?>