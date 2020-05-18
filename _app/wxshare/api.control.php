<?php
/**
 * 接口应用_实现微信的分享模式，支持是否关注公众号
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年03月29日 16时34分
**/
namespace phpok\app\control\wxshare;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class api_control extends \phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function create_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非会员不能执行此操作'));
		}
		//判断二维码是否存在
		$qrfile = $this->dir_root.'res/qrcode_user/'.$this->session->val('user_id').'.png';
		$qr_create = false;
		if(!file_exists($qrfile) || (file_exists($qrfile) && filemtime($qrfile) < ($this->time - 2590000))){
			$qr_create = true;
		}
		//已存在，未过时则忽略创建，直接返回真识地址
		if(!$qr_create){
			$this->success('res/qrcode/'.$this->session->val('user_id').'.png');
		}
		$token = $this->lib('weixin')->access_token();
		$post_data = '{"expire_seconds": 2592000, "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$this->session->val('user_id').'"}}}';
		$this->lib('curl')->post($post_data);
		$ip = $this->model('wxconfig')->ip('api.weixin.qq.com');
		if($ip){
			$this->lib('curl')->host_ip($ip);
		}
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$token;
		$info = $this->lib('curl')->get_json($url);
		if(!$info){
			$this->error('获取数据失败');
		}
		if($info['errcode']){
			$this->error($info['errcode'].':'.$info['errmsg']);
		}
		$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".rawurlencode($info['ticket']);
		$ip = $this->model('wxconfig')->ip('mp.weixin.qq.com');
		if($ip){
			$this->lib('curl')->host_ip($ip);
		}
		$content = $this->lib('curl')->get_content($url);
		if(!$content){
			$this->error('二维码生成失败，请联系管理员');
		}
		$this->lib('file')->save_pic($qrfile,$content);
		//生成分享图
	}

	public function index_f()
	{
		//$info = "";
		//$this->error($info);
		$this->success();
	}
}
