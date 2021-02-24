<?php
/**
 * 附件下载管理
 * @package phpok\www
 * @author qinggan <admin@phpok.com>
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @homepage http://www.phpok.com
 * @version 4.x
 * @license http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @update 2016年07月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class download_control extends phpok_control
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 下载触发页
	 * @参数 file，要下载的文件，该参数要求相对文件路径
	 * @参数 id，要下载的文件ID，id和file各选一个，只要有一个有值即可
	 * @参数 back，当附件不存在或报错时，返回页地址
	**/
	public function index_f()
	{
		$file = $this->get('file');
		$id = $this->get('id','int');
		$back = $this->get('back');
		if(!$back){
			$back = $this->lib('server')->referer();
		}
		if(!$back){
			$back = $this->config['url'];
		}
		if($back){
			$back = $this->format($back);
		}
		if(!$id && !$file){
			$this->error(P_Lang('未指定附件ID或附件文件'),$back);
		}
		if($file){
			$rs = $this->model('res')->get_one_filename($file,false);
		}else{
			$rs = $this->model('res')->get_one($id);
		}
		if(!$rs){
			$this->error(P_Lang('附件不存在'),$back);
		}
		if(!$rs['filename']){
			$this->error(P_Lang('附件不存在'),$back);
		}
		$title = $this->get('title');
		if(!$title){
			$title = $rs['title'];
		}
		//更新下载次数
		$download = $rs['download'] + 1;
		$this->model('res')->save(array('download'=>$download),$rs['id']);
		$this->lib('file')->download($rs['filename'],$title);
		exit;
	}
}