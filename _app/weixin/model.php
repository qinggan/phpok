<?php
/**
 * 模型内容信息_集成微信所有接口功能，包括公众号（mp），开放平台（op），小程序（ap）等相关服务
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年11月28日 11时26分
**/
namespace phpok\app\model\weixin;
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

	public function config_all()
	{
		return $this->config();
	}
	
	public function config_one($id='')
	{
		if(!$id){
			return false;
		}
		return $this->config($id);
	}

	public function config_save($data)
	{
		$this->lib('file')->vi($data,$this->dir_data.'wxconfig.php','config');
		return true;
	}

	/**
	 * 订阅参数保存
	**/
	public function config_subscribe($data='')
	{
		$file = $this->dir_data.'weixin_subscribe_config.php';
		if($data && is_array($data)){
			$this->lib('file')->vi($data,$file,'config');
			return true;
		}
		if(file_exists($file)){
			$config = array();
			include($file);
			return $config;
		}
		return false;
	}

	public function config_welcome($info='')
	{
		$file = $this->dir_data.'weixin_welcome.php';
		if($info){
			if(is_string($info)){
				$this->lib('file')->vi($info,$file);
				return true;
			}
			$this->lib('file')->rm($file);
			return true;
		}
		$welcome = '';
		if(file_exists($file)){
			$welcome = $this->lib('file')->cat($file);
		}
		return $welcome;
	}

	public function ip_list()
	{
		$list = array();
		$list[] = 'api.weixin.qq.com';
		$list[] = 'mp.weixin.qq.com';
		$list[] = 'open.weixin.qq.com';

		$data = $this->config_one('ip');
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
		$data = $this->config_one($ip);
		if(!$data){
			return false;
		}
		$m = 'ok'.md5($domain);
		if($data[$m]){
			return $data[$m];
		}
		return false;
	}

	public function mini_app_config()
	{
		if(!is_file($this->dir_data.'wxappconfig.php')){
			return false;
		}
		include_once($this->dir_data.'wxappconfig.php');
		return $wxconfig;
	}

	public function mini_app_save($data)
	{
		$this->lib('file')->vi($data,$this->dir_data.'wxappconfig.php','wxconfig');
		return true;
	}

	/**
	 * 通过 unionId 取得用户ID
	**/
	public function unionid2uid($unionid='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."weixin_user WHERE unionid='".$unionid."'";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$uid = 0;
		foreach($tmplist as $key=>$value){
			if($value['user_id']){
				$uid = $value['user_id'];
				break;
			}
		}
		return $uid;
	}

	/**
	 * 删除用户
	 * @参数 $id 支持多个ID，用英文逗号隔开
	**/
	public function user_delete($id)
	{
		if(!$id){
			return false;
		}
		if(is_array($id)){
			$id = implode(",",$id);
		}
		$sql = "DELETE FROM ".$this->db->prefix."weixin_user WHERE id IN(".$id.")";
		return $this->db->query($sql);
	}

	/**
	 * 登记微信用户最后一次登录时间
	**/
	public function user_lastlogin($openid='')
	{
		if(!$openid){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."weixin_user SET lastlogin='".$this->time."' WHERE openid='".$openid."'";
		$this->db->query($sql);
		$tmp = $this->user_one($openid);
		$this->model('log')->save('微信用户 '.$tmp['nickname'].' 登录成功');
		return true;
	}

	/**
	 * 绑定用户
	**/
	public function user_lock($id,$uid)
	{
		if(!$id || !$uid){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."weixin_user SET user_id='".$uid."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function user_one($openid='',$source='')
	{
		if(!$openid){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."weixin_user WHERE openid='".$openid."'";
		if($source){
			$sql .= " AND source='".$source."'";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 保存微信用户信息
	**/
	public function user_save($data)
	{
		if(!$data || !is_array($data) || !$data['openid']){
			return false;
		}
		$data['lastlogin'] = $this->time;
		$einfo = $data['source'] ? $data['source'] : '';
		$chk = $this->user_one($data['openid'],$einfo);
		if(!$chk){
			return $this->db->insert($data,'weixin_user');
		}
		$obj = $this->db->update($data,'weixin_user',array('id'=>$chk['id']));
		if($obj){
			return $chk['id'];
		}
		return false;
	}

	/**
	 * 解除用户关联
	**/
	public function user_unlock($id=0)
	{
		if(!$id){
			return false;
		}
		if(is_array($id)){
			$id = implode(",",$id);
		}
		$sql = "UPDATE ".$this->db->prefix."weixin_user SET user_id=0 WHERE id IN(".$id.")";
		$this->db->query($sql);
	}

	public function user_update($data,$id=0)
	{
		if(!$data || !is_array($data) || !$id){
			return false;
		}
		$this->db->update($data,'weixin_user',array('id'=>$id));
	}

	/**
	 * 更新 openid 与 用户ID 的关联
	**/
	public function user_update_uid($openid='',$user_id=0)
	{
		if(!$openid || !$user_id){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."weixin_user SET user_id='".$user_id."' WHERE openid='".$openid."'";
		return $this->db->query($sql);
	}
}
