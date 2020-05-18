<?php
/**
 * 模型内容信息_登记微信平台里所有会员，包括开放平台，公众平台及小程序平台
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年03月26日 03时25分
**/
namespace phpok\app\model\wxuser;
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

	/**
	 * 删除会员 openid 与 会员ID 的关联
	**/
	public function delete_user_id($openid='')
	{
		if(!$openid){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."wxuser SET user_id=0 WHERE openid='".$openid."'";
		return $this->db->query($sql);
	}

	public function get_one($openid='')
	{
		if(!$openid){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."wxuser WHERE openid='".$openid."'";
		return $this->db->get_one($sql);
	}

	public function lastlogin($openid='')
	{
		if(!$openid){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."wxuser SET lastlogin='".$this->time."' WHERE openid='".$openid."'";
		$this->db->query($sql);
		$tmp = $this->get_one($openid);
		$this->model('log')->save('微信会员 '.$tmp['nickname'].' 登录成功');
		return true;
	}

	public function save($data)
	{
		if(!$data || !is_array($data) || !$data['openid']){
			return false;
		}
		$chk = $this->get_one($data['openid']);
		if(!$chk){
			$data['lastlogin'] = $this->time;
			return $this->db->insert($data,'wxuser');
		}
		$openid = $data['openid'];
		unset($data['openid']);
		$this->db->update($data,'wxuser',array('openid'=>$openid));
		return true;
	}

	/**
	 * 通过 unionId 取得会员ID
	**/
	public function unionid2uid($unionid='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wxuser WHERE unionid='".$unionid."'";
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
	 * 更新 openid 与 会员ID 的关联
	**/
	public function update_user_id($openid='',$user_id=0)
	{
		if(!$openid || !$user_id){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."wxuser SET user_id='".$user_id."' WHERE openid='".$openid."'";
		return $this->db->query($sql);
	}

}
