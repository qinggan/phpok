<?php
/**
 * Token 创建，修改，过期等操作
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html 
 * @时间 2022年1月11日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class token_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function action()
	{
		$token_id = ($this->config['token'] && $this->config['token']['id']) ? $this->config['token']['id'] : 'userToken';
		$token_time = ($this->config['token'] && $this->config['token']['time']) ? $this->config['token']['time'] : 7200;
		$token = isset($_SERVER[$token_id]) ? $_SERVER[$token_id] : (isset($_SERVER['HTTP_'.$token_id]) ? $_SERVER['HTTP_'.$token_id] : $this->get($token_id));
		if(!$token){
			return false;
		}
		if(!$this->base64_check($token)){
			return false;
		}
		$info = base64_decode($token);
		if(!$info){
			return false;
		}
		$config = $this->model('config')->get_all();
		if(!$config || !$config['private_key'] || !$config['public_key']){
			return false;
		}
		//进行解密处理
		$this->lib("token")->etype("public_key");
		$this->lib('token')->public_key($config['public_key']);
		$this->lib('token')->private_key($config['private_key']);
		$this->lib('token')->expiry($token_time);
		$data = $this->lib('token')->decode($info);
		if($data['id'] && $data['chk'] && $data['time']){
			$rs = $this->model('user')->get_one($data['id'],'id',false,false);
			$sign = md5($rs['id'].'-'.$rs['user'].'-'.$rs['regtime'].'-'.$data['time']);
			if($sign == $data['chk']){
				$this->model('user')->login($rs,false);
				return true;
			}
		}
		return true;
	}

	/**
	 * 取得用户有验证串是否一致，一致则自动登录
	 * @参数 $uid 用户ID 或 用户数组
	 * @参数 $sign 验证串
	**/
	public function check($uid,$time,$sign)
	{
		if(!$sign || !$uid){
			return false;
		}
		if(is_numeric($uid)){
			$rs = $this->model('user')->get_one($uid,'id',false,false);
		}else{
			$rs = $uid;
		}
		if(!$rs){
			return false;
		}
		$code = md5($rs['id'].'-'.$rs['user'].'-'.$rs['regtime'].'-'.$time);
		if(strtolower($code) == strtolower($sign)){
			return $this->model('user')->login($rs,false);
		}
		return false;
	}

	public function create($uid=0)
	{
		if(!$uid){
			return false;
		}
		if(is_numeric($uid)){
			$rs = $this->model('user')->get_one($uid,'id',false,false);
			if(!$rs){
				return false;
			}
		}else{
			$rs = $uid;
		}
		$config = $this->model('config')->get_all();
		if(!$config){
			return false;
		}
		if(!$config['chktype']){
			$config['chktype'] = 'rsa';
		}
		if($config['chktype'] == 'code' && !$config['api_code']){
			return false;
		}
		if($config['chktype'] == 'rsa' && (!$config['private_key'] || !$config['public_key'])){
			return false;
		}
		$token_time = ($this->config['token'] && $this->config['token']['time']) ? $this->config['token']['time'] : 7200;
		$data = array('expire_time'=>($token_time+$this->time));
		$tmp = array();
		$tmp['id'] = $rs['id'];
		$tmp['time'] = $this->time;
		$tmp['chk'] = md5($rs['id'].'-'.$rs['user'].'-'.$rs['regtime'].'-'.$tmp['time']);
		if($config['chktype'] == 'code'){
			$this->lib("token")->etype("api_code");
			$this->lib("token")->keyid($config['api_code']);
		}
		if($config['chktype'] == 'rsa'){
			$this->lib("token")->etype("public_key");
			$this->lib('token')->public_key($config['public_key']);
			$this->lib('token')->private_key($config['private_key']);
		}		
		//生成 Token
		$this->lib('token')->expiry($token_time);
		$data['token'] = $this->lib('token')->encode($tmp);
		//生成 refreshToken
		$day = ($this->config['token'] && $this->config['token']['refresh_day']) ? $this->config['token']['refresh_day'] : 30;
		$this->lib('token')->expiry($day*24*60*60);
		$data['refresh_token'] = $this->lib('token')->encode($tmp);
		$data['expire_day'] = $day*24*60*60 + $this->time;
		return $data;
	}

	public function base64_check($str)
	{
		if($str === base64_encode(base64_decode($str))){
			return true;
		}
		return false;
	}

	/**
	 * 保存数据
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update($data,'token',array('id'=>$id));
		}
		$this->db->insert($data,'token');
	}
}
