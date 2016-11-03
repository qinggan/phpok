<?php
/**
 * Excel类，调用扩展PHPExcel
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年08月09日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class excel_lib
{
	private $app;

	/**
	 * 构造函数
	**/
	public function __construct()
	{
		$this->app = init_app();
		if(!file_exists($this->app->dir_root.'extension/phpexcel/PHPExcel.php')){
			if(!file_exists($this->app->dir_root.'extension/phpexcel/phpexcel.zip')){
				exit("Not Found PHPExcel Classes");
			}
			//执行解压
			$this->app->lib('phpzip')->unzip($this->app->dir_root.'extension/phpexcel/phpexcel.zip',$this->app->dir_root.'extension/phpexcel/');
			sleep(1);
		}
	}

	/**
	 * 创建一个Excel文件
	 * @参数 
	 * @返回 
	 * @更新时间 
	**/
	public function add($title='',$saveto='')
	{
		if(!$title){
			$title = $this->app->time;
		}
		if(!$saveto){
			$saveto = $this->app->dir_root.'/data/cache/'.$this->app->time.'.xls';
		}
	}
}