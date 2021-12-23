<?php
/**
 * 后台管理_针对社交信息增加的一些服务，如关注，粉丝，黑名单等功能
 * @作者 phpok.com <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @许可 www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年07月16日 10时13分
**/
namespace phpok\app\control\social;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class admin_control extends \phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('social');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		$this->display('admin_index');
	}

	public function help_f()
	{
		$this->addcss('static/md-editor/editormd.css');
		$this->addjs('static/md-editor/lib/marked.min.js');
		$this->addjs('static/md-editor/lib/prettify.min.js');
		$this->addjs('static/md-editor/lib/raphael.min.js');
		$this->addjs('static/md-editor/lib/underscore.min.js');
		$this->addjs('static/md-editor/lib/sequence-diagram.min.js');
		$this->addjs('static/md-editor/lib/flowchart.min.js');
		$this->addjs('static/md-editor/lib/jquery.flowchart.min.js');
		$this->addjs('static/md-editor/editormd.min.js');
		$file = $this->dir_app.'social/admin-help.md';
		if(file_exists($file)){
			$content = file_get_contents($file);
			$this->assign('content',$content);
		}
		$this->display('admin-help');
	}
}
