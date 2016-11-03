<?php
/**
 * 附件上传操作
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年07月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class upload_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 附件上传，上传的表单ID固定用upfile
	 * @参数 cateid 分类ID，数值
	**/
	public function save_f()
	{
		$cateid = $this->get('cateid','int');
		$rs = $this->upload_base('upfile',$cateid);
		if(!$rs || $rs['status'] != 'ok'){
			$tip = $rs['error'] ? $rs['error'] : $rs['content'];
			$this->json($tip);
		}
		unset($rs['status']);
		$rs['uploadtime'] = date("Y-m-d H:i:s",$rs['addtime']); 
		$this->json($rs,true);
	}

	/**
	 * 接收ZIP包上传，主要用于更新及数据导入，上传的表单ID固定用upfile
	**/
	public function zip_f()
	{
		$rs = $this->lib('upload')->zipfile('upfile');
		if($rs['status'] != 'ok'){
			$this->json($rs['error']);
		}
		$this->json($rs['filename'],true);
	}


	/**
	 * 基础上传
	 * @参数 $input_name，表单ID，默认是upfile
	 * @参数 $cateid，附件保存到哪个分类下
	**/
	private function upload_base($input_name='upfile',$cateid=0)
	{
		$rs = $this->lib('upload')->getfile($input_name,$cateid);
		if($rs['status'] != 'ok'){
			return $rs;
		}
		$array = array();
		$array["cate_id"] = $rs['cate']['id'];
		$array["folder"] = $rs['folder'];
		$array["name"] = basename($rs['filename']);
		$array["ext"] = $rs['ext'];
		$array["filename"] = $rs['filename'];
		$array["addtime"] = $this->time;
		$array["title"] = $rs['title'];
		$array['admin_id'] = $_SESSION['admin_id'];
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($rs["ext"],$arraylist)){
			$img_ext = getimagesize($this->dir_root.$rs['filename']);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		$id = $this->model('res')->save($array);
		if(!$id){
			$this->lib('file')->rm($this->dir_root.$rs['filename']);
			return array('status'=>'error','error'=>P_Lang('图片存储失败'));
		}
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$rs["status"] = "ok";
		return $rs;
	}

	/**
	 * 附件替换式上传，新文件表单ID固定用upfile
	 * @参数 oldid，旧附件ID
	**/
	public function replace_f()
	{
		$id = $this->get("oldid",'int');
		if(!$id){
			$this->json(P_Lang('没有指定要替换的附件'));
		}
		$old_rs = $this->model('res')->get_one($id);
		if(!$old_rs){
			$this->json(P_Lang('资源不存在'));
		}
		$rs = $this->lib('upload')->upload('upfile');
		if($rs["status"] != "ok"){
			$this->json(P_Lang('附件上传失败'));
		}
		$arraylist = array("jpg","gif","png","jpeg");
		$my_ext = array();
		if(in_array($rs["ext"],$arraylist)){
			$img_ext = getimagesize($rs["filename"]);
			$my_ext["width"] = $img_ext[0];
			$my_ext["height"] = $img_ext[1];
		}
		$this->lib('file')->mv($rs["filename"],$old_rs["filename"]);
		$tmp = array("addtime"=>$this->time);
		$tmp["attr"] = serialize($my_ext);
		$this->model('res')->save($tmp,$id);
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$this->json($rs,true);
	}

	/**
	 * 缩略图列表
	 * @参数 id 多个附件ID用英文逗号隔开
	 * @返回 Json字串
	**/
	public function thumbshow_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		$newlist = array();
		foreach($list AS $key=>$value){
			$value = intval($value);
			if($value){
				$newlist[] = $value;
			}
		}
		$id = implode(",",$newlist);
		if(!$id){
			$this->json(P_Lang('请传递正确的附件ID'));
		}
		$rslist = $this->model("res")->get_list_from_id($id);
		if($rslist){
			//排序
			$reslist = array();
			foreach($newlist as $key=>$value){
				if($rslist[$value]){
					$reslist[] = $rslist[$value];
				}
			}
			$this->json($reslist,true);
		}
		$this->json(P_Lang('附件信息获取失败，可能已经删除，请检查'));
	}

	/**
	 * 弹出窗口编辑附件信息
	 * @参数 id 附件ID
	**/
	public function editopen_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		$note = form_edit('note',$rs['note'],'editor','width=650&height=250&etype=simple');
		$this->assign('rs',$rs);
		$this->assign('note',$note);
		$this->view("res_editopen");
	}

	/**
	 * 保存附件信息
	 * @参数 id 附件ID
	 * @参数 title 附件名称
	 * @参数 note 附件备注，支持HTML代码
	 * @返回 Json字串
	**/
	public function editopen_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->json(P_Lang('附件标题不能为空'));
		}
		$note = $this->get('note','html');
		$this->model('res')->save(array('title'=>$title,'note'=>$note),$id);
		$this->json(true);
	}

	/**
	 * 附件预览
	 * @参数 id 附件ID
	**/
	public function preview_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		$arraylist = array('jpg','png','gif','jpeg');
		if($rs['ext'] && in_array($rs['ext'],$arraylist)){
			$this->assign('ispic',true);
		}
		$this->assign('rs',$rs);
		$this->view('res_openview');
	}

	/**
	 * 附件删除
	 * @参数 id 附件ID
	**/
	public function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('附件信息不存在'));
		}
		if(!$this->session->val('admin_rs.if_system') && $rs['admin_id'] != $this->session->val('admin_id')){
			$this->json(P_Lang('非系统管理员不能删除其他管理员上传的附件'));
		}
		$this->model('res')->delete($id);
		$this->json(true);
	}
}
?>