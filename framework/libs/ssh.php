<?php
/*****************************************************************************************
	文件： {phpok}/libs/ssh.php
	备注： SSH2连接器
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年01月26日 16时05分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ssh_lib
{
	private $ssh;
	private $conn;
	private $error_status = false;
	private $error_info = false;
	private $sftp;
	private $shell;
	public function __construct()
	{
		//
	}

	//初始化设置状态
	private function _reset_status()
	{
		$this->error_info = false;
		$this->error_status = false;
		return true;
	}

	public function status($info=false)
	{
		if($info)
		{
			return $this->error_info;
		}
		else
		{
			return $this->error_status;
		}
	}

	public function connect($host,$port='22')
	{
		$this->_reset_status();
		$this->conn = ssh2_connect($host,$port,array('comp'=>'zlib'));
		if(!$this->conn)
		{
			return $this->error("连接到服务器失败");
		}
		return $this->_reset_status();
	}

	public function login($user,$pass)
	{
		$this->_reset_status();
		if(!$this->conn)
		{
			return $this->error("未连接SSH服务器");
		}
		$this->ssh = ssh2_auth_password($this->conn,$user,$pass);
		if(!$this->ssh)
		{
			return $this->error("管理员验证失败");
		}
		return $this->_reset_status();
	}

	//
	public function exec($string,$async=false)
	{
		$this->_reset_status();
		$stream = ssh2_exec($this->conn,$string);
		if($async)
		{
			stream_set_blocking($stream,0);
			return $this->_reset_status();
		}
		else
		{
			stream_set_blocking($stream,1);
			$this->_reset_status();
			return fread($stream,10240);
		}
	}

	public function shell($cmd,$async=false)
	{
		$this->_reset_status();
		if(!$this->shell) $this->shell = ssh2_shell($this->conn);
		if($async)
		{
			stream_set_blocking($this->shell,0);
			fwrite($this->shell,$cmd.PHP_EOL);
			sleep(1);
			return $this->_reset_status();
		}
		else
		{
			stream_set_blocking($this->shell,1);
			fwrite($this->shell,$cmd.PHP_EOL);
			sleep(1);
			$this->_reset_status();
			return fread($this->shell,10240);
		}
	}

	//上传文件到指定服务器
	public function upload($localfile,$remotefile)
	{
		return ssh2_scp_send($this->conn,$localfile,$remotefile);
	}

	public function error($info)
	{
		$this->error_status = true;
		$this->error_info = $info;
		return false;
	}

	public function scp_not_pass($client,$server)
	{
		//读取服务端的ssh免登录配置
		$this->connect($server['ip'],$server['ssh_port']);
		$this->login($server['ssh_user'],$server['ssh_pass']);
		$me = $this->exec("cat /root/.ssh/id_rsa.pub");
		//读取客户端的ssh的免登录验证
		$this->connect($client['ip'],$client['ssh_port']);
		$this->login($client['ssh_user'],$client['ssh_pass']);
		$info = $this->exec("cat /root/.ssh/authorized_keys");
		$write = false;
		if(!$info)
		{
			$write = $me;
		}
		else
		{
			if(strpos($info,$me) === false)
			{
				$write = $info."\n".$me;
			}
		}
		if($write)
		{
			$sftp = ssh2_sftp($this->conn);
			$stream = fopen("ssh2.sftp://".$sftp."/root/.ssh/authorized_keys","wb");
			fwrite($stream,$write);
			fclose($stream);
			unset($sftp);
		}
		return true;
	}

	//判断文件内容
	public function statinfo($file)
	{
		$sftp = ssh2_sftp($this->conn);
		return ssh2_sftp_stat($sftp,$file);
	}

	public function string_to_array($info)
	{
		if(!$info || !trim($info))
		{
			return false;
		}
		$info = trim($info);
		$info = str_replace("\t"," ",$info);
		$info = preg_replace("/(\x20{2,})/"," ",$info);# 去除多余空格，只保留一个空格
		$list = explode(" ",$info);
		return $list;
	}
}
?>