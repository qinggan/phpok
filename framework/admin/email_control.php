<?php
/**
 * 通知类模板，包括短信通知及邮件通知
 * @package phpok\admin\control
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年02月24日
**/

class email_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("email");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 通知模板列表
	 * @参数 pageid 分页ID
	 * @参数 
	 * @返回 
	 * @更新时间 
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$condition = "site_id IN(".$this->session->val('admin_site_id').",0)";
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config["psize"] ? $this->config["psize"] : 20;
		$offset = ($pageid-1) * $psize;
		$total = $this->model('email')->get_count($condition);//读取模块总数
		if($total){
			$rslist = $this->model('email')->get_list($condition,$offset,$psize);
			$this->assign("rslist",$rslist);
			$pageurl = $this->url("email");
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			if($pagelist){
				$this->assign("pagelist",$pagelist);
			}
		}		
		$this->view("email_list");
	}

	/**
	 * 添加可配置通知模板的内容
	**/
	public function set_f()
	{
		$id = $this->get("id","int");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'),$this->url('email'));
			}
			$rs = $this->model('email')->get_one($id);
			$type = substr($rs['identifier'],0,4) == 'sms_' ? 'sms' : 'email';
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'),$this->url('email'));
			}
			$tid = $this->get('tid','int');
			if($tid){
				$rs = $this->model('email')->get_one($tid);
				$type = substr($rs['identifier'],0,4) == 'sms_' ? 'sms' : 'email';
				unset($rs['identifier'],$rs['id']);
				$this->assign("rs",$rs);
			}else{
				$type = $this->get('type');
			}
			if(!$type){
				$type = 'email';
			}
		}
		$this->assign('type',$type);
		if($type == 'sms'){
			$edit_content = form_edit('content',$rs['content'],'textarea','height=300&width=500');
		}else{
			$edit_content = form_edit('content',$rs['content'],'editor','height=300&btn_image=1&is_code=1&auto_height=1&is_float=1');
		}
		$this->assign('edit_content',$edit_content);
		$this->view("email_set");
	}

	public function setok_f()
	{
		$array = array();
		$id = $this->get("id","int");
		if(!$id){
			$array["site_id"] = $this->session->val('admin_site_id');
			$tip = P_Lang('通知内容添加成功，请稍候…');
		}else{
			$tip = P_Lang('通知内容编辑成功，请稍候…');
		}
		$array["title"] = $this->get("title");
		$array["identifier"] = $this->get("identifier");
		if(substr($array['identifier'],0,4) == 'sms_'){
			$array['content'] = $this->get('content','text');
		}else{
			$array["content"] = $this->get("content","html",false);
		}
		if(!$array["title"] || !$array["identifier"]){
			$this->error(P_Lang('信息填写不完整'),$this->url("email","set","id=".$id));
		}
		$array['note'] = $this->get('note');
		$this->model('email')->save($array,$id);
		$this->success($tip,$this->url("email"));
	}

	/**
	 * 删除通知模板
	 * @参数 id 模板内容ID
	 * @返回 JSON数据
	 * @更新时间 2017年02月25日
	**/
	public function del_f()
	{
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('email')->del($id);
		$this->json(true);
	}

	/**
	 * 验证标识是否符合要求
	 * @参数 id 通知模板ID
	 * @参数 identifier 标识
	 * @参数 type 类型, sms 表示短信,其他表示邮件
	 * @返回 JSON数据
	 * @更新时间 2017年02月25日
	**/
	public function check_f()
	{
		$id = $this->get("id","int");
		$identifier = $this->get("identifier");
		if(!$identifier){
			$this->json(P_Lang('未指定标识串'));
		}
		$type = $this->get('type');
		if($type == 'sms' && substr($identifier,0,4) != 'sms_'){
			$this->json(P_Lang('短信必须是以sms_开头'));
		}
		$rs = $this->model('email')->get_identifier($identifier,$id);
		if($rs){
			$this->json(P_Lang('标识符已被使用'));
		}
		$this->json(true);
	}
}