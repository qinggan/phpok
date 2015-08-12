<?php
/*****************************************************************************************
	文件： {phpok}/libs/upload.php
	备注： 附件上传操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年7月10日
*****************************************************************************************/
class upload_lib
{
	private $folder = 'res/';
	private $dir_root = '/';
	private $file_type = 'jpg,png,gif,zip,rar,jpeg';
	private $cateid = 0;
	private $up_error;
	private $cate;

	public function __construct()
	{
		$this->dir_root = $GLOBALS['app']->dir_root;
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
		if(!$dir){
			return false;
		}
		$root_num = strlen($this->dir_root);
		if(substr($dir,0,$root_num) == $this->dir_root){
			$dir = substr($dir,$root_num);
		}
		if(!file_exists($this->dir_root.$dir)){
			$GLOBALS['app']->lib('file')->make($this->dir_root.$dir);
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
		if(!$cate_rs){
			$cate_rs = array('id'=>0,'root'=>'res/','folder'=>'Y/md/');
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/"){
			$folder .= date($cate_rs["folder"],$GLOBALS['app']->time);
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

	private function file_ext($tmpname)
	{
		$tmp = explode(".",$tmpname);
	    if(count($tmp)<1){
		    $ext = 'unknown';
	    }else{
		    $tmptotal = count($tmp);
		    $ext = $tmp[($tmptotal-1)];
	    }
	    return strtolower($ext);
	}

	private function _upload($input)
	{
		$basename = substr(md5(time().uniqid()),9,16);
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
		$tmpname = $_FILES[$input]["name"];
		$tmpid = 'u_'.md5($tmpname);
		$ext = $this->file_ext($tmpname);
		$out_tmpfile = $this->dir_root.'data/cache/'.$tmpid.'_'.$chunk;
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
		$GLOBALS['app']->lib('file')->mv($out_tmpfile.'.parttmp',$out_tmpfile.'.part');
		$index = 0;
		$done = true;
		for($index=0;$index<$chunks;$index++) {
		    if (!file_exists($this->dir_root.'data/cache/'.$tmpid.'_'.$index.".part") ) {
		        $done = false;
		        break;
		    }
		}
		if(!$done){
			return array('status'=>'error','error'=>'上传的文件异常');
		}
		$outfile = $this->folder.$basename.'.'.$ext;
	    if(!$out = @fopen($this->dir_root.$outfile,"wb")) {
		    return array('status'=>'error','error'=>P_Lang('无法打开输出流'));
	    }
	    if(flock($out,LOCK_EX)){
	        for($index=0;$index<$chunks;$index++) {
	            if (!$in = @fopen($this->dir_root.'data/cache/'.$tmpid.'_'.$index.'.part','rb')){
	                break;
	            }
	            while ($buff = fread($in, 4096)) {
	                fwrite($out, $buff);
	            }
	            @fclose($in);
	            $GLOBALS['app']->lib('file')->rm($this->dir_root.'data/cache/'.$tmpid."_".$index.".part");
	        }
	        flock($out,LOCK_UN);
	    }
	    @fclose($out);
	    $tmpname = $GLOBALS['app']->lib('string')->to_utf8($tmpname);
	    $title = str_replace(".".$ext,'',$tmpname);
	    return array('title'=>$title,'ext'=>$ext,'filename'=>$outfile,'folder'=>$this->folder,'status'=>'ok');
	}

	private function _save($input)
	{
		$basename = substr(md5(time().uniqid()),9,16);
		$tmpname = isset($_REQUEST['name']) ? $_REQUEST["name"] : uniqid($input.'_');
		$ext = $this->file_ext($tmpname);
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
		$tmpid = 's_'.md5($tmpname);
		$out_tmpfile = $this->dir_root.'data/cache/'.$tmpid.'_'.$chunk;
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
		$GLOBALS['app']->lib('file')->mv($out_tmpfile.'.parttmp',$out_tmpfile.'.part');
		$index = 0;
		$done = true;
		for($index=0;$index<$chunks;$index++) {
		    if (!file_exists($this->dir_root.'data/cache/'.$tmpid.'_'.$index.".part") ) {
		        $done = false;
		        break;
		    }
		}
		if(!$done){
			return array('status'=>'error','error'=>'上传的文件异常');
		}
		$outfile = $this->folder.$basename.'.'.$ext;
	    if(!$out = @fopen($this->dir_root.$outfile,"wb")) {
		    return array('status'=>'error','error'=>P_Lang('无法打开输出流'));
	    }
	    if(flock($out,LOCK_EX)){
	        for($index=0;$index<$chunks;$index++) {
	            if (!$in = @fopen($this->dir_root.'data/cache/'.$tmpid.'_'.$index.'.part','rb')){
	                break;
	            }
	            while ($buff = fread($in, 4096)) {
	                fwrite($out, $buff);
	            }
	            @fclose($in);
	            $GLOBALS['app']->lib('file')->rm($this->dir_root.'data/cache/'.$tmpid."_".$index.".part");
	        }
	        flock($out,LOCK_UN);
	    }
	    @fclose($out);
	    $tmpname = $GLOBALS['app']->lib('string')->to_utf8($tmpname);
	    $title = str_replace(".".$ext,'',$tmpname);
	    return array('title'=>$title,'ext'=>$ext,'filename'=>$outfile,'folder'=>$this->folder,'status'=>'ok');
	}

	private function _cate($id=0)
	{
		$cate_rs = '';
		if($id){
			$cate_rs = $GLOBALS['app']->model('rescate')->get_one($id);
		}
		if(!$cate_rs){
			$cate_rs = $GLOBALS['app']->model('rescate')->get_default();
		}
		if(!$cate_rs){
			$cate_rs = array('id'=>0,'filemax'=>50000,'root'=>'res/','folder'=>'Ym/d/','filetypes'=>'jpg,gif,png,zip,rar','gdall'=>1,'ico'=>1);
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/"){
			$folder .= date($cate_rs["folder"],$GLOBALS['app']->time);
		}
		if(!file_exists($this->dir_root.$folder)){
			$GLOBALS['app']->lib('file')->make($this->dir_root.$folder);
		}
		if(!$cate_rs['filetypes']){
			$cate_rs['filetypes'] = 'jpg,gif,png,zip,rar';
		}
		$this->cate = $cate_rs;
		$this->file_type = $cate_rs['filetypes'];
		$this->folder = $folder;
		return $cate_rs;
	}

	//附件上传
	function upload($inputname)
	{
		if(!$inputname){
			return array('status'=>'error','content'=>P_Lang('未指定表单名称'));
		}
		if(!isset($_FILES[$inputname])){
			return array('status'=>'error','content'=>P_Lang('没有指定上传的图片'));
		}
		$t = $_FILES[$inputname]['error'];
		if($t){
			$tinfo = $this->up_error[$t] ? $this->up_error[$t] : P_Lang('附件上传失败');
			return array('status'=>'error','content'=>$tinfo);
		}
		if(!is_uploaded_file($_FILES[$inputname]['tmp_name'])){
			return array('status'=>'error','content'=>P_Lang('没有找到临时文件'));
		}
		$file_info = $this->title_format($_FILES[$inputname]['name']);
		$filetype = $file_info['ext'];
		if(!$filetype || $filetype == 'unknown'){
			return array('status'=>'error','content'=>P_Lang('获取文件类型失败'));
		}
		$filetype = strtolower($filetype);
		if(!in_array($filetype,explode(",",$this->file_type))){
			return array('status'=>'error','content'=>P_Lang('文件类型不符合系统要求'));
		}
		$filename = substr(md5(time().uniqid()),9,16);
		$file = $this->dir_root.$this->folder.$filename.'.'.$filetype;
		if(move_uploaded_file($_FILES[$inputname]['tmp_name'],$file)){
			$title = $file_info['title'];
			if(!$title) $title = $filename;
			$title = $GLOBALS['app']->lib('string')->to_utf8($title);
			$title = strtolower($title);
			$title = str_replace('.'.$filetype,'',$title);
			$title = $GLOBALS['app']->format($title);
			return array("status"=>"ok","title"=>$title,"filename"=>$this->folder.$filename.'.'.$filetype,"ext"=>$filetype,'name'=>$filename);
		}
		return array('status'=>'error','content'=>'附件上传失败');
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
		if(count($tmp)<2)
		{
			return array('title'=>$title,'ext'=>'unknown');
		}
		elseif(count($tmp) == 2)
		{
			return array('title'=>$tmp[0],'ext'=>strtolower($tmp[1]));
		}
		else
		{
			$title = $ext = '';
			$total = count($tmp);
			foreach($tmp as $key=>$value)
			{
				if($key<1)
				{
					$title = $value;
					continue;
				}
				if($key==($total-1))
				{
					$ext = strtolower($value);
					break;
				}
				$title .= ".".$value;
			}
			return array('title'=>$title,'ext'=>$ext);
		}
	}
	
}
?>