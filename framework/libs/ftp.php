<?php
/***********************************************************
	Filename: {phpok}/libs/ftp.php
	Note	: FTP基本操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-02-18 17:46
***********************************************************/
class ftp_lib
{

	var $hostname	= ''; # FTP服务器
	var $username	= ''; # FTP账号
	var $password	= ''; # FTP密码
	var $port 		= 21; # 连接端口
	var $passive 	= true; # 是否使用被动模式
	var $conn_id 	= false; # 连接ID
	var $root_dir   = "/"; # 服务器根目录
	var $timeout    = 60; # FTP连接超时时间

	# 构造函数，此参数在PHPOK中极少使用或是不使用
	function __construct() {}

	# 设置FTP服务器信息
	function hostname($hostname="")
	{
		if(!$hostname) return false;
		$hostname = preg_replace('|.+?://|','',$hostname);
		$this->hostname = $hostname;
	}

	# 设置登录账号
	function username($username="Anonymous")
	{
		$this->username = $username;
	}

	# 设置登录密码
	function password($password="")
	{
		$this->password = $password;
	}

	# 设置FTP的端口
	function port($port=21)
	{
		$this->port = 21;
	}

	# 设置根目录
	function root_dir($root_dir="/")
	{
		$this->root_dir = $root_dir;
	}

	function connect($config = array())
	{
		$this->init($config);
		if(!$this->hostname)
		{
			return false;
		}
		# 连接FTP信息
		$this->conn_id = @ftp_connect($this->hostname,$this->port,$this->timeout);
		if(!$this->conn_id) return false;
		# FTP登录
		$login_status = $this->login();
		if(!$login_status) return false;
		# 启用被动模式
		if($this->passive) ftp_pasv($this->conn_id, true);
		return true;

		# 改变目录
		$dir_status = $this->change_dir($this->root_dir);
	}

	# 改变目录
	function change_dir($path='')
	{
		if(!$path || !$this->is_conn()) return false;
		$status = @ftp_chdir($this->conn_id,$path);
		if(!$status) return false;
		return true;
	}

	# 创建目录
	function make_dir($path,$chmod=null)
	{
		if(!$path || !$this->is_conn()) return false;
		$status = @ftp_mkdir($this->conn_id,$path);
		if(!$status) return false;
		if(!is_null($chmod))
		{
			$this->chmod($path,(int)$chmod);
		}
		return true;
	}

	# 更改目录权限
	function chmod($path,$chmod)
	{
		if(!$this->is_conn || !$path || !$chmod) return false;
		return @ftp_chmod($this->conn_id, $chmod, $path);
	}

	# 上传
	function upload($local,$remote,$mode="auto",$chmod=null)
	{
		if(!$this->is_conn() || !$local || !file_exists($local) || !$remote) return false;
		if($mode == "auto")
		{
			$ext = $this->get_ext($local);
			$mode = $this->set_type($ext);
		}
		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;
		if(substr($this->root_dir,-1) != "/") $this->root_dir .= "/";
		$remote = $this->root_dir . $remote;

		$status = @ftp_put($this->conn_id, $remote, $local, $mode);
		if(!$status) return false;
		if( ! is_null($chmod))
		{
			$this->chmod($remote,(int)$chmod);
		}
		return true;
	}

	# 下载
	function download($remote,$local,$mode="auto")
	{
		if(!$this->is_conn() || !$local || !file_exists($local) || !$remote) return false;
		if($mode == 'auto')
		{
			$ext = $this->get_ext($remote);
			$mode = $this->set_type($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;
		return @ftp_get($this->conn_id, $local, $remote, $mode);
	}

	# 重命名
	function rename($old,$new)
	{
		if(!$old || !$new || !$this->is_conn()) return false;
		return @ftp_rename($this->conn_id, $oldname, $newname);
	}

	# 移动
	function move($old,$new)
	{
		return $this->rename($old,$new);
	}

	# 删除文件
	function delete($file)
	{
		if(!$file || !$this->is_conn()) return false;
		return @ftp_delete($this->conn_id, $file);
	}

	# 删除文件夹
	function delete_dir($path)
	{
		if(!$path || !$this->is_conn()) return false;
		$path = preg_replace("/(.+?)\/*$/", "\\1/", $path);
		//获取目录文件列表
		$filelist = $this->filelist($path);
		if($filelist && is_array($filelist) && count($filelist) > 0)
		{
			foreach($filelist AS $item)
			{
				if(!$this->delete($item))
				{
					$this->delete_dir($item);
				}
			}
		}
		return @ftp_rmdir($this->conn_id, $path);
	}

	# 取得文件夹列表
	function filelist($path = '.')
	{
		if(!$path || ! $this->is_conn()) return false;
		return ftp_nlist($this->conn_id, $path);
	}

	# 关闭FTP连接
	function close()
	{
		if(!$this->is_conn()) return false;
		return @ftp_close($this->conn_id);
	}

	# FTP参数初始化
	function init($config = array())
	{
		foreach($config as $key => $val)
		{
			if(isset($this->$key))
			{
				$this->$key = $val;
			}
		}

		//特殊字符过滤
		if($this->hostname)
		{
			$this->hostname = preg_replace('|.+?://|','',$this->hostname);
		}
	}

	# FTP登录
	function login()
	{
		return @ftp_login($this->conn_id, $this->username, $this->password);
	}

	# 判断是否连接
	function is_conn()
	{
		if( ! is_resource($this->conn_id))
		{
			return false;
		}
		return true;
	}

	# 取得文件后缀名
	function get_ext($filename)
	{
		if(FALSE === strpos($filename, '.'))
		{
			return 'txt';
		}
		$extarr = explode('.', $filename);
		return strtolower(end($extarr));
	}

	# 从后缀扩展定义FTP传输模式  ascii 或 binary
	function set_type($ext)
	{
		$text_type = array ('txt','text','php','phps','php4','js','css','htm','html','phtml','shtml','log','xml');
		return (in_array($ext, $text_type)) ? 'ascii' : 'binary';
	}
}
?>