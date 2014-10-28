<?php
/*****************************************************************************************
	文件： {phpok}/libs/phpzip.php
	备注： PHPZIP类
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年3月15日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class phpzip_lib
{
	private $total_files = 0;
	private $total_folders = 0;

	public $file_count = 0 ;
	public $datastr_len   = 0;
	public $dirstr_len = 0;
	public $gzfilename;
	public $fp;
	public $dirstr='';

	function __construct()
	{
		$this->total_files = 0;
		$this->total_folders = 0;
	}

	function unzip($file,$to='',$index= array(-1))
	{
		return $this->Extract($file,$to,$index);
	}

	function Extract ( $zn, $to, $index = array(-1) )
	{
		$ok = 0;
		$zip = @fopen($zn,'rb');
		if(!$zip) return(-1);
		$cdir = $this->ReadCentralDir($zip,$zn);
		$pos_entry = $cdir['offset'];

		if(!is_array($index))
		{
			$index = array($index);
		}
		for($i=0; $index[$i];$i++)
		{
			if(intval($index[$i])!=$index[$i]||$index[$i]>$cdir['entries'])
			{
				return(-1);
			}
		}
		for ($i=0; $i<$cdir['entries']; $i++)
		{
			@fseek($zip, $pos_entry);
			$header = $this->ReadCentralFileHeaders($zip);
			$header['index'] = $i; $pos_entry = ftell($zip);
			@rewind($zip);
			fseek($zip, $header['offset']);
			if(in_array("-1",$index)||in_array($i,$index))
			{
				$stat[$header['filename']]=$this->ExtractFile($header, $to, $zip);
			}
		}
		fclose($zip);
		return $stat;
	}

	function ReadFileHeader($zip)
	{
		$binary_data = fread($zip, 30);
		$data = unpack('vchk/vid/vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len',$binary_data);

		$header['filename'] = fread($zip, $data['filename_len']);
		if ($data['extra_len'] != 0)
		{
			$header['extra'] = fread($zip, $data['extra_len']);
		}
		else
		{
			$header['extra'] = '';
		}
		$header['compression'] = $data['compression'];
		$header['size'] = $data['size'];
		$header['compressed_size'] = $data['compressed_size'];
		$header['crc'] = $data['crc'];
		$header['flag'] = $data['flag'];
		$header['mdate'] = $data['mdate'];
		$header['mtime'] = $data['mtime'];

		if ($header['mdate'] && $header['mtime'])
		{
			$hour=($header['mtime']&0xF800)>>11;$minute=($header['mtime']&0x07E0)>>5;
			$seconde=($header['mtime']&0x001F)*2;$year=(($header['mdate']&0xFE00)>>9)+1980;
			$month=($header['mdate']&0x01E0)>>5;$day=$header['mdate']&0x001F;
			$header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
		}
		else
		{
			$header['mtime'] = time();
		}
		$header['stored_filename'] = $header['filename'];
		$header['status'] = "ok";
		return $header;
	}

	function ReadCentralFileHeaders($zip)
	{
    	$binary_data = fread($zip, 46);
    	$header = unpack('vchkid/vid/vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $binary_data);

    	if ($header['filename_len'] != 0)
    	{
	    	$header['filename'] = fread($zip,$header['filename_len']);
    	}
    	else
    	{
	    	$header['filename'] = '';
    	}
    	if ($header['extra_len'] != 0)
    	{
	    	$header['extra'] = fread($zip, $header['extra_len']);
    	}
    	else
    	{
	    	$header['extra'] = '';
    	}
    	if ($header['comment_len'] != 0)
    	{
	    	$header['comment'] = fread($zip, $header['comment_len']);
    	}
    	else
    	{
	    	$header['comment'] = '';
    	}

		if ($header['mdate'] && $header['mtime'])
		{
			$hour = ($header['mtime'] & 0xF800) >> 11;
			$minute = ($header['mtime'] & 0x07E0) >> 5;
			$seconde = ($header['mtime'] & 0x001F)*2;
			$year = (($header['mdate'] & 0xFE00) >> 9) + 1980;
			$month = ($header['mdate'] & 0x01E0) >> 5;
			$day = $header['mdate'] & 0x001F;
			$header['mtime'] = mktime($hour, $minute, $seconde, $month, $day, $year);
		}
		else
		{
			$header['mtime'] = time();
		}
		$header['stored_filename'] = $header['filename'];
		$header['status'] = 'ok';
		if (substr($header['filename'], -1) == '/')
		{
			$header['external'] = 0x41FF0010;
		}
		return $header;
	}

	function ReadCentralDir($zip,$zip_name)
	{
		$size = filesize($zip_name);
		if ($size < 277)
		{
			$maximum_size = $size;
		}
		else
		{
			$maximum_size=277;
		}
		@fseek($zip, $size-$maximum_size);
		$pos = ftell($zip); $bytes = 0x00000000;
		while ($pos < $size)
		{
			$byte = @fread($zip, 1);
			$bytes=($bytes << 8) | ord($byte);
			if ($bytes == 0x504b0506 or $bytes == 0x2e706870504b0506)
			{
				$pos++;
				break;
			}
			$pos++;
		}
		$fdata=fread($zip,18);
		$data=@unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size',$fdata);
		if ($data['comment_size'] != 0)
		{
			$centd['comment'] = fread($zip, $data['comment_size']);
		}
		else
		{
			$centd['comment'] = '';
		}
		$centd['entries'] = $data['entries'];
		$centd['disk_entries'] = $data['disk_entries'];
		$centd['offset'] = $data['offset'];$centd['disk_start'] = $data['disk_start'];
		$centd['size'] = $data['size'];  $centd['disk'] = $data['disk'];
		return $centd;
	}

	function ExtractFile($header,$to,$zip)
	{
		$header = $this->readfileheader($zip);
		if(substr($to,-1)!="/")
		{
			$to.="/";
		}
		if($to=='./')
		{
			$to = '';
		}
		$pth = explode("/",$to.$header['filename']);
		$mydir = '';
		for($i=0;$i<count($pth)-1;$i++)
		{
			if(!$pth[$i])
			{
				continue;
			}
			$mydir .= $pth[$i]."/";
			if((!is_dir($mydir) && @mkdir($mydir,0777)) || (($mydir==$to.$header['filename'] || ($mydir==$to && $this->total_folders==0)) && is_dir($mydir)) )
			{
				@chmod($mydir,0777);
				$this->total_folders ++;
				//echo "<input name='dfile[]' type='checkbox' value='$mydir' checked> <a href='$mydir' target='_blank'>目录: $mydir</a><br>";
			}
		}
	
		if(strrchr($header['filename'],'/')=='/')
		{
			return;
		}

		if (!($header['external']==0x41FF0010)&&!($header['external']==16))
		{
			if ($header['compression']==0)
			{
				$fp = @fopen($to.$header['filename'], 'wb');
				if(!$fp)
				{
					return(-1);
				}
				$size = $header['compressed_size'];
			
				while ($size != 0)
				{
					$read_size = ($size < 2048 ? $size : 2048);
					$buffer = fread($zip, $read_size);
					$binary_data = pack('a'.$read_size, $buffer);
					@fwrite($fp, $binary_data, $read_size);
					$size -= $read_size;
				}
				fclose($fp);
				touch($to.$header['filename'], $header['mtime']);
			}
			else
			{
				$fp = @fopen($to.$header['filename'].'.gz','wb');
				if(!$fp)
				{
					return(-1);
				}
				$binary_data = pack('va1a1Va1a1', 0x8b1f, chr($header['compression']),chr(0x00), time(), chr(0x00), chr(3));
				
				fwrite($fp, $binary_data, 10);
				$size = $header['compressed_size'];
				while ($size != 0)
				{
					$read_size = ($size < 1024 ? $size : 1024);
					$buffer = fread($zip, $read_size);
					$binary_data = pack('a'.$read_size, $buffer);
					@fwrite($fp, $binary_data, $read_size);
					$size -= $read_size;
				}
			
				$binary_data = pack('VV', $header['crc'], $header['size']);
				fwrite($fp, $binary_data,8); fclose($fp);
		
				$gzp = @gzopen($to.$header['filename'].'.gz','rb') or die("Cette archive gzcompress error");
				if(!$gzp)
				{
					return(-2);
				}
				$fp = @fopen($to.$header['filename'],'wb');
				if(!$fp)
				{
					return(-1);
				}
				$size = $header['size'];
				while ($size != 0)
				{
					$read_size = ($size < 2048 ? $size : 2048);
					$buffer = gzread($gzp, $read_size);
					$binary_data = pack('a'.$read_size, $buffer);
					@fwrite($fp, $binary_data, $read_size);
					$size -= $read_size;
				}
				fclose($fp); gzclose($gzp);
				touch($to.$header['filename'], $header['mtime']);
				@unlink($to.$header['filename'].'.gz');
			}
		}
		$this->total_files ++;
		//echo "<input name='dfile[]' type='checkbox' value='$to$header[filename]' checked> <a href='$to$header[filename]' target='_blank'>文件: $to$header[filename]</a><br>";
		return true;
 	}

 	//返回文件的修改时间格式
 	//只为本类内部函数调用.
	function unix2DosTime($unixtime = 0)
	{
		$timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
		if ($timearray['year'] < 1980) 
		{
			$timearray['year']    = 1980;
			$timearray['mon']     = 1;
			$timearray['mday']    = 1;
			$timearray['hours']   = 0;
			$timearray['minutes'] = 0;
			$timearray['seconds'] = 0;
		}

		return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	}

	/*
	初始化文件,建立文件目录,
	并返回文件的写入权限.
	*/
	function startfile($path = 'tmp.zip')
	{
		$this->gzfilename=$path;
		$mypathdir=array();
		do
		{
			$mypathdir[] = $path = dirname($path);
		}while($path != '.');
		@end($mypathdir);
		do
		{
			$path = @current($mypathdir);
			@mkdir($path);
		}while(@prev($mypathdir));

		if($this->fp=@fopen($this->gzfilename,"wb"))
		{
			return true;
		}
		return false;
	}

	/*
	添加一个文件到 zip 压缩包中.
	*/
    function addfile($data, $name)
    {
        $name     = str_replace('\\', '/', $name);
		
		if(strrchr($name,'/')=='/')
		{
			return $this->adddir($name);
		}
		
        $dtime    = dechex($this->unix2DosTime());
        $hexdtime = '\x' . $dtime[6] . $dtime[7]
                  . '\x' . $dtime[4] . $dtime[5]
                  . '\x' . $dtime[2] . $dtime[3]
                  . '\x' . $dtime[0] . $dtime[1];
        eval('$hexdtime = "' . $hexdtime . '";');

        $unc_len = strlen($data);
        $crc     = crc32($data);
        $zdata   = gzcompress($data);
        $c_len   = strlen($zdata);
        $zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
		
		//新添文件内容格式化:
        $datastr  = "\x50\x4b\x03\x04";
        $datastr .= "\x14\x00";            // ver needed to extract
        $datastr .= "\x00\x00";            // gen purpose bit flag
        $datastr .= "\x08\x00";            // compression method
        $datastr .= $hexdtime;             // last mod time and date
        $datastr .= pack('V', $crc);             // crc32
        $datastr .= pack('V', $c_len);           // compressed filesize
        $datastr .= pack('V', $unc_len);         // uncompressed filesize
        $datastr .= pack('v', strlen($name));    // length of filename
        $datastr .= pack('v', 0);                // extra field length
        $datastr .= $name;
        $datastr .= $zdata;
        $datastr .= pack('V', $crc);                 // crc32
        $datastr .= pack('V', $c_len);               // compressed filesize
        $datastr .= pack('V', $unc_len);             // uncompressed filesize


		fwrite($this->fp,$datastr);	//写入新的文件内容
		$my_datastr_len = strlen($datastr);
		unset($datastr);
		
		//新添文件目录信息
        $dirstr  = "\x50\x4b\x01\x02";
        $dirstr .= "\x00\x00";                	// version made by
        $dirstr .= "\x14\x00";                	// version needed to extract
        $dirstr .= "\x00\x00";                	// gen purpose bit flag
        $dirstr .= "\x08\x00";                	// compression method
        $dirstr .= $hexdtime;                 	// last mod time & date
        $dirstr .= pack('V', $crc);           	// crc32
        $dirstr .= pack('V', $c_len);         	// compressed filesize
        $dirstr .= pack('V', $unc_len);       	// uncompressed filesize
        $dirstr .= pack('v', strlen($name) ); 	// length of filename
        $dirstr .= pack('v', 0 );             	// extra field length
        $dirstr .= pack('v', 0 );             	// file comment length
        $dirstr .= pack('v', 0 );             	// disk number start
        $dirstr .= pack('v', 0 );             	// internal file attributes
        $dirstr .= pack('V', 32 );            	// external file attributes - 'archive' bit set
        $dirstr .= pack('V',$this->datastr_len ); // relative offset of local header
        $dirstr .= $name;
		
		$this->dirstr .= $dirstr;	//目录信息
		
		$this -> file_count ++;
		$this -> dirstr_len += strlen($dirstr);
		$this -> datastr_len += $my_datastr_len;	
    }

	//添加一个目录
	function adddir($name)
	{ 
		$name = str_replace("\\", "/", $name); 
		$datastr = "\x50\x4b\x03\x04\x0a\x00\x00\x00\x00\x00\x00\x00\x00\x00"; 
		
		$datastr .= pack("V",0).pack("V",0).pack("V",0).pack("v", strlen($name) ); 
		$datastr .= pack("v", 0 ).$name.pack("V", 0).pack("V", 0).pack("V", 0); 

		fwrite($this->fp,$datastr);	//写入新的文件内容
		$my_datastr_len = strlen($datastr);
		unset($datastr);
		
		$dirstr = "\x50\x4b\x01\x02\x00\x00\x0a\x00\x00\x00\x00\x00\x00\x00\x00\x00"; 
		$dirstr .= pack("V",0).pack("V",0).pack("V",0).pack("v", strlen($name) ); 
		$dirstr .= pack("v", 0 ).pack("v", 0 ).pack("v", 0 ).pack("v", 0 ); 
		$dirstr .= pack("V", 16 ).pack("V",$this->datastr_len).$name; 
		
		$this->dirstr .= $dirstr;	//目录信息

		$this -> file_count ++;
		$this -> dirstr_len += strlen($dirstr);
		$this -> datastr_len += $my_datastr_len;	
	}


	function createfile()
	{
		//压缩包结束信息,包括文件总数,目录信息读取指针位置等信息
		$endstr = "\x50\x4b\x05\x06\x00\x00\x00\x00" .
					pack('v', $this -> file_count) .
					pack('v', $this -> file_count) .
					pack('V', $this -> dirstr_len) .
					pack('V', $this -> datastr_len) .
					"\x00\x00";

		fwrite($this->fp,$this->dirstr.$endstr);
		fclose($this->fp);
	}
}