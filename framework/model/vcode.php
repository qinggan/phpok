<?php
/**
 * 验证码处理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年10月27日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class vcode_model_base extends phpok_model
{
	private $type = 'sms';
	private $error_info = '';
	public function __construct()
	{
		parent::model();
	}

	public function error_info($info='')
	{
		if($info && $info !=''){
			$this->error_info = $info;
		}
		return $this->error_info;
	}

	public function error_reset()
	{
		$this->error_info = '';
		return true;
	}

	/**
	 * 设置验证码的类型
	**/
	public function type($val='')
	{
		if($val && $val != ''){
			$this->type = $val;
		}
		return $this->type;
	}

	/**
	 * 创建验证码
	 * @参数 $type 验证码的类型
	 * @参数 $length 验证码的长度
	 * @参数 
	**/
	public function create($type='',$length=4)
	{
		$this->error_reset();
		if($type && $type != '' && !is_numeric($type)){
			$this->type($type);
		}
		if($type && is_numeric($type) && intval($type)){
			$length = $type;
		}
		$data = $this->session->val('verification_code');
		if($data && is_array($data) && $data['type'] == $this->type){
			if( ($data['time'] + 60) > $this->time){
				$this->error_info(P_Lang('禁止频繁发送验证码，请于一分钟后请求'));
				return false;
			}
		}
		$data = array('time'=>$this->time,'count'=>0,'type'=>$this->type);
		$type = $this->type == 'sms' ? 'number' : 'all';
		$data['code'] = $this->code($length,$type);
		$this->session->assign('verification_code',$data);
		return $data;
	}

	/**
	 * 检验验证码
	 * @参数 $code 验证码值
	 * @返回 布尔值 true 或 false 
	**/
	public function check($code='')
	{
		$this->error_reset();
		if(!$code){
			$this->error_info(P_Lang('验证码不能为空'));
			return false;
		}
		$data = $this->session->val('verification_code');
		if(!$data || !is_array($data)){
			$this->error_info(P_Lang('服务器没有找到匹配数据'));
			return false;
		}
		if($data['code'] != $code){
			$this->error_info(P_Lang('验证码不匹配'));
			//更新验证码错误次数
			$data['count'] = $data['count'] + 1;
			$this->session->assign('verification_code',$data);
			return false;
		}
		if($data['count'] >= 5){
			$this->error_info(P_Lang('验证码错误次数超过5次，请重新获取验证码'));
			return false;
		}
		$longtime = $this->type == 'sms' ? 600 : 1800;
		if(($data['time'] + $longtime) < $this->time){
			$this->error_info(P_Lang('验证码已过期，请重新获取验证码'));
			return false;
		}
		return true;
	}

	/**
	 * 销毁验证码
	**/
	public function delete()
	{
		$this->error_reset();
		$this->session->unassign('verification_code');
		return true;
	}

	/**
	 * 验证码格式
	 * @参数 $length 长度
	 * @参数 $type 类型，支持all字母+数字，number存数字
	 * @返回 字符串 
	**/
	private function code($length=6,$type="all")
	{
		$a = 'ABCDEFGHJKLMNPQRSTUVWXY3456789';
		if($type == 'number'){
			$a = '0123456789';
		}
		$maxlength = strlen($a)-1;
		$rand_str = '';
		for($i=0;$i<$length;++$i){
			$rand_str .= $a[rand(0,$maxlength)];
		}
		return $rand_str;
	}
}
