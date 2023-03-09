<?php
/**
 * 适应用简单的可视化编辑
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html
 * @时间 2023年3月1日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class blockhtml_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function cssjs()
	{

	}

	public function phpok_config()
	{
		$this->view($this->dir_phpok.'form/html/blockhtml_admin.html','abs-file');
	}

	public function phpok_format($rs,$appid="admin")
	{
		if($rs['content'] && is_string($rs['content'])){
			$rs['content'] = unserialize($rs['content']);
		}
		$this->assign('_rs',$rs);
		$this->assign('info',$rs['content']);
		$str = '';
		if($rs['tplfile'] && file_exists($this->dir_root.$rs['tplfile'])){
			$str = $this->lib('file')->cat($this->dir_root.$rs['tplfile']);
		}
		if($rs['codetpl']){
			$str = $rs['codetpl'];
		}
		//生成表单列表
		preg_match_all('/blockhtml\-type=\"([^\"]*?)\"([^>]*)>/is',$str,$matches);
		if(!$matches || !$matches[1]){
			$matches = array();
			$matches[1] = array();
			$matches[2] = array();
		}
		$list = array();
		foreach($matches[1] as $key=>$value){
			$tmp = $matches[2][$key];
			$data = array();
			$data['type'] = $value;
			if($tmp){
				preg_match_all('/blockhtml\-([a-zA-Z0-9\-\_]+)=\"([^\"]*?)\"/is',$tmp,$chks);
				if($chks && $chks[1] && $chks[2]){
					foreach($chks[1] as $k=>$v){
						$data[$v] = $chks[2][$k];
					}
				}
			}
			if($data['name']){
				$list[$data['name']] = $data;
			}
		}
		foreach($list as $key=>$value){
			$value['value'] = $rs['content'][$key];
			$list[$key] = $value;
		}
		$this->assign('bh_list',$list);
		return $this->fetch($this->dir_phpok."form/html/blockhtml_admin_tpl.html",'abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		$info = $this->get($rs['identifier']);
		if($info && is_array($info)){
			return serialize($info);
		}
		return ;
	}

	public function phpok_show($rs,$appid="admin")
	{
		if($appid == 'admin'){
			$content = array();
			if($rs['content'] && is_string($rs['content'])){
				$content = unserialize($rs['content']);
			}
			$html = '';
			foreach($content as $key=>$value){
				$html .= '<div>'.$value.'</div>';
			}
			return $html;
		}
		$content = array();
		if($rs['content'] && is_string($rs['content'])){
			$content = unserialize($rs['content']);
		}
		if(!$rs['outhtml']){
			return $content;
		}
		$str = '';
		if($rs['tplfile'] && file_exists($this->dir_root.$rs['tplfile'])){
			$str = $this->lib('file')->cat($this->dir_root.$rs['tplfile']);
		}
		if($rs['codetpl']){
			$str = $rs['codetpl'];
		}
		$str = str_replace('{$info.','{$_info.',$str);
		$this->assign('_info',$content);
		$info = $this->fetch($str,'content');
		//参除参数内容
		$info = preg_replace('/blockhtml\-[a-zA-Z0-9\-\_]+=\"[^\"]*\"/is','',$info);
		return $info;
	}
}