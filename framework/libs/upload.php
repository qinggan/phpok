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

	public function __construct()
	{
		$this->dir_root = $GLOBALS['app']->dir_root;
	}

	//设置附件上传的目录
	//目录不存在，就自动创建，创建失败即就存到res/根目录下
	public function set_dir($dir="")
	{
		if(!$dir)
		{
			return false;
		}
		$root_num = strlen($this->dir_root);
		if(substr($dir,0,$root_num) == $this->dir_root)
		{
			$dir = substr($dir,$root_num);
		}
		if(!file_exists($this->dir_root.$dir))
		{
			$GLOBALS['app']->lib('file')->make($this->dir_root.$dir);
			if(!file_exists($this->dir_root.$dir))
			{
				$dir = 'res/';
			}
		}
		if(substr($dir,-1) != "/") $dir .= "/";
		if(substr($dir,0,1) == "/") $dir = substr($dir,1);
		if($dir)
		{
			$dir = str_replace("//","/",$dir);
		}
		$this->folder = $dir;
		return true;
	}

	//自定义设置要上传的附件类型
	public function set_type($type='')
	{
		if(!$type)
		{
			return false;
		}
		if(is_array($type))
		{
			$type = implode(",",$type);
		}
		$type = str_replace(array('*','.'),'',$type);
		$this->file_type = $type;
	}

	//设置分类
	public function set_cate($cateid=0)
	{
		$cate_rs = $GLOBALS['app']->model('res')->cate_one($cateid);
		if(!$cate_rs)
		{
			$cate_rs = array('id'=>0,'root'=>'res/','folder'=>'Y/md/');
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/")
		{
			$folder .= date($cate_rs["folder"],$GLOBALS['app']->time);
		}
		$this->set_dir($folder);
		$this->cateid = $cate_rs['id'];
	}

	//附件上传
	function upload($inputname,$cateid=0,$typelist='')
	{
		if(!$inputname)
		{
			return array('status'=>'error','content'=>P_Lang('未指定表单名称'));
		}
		if(!isset($_FILES[$inputname]))
		{
			return array('status'=>'error','content'=>P_Lang('没有指定上传的图片'));
		}
		if($_FILES[$inputname]["error"])
		{
			return array('status'=>'error','content'=>$_FILES[$inputname]["error"]);
		}
		if($cateid)
		{
			$this->set_cate($cateid);
		}
		if($typelist)
		{
			$this->set_type($typelist);
		}
		if(!is_uploaded_file($_FILES[$inputname]['tmp_name']))
		{
			return array('status'=>'error','content'=>P_Lang('没有找到临时文件'));
		}
		$file_info = $this->title_format($_FILES[$inputname]['name']);
		$filetype = $file_info['ext'];
		if(!$filetype || $filetype == 'unknown')
		{
			return array('status'=>'error','content'=>P_Lang('获取文件类型失败'));
		}
		$filetype = strtolower($filetype);
		if(!in_array($filetype,explode(",",$this->file_type)))
		{
			return array('status'=>'error','content'=>P_Lang('文件类型不符合系统要求'));
		}
		$filename = substr(md5(time().rand(0,9999)),9,16);
		$file = $this->dir_root.$this->folder.$filename.'.'.$filetype;
		if(move_uploaded_file($_FILES[$inputname]['tmp_name'],$file))
		{
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