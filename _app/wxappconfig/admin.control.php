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
		//菜单项
		$rslist = array();
		for($i=0;$i<5;$i++){
			$tmp = array('title'=>$rs['rslist'][$i]['title'],'thumb'=>$rs['rslist'][$i]['thumb']);
			$tmp['thumb_selected'] = $rs['rslist'][$i]['thumb_selected'];
			$tmp['param'] = $rs['rslist'][$i]['param'];
			if($load_app_json){
				$tmp['title'] = $info['tabBar']['list'][$i]['text'];
				$tmp['thumb'] = $this->config['url'].'wxapp/'.$info['tabBar']['list'][$i]['iconPath'];
				$tmp['thumb_selected'] = $this->config['url'].'wxapp/'.$info['tabBar']['list'][$i]['selectedIconPath'];
				$tmp['page'] = $info['tabBar']['list'][$i]['pagePath'];
			}
			$rslist[$i] = $tmp;
		}
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
		$list = array();
		foreach($rslist as $key=>$value){
			$tmplist = array();
			$html = form_edit('title_'.$key,$value['title'],'text');
			$tmp = array('title'=>P_Lang('栏目名称'),'html'=>$html,'note'=>P_Lang('导航文字不要超过4个中文字'));
			$tmplist[0] = $tmp;
			$html = form_edit('thumb_'.$key,$value['thumb'],'text','form_btn=image&form_style=width:345px');
			$tmp = array('title'=>P_Lang('默认图标'),'html'=>$html,'note'=>P_Lang('显示的图标，规格81x81'));
			$tmplist[1] = $tmp;
			$html = form_edit('thumb_selected_'.$key,$value['thumb_selected'],'text','form_btn=image&form_style=width:345px');
			$tmp = array('title'=>P_Lang('高亮图标'),'html'=>$html,'note'=>P_Lang('高亮当前标签用到的图标，规格81x81'));
			$tmplist[2] = $tmp;
			if($key>0){
				$html = form_edit('param_'.$key,$value['param'],'text');
				$note = P_Lang('article及list支持项目标识，about及contact支持文章的标识或ID');
				$tmp = array('title'=>P_Lang('参数'),'html'=>$html,'note'=>$note);
				$tmplist[3] = $tmp;
				if($load_app_json){
					$tmp = array('title'=>P_Lang('执行'),'html'=>'','note'=>P_Lang('选择要执行的文件'),'val'=>$value['page']);
					$tmplist[4] = $tmp;
				}
			}
			$list[$key] = $tmplist;
		}
		$this->assign('list',$list);
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
		$tmplist = array();
		$clear_url = $this->config['url'].'wxapp/';
		for($i=0;$i<5;$i++){
			$tmp = array();
			$tmp['title'] = $this->get('title_'.$i);
			$tmp['thumb'] = $this->get('thumb_'.$i);
			if($tmp['thumb']){
				$tmp['thumb'] = str_replace($clear_url,'',$tmp['thumb']);
			}
			$tmp['thumb_selected'] = $this->get('thumb_selected_'.$i);
			if($tmp['thumb_selected']){
				$tmp['thumb_selected'] = str_replace($clear_url,'',$tmp['thumb_selected']);
			}
			$tmp['param'] = $this->get('param_'.$i);
			$tmp['page'] = $this->get('page_'.$i);
			$tmplist[$i] = $tmp;
		}
		$data['rslist'] = $tmplist;
		$this->model('wxappconfig')->save($data);
		if(file_exists($this->dir_root.'wxapp/app.json')){
			$write_app_json = $this->get('write_app_json','int');
			if($write_app_json){
				$this->update_wxapp_json($data);
			}
		}
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
