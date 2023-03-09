<?php
/**
 * 批量加图<后台应用>
 * @作者 phpok.com
 * @版本 6.3.153
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2023年03月06日 13时59分
**/
class admin_pladdpicture extends phpok_plugin
{
	public $me;
	private $pid = 0;
	private $pids = array();
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
		$this->pid = $this->me["pid"];
		$this->pids = $this->me["pids"];
		if($this->me && $this->me["param"]){
			//
		}
	}

	public function add()
	{
		if(!$this->pid){
			$this->error('请管理员到插件中心设置绑定的项目');
		}
		$page_rs = $this->model('project')->get_one($this->pid);
		$catelist = $this->model('cate')->get_all($page_rs["site_id"],1,$page_rs["cate"]);
		$catelist = $this->model('cate')->cate_option_list($catelist);
		$this->assign("catelist",$catelist);
		$this->assign('page_rs',$page_rs);

		$cate = $this->model('rescate')->get_default();
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
		$btns = form_edit('pictures','','upload','cate_id='.$cate['id'].'&manage_forbid=1&auto_forbid=1&is_multiple=1');
		$this->assign('html',$btns);
		$this->_view('admin-add.html');
	}

	public function save()
	{
		if(!$this->pid){
			$this->error('请管理员到插件中心设置绑定的项目');
		}
		$ids = $this->get('pictures','int');
		if(!$ids){
			$this->error('未上传图片');
		}
		$page_rs = $this->model('project')->get_one($this->pid);
		if($page_rs && $page_rs['cate']){
			$cate_id = $this->get('cate_id','int');
			if(!$cate_id){
				$this->error('未指定分类');
			}
		}
		$list = explode(",",$ids);
		foreach($list as $key=>$value){
			$res = $this->model('res')->get_one($value);
			if(!$res){
				continue;
			}
			$data = array();
			$data['title'] = $res['title'];
			$data['cate_id'] = $cate_id ? $cate_id : 0;
			$data['thumb'] = $res['id'];
			phpok_post_save($data,$this->pid);
		}
		$this->success();
	}



	/**
	 * 内容列表使用的HTML/JS改靠
	**/
	public function html_list_action_body()
	{
		$rs = $this->tpl->val('rs');
		if($rs['id'] == $this->pid){
			$this->_show("admin-list-action-body.html");
		}
	}

	/**
	 * 更新或添加保存完主题后触发动作，如果不使用，请删除这个方法
	 * @参数 $id 主题ID
	 * @参数 $project 项目信息，数组
	 * @返回 true
	**/
	public function system_admin_title_success($id,$project)
	{
		//PHP代码;
	}


}