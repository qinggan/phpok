<?php
/**
 * 引入Lib库
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html 
 * @时间 2021年3月1日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

/**
 * 初始化第三方类，如果第三方类继承该类，则可以直接使用一些变量，而无需再定位及初化，
 * 继承该类后可以直接使用下类属性：
 *     1. $this->dir_root，程序根目录
 *     2. $this->dir_phpok，程序框架目录
 *     3. $this->dir_data，程序数据保存目录
 *     4. $this->dir_cache，缓存目录
 *     5. $this->dir_extension，第三方扩展类根目录
 *     6. $this->db，连接数据库
 *     7. $this->cache，连接缓存库
 *     8. $this->session，连接SESSION
 *     9. $this->lib(库名)，连接另一个库
 *     10. $this->model(模块名)，模块名称
 *     11. $this->ctrl(控制器,AppID)，连接控制器，注意需要指定是后台，前台还是接口
 *     12. $this->control(控制器,AppID)，控制器别名
 *     13. $this->time，当前时间
**/
class _init_lib
{
	protected $dir_root;
	protected $dir_phpok;
	protected $dir_data;
	protected $dir_cache;
	protected $dir_extension;
	public function __construct()
	{
		$this->dir_root = $GLOBALS['app']->dir_root;
		$this->dir_phpok = $GLOBALS['app']->dir_phpok;
		$this->dir_data = $GLOBALS['app']->dir_data;
		$this->dir_cache = $GLOBALS['app']->dir_cache;
		$this->dir_extension = $GLOBALS['app']->dir_extension;
		$this->db = $GLOBALS['app']->db;
		$this->cache = $GLOBALS['app']->cache;
		$this->session = $GLOBALS['app']->session;
		$this->time = $GLOBALS['app']->time;
	}

	protected function control($name='',$appid='')
	{
		return $GLOBALS['app']->control($name,$appid);
	}

	protected function ctrl($name='',$appid='')
	{
		return $GLOBALS['app']->control($name,$appid);
	}

	protected function dir_root($dir='')
	{
		if($dir){
			$this->dir_root = $dir;
		}
		return $this->dir_root;
	}

	protected function dir_phpok($dir='')
	{
		if($dir){
			$this->dir_phpok = $dir;
		}
		return $this->dir_phpok;
	}

	protected function dir_data($dir='')
	{
		if($dir){
			$this->dir_data = $dir;
		}
		return $this->dir_data;
	}

	protected function dir_cache($dir='')
	{
		if($dir){
			$this->dir_cache = $dir;
		}
		return $this->dir_cache;
	}

	protected function dir_extension($dir='')
	{
		if($dir){
			$this->dir_extension = $dir;
		}
		return $this->dir_extension;
	}

	protected function lib($name='')
	{
		return $GLOBALS['app']->lib($name);
	}

	protected function model($name='')
	{
		return $GLOBALS['app']->model($name);
	}
}