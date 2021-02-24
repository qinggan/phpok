<?php
/**
 * 公共操作，不限前台，后台
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月12日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ajax_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 返回默认结果集信息
	 * @参数 file 文件名，不包含目录，仅限字母，数字
	**/
	public function index_f()
	{
		$filename = $this->get("file","system");
		if(!$filename){
			$this->error(P_Lang('目标文件不能为空'));
		}
		if($this->app_id == 'api'){
			$this->config('is_ajax',true);
		}
		$filelist = array();
		$filelist[] = $this->dir_phpok."ajax/".$this->app_id."_".$filename.".php";
		$filelist[] = $this->dir_root."phpinc/".$this->app_id."_".$filename.".php";
		$filelist[] = $this->dir_root."phpinc/".$filename.".php";
		$ajax_file = false;
		foreach($filelist as $key=>$value){
			if(file_exists($value)){
				$ajax_file = $value;
				break;
			}
		}
		if(!$ajax_file){
			$this->error(P_Lang("文件 {file} 不存在",array('file'=>$filename)));
		}
		include $ajax_file;
	}
}