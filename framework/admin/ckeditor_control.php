<?php
/**
 * CKeditor 上传组件
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年10月12日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class ckeditor_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		//
	}

	public function upload_f()
	{
		//
	}

	/**
	 * 获取服务器上所有图片
	**/
	public function images_f()
	{
		$pageurl = $this->url('ckeditor','images');
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'];
		$offset = ($pageid - 1) * $psize;
		$condition = "ext IN ('gif','jpg','png','jpeg') ";
		$gd_rs = $this->model('gd')->get_editor_default();
		$keywords = $this->get('keywords');
		if($keywords){
			$condition .= " AND (filename LIKE '%".$keywords."%' OR title LIKE '%".$keywords."%') ";
		}
		$total = $this->model('res')->edit_pic_total($condition,$gd_rs);
		if($total){
			$rslist = $this->model('res')->edit_pic_list($condition,$offset,$psize,$gd_rs);
			if($rslist){
				$piclist = array();
				foreach($rslist as $key=>$value){
					$tmp = array('url'=>$value['filename'],'ico'=>$value['ico'],'mtime'=>$value['addtime'],'title'=>$value['title'],'id'=>$value['id']);
					if($value['attr']){
						$attr = is_string($value['attr']) ? unserialize($value['attr']) : $value['attr'];
						$tmp['width'] = $attr['width'];
						$tmp['height'] = $attr['height'];
					}
					$piclist[] = $tmp;
				}
				$this->assign('rslist',$piclist);
			}
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
			$this->assign("pageurl",$pageurl);
			$this->lib('form')->cssjs(array('form_type'=>'upload'));
			$this->addjs('js/webuploader/admin.upload.js');
		}
		$this->view($this->dir_phpok.'open/ckeditor_images.html','abs-file');
	}
}
