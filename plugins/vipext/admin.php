<?php
/**
 * PHPOK-VIP插件扩展<后台应用>
 * @作者 phpok.com
 * @版本 5.3.135
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年11月11日 10时21分
**/
class admin_vipext extends phpok_plugin
{
	public $me;
	private $e_pids = array();
	private $e_fields = array('email');
	private $e_reply = array('content');
	private $e_fullname = array('fullname');
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
		if($this->me && $this->me['param']){
			$this->e_pids = $this->me['param']['email_pid'];
			$this->e_fields = $this->me['param']['email_fields'] ? explode(",",$this->me['param']['email_fields']) : array('email');
			$this->e_reply = $this->me['param']['email_reply'] ? explode(",",$this->me['param']['email_reply']) : array('content');
			$this->e_fullname = $this->me['param']['email_fullname'] ? explode(",",$this->me['param']['email_fullname']) : array('fullname');
		}
	}

	public function html_index_index_foot()
	{
		$this->_show('admin_index_index.html');
	}

	/**
	 * 项目管理扩展按钮
	**/
	public function html_project_index_foot()
	{
		$this->_show('admin_project.html');
	}

	public function html_list_action_foot()
	{
		$rs = $this->tpl->val('rs');
		if($this->e_pids && in_array($rs['id'],$this->e_pids)){
			$this->_show('admin_quick_button.html');
		}
	}

	public function data_move()
	{
		$condition = "module>0";
		$project_list = $this->model('project')->get_all_project($this->session->val('admin_site_id'),$condition);
		$this->assign('plist',$project_list);
		$this->_view('admin_data_move.html');
	}

	public function data_clear()
	{
		$condition = "module>0";
		$project_list = $this->model('project')->get_all_project($this->session->val('admin_site_id'),$condition);
		$this->assign('plist',$project_list);
		$this->_view('admin_data_clear.html');
	}

	public function email()
	{
		$tid = $this->get('tid','int');
		if(!$tid){
			$this->error('未指定主题');
		}
		$typelist = $this->model('gateway')->all('email');
		if(!$typelist){
			$this->error('系统未配置发邮件网关路由，请先配置');
		}
		$this->assign('typelist',$typelist);
		$rs = $this->model('list')->get_one($tid);
		if(!$rs){
			$this->error('内容信息不存在');
		}
		$email = '';
		foreach($this->e_fields as $key=>$value){
			if($rs[$value]){
				$email = $rs[$value];
				break;
			}
		}
		$this->assign('email',$email);
		$content = '';
		foreach($this->e_reply as $key=>$value){
			if($rs[$value]){
				$content = $rs[$value];
				break;
			}
		}
		$content = form_edit('content','<p>管理员回复：</p><blockquote style="border:1px solid #ccc;padding:10px;"><p>请填写您的回复的信息…</p></blockquote>'.'<p>-----------以下是原内容----------------</p>'.$content,'editor','height=360');
		$this->assign('content',$content);
		$fullname = '';
		foreach($this->e_fullname as $key=>$value){
			if($rs[$value]){
				$fullname = $rs[$value];
				break;
			}
		}
		$this->assign('fullname',$fullname);
		$this->assign('title','回复：'.$rs['title']);
		$this->assign('rs',$rs);
		$this->_view("admin_sendemail.html");
	}

	public function sendemail()
	{
		$typelist = $this->model('gateway')->all('email');
		if(!$typelist){
			$this->error('系统未配置发邮件网关路由，请先配置');
		}
		$this->assign('typelist',$typelist);
		$email = $this->get('email');
		$fullname = $this->get('fullname');
		if($email && !$fullname){
			$fullname = strstr($fullname,'@',true);
		}
		$this->assign('email',$email);
		$this->assign('fullname',$fullname);
		$content = form_edit('content','','editor','height=360');
		$this->assign('content',$content);
		$wlist = array();
		$wlist[] = '您的留言已处理，请查阅';
		$wlist[] = '您的订单已更新，请查阅';
		$wlist[] = '您的幕布已寄出，请查收';
		$this->assign('wlist',$wlist);
		$this->_view("admin_sendemail.html");
	}

	public function sendok()
	{
		$server_id = $this->get('server_id');
		if(!$server_id){
			$this->error('未指定邮件服务器');
		}
		$fullname = $this->get('fullname');
		$email = $this->get('email');
		$title = $this->get('title');
		$content = $this->get('content','html');
		if(!$email){
			$this->error('未指定邮箱');
		}
		if(!$this->lib('common')->email_check($email)){
			$this->error('邮箱不合法，请检查');
		}
		if(!$fullname){
			$fullname = strstr($email,'@',true);
		}
		if(!$content){
			$this->error('邮件内容不能为空');
		}
		if(!$title){
			$title = phpok_cut($content,20,'…');
		}
		$this->gateway('type','email');
		$this->gateway('param',$server_id);
		$array = array('email'=>$email,'fullname'=>$fullname,'title'=>$title,'content'=>$content);
		$this->gateway('exec',$array);
		$this->success();
	}

	public function start_clear()
	{
		$clear_id = $this->get('clear_id','int');
		if(!$clear_id){
			$this->error('未指定项目ID');
		}
		$project = $this->model('project')->get_one($clear_id,false);
		if(!$project){
			$this->error('项目不存在');
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error('没有模块信息');
		}
		if($module['mtype']){
			$sql = "DELETE FROM ".$this->db->prefix.$module['id']." WHERE project_id='".$project['id']."'";
			$this->db->query($sql);
			//查看是否还有内容
			$sql = "SELECT count(id) FROM ".$this->db->prefix.$module['id'];
			$chk = $this->db->count($sql);
			if(!$chk){
				$sql = "TRUNCATE ".$this->db->prefix.$module['id'];
				$this->db->query($sql);
			}
			$this->success();
		}
		//删除扩展分类
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id IN(SELECT n.id FROM (SELECT id FROM ".$this->db->prefix."list WHERE project_id='".$project['id']."') as n)";
		$this->db->query($sql);
		//删除电商信息
		$sql = "DELETE FROM ".$this->db->prefix."list_biz WHERE id IN(SELECT n.id FROM (SELECT id FROM ".$this->db->prefix."list WHERE project_id='".$project['id']."') as n)";
		$this->db->query($sql);
		//删除电商属性
		$sql = "DELETE FROM ".$this->db->prefix."list_attr WHERE tid IN(SELECT n.id FROM (SELECT id FROM ".$this->db->prefix."list WHERE project_id='".$project['id']."') as n)";
		$this->db->query($sql);
		//删除扩展模块信息
		$sql = "DELETE FROM ".$this->db->prefix.'list_'.$module['id']." WHERE project_id='".$project['id']."'";
		$this->db->query($sql);
		//删除主题内容
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE project_id='".$project['id']."'";
		$this->db->query($sql);
		$this->success();
	}

	public function start_move()
	{
		$output_id = $this->get('output_id','int');
		$input_id = $this->get('input_id','int');
		if(!$output_id || !$input_id){
			$this->error('未选择要迁移的项目数据或要导入的目标项目');
		}
		if($output_id == $input_id){
			$this->error('项目一样，不支持导入');
		}
		$old = $this->model('project')->get_one($output_id,false);
		$new = $this->model('project')->get_one($input_id,false);
		if(!$old || !$new){
			$this->error('项目不存在');
		}
		if(!$old['module'] || !$new['module']){
			$this->error('有项目未绑定模块');
		}
		$old_m = $this->model('module')->get_one($old['module']);
		$new_m = $this->model('module')->get_one($new['module']);
		if($old_m['mtype'] || $new_m['mtype']){
			$this->error('迁移功暂时不支持独立模块');
		}
		if($old_m['tbl'] != $new_m['tbl']){
			$this->error('模块类型不一致');
		}
		$old_f = $this->model('fields')->flist($old['module'],'identifier');
		$new_f = $this->model('fields')->flist($new['module'],'identifier');
		if(!$old_f || !$new_f){
			$this->error('模块未定义相关字段');
		}
		$old_ids = array_keys($old_f);
		$new_ids = array_keys($new_f);
		if(!array_intersect($old_ids,$new_ids)){
			$this->error('两个模块没有共同的字段');
		}
		//删除扩展分类
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id IN(SELECT n.id FROM (SELECT id FROM ".$this->db->prefix."list WHERE project_id='".$old['id']."') as n)";
		$this->db->query($sql);
		//更改分类
		if($old['cate'] && $old['cate'] != $new['cate']){
			$sql = "UPDATE ".$this->db->prefix."list SET cate_id=0 WHERE project_id='".$old['id']."'";
			$this->db->query($sql);
			$sql = "UPDATE ".$this->db->prefix."list_".$old['module']." SET cate_id=0 WHERE project_id='".$old['id']."'";
			$this->db->query($sql);
		}
		if($old['module'] == $new['module']){
			//将旧的主题项目ID改成新的项目ID
			$sql = "UPDATE ".$this->db->prefix."list SET project_id='".$new['id']."' WHERE project_id='".$old['id']."'";
			$this->db->query($sql);
			$sql = "UPDATE ".$this->db->prefix."list_".$old['module']." SET project_id='".$new['id']."' WHERE project_id='".$old['id']."'";
			$this->db->query($sql);
			$this->success();
		}
		$flist = array_intersect($old_ids,$new_ids);
		$fields = "id,site_id,project_id,cate_id";
		foreach($flist as $key=>$value){
			$fields.= ",".$value;
		}
		$sql = "INSERT INTO ".$this->db->prefix."list_".$new['module']."(".$fields.") SELECT ".$fields." FROM ".$this->db->prefix."list_".$old['module']." WHERE project_id='".$old['id']."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."list_".$new['module']." SET project_id='".$new['id']."' WHERE project_id='".$old['id']."'";
		$this->db->query($sql);
		//删除旧的数据
		$sql = "DELETE FROM ".$this->db->prefix."list_".$old['module']." WHERE project_id='".$old['id']."'";
		$this->db->query($sql);
		//更新主表关联
		$sql = "UPDATE ".$this->db->prefix."list SET project_id='".$new['id']."',module_id='".$new['module']."' WHERE project_id='".$old['id']."'";
		$this->db->query($sql);
		$this->success();
	}
}