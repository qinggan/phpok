<?php
/**
 * 七牛云对象存储接口
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月13日
**/
include_once(__DIR__ .'/autoload.php');

use Qiniu\Auth;

class qiniu_lib
{
	private $ak;
	private $sk;
	private $bucket;
	private $url;
	private $params = array();
	
	public function __construct()
	{
		//
	}

	public function ak($access_key='')
	{
		if($access_key){
			$this->ak = $access_key;
		}
		return $this->ak;
	}

	public function sk($secret_key='')
	{
		if($secret_key){
			$this->sk = $secret_key;
		}
		return $this->sk;
	}

	public function bucket($bucket='')
	{
		if($bucket){
			$this->bucket = $bucket;
		}
		return $this->bucket;
	}

	public function url($url)
	{
		if($bucket){
			$this->url = $bucket;
		}
		return $this->url;
	}

	public function set_param($key,$value='')
	{
		if($value != ''){
			$this->params[$key] = $value;
		}
		return true;
	}

	public function token()
	{
		$auth = new Auth($this->ak, $this->sk);
		$policy = array(
			'callbackUrl' => $this->url,
			'callbackBody' => json_encode($this->params)
		);
		return $auth->uploadToken($this->bucket, null, 3600, $policy);
	}

	/**
	 * 删除文件
	 * @参数 $id 就是对应七牛传过来的 hash和key中的key值
	**/
	public function delete_file($id)
	{
		$auth = new Auth($this->ak, $this->sk);
		$config = new \Qiniu\Config();
		$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
		return $bucketManager->delete($this->bucket, $id);
	}
}