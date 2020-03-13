<?php
/**
 * 接入节点_用于过滤敏感的，粗爆的字词，一行一个，用户提交表单数据时直接报错
 * @作者 锟铻科技
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年09月04日 15时50分
**/
namespace phpok\app\dirtywords;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class nodes_phpok extends \_init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function PHPOK_post_save()
	{
		$act = $this->model('dirtywords')->check();
		if(!$act){
			$word = $this->model('dirtywords')->error_word();
			$this->json(P_Lang('系统限字符：{dirtywords}',array('dirtywords'=>$word)));
		}
		return true;
	}

	public function PHPOK_post_ok()
	{
		$act = $this->model('dirtywords')->check();
		if(!$act){
			$word = $this->model('dirtywords')->error_word();
			$this->error(P_Lang('系统限字符：{dirtywords}',array('dirtywords'=>$word)));
		}
		return true;
	}

	/**
	 * 内容节点格式化
	 */
	public function PHPOK_arc()
	{
		$arc = $this->data('arc');
		if(!$arc){
			return false;
		}
		$info = $this->model('dirtywords')->read();
		if(!$info){
			return false;
		}
		$list = explode("\n",trim($info));
		$arc['title'] = str_replace($list,'[**]',$arc['title']);
		if($arc['content']){
			$arc['content'] = str_replace($list,'[**]',$arc['content']);
		}
		$this->data('arc',$arc);
		return true;
	}
}
