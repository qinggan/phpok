<?php
/**
 * 附件上传操作类
 * @package phpok\libs\upload
 * @author qinggan <admin@phpok.com>
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @homepage http://www.phpok.com
 * @version 4.x
 * @license http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @update 2014年7月10日
**/

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
		global $app;
		$this->dir_root = $app->dir_root;
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
	 * @参数 $folder，存储目录，为空使用data/cache/
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
			$folder = 'data/cache/';
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

	private function file_ext($tmpname)
	{
		$ext = pathinfo($tmpname,PATHINFO_EXTENSION);
		if(!$ext){
			return false;
		}
		$ext = strtolower($ext);
		$filetypes = "jpg,gif,png";
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

	private function _upload($input)
	{
		global $app;
		$basename = substr(md5(time().uniqid()),9,16);
		$chunk = $app->get('chunk','int');
		$chunks = $app->get('chunks','int');
		if(!$chunks){
			$chunks = 1;
		}
		$tmpname = $_FILES[$input]["name"];
	    $tmpname = $app->lib('string')->to_utf8($tmpname);
	    $tmpname = $app->format($tmpname);
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
		$app->lib('file')->mv($out_tmpfile.'.parttmp',$out_tmpfile.'.part');
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
	            $app->lib('file')->rm($this->dir_root.'data/cache/'.$tmpid."_".$index.".part");
	        }
	        flock($out,LOCK_UN);
	    }
	    @fclose($out);
	    $title = str_replace(".".$ext,'',$tmpname);
	    return array('title'=>$title,'ext'=>$ext,'filename'=>$outfile,'folder'=>$this->folder,'status'=>'ok');
	}

	private function _save($input)
	{
		global $app;
		$basename = substr(md5(time().uniqid()),9,16);
		$tmpname = $app->get('name');
	    $tmpname = $app->lib('string')->to_utf8($tmpname);
	    $tmpname = $app->format($tmpname); //安全格式化数据
		if(!$tmpname){
			$tmpname = uniqid($input.'_');
		}
		$ext = $this->file_ext($tmpname);
		if(!$ext){
			return array('status'=>'error','error'=>P_Lang('附件类型不符合要求'));
		}
		$chunk = $app->get('chunk','int');
		$chunks = $app->get('chunks','int');
		if(!$chunks){
			$chunks = 1;
		}
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
		$app->lib('file')->mv($out_tmpfile.'.parttmp',$out_tmpfile.'.part');
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
	            $app->lib('file')->rm($this->dir_root.'data/cache/'.$tmpid."_".$index.".part");
	        }
	        flock($out,LOCK_UN);
	    }
	    @fclose($out);
	    $title = str_replace(".".$ext,'',$tmpname);
	    return array('title'=>$title,'ext'=>$ext,'filename'=>$outfile,'folder'=>$this->folder,'status'=>'ok');
	}

	private function _cate($id=0)
	{
		global $app;
		$cate_rs = '';
		if($id){
			$cate_rs = $app->model('rescate')->get_one($id);
		}
		if(!$cate_rs){
			$cate_rs = $app->model('rescate')->get_default();
		}
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
	public function upload($inputname)
	{
		if(!$inputname){
			return array('status'=>'error','content'=>P_Lang('未指定表单名称'));
		}
		if(isset($_FILES[$inputname])){
			return $this->_upload($inputname);
		}
		return $this->_save($inputname);
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