<?php
/**
 * 微信小程序配置
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年06月04日
**/
namespace phpok\app\model\wxappconfig;

class model extends \phpok_model
{
	public function __construct()
	{
		parent::model();
	}
	
	public function get_one()
	{
		if(!is_file($this->dir_data.'wxappconfig.php')){
			return false;
		}
		include_once($this->dir_data.'wxappconfig.php');
		return $wxconfig;
	}

	public function mini_app_config()
	{
		//
	}
	
	public function save($data)
	{
		$this->lib('file')->vi($data,$this->dir_data.'wxappconfig.php','wxconfig');
		return true;
	}
	
	//取得微信用户信息
	public function get_user($openid)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."weixin_user WHERE openid='".$openid."'";
		$rs = $this->db->get_one($sql);
		if($rs){
			return $rs;
		}
		return false;
	}

	public function user_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."weixin_user WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function update_save($data,$id)
	{
		return $this->db->update_array($data,'weixin_user',array('id'=>$id));
	}

	public function insert_save($data)
	{
		return $this->db->insert_array($data,'weixin_user');
	}
}