<?php
/**
 * 公共操作，不限前台，后台
 * @作者 qinggan <admin@phpok.com>
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

	/**
	 * 加载表单参数
	 * @参数 id 表单标识
	 * @参数 type 表单类型
	 * @参数 其他参数根据实际需要，在 form 文件中体现
	**/
	public function form_f()
	{
		$id = $this->get('id');
		$type = $this->get('type');
		if(!$id && !$type){
			$this->error(P_Lang('未指定标识或类型'));
		}
		$info = $this->lib('form')->ajax($id,$type);
		if(!$info){
			$this->error(P_Lang('没有找到内容'));
		}
		if(!$info['status']){
			$this->error($info['info']);
		}
		$this->success($info['info']);
	}
}