<?php
/**
 * 
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年11月15日
**/
namespace phpok\app\control\wxappconfig;

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
		$this->popedom = appfile_popedom('wxappconfig');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		$rs = $this->model('wxappconfig')->get_one();
		$load_app_json = false;
		if(file_exists($this->dir_root.'wxapp/app.json')){
			$this->assign('update_wxapp_json',true);
			$info = $this->lib('file')->cat($this->dir_root.'wxapp/app.json');
			if($info){
				$load_app_json = true;
				$info = $this->lib('json')->decode($info);
				if($info && $info['window'] && $info['window']['navigationBarTitleText']){
					$rs['title'] = $info['window']['navigationBarTitleText'];
				}
				if($info && $info['window'] && $info['window']['navigationBarTextStyle']){
					$rs['top_txtcolor'] = $info['window']['navigationBarTextStyle'];
				}
				if($info && $info['window'] && $info['window']['navigationBarBackgroundColor']){
					$rs['top_bgcolor'] = $info['window']['navigationBarBackgroundColor'];
				}
				if($info && $info['window'] && $info['tabBar']['color']){
					$rs['text_color'] = $info['tabBar']['color'];
				}
				if($info && $info['window'] && $info['tabBar']['selectedColor']){
					$rs['text_color_highlight'] = $info['tabBar']['selectedColor'];
				}
				if($info && $info['window'] && $info['tabBar']['backgroundColor']){
					$rs['tab_bgcolor'] = $info['tabBar']['backgroundColor'];
				}
				if($info && $info['window'] && $info['tabBar']['borderStyle']){
					$rs['tab_bordercolor'] = $info['tabBar']['borderStyle'];
				}
			}
		}
		$text_color = form_edit('text_color',$rs['text_color'],'text','form_btn=color&ext_include_3=1');
		$this->assign('text_color',$text_color);
		$text_color_highlight = form_edit('text_color_highlight',$rs['text_color_highlight'],'text','form_btn=color&ext_include_3=1');
		$this->assign('text_color_highlight',$text_color_highlight);
		$tab_bgcolor = form_edit('tab_bgcolor',$rs['tab_bgcolor'],'text','form_btn=color&ext_include_3=1');
		$this->assign('tab_bgcolor',$tab_bgcolor);
		$top_bgcolor = form_edit('top_bgcolor',$rs['top_bgcolor'],'text','form_btn=color&ext_include_3=1');
		$this->assign('top_bgcolor',$top_bgcolor);
		$usercp_bgcolor = form_edit('usercp_bgcolor',$rs['usercp_bgcolor'],'text','form_btn=color&ext_include_3=1');
		$this->assign('usercp_bgcolor',$usercp_bgcolor);
		//usercp_txtcolor
		$usercp_txtcolor = form_edit('usercp_txtcolor',$rs['usercp_txtcolor'],'text','form_btn=color&ext_include_3=1');
		$this->assign('usercp_txtcolor',$usercp_txtcolor);

		if($load_app_json){
			$tmplist = $info['pages'];
			foreach($tmplist as $key=>$value){
				if($value == 'pages/index/index'){
					unset($tmplist[$key]);
					break;
				}
			}
			$this->assign('pagelist',$tmplist);
		}
		$this->assign('rs',$rs);
		$this->display('admin_index');
	}

	public function save_f()
	{
		$data = array();
		$data['wxapp_id'] = $this->get('wxapp_id');
		if(!$data['wxapp_id']){
			$this->error(P_Lang('小程序的AppID不能为空'));
		}
		$data['wxapp_secret'] = $this->get('wxapp_secret');
		if(!$data['wxapp_secret']){
			$this->error(P_Lang('小程序密钥不能为空'));
		}
		$data['title'] = $this->get('title');
		$data['top_bgcolor'] = $this->get('top_bgcolor');
		$data['top_txtcolor'] = $this->get('top_txtcolor');
		$data['text_color'] = $this->get('text_color');
		$data['text_color_highlight'] = $this->get('text_color_highlight');
		$data['tab_bgcolor'] = $this->get('tab_bgcolor');
		$data['tab_bordercolor'] = $this->get('tab_bordercolor');
		if(!$data['text_color']){
			$this->error(P_Lang('文本颜色代码不能为空'));
		}
		$data['usercp_bgcolor'] = $this->get('usercp_bgcolor');
		$data['usercp_bgimg'] = $this->get('usercp_bgimg');
		$data['usercp_txtcolor'] = $this->get('usercp_txtcolor');
		$this->model('wxappconfig')->save($data);
		$this->success();
	}

	private function update_wxapp_json($data)
	{
		if(!file_exists($this->dir_root.'wxapp/app.json')){
			return false;
		}
		$info = $this->lib('file')->cat($this->dir_root.'wxapp/app.json');
		if(!$info){
			return false;
		}
		$info = $this->lib('json')->decode($info);
		if(!$info){
			return false;
		}
		if($data['title']){
			$info['window']['navigationBarTitleText'] = $data['title'];
		}
		if($data['top_txtcolor']){
			$info['window']['navigationBarTextStyle'] = $data['top_txtcolor'];
		}
		if($data['top_bgcolor']){
			$info['window']['navigationBarBackgroundColor'] = $data['top_bgcolor'];
		}
		if($data['text_color']){
			$info['tabBar']['color'] = $data['text_color'];
		}
		if($data['text_color_highlight']){
			$info['tabBar']['selectedColor'] = $data['text_color_highlight'];
		}
		if($data['tab_bgcolor']){
			$info['tabBar']['backgroundColor'] = $data['tab_bgcolor'];
		}
		if($data['tab_bordercolor']){
			$info['tabBar']['borderStyle'] = $data['tab_bordercolor'];
		}
		$clear_url = $this->config['url'].'wxapp/';
		if($data['rslist'] && is_array($data['rslist'])){
			foreach($data['rslist'] as $key=>$value){
				if(!$value['title']){
					continue;
				}
				if($value['title']){
					$info['tabBar']['list'][$key]['text'] = $value['title'];
				}
				if($value['page']){
					$info['tabBar']['list'][$key]['pagePath'] = $value['page'];
				}
				if($value['thumb']){
					$tmp = str_replace($clear_url,'',$value['thumb']);
					if(file_exists($this->dir_root.$tmp)){
						$this->lib('file')->mv($this->dir_root.$tmp,$this->dir_root.'wxapp/images/server/'.$i.'.png');
						if(file_exists($this->dir_root.'wxapp/images/server/'.$i.'.png')){
							$info['tabBar']['list'][$key]['iconPath'] = 'images/server/'.$i.'.png';
						}
					}
				}
				if($value['thumb_selected']){
					$tmp = str_replace($clear_url,'',$value['thumb_selected']);
					if(file_exists($this->dir_root.$tmp)){
						$this->lib('file')->mv($this->dir_root.$tmp,$this->dir_root.'wxapp/images/server/'.$i.'_selected.png');
						if(file_exists($this->dir_root.'wxapp/images/server/'.$i.'_selected.png')){
							$info['tabBar']['list'][$key]['selectedIconPath'] = 'images/server/'.$i.'_selected.png';
						}
					}
				}
			}
		}
		$this->lib('file')->vim($this->lib('json')->encode($info,false,true),$this->dir_root.'wxapp/app.json');
		return true;
	}
}
