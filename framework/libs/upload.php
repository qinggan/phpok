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
			return array('status'=>'tip','error'=>'等待分片完成');
		}
		return $this->_join($tmpid,$chunks,$ext,$tmpname,$mime_type);
	}

	private function _save($input,$chk=true)
	{
		global $app;
		$tmpname = $app->get('name','safe_text');
	    $tmpname = $app->lib('string')->to_utf8($tmpname);
		if(!$tmpname){
			$tmpname = uniqid($input.'_');
		}
		$ext = $this->file_ext($tmpname,true);
		if(!$ext){
			return array('status'=>'error','error'=>P_Lang('附件类型不符合要求'));
		}
		$chunk = $app->get('chunk','int');
		$chunks = $app->get('chunks','int');
		$mime_type = $app->get('type','safe_text');
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
		if(in_array($ext,array('mpeg','mpg','mp4','avi','wmv','mov','rm','rmvb','ram','swf','flv','asf','mkv','3gp'))){
			if(!$this->video_check($bin)){
				return false;
			}
			return true;
		}
		if(in_array($ext,array('mp3','wma','ra','wav','mid','midi'))){
			if(!$this->audio_check($bin)){
				return false;
			}
			return true;
		}
		if(in_array($ext,array('zip','rar','gz','tar','tgz'))){
			if(!$this->compress_check($bin)){
				return false;
			}
			return true;
		}
		if(in_array($ext,array('doc','docx','xls','xlsx','ppt','pptx','pdf'))){
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

	private function type_list()
	{
		return array(
			array('28546869732066696C65206D75737420626520636F6E76657274656420776974682042696E48657820','HQX'),
			array('23204D6963726F736F667420446576656C6F7065722053747564696F','DSP'),
			array('496E6E6F20536574757020556E696E7374616C6C204C6F6720286229','DAT'),
			array('1A52545320434F4D5052455353454420494D4147452056312E301A','DAT'),
			array('2A2A2A2020496E7374616C6C6174696F6E205374617274656420','LOG'),
			array('30314F52444E414E43452053555256455920202020202020','NTF'),
			array('436C69656E742055726C4361636865204D4D462056657220','DAT'),
			array('415647365F496E746567726974795F4461746162617365','DAT'),
			array('24464C3240282329205350535320444154412046494C45','SAV'),
			array('7273696F6E3D22313C3F786D6C2076652E30223F3E','XUL'),
			array('FFFE3C0052004F004F0054005300540055004200','XML'),
			array('000100005374616E64617264204A6574204442','MDB'),
			array('000100005374616E6461726420414345204442','ACCDB'),
			array('00000020667479704D34412000000000','M4A/M4V'),
			array('454E5452595643440200000102001858','VCD'),
			array('49491A00000048454150434344520200','CRW'),
			array('45524653534156454441544146494C45','DAT'),
			array('0006156100000002000004D200001000','DB'),
			array('1A45DFA3934282886D6174726F736B61','MKV'),
			array('00FFFFFFFFFFFFFFFFFFFF0000020001','MDF'),
			array('454C49544520436F6D6D616E64657220','CDR'),
			array('3C3F786D6C2076657273696F6E3D','MANIFEST'),
			array('44656C69766572792D646174653A','EML'),
			array('0000020006040600080000000000','WK1'),
			array('424547494E3A56434152440D0A','VCF'),
			array('436174616C6F6720332E303000','CTF'),
			array('0902060000001000B9045C00','XLS'),
			array('000000186674797033677035','MP4'),
			array('0904060000001000F6055C00','XLS'),
			array('464158434F5645522D564552','CPE'),
			array('3C4D616B657246696C6520','FM'),
			array('0000002066747970336770','3GG/3GP/3G2'),
			array('414F4C2046656564626167','BAG'),
			array('233F52414449414E43450A','HDR'),
			array('0000001466747970336770','3GG/3GP/3G2'),
			array('43232B44A4434DA5486472','RTD'),
			array('5374616E64617264204A','MDB/MDA/MDE/MDT'),
			array('4B47425F61726368202D','KGB'),
			array('28546869732066696C65','HQX'),
			array('40404020000040404040','ENL'),
			array('000100080001000101','IMG'),
			array('2E524D460000001200','RA'),
			array('3E000300FEFF090006','WB3'),
			array('46726F6D203F3F3F','EML'),
			array('4746315041544348','PAT'),
			array('1A00000300001100','NSF'),
			array('0000010001002020','ICO'),
			array('00001A0007800100','FM3'),
			array('300000004C664C65','EVT'),
			array('CFAD12FEC5FD746F','DBX'),
			array('3C21646F63747970','DCI'),
			array('4C00000001140200','LNK'),
			array('0E4E65726F49534F','NRI'),
			array('03000000C466C456','EVT'),
			array('0100000058000000','EMF'),
			array('4D47582069747064','DS4'),
			array('213C617263683E0A','LIB'),
			array('3C21454E54495459','DTD'),
			array('0764743264647464','DTD'),
			array('D0CF11E0A1B11AE1','DOC/DOT/XLS/XLT/XLA/PPT/APR/PPA/PPS/POT/MSI/SDW/DB'),
			array('49544F4C49544C53','LIT'),
			array('7F454C4601010100','ELF'),
			array('31BE000000AB0000','DOC'),
			array('456C6646696C6500','EVTX'),
			array('4D535F564F494345','MSV/CDR/DVF'),
			array('4D5A900003000000','FLT/AX/API'),
			array('414F4C564D313030','ORG/PFC'),
			array('4350543746494C45','CPT'),
			array('3A56455253494F4E','SLE'),
			array('00001A0000100400','WK3'),
			array('5245474544495434','REG'),
			array('504B3030504B0304','ZIP'),
			array('4D53465402000100','TLB'),
			array('0908100000060500','XLS'),
			array('0300000041505052','ADX'),
			array('1100000053434341','PF'),
			array('0000020001002020','CUR'),
			array('001E849000000000','SNM'),
			array('424F4F4B4D4F4249','PRC'),
			array('00001A0002100400','WK4/WK5'),
			array('414F4C494E444558','ABI'),
			array('46726F6D202020','EML'),
			array('255044462D312E','PDF'),
			array('4A47030E000000','ART'),
			array('4C000000011402','LNK'),
			array('49443303000000','KOZ'),
			array('4A47040E000000','ART'),
			array('57415645666D74','WAV'),
			array('424C4932323351','BIN'),
			array('576F726450726F','LWP'),
			array('53747566664974','SIT'),
			array('43505446494C45','CPT'),
			array('43525553482076','CRU'),
			array('00001A00051004','123'),
			array('00004D4D585052','QXD'),
			array('00004949585052','QXD'),
			array('1234567890FF','DOC'),
			array('458600000600','QBB'),
			array('4D444D5093A7','DMP/HDMP'),
			array('1A0000030000','NSF/NTF'),
			array('010009000003','WMF'),
			array('4A4152435300','JAR'),
			array('0000FFFFFFFF','HLP'),
			array('1A0000040000','NSF'),
			array('377ABCAF271C','7Z'),
			array('4D4D4D440000','MMF'),
			array('46726F6D3A20','EML'),
			array('303730373037','TAR/CPIO'),
			array('3C68746D6C3E','HTM/HTML'),
			array('01DA01010003','RGB'),
			array('3C48544D4C3E','HTM/HTML'),
			array('434246494C45','CBD'),
			array('414F4C494458','IND'),
			array('3C21444F4354','HTM/HTML'),
			array('01FF02040302','DRW'),
			array('20006800200','FMT'),
			array('68746D6C3E','HTML'),
			array('0000010000','ICO'),
			array('3C3F786D6C','XML'),
			array('0000020000','TGA'),
			array('2E7261FD00','RA'),
			array('7B5C727466','RTF'),
			array('4D494C4553','MLS'),
			array('4D56323134','MLS'),
			array('0100000001','PIC'),
			array('4848474231','SH3'),
			array('4D41723000','MAR'),
			array('2D6C68352D','LHA'),
			array('2000604060','WK1/WKS'),
			array('0000100000','TGA'),
			array('4344303031','ISO'),
			array('4D41523100','MAR'),
			array('2321414D52','AMR'),
			array('414F4C4442','ABY/IDX'),
			array('464F524D00','AIFF'),
			array('4352555348','CRU/CRUSH'),
			array('005C41B1FF','ENC'),
			array('5B7665725D','AMI'),
			array('3A42617365','CNT'),
			array('0A020101','PCX'),
			array('4D534346','SNP/CAB/PPZ'),
			array('47504154','PAT'),
			array('0A030101','PCX'),
			array('00000100','SPL'),
			array('00014244','DBA'),
			array('0A050108','PCX'),
			array('25504446','PDF/FDF'),
			array('0A050101','PCX'),
			array('414D594F','SYW'),
			array('2E524D46','RM/RMVB'),
			array('000001B3','MPG/MPEG'),
			array('D0CF11E0','DOT/PPT/XLA/PPA/PPS/POT/MSI/SDW/DB/XLS'),
			array('4D4D002B','TIF/TIFF'),
			array('6D646174','MOV/QT'),
			array('000001BA','MPG/VOB'),
			array('49492A00','TIF/TIFF'),
			array('3ADE68B1','DCX'),
			array('49536328','CAB/HDR'),
			array('444D5321','DMS'),
			array('0D444F43','DOC'),
			array('7FFE340A','DOC'),
			array('CFAD12FE','DBX'),
			array('44424648','DB'),
			array('46454446','SBV'),
			array('03000000','QPH'),
			array('AC9EBD8F','QDF'),
			array('E3828596','PWL'),
			array('49545346','CHI/CHM'),
			array('2E7261FD','RA/RAM'),
			array('434F4D2B','CLB'),
			array('EDABEEDB','RPM'),
			array('52617221','RAR'),
			array('434D5831','CLB'),
			array('2142444E','PST'),
			array('7E424B00','PSP'),
			array('43524547','DAT'),
			array('53495421','SIT'),
			array('6D6F6F76','MOV'),
			array('4B490000','SHD'),
			array('006E1EF0','PPT'),
			array('4C4E0200','HLP/GID'),
			array('0F00E803','PPT'),
			array('53520100','SLY/SRT/SLT'),
			array('4D4D002A','TIF/TIFF'),
			array('41433130','DWG'),
			array('4D415243','MAR'),
			array('0E574B53','WKS'),
			array('52494646','ANI'),
			array('FF575043','WPD/WP'),
			array('47494638','GIF'),
			array('2E524543','IVR'),
			array('3F5F0300','GID/HLP/LHP'),
			array('1A350100','ETH'),
			array('4C000000','LNK'),
			array('D7CDC69A','WMF'),
			array('25215053','EPS'),
			array('5F27A889','JAR'),
			array('01000900','WMF'),
			array('02000900','WMF'),
			array('FF575047','WPG'),
			array('464F524D','IFF'),
			array('57415645','WAV'),
			array('4D546864','MID/MIDI'),
			array('504B3030','ZIP'),
			array('91334846','HAP'),
			array('4D563243','MLS'),
			array('00014241','ABA'),
			array('4D4C5357','MLS'),
			array('41564920','AVI'),
			array('00000200','WB2'),
			array('02647373','DSS'),
			array('5A4F4F20','ZOO'),
			array('2E736E64','AU'),
			array('504B0304','ZIP/JAR/ZIPX'),
			array('464C5601','FLV'),
			array('010F0000','MDF'),
			array('3C3F78','XML/MSC'),
			array('805343','SCM'),
			array('5B436C','CCD'),
			array('2A7665','SCH'),
			array('495453','CHM'),
			array('7B5C72','RTF'),
			array('504B03','ZIP'),
			array('1F9D8C','Z'),
			array('005001','XMV'),
			array('FFFE3C','XSL'),
			array('444F53','ADF'),
			array('FFFFFF','SUB'),
			array('492049','TIF/TIFF'),
			array('000002','TGA/TAG'),
			array('425A68','BZ/BZ2/TB2/TBZ2/TAR.BZ2'),
			array('1F9D90','TAR.Z'),
			array('49492A','TIF/TIFF'),
			array('524946','WAV'),
			array('554641','UFA'),
			array('000100','TTF/TST/DDB'),
			array('4D4D2A','TIF/TIFF'),
			array('60EA27','ARJ'),
			array('495363','CAB'),
			array('00FFFF','SMD/MDF/IMG'),
			array('3026B2','WMV/WMA'),
			array('414376','SLE'),
			array('31BE00','WRI'),
			array('584245','XBE'),
			array('202020','BAS'),
			array('465753','SWF'),
			array('435753','SWF'),
			array('D0CF11','XLS/MAX/PPT'),
			array('384250','PSD'),
			array('414331','DWG'),
			array('445644','DVR/IFO'),
			array('455646','ENN'),
			array('234558','M3U'),
			array('526563','EML/PPC'),
			array('2A5052','ECO'),
			array('4D4544','MDS'),
			array('4D5A16','DRV'),
			array('FFFB50','MP3'),
			array('000001','MPA'),
			array('494433','MP3'),
			array('000077','MOV'),
			array('4D5A50','DPL'),
			array('2E524D','RM'),
			array('C5D0D3','EPS'),
			array('4C0000','LNK'),
			array('7B5072','GTD'),
			array('FFD8FF','JPG/JPEG'),
			array('475832','GX2'),
			array('3C2144','HTM'),
			array('3F5F03','HLP/LHP'),
			array('1F8B08','GZ/TGZ'),
			array('87F53E','GBC'),
			array('434841','FNT'),
			array('0011AF','FLI'),
			array('2A2420','LIB'),
			array('4D5A90','EXE/DLL/OCX/OLB/IMM/IME'),
			array('2D6C68','LHA/LZH'),
			array('42494C','LDB'),
			array('000101','FLT'),
			array('4E4553','NES'),
			array('00000F','MOV'),
			array('484802','PDG'),
			array('526172','RAR'),
			array('4D5AEE','COM'),
			array('0A0501','PCS'),
			array('5B4144','PBK'),
			array('17A150','PCB'),
			array('000007','PJT'),
			array('24536F','PLL'),
			array('5B5769','CPX'),
			array('E93B03','COM'),
			array('234445','PRG'),
			array('89504E','PNG'),
			array('060500','RAW'),
			array('255044','PDF'),
			array('1A0000','NTF'),
			array('C22020','NLS'),
			array('0110','TR1'),
			array('2112','AIN'),
			array('0CED','MP'),
			array('1D7D','WS'),
			array('32BE','WRI'),
			array('424D','BMP/DIB'),
			array('4C01','OBJ'),
			array('4D5A','FON/CPL/ACM/AX/VXD/386/COM/DLL/DRV/EXE/PIF/QTS/QTX/SYS/OLB/VBX/OCX/FLT/SCR/LRC/X32'),
			array('1F9D','Z'),
			array('1F8B','GZ/TAR/TGZ'),
			array('4D56','DSN'),
			array('31BE','WRI'),
			array('1A0B','PAK'),
			array('60EA','ARJ'),
			array('4550','MDI'),
			array('00BF','SOL'),
			array('2320','MSI'),
			array('3C','XDR/ASX'),
			array('30','CAT'),
			array('4','DB4'),
			array('7','DRW'),
			array('3','DB3/DAT'),
			array('8','DB')
		);
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