<?php
/*****************************************************************************************
	文件： {phpok}/form/password_form.php
	备注： 密码字段
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月12日 22时09分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class password_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$this->view($this->dir_phpok.'form/html/password_admin.html','abs-file');
	}

	public function phpok_format($rs,$appid="admin")
	{
		if($rs["content"] && $rs["password_type"] == "show" && strlen($rs["content"]) > 2){
			$length = strlen($rs["content"]);
			$new_str = "";
			for($i=0;$i<($length-2);$i++){
				$new_str .= "*";
			}
			$old = substr($rs["content"],1,($length-2));
			$rs["content"] = str_replace($old,$new_str,$rs["content"]);
		}
		if($rs["content"] && $rs["password_type"] == "md5" && strlen($rs["content"]) != 32){
			$rs["content"] = "";
		}
		$this->assign("_rs",$rs);
		return $this->fetch($this->dir_phpok.'form/html/password_admin_tpl.html','abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		$ext = array();
		if($rs['ext']){
			if(is_string($rs['ext'])){
				$ext = unserialize($rs['ext']);
			}else{
				$ext = $rs['ext'];
			}
		}
		$info = $this->get($rs['identifier'],$rs['format']);
		if($ext['password_type'] == 'default'){
			return $info;
		}
		if($ext['password_type'] == 'md5'){
			if($info && strlen($info) != 32){
				return md5($info);
			}
			if(!$info && $rs['content'] && strlen($rs['content']) == 32){
				return $rs['content'];
			}
			return $info;
		}
		if($info){
			if(strlen($info) == strlen($rs['content']) && substr($info,0,1) == substr($rs['content'],0,1) && substr($info,-1) == substr($rs['content'],-1)){
				return $rs['content'];
			}else{
				return $info;
			}
		}
		return $rs['content'];
	}

	public function phpok_show($rs,$appid="admin")
	{
		return $rs['content'];
	}
}
?>