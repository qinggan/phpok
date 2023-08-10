<?php
/**
 * 附件上传操作类
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2014年7月10日
 * @更新 2023年7月18日
**/


/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class upload_lib
{
	private $folder = 'res/';
	private $dir_root = '/';
	private $dir_cache = '/';
	private $file_type = 'jpg,png,gif,zip,rar,jpeg';
	private $cateid = 0;
	private $up_error;
	private $cate;
	private $ext = '';

	public function __construct()
	{
		global $app;
		$this->dir_root = $app->dir_root;
		$this->dir_cache = $app->dir_cache;
		$this->up_error = array(
			 0 => P_Lang('上传成功'),
			 1 => P_Lang('上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值'),
			 2 => P_Lang('上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值'),
			 3 => P_Lang('文件只有部分被上传'),
			 4 => P_Lang('没有文件被上传'),
			 6 => P_Lang('找不到临时文件夹，通过php.ini配置参数：upload_tmp_dir'),
			 7 => P_Lang('文件写入失败'),
			 8 => P_Lang('PHP扩展停止了文件上传。')
		 );
	}

	//设置附件上传的目录
	//目录不存在，就自动创建，创建失败即就存到res/根目录下
	public function set_dir($dir="")
	{
		global $app;
		if(!$dir){
			return false;
		}
		$root_num = strlen($this->dir_root);
		if(substr($dir,0,$root_num) == $this->dir_root){
			$dir = substr($dir,$root_num);
		}
		if(!file_exists($this->dir_root.$dir)){
			$app->lib('file')->make($this->dir_root.$dir);
			if(!file_exists($this->dir_root.$dir)){
				$dir = 'res/';
			}
		}
		if(substr($dir,-1) != "/"){
			$dir .= "/";
		}
		if(substr($dir,0,1) == "/"){
			$dir = substr($dir,1);
		}
		if($dir){
			$dir = str_replace("//","/",$dir);
		}
		$this->folder = $dir;
		return $dir;
	}

	//自定义设置要上传的附件类型
	public function set_type($type='')
	{
		if(!$type){
			return false;
		}
		if(is_array($type)){
			$type = implode(",",$type);
		}
		$type = str_replace(array('*','.'),'',$type);
		$this->file_type = $type;
	}

	//设置分类
	public function set_cate($cate_rs)
	{
		global $app;
		if(!$cate_rs){
			$cate_rs = array('id'=>0,'root'=>'res/','folder'=>'Y/md/');
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/"){
			$folder .= date($cate_rs["folder"],$app->time);
		}
		$this->cateid = $cate_rs['id'];
		return $this->set_dir($folder);
	}

	public function getfile($input='upfile',$cateid=0)
	{
		if(!$input){
			return array('status'=>'error','content'=>P_Lang('未指定表单名称'));
		}
		$this->_cate($cateid);
		if(isset($_FILES[$input])){
			$rs = $this->_upload($input);
		}else{
			$rs = $this->_save($input);
		}
		if($rs['status'] != 'ok'){
			return $rs;
		}
		$rs['cate'] = $this->cate;
		return $rs;
	}

	/**
	 * 上传ZIP文件
	 * @参数 $input，表单名
	 * @参数 $folder，存储目录，为空使用_cache
	 * @返回 数组，上传状态status及保存的路径
	 * @更新时间 2016年07月18日
	**/
	public function zipfile($input,$folder='')
	{
		if(!$input){
			return array('status'=>'error','content'=>P_Lang('未指定表单名称'));
		}
		//如果未指定存储文件夹，则使用
		if(!$folder){
			$folder = $this->dir_cache;
		}
		$this->cateid = 0;
		$this->set_dir($folder);
		$this->set_type('zip');
		$this->cate = array('id'=>0,'filemax'=>104857600,'root'=>$folder,'folder'=>'/','filetypes'=>'zip');
		if(isset($_FILES[$input])){
			$rs = $this->_upload($input);
		}else{
			$rs = $this->_save($input);
		}
		if($rs['status'] != 'ok'){
			return $rs;
		}
		$rs['cate'] = $this->cate;
		return $rs;
	}

	/**
	 * 上传图片文件
	**/
	public function imgfile($input,$folder='')
	{
		if(!$input){
			return array('status'=>'error','content'=>P_Lang('未指定表单名称'));
		}
		//如果未指定存储文件夹，则使用
		if(!$folder){
			$folder = $this->dir_cache;
		}
		$this->cateid = 0;
		$this->set_dir($folder);
		$this->set_type('jpg,png,gif,jpeg');
		$this->cate = array('id'=>0,'filemax'=>104857600,'root'=>$folder,'folder'=>'/','filetypes'=>'jpg,gif,png,jpeg');
		if(isset($_FILES[$input])){
			$rs = $this->_upload($input);
		}else{
			$rs = $this->_save($input);
		}
		if($rs['status'] != 'ok'){
			return $rs;
		}
		$rs['cate'] = $this->cate;
		return $rs;
	}

	private function file_ext($tmpname,$chk=true)
	{
		$ext = pathinfo($tmpname,PATHINFO_EXTENSION);
		if(!$ext){
			return false;
		}
		$ext = strtolower($ext);
		if(!$chk){
			return $ext;
		}
		$filetypes = "jpg,gif,png,jpeg";
		if($this->cate && $this->cate['filetypes']){
			$filetypes .= ",".$this->cate['filetypes'];
		}
		if($this->file_type){
			$filetypes .= ",".$this->file_type;
		}
		$list = explode(",",$filetypes);
		$list = array_unique($list);
		if(!in_array($ext,$list)){
			return false;
		}
		return $ext;
	}

	private function _upload($input,$chk=true)
	{
		global $app;
		$basename = substr(md5(time().uniqid()),9,16);
		$chunk = $app->get('chunk','int');
		$chunks = $app->get('chunks','int');
		if(!$chunks){
			$chunks = 1;
		}
		if(!$_FILES){
			return array('status'=>'error','error'=>P_Lang('没有要上传的附件'));
		}
		$tmpname = $_FILES[$input]["name"];
		if($tmpname){
			$tmpname = $app->lib('string')->to_utf8($tmpname);
	    	$tmpname = $app->format($tmpname,"safe_text");
		}
		$mime_type = $_FILES[$input]["type"];
		if($mime_type){
			$mime_type = $app->format($mime_type,'safe_text');
		}
		$tmpid = 'u_'.md5($tmpname);
		$ext = $this->file_ext($tmpname,$chk);
		if(!$ext){
			return array('status'=>'error','error'=>P_Lang('附件类型不符合要求'));
		}
		$out_tmpfile = $this->dir_cache.$tmpid.'_'.$chunk;
		if (!$out = @fopen($out_tmpfile.".parttmp", "wb")) {
			return array('status'=>'error','error'=>P_Lang('无法打开输出流'));
		}
		$error_id = $_FILES[$input]['error'] ? $_FILES[$input]['error'] : 0;
		if($error_id){
			return array('status'=>'error','error'=>$this->up_error[$error_id]);
		}
		if(!is_uploaded_file($_FILES[$input]['tmp_name'])){
			return array('status'=>'error','error'=>P_Lang('上传失败，临时文件无法写入'));
		}
		if(!$in = @fopen($_FILES[$input]["tmp_name"], "rb")) {
			return array('status'=>'error','error'=>P_Lang('无法打开输入流'));
	    }
	    while ($buff = fread($in, 4096)) {
		    fwrite($out, $buff);
		}
		@fclose($out);
		@fclose($in);
		$app->lib('file')->mv($out_tmpfile.'.parttmp',$out_tmpfile.'.part');
		if(($chunk+1) < $chunks){
			return array('status'=>'error','error'=>'等待分片完成');
		}
		return $this->_join($tmpid,$chunks,$ext,$tmpname,$mime_type);
	}

	private function _save($input,$chk=true)
	{
		global $app;
		$tmpname = $app->get('name','safe_text');
	    $tmpname = $app->lib('string')->to_utf8($tmpname);
	    $mime_type = $app->get('type','safe_text');
		if(!$tmpname){
			$tmpname = uniqid($input.'_');
		}
		$ext = $this->file_ext($tmpname,true);
		if(!$ext){
			$ext = $this->type2ext($mime_type);
			if(!$ext){
				return array('status'=>'error','error'=>P_Lang('附件类型不符合要求'.$mime_type));
			}
		}
		$chunk = $app->get('chunk','int');
		$chunks = $app->get('chunks','int');
		
		if(!$chunks){
			$chunks = 1;
		}
		$tmpid = 's_'.md5($tmpname);
		$out_tmpfile = $this->dir_cache.$tmpid.'_'.$chunk;
		if (!$out = @fopen($out_tmpfile.".parttmp", "wb")) {
			return array('status'=>'error','error'=>P_Lang('无法打开输出流'));
		}
		if (!$in = @fopen("php://input", "rb")) {
			return array('status'=>'error','error'=>P_Lang('无法打开输入流'));
	    }
	    while ($buff = fread($in, 4096)) {
		    fwrite($out, $buff);
		}
		@fclose($out);
		@fclose($in);
		$app->lib('file')->mv($out_tmpfile.'.parttmp',$out_tmpfile.'.part');
		if(($chunk+1) < $chunks){
			return array('status'=>'tip','error'=>'等待分片完成');
		}
		return $this->_join($tmpid,$chunks,$ext,$tmpname,$mime_type);
	}

	public function ext()
	{
		return $this->ext;
	}

	public function type2ext($type='')
	{
		if(!$type){
			return false;
		}
		$list = array();
		$list[] = "pdf=application/pdf";
		$list[] = "xls=application/vnd.ms-excel";
		$list[] = "xlsx=application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
		$list[] = "csv=text/csv";
		$list[] = "doc=application/msword";
		$list[] = "docx=application/vnd.openxmlformats-officedocument.wordprocessingml.document";
		$list[] = "ppt=application/vnd.ms-powerpoint";
		$list[] = "pptx=application/vnd.openxmlformats-officedocument.presentationml.presentation";
		$list[] = "json=application/json";
		$list[] = "txt=text/plain";
		$list[] = "jpeg=image/jpeg";
		$list[] = "jpg=image/jpeg";
		$list[] = "gif=image/gif";
		$list[] = "png=image/png";
		$list[] = "ico=image/vnd.microsoft.icon";
		$list[] = "svg=image/svg+xml";
		$list[] = "wav=audio/wav";
		$list[] = "mp3=audio/mpeg";
		$list[] = "aac=audio/aac";
		$list[] = "oga=audio/ogg";
		$list[] = "ogv=video/ogg";
		$list[] = "mpeg=video/mpeg";
		$list[] = "7z=application/x-7z-compressed";
		$list[] = "zip=application/zip";
		$list[] = "rar=application/x-rar-compressed";
		$list[] = "html=text/html";
		$list[] = "css=text/css";
		$list[] = "js=text/javascript";
		$list[] = "abw=application/x-abiword";
		$list[] = "arc=application/x-freearc";
		$list[] = "avi=video/x-msvideo";
		$list[] = "azw=application/vnd.amazon.ebook";
		$list[] = "bin=application/octet-stream";
		$list[] = "bmp=image/bmp";
		$list[] = "bz=application/x-bzip";
		$list[] = "bz2=application/x-bzip2";
		$list[] = "csh=application/x-csh";
		$list[] = "eot=application/vnd.ms-fontobject";
		$list[] = "epub=application/epub+zip";
		$list[] = "htm=text/html";
		$list[] = "ics=text/calendar";
		$list[] = "jar=application/java-archive";
		$list[] = "jsonld=application/ld+json";
		$list[] = "mid=audio/midi audio/x-midi";
		$list[] = "midi=audio/midi audio/x-midi";
		$list[] = "mjs=text/javascript";
		$list[] = "mpkg=application/vnd.apple.installer+xml";
		$list[] = "odp=application/vnd.oasis.opendocument.presentation";
		$list[] = "ods=application/vnd.oasis.opendocument.spreadsheet";
		$list[] = "odt=application/vnd.oasis.opendocument.text";
		$list[] = "ogx=application/ogg";
		$list[] = "otf=font/otf";
		$list[] = "rtf=application/rtf";
		$list[] = "sh=application/x-sh";
		$list[] = "swf=application/x-shockwave-flash";
		$list[] = "tar=application/x-tar";
		$list[] = "tif=image/tiff";
		$list[] = "tiff=image/tiff";
		$list[] = "ttf=font/ttf";
		$list[] = "vsd=application/vnd.visio";
		$list[] = "weba=audio/webm";
		$list[] = "webm=video/webm";
		$list[] = "webp=image/webp";
		$list[] = "woff=font/woff";
		$list[] = "woff2=font/woff2";
		$list[] = "xhtml=application/xhtml+xml";
		$list[] = "xml=text/xml";
		$list[] = "xul=application/vnd.mozilla.xul+xml";
		$list[] = "3gp=audio/3gpp";
		$list[] = "3g2=audio/3gpp2";
		$list[] = "xml=application/xml";
		$list[] = "3gp=video/3gpp";
		$list[] = "3g2=video/3gpp2";
		$ext = '';
		foreach($list as $key=>$value){
			$tmp = explode("=",$value);
			if($tmp[1] == $type){
				$ext = $tmp[0];
				break;
			}
		}
		return $ext;

	}

	/**
	 * 适用于外部接口检查
	**/
	public function bin2ext($bin,$ext='')
	{
		return $this->bin_filetype_check($bin,$ext);
	}

	private function _join($tmpid,$chunks,$ext,$tmpname,$mime_type='')
	{
		global $app;
		$this->ext = $ext;
		$extnew = strtolower($ext);
		$basename = substr(md5(time().uniqid()),9,16);
		$tmp_obj = @fopen($this->dir_cache.$tmpid.'_0.part', "rb");
		$bin = @fread($tmp_obj,128); //只读128字节
		fclose($tmp_obj);
		if(!$bin){
			$this->delcache($tmpid,$chunks);
			return array('status'=>'error','error'=>P_Lang('上传附件类型不符合要求')); 
		}else{
			$check = $this->bin_filetype_check($bin,$ext);
			if(!$check){
				$this->delcache($tmpid,$chunks);
				return array('status'=>'error','error'=>P_Lang('系统检测附件类型与文件后缀 {ext} 不一致，请修改后上传',array('ext'=>$ext)));
			}else{
				$safe_ext = array('php','asp','js','jsp','css','html','htm');
				if(in_array($extnew,$safe_ext)){
					$extnew = $ext.'.txt';
				}
			}
		}
		$done = true;
		for($index=0;$index<$chunks;$index++){
			if(!file_exists($this->dir_cache.$tmpid.'_'.$index.'.part')){
				$done = false;
				break;
			}
		}
		if(!$done){
			$this->delcache($tmpid,$chunks);
			return array('status'=>'error','error'=>'文件上传失败，请检查');
		}
		$outfile = $this->folder.$basename.'.'.$extnew;//使用新的文件名，防止上传脚本文件（后台管理员允许重命名）
	    if(!$out = @fopen($this->dir_root.$outfile,"wb")) {
		    return array('status'=>'error','error'=>P_Lang('无法打开输出流'));
	    }
	    if(flock($out,LOCK_EX)){
	        for($index=0;$index<$chunks;$index++) {
	            if (!$in = @fopen($this->dir_cache.$tmpid.'_'.$index.'.part','rb')){
	                break;
	            }
	            while ($buff = fread($in, 4096)) {
	                fwrite($out, $buff);
	            }
	            @fclose($in);
	            $app->lib('file')->rm($this->dir_cache.$tmpid."_".$index.".part");
	        }
	        flock($out,LOCK_UN);
	    }
	    @fclose($out);
	    $title = str_replace(".".$ext,'',$tmpname);
	    return array('title'=>$title,'ext'=>$ext,'mime_type'=>$mime_type,'filename'=>$outfile,'folder'=>$this->folder,'status'=>'ok');
	}

	private function delcache($tmpid,$total)
	{
		global $app;
		for($i=0;$i<$total;$i++){
			$file = $this->dir_cache.$tmpid."_".$i.".part";
			if(file_exists($file)){
				$app->lib('file')->rm($file);
			}
		}
		return true;
	}

	private function bin_filetype_check($bin,$ext='')
	{
		
		if(!$bin || !$ext){
			return false;
		}
		$ext = strtolower($ext);
		if(in_array($ext,array('jpg','jpeg','gif','png','bmp'))){
			if(!$this->pic_check($bin)){
				return false;
			}
			return true;
		}
		// 忽略视频的检测
		if(in_array($ext,array('mpeg','mpg','mp4','avi','wmv','mov','rm','rmvb','ram','swf','flv','asf','mkv','3gp'))){
			return true;
		}
		// 忽略音频的检测
		if(in_array($ext,array('mp3','wma','ra','wav','mid','midi'))){
			return true;
		}
		if(in_array($ext,array('zip','rar','gz','tar','tgz'))){
			if(!$this->compress_check($bin)){
				return false;
			}
			return true;
		}
		if(in_array($ext,array('doc','docx','xls','xlsx','ppt','pptx','pdf','wps','et','dps'))){
			if(!$this->document_check($bin)){
				return false;
			}
			return true;
		}
		return true;
	}

	private function compress_check($bin)
	{
		$list = array(
			array('504B3030','ZIP'),
			array('504B03','ZIP'),
			array('526172','RAR'),
			array('1F8B','GZ'),
			array('1F8B','TAR'),
			array('1F8B','TGZ')
		);
		return $this->_chk($list,$bin);
	}

	private function document_check($bin)
	{
		$list = array(
			array('D0CF11E0','DOC'),
			array('31BE000000AB0000','DOC'),
			array('1234567890FF','DOC'),
			array('0D444F43','DOC'),
			array('7FFE340A','DOC'),
			array('255044','PDF'),
			array('D0CF11E0','XLS'),
			array('504B0304','XLSX'),
			array('D0CF11E0','PPT'),
			array('D0CF11E0A1B11AE10000','WPS'),
		);
		return $this->_chk($list,$bin);
	}

	private function audio_check($bin)
	{
		$list = array(
			array('FFFB50','MP3'),
			array('494433','MP3'),
			array('3026B2','WMA'),
			array('2E7261FD','RA'),
			array('57415645','WAV'),
			array('524946','WAV'),
			array('4D546864','MID'),
			array('4D546864','MIDI')
		);
		return $this->_chk($list,$bin);
	}

	private function video_check($bin)
	{
		$list = array(
			array('00000018','MP4'),
			array('2E524D46','RM'),
			array('2E524D46','RMVB'),
			array('000001B3','MPG'),
			array('000001B3','MPEG'),
			array('000001BA','MPG'),
			array('6D646174','MOV'),
			array('6D6F6F76','MOV'),
			array('000077','MOV'),
			array('00000F','MOV'),
			array('41564920','AVI'),
			array('2E7261FD','RAM'),
			array('3026B2','WMV'),
			array('3026B2','ASF'),
			array('465753','SWF'),
			array('435753','SWF'),
			array('464C5601','FLV'),
			array('00000020','M4A'),
			array('1A45DF','MKV')
		);
		return $this->_chk($list,$bin);
	}

	/**
	 * 检测上传的文件后缀是否符合图片
	**/
	private function pic_check($bin)
	{
		$list = array(
			array('FFD8FF','JPG'),
			array('FFD8FF','JPEG'),
			array('47494638','GIF'),
			array('89504E','PNG'),
			array('424D','BMP')
		);
		return $this->_chk($list,$bin);
	}

	private function _chk($typelist,$bin)
	{
		$ok = false;
		foreach($typelist as $key=>$value){
			$blen = strlen(pack("H*", $value[0])); //得到文件头标记字节数
			$tbin = substr($bin,0,intval($blen)); ///需要比较文件头长度
			$chk2 = unpack("H*",$tbin);
			$chk2 = array_shift($chk2);
			$chk1 = strtolower($value[0]);
			if($chk1 == strtolower($chk2)){
				$ok = true;
				break;
			}
		}
		if(!$ok){
			return false;
		}
		return true;
	}

	private function _cate($id=0)
	{
		global $app;
		$cate_rs = $app->model('rescate')->get_one($id);
		if(!$cate_rs){
			$cate_rs = array('id'=>0,'filemax'=>50000,'root'=>'res/','folder'=>'Ym/d/','filetypes'=>'jpg,gif,png,zip,rar','gdall'=>1,'ico'=>1);
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/"){
			$folder .= date($cate_rs["folder"],$app->time);
		}
		if(!file_exists($this->dir_root.$folder)){
			$app->lib('file')->make($this->dir_root.$folder);
		}
		if(!$cate_rs['filetypes']){
			$cate_rs['filetypes'] = 'jpg,gif,png,zip,rar';
		}
		$this->cate = $cate_rs;
		$this->file_type = $cate_rs['filetypes'];
		$this->folder = $folder;
		return $cate_rs;
	}

	/**
	 * 附件上传
	 * @参数 $inputname 上传表单名
	**/
	public function upload($inputname,$folder='')
	{
		if(!$inputname){
			return array('status'=>'error','content'=>P_Lang('未指定表单名称'));
		}
		if($folder){
			$this->set_dir($folder);
		}
		if(isset($_FILES[$inputname])){
			return $this->_upload($inputname,true);
		}
		return $this->_save($inputname,false);
	}

	public function get_folder()
	{
		return $this->folder;
	}

	public function get_cate()
	{
		return $this->cateid;
	}

	public function title_format($title)
	{
		$tmp = explode(".",$title);
		if(count($tmp)<2){
			return array('title'=>$title,'ext'=>'unknown');
		}elseif(count($tmp) == 2){
			return array('title'=>$tmp[0],'ext'=>strtolower($tmp[1]));
		}else{
			$title = $ext = '';
			$total = count($tmp);
			foreach($tmp as $key=>$value){
				if($key<1){
					$title = $value;
					continue;
				}
				if($key==($total-1)){
					$ext = strtolower($value);
					break;
				}
				$title .= ".".$value;
			}
			return array('title'=>$title,'ext'=>$ext);
		}
	}
}