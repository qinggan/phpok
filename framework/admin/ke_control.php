<?php
/***********************************************************
	Filename: {phpok}/admin/ke_control.php
	Note	: 基于KindEditor的附件管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-02-07 15:24
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ke_control extends phpok_control
{
	var $order = "name";
	function __construct()
	{
		parent::control();
		$this->model("gd");
	}

	function index_f()
	{
		$php_path = $this->dir_root;
		$php_url = '';
		//根目录路径，可以指定绝对路径，比如 /var/www/attached/
		$root_path = $php_path . 'res/';
		//根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
		$root_url = $php_url . 'res/';
		//图片扩展名
		$ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
		$path = $this->get("path");


		//根据path参数，设置各路径和URL
		if (!$path)
		{
			$current_path = realpath($root_path) . '/';
			$current_url = $root_url;
			$current_dir_path = '';
			$moveup_dir_path = '';
		}
		else
		{
			$current_path = realpath($root_path) . '/' . $path;
			$current_url = $root_url . $path;
			$current_dir_path = $path;
			$moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
		}
		echo realpath($root_path);
		$order = $this->get("order");
		if(!$order) $order = "name";
		$order = strtolower($order);
		$this->order = $order;

		//不允许使用..移动到上一级目录
		if (preg_match('/\.\./', $current_path))
		{
			echo 'Access is not allowed.';
			exit;
		}
		//最后一个字符不是/
		if (!preg_match('/\/$/', $current_path))
		{
			echo 'Parameter is not valid.';
			exit;
		}
		//目录不存在或不是目录
		if (!file_exists($current_path) || !is_dir($current_path))
		{
			echo 'Directory does not exist.';
			exit;
		}
		$rs = $this->model('gd')->get_editor_default();

		//遍历目录取得文件信息
		$file_list = array();
		if ($handle = opendir($current_path)) {
			$i = 0;
			while (false !== ($filename = readdir($handle)))
			{
				if ($filename{0} == '.') continue;
				$file = $current_path . $filename;
				if (is_dir($file))
				{
					$file_list[$i]['is_dir'] = true; //是否文件夹
					$file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
					$file_list[$i]['filesize'] = 0; //文件大小
					$file_list[$i]['is_photo'] = false; //是否图片
					$file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
				}
				else
				{
					$is_photo = in_array($file_ext, $ext_arr);
					if($is_photo)
					{
						$tmpname = basename($filename);
						if(substr($tmpname,0,strlen($rs["identifier"]))."_" != $rs["identifier"]."_")
						{
							continue;
						}
					}
					$file_list[$i]['is_dir'] = false;
					$file_list[$i]['has_file'] = false;
					$file_list[$i]['filesize'] = filesize($file);
					$file_list[$i]['dir_path'] = '';
					$file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
					$file_list[$i]['is_photo'] = $is_photo;
					$file_list[$i]['filetype'] = $file_ext;
				}
				$file_list[$i]['filename'] = $filename; //文件名，包含扩展名
				$file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
				$i++;
			}
			closedir($handle);
		}

		usort($file_list, array($this,"cmp_func"));

		$result = array();
		//相对于根目录的上一级目录
		$result['moveup_dir_path'] = $moveup_dir_path;
		//相对于根目录的当前目录
		$result['current_dir_path'] = $current_dir_path;
		//当前目录的URL
		$result['current_url'] = $current_url;
		//文件数
		$result['total_count'] = count($file_list);
		//文件列表数组
		$result['file_list'] = $file_list;

		//输出JSON字符串
		header('Content-type: application/json; charset=UTF-8');
		echo $this->lib('json')->encode($result);
		//echo $json->encode($result);
	}

		//排序
	function cmp_func($a, $b)
	{
		$order = $this->order;
		if ($a['is_dir'] && !$b['is_dir']) {
			return -1;
		} else if (!$a['is_dir'] && $b['is_dir']) {
			return 1;
		} else {
			if ($order == 'size') {
				if ($a['filesize'] > $b['filesize']) {
					return 1;
				} else if ($a['filesize'] < $b['filesize']) {
					return -1;
				} else {
					return 0;
				}
			} else if ($order == 'type') {
				return strcmp($a['filetype'], $b['filetype']);
			} else {
				return strcmp($a['filename'], $b['filename']);
			}
		}
	}

}
?>