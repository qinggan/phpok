<?php
/***********************************************************
	Filename: {phpok}/www/download_control.php
	Note	: 附件下载管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年06月08日 09时13分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class download_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$file = $this->get('file');
		$id = $this->get('id','int');
		$back = $this->get('back');
		if(!$back) $back = $_SERVER['HTTP_REFERER'];
		if(!$back) $back = $this->url;
		if(!$id && !$file)
		{
			error('未指定附件ID或附件文件',$back,'error');
		}
		if($file)
		{
			$rs = $this->model('res')->get_one_filename($dfile,false);
		}
		else
		{
			$rs = $this->model('res')->get_one($id);
		}
		if(!$rs)
		{
			error("附件不存在",$back,"error");
		}
		$download = $rs['download'] + 1;
		//登记下载次数
		$this->model('res')->save(array('download'=>$download),$rs['id']);
		$this->download($rs,$back);
		exit;
	}

	private function download($rs,$back='')
	{
		if(!$back){
			$back = $this->url;
		}
		if(!$rs || !$rs["filename"] || !is_file($this->dir_root.$rs["filename"])){
			error("附件不存在",$back,"error");
		}
		$filesize = filesize($this->dir_root.$rs["filename"]);
		$title = $rs["title"] ? $rs['title'] : basename($rs['filename']);
		$title = str_replace(".".$rs["ext"],"",$title);
		ob_end_clean();
		$dname = $title.'.'.$rs['ext'];
		if(isset($_SERVER["HTTP_USER_AGENT"])){
			if(preg_match("/MSIE/",$_SERVER["HTTP_USER_AGENT"])){
				$dname = rawurlencode($title.'.'.$rs['ext']);
			}elseif(preg_match("/Firefox/",$_SERVER["HTTP_USER_AGENT"])){
				$dname = 'utf8'.$title.'.'.$rs['ext'];
			}
		}
		header("Date: ".gmdate("D, d M Y H:i:s", $this->time)." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", $this->time)." GMT");
		header("Content-Encoding: none");
		header("Content-Disposition: attachment; filename=".$dname);
		header("Accept-Ranges: bytes");
		$range = 0;
		$size2 = $filesize -1;
		if (isset ($_SERVER['HTTP_RANGE'])) {
		    list ($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
		    $new_length = $size2 - $range;
		    header("HTTP/1.1 206 Partial Content");
		    header("Content-Length: ".$new_length); //输入总长
		    header("Content-Range: bytes ".$range."-".$size2."/".$filesize);
		} else {
		    header("Content-Range: bytes 0-".$size2."/".$filesize); //Content-Range: bytes 0-4988927/4988928
		    header("Content-Length: ".$filesize);
		}
		$handle = fopen($this->dir_root.$rs['filename'], "rb");
		fseek($handle, $range);  
		while (!feof($handle)) {
			set_time_limit(0);
			print (fread($handle, 1024 * 8));
			flush();
			ob_flush();
		}
		fclose($handle);
		exit();
	}
}
?>