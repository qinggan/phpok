<?php
/**
 * 模型内容信息_用于配置微信各种参数信息
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年03月24日 20时22分
**/
namespace phpok\app\model\wxconfig;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class model extends \phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function config($id='')
	{
		if(file_exists($this->dir_data.'wxconfig.php')){
			include($this->dir_data.'wxconfig.php');
			if($id){
				if(isset($config[$id])){
					return $config[$id];
				}
				return false;
			}
			return $config;
		}
		return false;
	}

	public function get_all()
	{
		return $this->config();
	}

	public function get_one($id='')
	{
		if(!$id){
			return false;
		}
		return $this->config($id);
	}

	public function save($data)
	{
		$this->lib('file')->vi($data,$this->dir_data.'wxconfig.php','config');
		return true;
	}

	public function iplist()
	{
		$list = array();
		$list[] = 'api.weixin.qq.com';
		$list[] = 'mp.weixin.qq.com';


		
		$data = $this->get_one('ip');
		$rslist = array();
		foreach($list as $key=>$value){
			$m = 'ok'.md5($value);
			if($data && $data[$m]){
				$rslist[$m] = array('domain'=>$value,'ip'=>$data[$m]);
			}else{
				$rslist[$m] = array('domain'=>$value,'ip'=>'');
			}
		}
		return $rslist;
	}

	public function ip($domain='')
	{
		if(!$domain){
			return false;
		}
		$domain = strtolower($domain);
		$data = $this->get_one($ip);
		if(!$data){
			return false;
		}
		$m = 'ok'.md5($domain);
		if($data[$m]){
			return $data[$m];
		}
		return false;
	}
}
