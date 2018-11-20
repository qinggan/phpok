<?php
/**
 * 存储发布的项目信息
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年01月28日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class post_control extends phpok_control
{
	private $user_groupid;
	private $user_id = 0;
	public function __construct()
	{
		parent::control();
		$this->model('popedom')->siteid($this->site['id']);
		$token = $this->get('token');
		if($token){
			$info = $this->lib('token')->decode($token);
			if(!$info || !$info['user_id']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
			$this->user_id = $info['user_id'];
		}else{
			$this->user_id = $_SESSION['user_id'];
		}
		$groupid = $this->model('usergroup')->group_id($this->user_id);
		if(!$groupid){
			$this->json(P_Lang('无法获取前端用户组信息'));
		}
		$this->user_groupid = $groupid;
	}

	public function edit_f()
	{
		$this->save_f();
	}

	public function save_f()
	{
		$id = $this->get('id','system');
		if(!$id){
			$this->json(P_Lang('未绑定相应的项目'));
		}
		$project_rs = $this->call->phpok('_project','phpok='.$id);
		if(!$project_rs || !$project_rs['status']){
			$this->json(P_Lang('项目信息不存在或未启用'));
		}
		if(!$project_rs['module']){
			$this->json(P_Lang('此项目没有表单功能'));
		}
		if(!$this->model('popedom')->check($project_rs['id'],$this->user_groupid,'post')){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$array = array();
		$array["title"] = $this->get("title");
		if(!$array['title']){
			$tip = $project_rs['alias_title'] ? $project_rs['alias_title'] : P_Lang('主题');
			$this->json(P_Lang('{title}不能为空',array('title'=>$tip)));
		}
		$tid = $this->get('tid','int');
		$vcode_act = 'add';
		if($tid){
			$chk = $this->model('list')->call_one($tid);
			if($chk['user_id'] != $this->session->val('user_id')){
				$this->json(P_Lang('您没有权限编辑此内容'));
			}
			$vcode_act = 'edit';
		}
		//增加是否需要验证码 2017年08月28日
		if($this->model('site')->vcode($project_rs['id'],$vcode_act)){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->json(P_Lang('验证码不能为空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->json(P_Lang('验证码填写不正确'));
			}
			$this->session->unassign('vcode');
		}
		//----结束
		$array["status"] = $this->model('popedom')->val($project_rs['id'],$this->user_groupid,'post1');
		$array["hidden"] = 0;
		$array["module_id"] = $project_rs["module"];
		$array["project_id"] = $project_rs["id"];
		$array["site_id"] = $project_rs["site_id"];
		$array["cate_id"] = $this->get("cate_id","int");
		$array['user_id'] = $_SESSION['user_id'] ? $_SESSION['user_id'] : 0;
		if($tid){
			$get_result = $this->model('list')->save($array,$tid);
			if(!$get_result){
				$this->json(P_Lang('编辑失败，请联系管理员'));
			}
			$this->model('list')->list_cate_clear($tid);
			if($array["cate_id"]){
		 		$ext_cate = $this->get('ext_cate_id');
		 		if(!$ext_cate){
			 		$ext_cate = array($array["cate_id"]);
		 		}
		 		$this->model('list')->save_ext_cate($tid,$ext_cate);
	 		}
		}else{
			$array["dateline"] = $this->time;
			$insert_id = $this->model('list')->save($array);
			if(!$insert_id){
				$this->json(P_Lang('添加失败，请联系管理'));
			}
			if($array["cate_id"]){
		 		$ext_cate = $this->get('ext_cate_id');
		 		if(!$ext_cate){
			 		$ext_cate = array($array["cate_id"]);
		 		}
		 		$this->model('list')->save_ext_cate($insert_id,$ext_cate);
	 		}
		}
		//电商模块扩展
		if($project_rs['is_biz']){
			$biz = array('price'=>$this->get('price','float'),'currency_id'=>$project_rs['currency_id']);
	 		$biz['weight'] = $this->get('weight','float');
	 		$biz['volume'] = $this->get('volume','float');
	 		$biz['unit'] = $this->get('unit');
	 		$biz['id'] = $tid ? $tid : $insert_id;
	 		$biz['is_virtual'] = $this->get('is_virtual','int');
	 		$this->model('list')->biz_save($biz);
		}
		$ext_list = $this->model('module')->fields_all($project_rs["module"]);
		if(!$ext_list){
			$ext_list = array();
		}
		$tmplist = array();
		if(!$tid){
			$tmplist["id"] = $insert_id;
		}
		$tmplist["site_id"] = $project_rs["site_id"];
		$tmplist["project_id"] = $project_rs["id"];
		$tmplist["cate_id"] = $array["cate_id"];
		foreach($ext_list as $key=>$value){
			if(!$value['is_front']){
				continue;
			}
			$val = ext_value($value);
			if($value["ext"]){
				$ext = unserialize($value["ext"]);
				foreach($ext AS $k=>$v){
					$value[$k] = $v;
				}
			}
			if($value["form_type"] == "password"){
				$content = $rs[$value["identifier"]] ? $rs[$value["identifier"]] : $value["content"];
				$val = ext_password_format($val,$content,$value["password_type"]);
			}
			if($val){
				$tmplist[$value["identifier"]] = $val;
			}
		}
		if($tid){
			$this->model('list')->update_ext($tmplist,$project_rs['module'],$tid);
			$this->cache->delete_index($this->db->prefix.'list');
			$this->json(P_Lang('内容编辑成功'),true);
		}
		$this->model('list')->save_ext($tmplist,$project_rs["module"]);
		if($project_rs['etpl_admin'] || $project_rs['etpl_user']){
			if($tid){
				$param = 'id='.$tid.'&status=update';
			}else{
				$param = 'id='.$insert_id.'&status=create';
			}
			$this->model('task')->add_once('post',$param);
		}
		$this->cache->delete_index($this->db->prefix.'list');
		if(!$tid && $array['user_id'] && $array["status"]){
			$this->model('wealth')->add_integral($insert_id,$array['user_id'],'post',P_Lang('发布：{title}',array('title'=>$array['title'])));
		}
		$this->json(true);
	}

	public function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定主题ID'));
		}
		$rs = $this->model('list')->get_one($id,false);
		if(!$rs){
			$this->json(P_Lang('主题信息不存在'));
		}
		if($rs['user_id'] != $_SESSION['user_id']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$elist = $this->model('module')->fields_all($rs["module_id"]);
		if($elist){
			foreach($elist as $key=>$value){
				if($value['form_type'] == 'upload' && $rs[$value['identifier']]){
					$list = $this->model('res')->get_list_from_id($rs[$value['identifier']]);
					foreach($list as $k=>$v){
						if($v['user_id'] == $_SESSION['user_id']){
							$this->model('res')->delete($v['id']);
						}
					}
				}
			}
		}
		$this->model('list')->delete($id,$rs['module_id']);
		$this->json(true);
	}
}