<?php
/**
 * CSV 操作
 * @作者 qinggan <admin@phpok.com>
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2022年10月9日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

#[\AllowDynamicProperties]
class csv_lib
{
	private $dir_root = '';
	public function __construct()
	{
		global $app;
		$this->dir_root = $app->dir_root;
		$this->dir_cache = $app->dir_cache;
	}

	public function read($file='')
	{
		if(!$file){
			return false;
		}
		if(!file_exists($file)){
			return false;
		}
		$handle = fopen($file,'rb');
		if(!$handle){
			return false;
		}
		$data = array();
		while (!feof($handle)) {
			$data[] = fgetcsv($handle);
		}
		fclose($handle);
		if(!$data){
			return false;
		}
		$data = $this->charset($data,'GBK','UTF-8');
		return $data;
	}

	public function write($data,$file='',$is_download=false)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if(!$file || is_bool($file)){
			$file = $this->dir_cache.''.time().'.csv';
			$is_download = true;
		}
		$handle = fopen($file,'wb');
		if(!$handle){
			return false;
		}
		foreach($data as $key=>$value){
			$value = $this->charset($value,'UTF-8','GBK');
			fputcsv($handle,$value);
		}
		fclose($handle);
		if(!$is_download){
			return true;
		}
		$ext = 'csv';
		$filesize = filesize($file);
		$title = basename($file);
		ob_end_clean();
		set_time_limit(0);
		header("Content-type: applicatoin/octet-stream");
		header("Date: ".gmdate("D, d M Y H:i:s",time())." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s",time())." GMT");
		header("Content-Encoding: none");
		header("Content-Disposition: attachment; filename=".rawurlencode($title)."; filename*=utf-8''".rawurlencode($title));
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
		$read_buffer=4096;
		$sum_buffer = 0;
		$handle = fopen($file, "rb");
		fseek($handle, $range);
		ob_start();
		while (!feof($handle) && $sum_buffer<$filesize) {
			echo fread($handle,$read_buffer);
			$sum_buffer+=$read_buffer;
			ob_flush();
			flush();
		}
		ob_end_clean();
		fclose($handle);
	}

	public function charset($msg, $s_code="UTF-8", $e_code="GBK")
	{
		if(!$msg){
			return false;
		}
		if(is_array($msg)){
			foreach($msg as $key=>$value){
				$msg[$key] = $this->charset($value,$s_code,$e_code);
			}
		}else{
			//检测如果目标是UTF-8，且自身也是UTF-8，则跳过
			$tmp = strtoupper($e_code);
			if($tmp == 'UTF-8' || $tmp == 'UTF' || $tmp == 'UTF8'){
				$chk = $this->is_utf8($msg);
				if($chk){
					return $msg;
				}
			}
			if(function_exists("iconv")){
				$msg = iconv($s_code,$e_code.'//IGNORE',$msg);
			}elseif(function_exists("mb_convert_encoding")){
				$msg = mb_convert_encoding($msg, $e_code, $s_code);
			}
		}
		return $msg;
	}

	public function is_utf8($string)
	{
		if(function_exists('mb_detect_encoding')){
			return mb_detect_encoding($string,'UTF-8') === 'UTF-8';
		}
		return preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*$%xs', $string);
	}
}