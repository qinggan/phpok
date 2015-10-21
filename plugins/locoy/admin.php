<?php
/*****************************************************************************************
	文件： plugins/locoy/admin.php
	备注： 后台操作插件
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月16日 21时49分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_locoy extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->pid = $this->me['param']['pid'];
	}

	public function catelist()
	{
		if(!$this->pid){
			error("未指定项目ID");
		}
		$project = $this->model('project')->get_one($this->pid);
		if(!$project || !$project['module'] || !$project['cate']){
			error('项目不存在或异常或未绑定分类');
		}
		$catelist = $this->model('cate')->get_all($project["site_id"],1,$project["cate"]);
		if(!$catelist){
			error('分类信息不存在');
		}
		$catelist = $this->model('cate')->cate_option_list($catelist);
		if(!$catelist){
			error('分类信息不存在');
		}
		$html = '<select name="cate_id" id="cate_id">';
		foreach($catelist as $key=>$value){
			$html .= '<option value="'.$value['id'].'">'.$value['title'].'</option>';
		}
		$html.= '</select>';
		echo $html;
		exit;
	}

	public function save()
	{
		if(!$this->pid){
			error("未指定项目ID");
		}
		$project = $this->model('project')->get_one($this->pid);
		if(!$project || !$project['module'] || !$project['cate']){
			error('项目不存在或异常或未绑定分类');
		}
		$title = $this->get('title');
		if(!$title){
			error('未指定主题');
		}
		$cate_id = $this->get('cate_id','int');
		if(!$cate_id){
			error("未指定分类");
		}
		$dateline = $this->get('dateline','time');
		if(!$dateline){
			$dateline = $this->time;
		}
		$main = array('cate_id'=>$cate_id,'module_id'=>$project['module'],'site_id'=>$project['site_id']);
		$main['project_id'] = $project['id'];
		$main['title'] = $title;
		$main['dateline'] = $dateline;
		$main['status'] = $this->get('status','int');
		$main["hidden"] = $this->get("hidden","int");
		$main["hits"] = $this->get("hits","int");
		$main["sort"] = $this->get("sort","int");
		$main["seo_title"] = $this->get("seo_title");
		$main["seo_keywords"] = $this->get("seo_keywords");
		$main["seo_desc"] = $this->get("seo_desc");
		$tid = $this->model('list')->save($main);
		if(!$tid){
			error('内容保存失败');
		}
		//保存电商信息
		if($project['is_biz']){
			$biz = array('price'=>$this->get('price','float'),'currency_id'=>$project['currency_id']);
	 		$biz['weight'] = $this->get('weight','float');
	 		$biz['volume'] = $this->get('volume','float');
	 		$biz['unit'] = $this->get('unit');
	 		$biz['id'] = $tid;
	 		$this->model('list')->biz_save($biz);
	 		unset($biz);
		}
		//保存扩展分类
		$ext_cate = array($cate_id);
		$this->model('list')->save_ext_cate($tid,$ext_cate);
		//保存扩展数据
		$extlist = $this->model('module')->fields_all($project["module"]);
		$tmplist = array();
 		$tmplist["id"] = $tid;
 		$tmplist["site_id"] = $project["site_id"];
 		$tmplist["project_id"] = $this->pid;
 		$tmplist["cate_id"] = $cate_id;
 		if($extlist){
	 		foreach($extlist as $key=>$value){
		 		if($value['form_type'] == 'upload'){
			 		$info = $this->_upload_format($value);
			 		if(!$info){
				 		$info = '';
			 		}
		 		}else{
			 		$info = $this->get($value['identifier'],$value['format']);
		 		}
		 		$tmplist[$value['identifier']] = $info;
	 		}
 		}
		$this->model('list')->save_ext($tmplist,$project["module"]);
		error('数据发布成功','','ok');
	}

	private function _upload_format($rs)
	{
		$ext = $rs['ext'];
		if($ext && is_string($ext)){
			$ext = unserialize($ext);
		}
		$info = $this->get($rs['identifier'],'html_js');
		if(!$info){
			return false;
		}
		$multiple = ($ext && $ext['is_multiple']) ? true : false;
		$info = strtolower($info);
		if(strpos($info,'<img') !== false){
			$info = stripslashes($info);
			preg_match_all("/<img.+src=(\"|\'){0,1}(.+)(\"|\'| |>){1}/isU",$info,$matches);
			if(!$matches[0] || !is_array($matches[0])){
				return false;
			}
			$picurl = array();
			foreach($matches[0] AS $k=>$v){
				$mypic_url = str_replace('"',"",$matches[2][$k]);
				if(substr($mypic_url,-1) == "/"){
					$mypic_url = substr($mypic_url,0,-1);
				}
				$picurl[] = $mypic_url;
			}
			$picurl = array_unique($picurl);
			foreach($picurl as $key=>$value){
				$ext = substr($value,-3);
				if(!in_array($ext,array('jpg','png','gif'))){
					unset($picurl[$key]);
				}
			}
			reset($picurl);
		}else{
			$picurl = explode(";",$info);
		}
		if(!$picurl || !is_array($picurl)){
			return false;
		}
		$cate = $this->model('rescate')->get_default();
		if($multiple){
			$ids = array();
			foreach($picurl as $key=>$value){
				if(!file_exists($this->dir_root.$value)){
					continue;
				}
				$ids[] = $this->_gd_save($value,$cate);
			}
			if($ids && count($ids)>0){
				return implode(",",$ids);
			}else{
				return false;
			}
		}else{
			$ids = false;
			foreach($picurl as $key=>$value){
				if(!file_exists($this->dir_root.$value)){
					continue;
				}
				$ids = $this->_gd_save($value,$cate);
				break;
			}
			return $ids;
		}
	}

	private function _gd_save($file,$cate='')
	{
		$img_ext = getimagesize($this->dir_root.$file);
		$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
		$info = pathinfo($file);
		$ext = substr($basename,-3);
		$array = array();
		$array["cate_id"] = ($cate && is_array($cate)) ? $cate['id'] : 0;
		$array["folder"] = $info['dirname'].'/';
		$array["name"] = $info['basename'];
		$array["ext"] = substr($info['basename'],-3);
		$array["filename"] = $file;
		$array["addtime"] = $this->time;
		$array["title"] = $info['basename'];
		$array['admin_id'] = $_SESSION['admin_id'];
		$array["attr"] = serialize($my_ext);
		$id = $this->model('res')->save($array);
		if(!$id){
			return false;
		}
		$this->model('res')->gd_update($id);
		return $id;
	}

}

?>