<?php
/**
 * 用户 Token 及 refreshToken
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html 
 * @时间 2022年1月7日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class token_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$this->refresh_f();
	}

	/**
	 * 通过 refresh 获取新的 Token
	**/
	public function refresh_f()
	{
		$token_id = ($this->config['token'] && $this->config['token']['refresh_id']) ? $this->config['token']['refresh_id'] : 'refreshToken';
		$day = ($this->config['token'] && $this->config['token']['refresh_day']) ? $this->config['token']['refresh_day'] : 30;
		$token = $this->get($token_id,'html'));
		if(!$token){
			$this->error(P_Lang('未指定 refreshToken 信息'));
		}
		if(!$this->model('token')->base64_check($token)){
			$this->error(P_Lang('不是合法的 Token 信息'));
		}
		$info = base64_decode($token);
		if(!$info){
			$this->error(P_Lang('Token 信息不存在'));
		}
		$api_code = $this->model('config')->get_one('api_code');
		if(!$api_code){
			$this->error(P_Lang('API 密钥未配置，不能使用 Token 功能'));
		}
		//对 token 数据进行解密
		$this->lib('token')->etype('api_code');
		$this->lib('token')->keyid($api_code);
		$this->lib('token')->expiry($day*24*60*60);
		$data = $this->lib('token')->decode($info);
		if($data['id'] && $data['chk'] && $data['time']){
			$this->model('token')->check($data['id'],$data['chk']);
		}
		$data = $this->model('token')->create($data['id']);
		$this->success($data);
	}
}
