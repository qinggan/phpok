<?php
/***********************************************************
	Filename: libs/autoload/file.php
	Note	: 文件操作类，本类在系统自动加载
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-17
***********************************************************/
class file_lib
{
	public $read_count;
	public function __construct()
	{
		$this->read_count = 0;
	}

	public function __destruct()
	{
		unset($this);
	}

	//读取数据
	public function cat($file="",$length=0)
	{
		if($file){
			$this->read_count++;
			$check = strtolower($file);
			if(strpos($check,"http://") === false){
				if(!file_exists($file)){
					return false;
				}
			}
			if(!$length){
				$content = file_get_contents($file);
				if(!$content){
					return false;
				}
			}else{
				$fp = fopen($file,'rb');
				$content = fread($fp,$length);
				fclose($fp);
			}
			$content = str_replace("<?php die('forbidden'); ?>\n","",$content);
			return $content;
		}else{
			return false;
		}
	}

	#[存储数据]
	public function vi($content,$file,$var="",$type="wb",$ext='')
	{
		$this->make($file,"file");
		if(is_array($content) && $var)
		{
			$content = $this->__array($content,$var);
			$content = "<?php\n".$ext."\n".$content."\n ?".">";
		}
		else
		{
			if(strtolower($type) == 'wb' || strtolower($type) == 'w')
			{
				$content = "<?php die('forbidden'); ?>\n".$content;
			}
		}
		$this->_write($content,$file,$type);
		return true;
	}

	#[存储php等源码文件]
	public function vim($content,$file)
	{
		$this->make($file,"file");
		$this->_write($content,$file,"wb");
		return true;
	}

	//存储图片
	public function save_pic($content,$file)
	{
		$this->make($file,"file");
		$handle = $this->_open($file,"wb");
		fwrite($handle,$content);
		unset($content);
		$this->close($handle);
		return true;
	}

	#[删除数据操作]
	#[这一步操作一定要小心，在程序中最好严格一些，不然有可能将整个目录删掉！]
	public function rm($file,$type="file")
	{
		if(!file_exists($file))
		{
			return false;
		}
		$array = $this->_dir_list($file);
		if(is_array($array))
		{
			foreach($array as $key=>$value)
			{
				if(file_exists($value))
				{
					if(is_dir($value))
					{
						$this->rm($value,$type);
					}
					else
					{
						unlink($value);
					}
				}
			}
		}
		else
		{
			if(file_exists($array) && is_file($array))
			{
				unlink($array);
			}
		}
		//如果要删除目录，同时设置
		if($type == "folder")
		{
			rmdir($file);
		}
		return true;
	}

	#[创建文件或目录]
	public function make($file,$type="dir")
	{
		$newfile = $file;
		$msg = "";
		if(defined("ROOT"))
		{
			$root_strlen = strlen(ROOT);
			if(substr($file,0,$root_strlen) == ROOT)
			{
				$newfile = substr($file,$root_strlen);
			}
			$msg = ROOT;//从根目录记算起是否有文件写入
		}
		$array = explode("/",$newfile);
		$count = count($array);
		if($type == "dir")
		{
			for($i=0;$i<$count;$i++)
			{
				$msg .= $array[$i];
				if(!file_exists($msg) && ($array[$i]))
				{
					mkdir($msg,0777);
				}
				$msg .= "/";
			}
		}
		else
		{
			for($i=0;$i<($count-1);$i++)
			{
				$msg .= $array[$i];
				if(!file_exists($msg) && ($array[$i]))
				{
					mkdir($msg,0777);
				}
				$msg .= "/";
			}
			if(!is_file($file))
			{
				@touch($file);//创建文件
			}
		}
		return true;
	}

	//复制操作
	public function cp($old,$new,$recover=true)
	{
		if(is_file($old))
		{
			//如果目标是文件夹
			if(substr($new,-1) == '/')
			{
				$this->make($new,'dir');
				$basename = basename($old);
				if(file_exists($new.$basename) && !$recover)
				{
					return false;
				}
				copy($old,$new.$basename);
				return true;
			}
			//如果目标是文件
			if(file_exists($new) && !$recover)
			{
				return false;
			}
			copy($old,$new);
			return true;
		}
		//复制目录
		$basename = basename($old);
		$this->make($new.$basename,'dir');
		//复制目录下的内容
		$dlist = $this->ls($old);
		if($dlist && count($dlist)>0)
		{
			foreach($dlist as $key=>$value)
			{
				$this->cp($value,$new.$basename.'/',$recover);
			}
		}
		return true;
	}

	#[文件移动操作]
	public function mv($old,$new,$recover=true)
	{
		if(substr($new,-1) == "/")
		{
			$this->make($new,"dir");
		}
		else
		{
			$this->make($new,"file");
		}
		if(is_file($new))
		{
			if($recover)
			{
				unlink($new);
			}
			else
			{
				return false;
			}
		}
		else
		{
			$new = $new.basename($old);
		}
		rename($old,$new);
		return true;
	}

	#[获取文件夹列表]
	public function ls($folder)
	{
		$this->read_count++;
		$list = $this->_dir_list($folder);
		if(is_array($list)) sort($list,SORT_STRING);
		return $list;
	}


	public function deep_ls($folder,&$list)
	{
		$this->read_count++;
		$tmplist = $this->_dir_list($folder);
		foreach($tmplist AS $key=>$value)
		{
			if(is_dir($value))
			{
				$this->deep_ls($value,$list);
			}
			else
			{
				$list[] = $value;
			}
		}
	}

	private function _dir_list($file,$type="folder")
	{
		if(substr($file,-1) == "/") $file = substr($file,0,-1);
		if($type == "file")
		{
			return $file;
		}
		elseif(is_dir($file))
		{
			$handle = opendir($file);
			$array = array();
			while(false !== ($myfile = readdir($handle)))
			{
				if($myfile != "." && $myfile != ".." && $myfile != ".svn") $array[] = $file."/".$myfile;
			}
			closedir($handle);
			return $array;
		}
		else
		{
			return $file;
		}
	}

	private function __array($array,$var,$content="")
	{
		foreach($array AS $key=>$value)
		{
			if(is_array($value))
			{
				$content .= $this->__array($value,"".$var."[\"".$key."\"]");
			}
			else
			{
				$old_str = array('"',"<?php","?>","\r");
				$new_str = array("'","&lt;?php","?&gt;","");
				$value = str_replace($old_str,$new_str,$value);
				$content .= "\$".$var."[\"".$key."\"] = \"".$value."\";\n";
			}
		}
		return $content;
	}

	#[打开文件]
	private function _open($file,$type="wb")
	{
		$handle = fopen($file,$type);
		$this->read_count++;
		return $handle;
	}

	#[写入信息]
	private function _write($content,$file,$type="wb")
	{
		if($content){
			$content = stripslashes($content);
		}
		$handle = $this->_open($file,$type);
		fwrite($handle,$content);
		unset($content);
		$this->close($handle);
		return true;
	}

	public function close($handle)
	{
		return fclose($handle);
	}
}
?>