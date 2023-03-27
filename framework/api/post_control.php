<?php
/**
 * 存储发布的项目信息
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2021年5月10日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class post_control extends phpok_control
{
	private $user_groupid;
	private $user_id = 0;
	public function __construct()
	{
		parent::control();
	}

	public function edit_f()
	{
		$this->save_f();
	}

	public function save_f()
	{
		$this->config('is_ajax',true);
		$this->node('PHPOK_post_save');
		$tmp = $this->_save();
		if(!$tmp['status']){
			$this->json($tmp['info']);
		}
		$this->json($tmp['info'],true);
	}

	/**
	 * 新版保存数据
	**/
	public function ok_f()
	{
		$this->config('is_ajax',true);
		$this->node('PHPOK_post_ok');
		$tmp = $this->_save();
		if(!$tmp['status']){
			$this->error($tmp['info']);
		}
		$this->success($tmp['info']);
	}

	/**
	 * 删除主题
	 * @参数 id 主题ID
	**/
	public function del_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定主题ID'));
		}
		$rs = $this->model('list')->get_one($id,false);
		if(!$rs){
			$this->error(P_Lang('主题信息不存在'));
		}
		if($rs['user_id'] != $this->session->val('user_id')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$this->model('list')->delete($id,$rs['module_id']);
		$this->success();
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
		if($rs['user_id'] != $this->session->val('user_id')){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$this->model('list')->delete($id,$rs['module_id']);
		$this->json(true);
	}

	private function _e($info,$status=false)
	{
		if(is_bool($info)){
			$n_status = $info;
			$info = $status;
			$status = $n_status;
		}
		return array('status'=>$status,'info'=>$info);
	}

	private function _save()
	{
		$groupid = $this->model('usergroup')->group_id($this->session->val('user_id'));
		if(!$groupid){
			return $this->_e(P_Lang('无法获取前端用户组信息'));
		}
		$id = $this->get('id','system');
		if(!$id){
			return $this->_e(P_Lang('未绑定相应的项目'));
		}
		$project_rs = $this->model('project')->get_one($id,false);
		if(!$project_rs || !$project_rs['status']){
			return $this->_e(P_Lang('项目信息不存在或未启用'));
		}
		if(!$project_rs['module']){
			return $this->_e(P_Lang('此项目没有表单功能'));
		}
		$module = $this->model('module')->get_one($project_rs['module']);
		if(!$module || !$module['status']){
			return $this->_e(P_Lang('模块未启用'));
		}
		if($module['mtype']){
			return $this->_single($project_rs,$module);
		}
		if(!$this->model('popedom')->check($project_rs['id'],$groupid,'post')){
			return $this->_e(P_Lang('您没有权限执行此操作'));
		}
		$array = array();
		$array["title"] = $this->get("title",'safe_text');
		if(!$array['title']){
			$tip = $project_rs['alias_title'] ? $project_rs['alias_title'] : P_Lang('主题');
			return $this->_e(P_Lang('{title}不能为空',array('title'=>$tip)));
		}
		if($project_rs['cate']){
			$array["cate_id"] = $this->get("cate_id","int");
			if(!$array['cate_id']){
				return $this->_e(P_Lang('分类不能为空'));
			}
		}
		$tid = $this->get('tid','int');
		$tmp = $this->_filter($array['title'],$project_rs,$module,$tid);
		if(!$tmp['status']){
			return $this->_e($tmp['info']);
		}
		$vcode_act = 'add';
		if($tid){
			$rs = $this->model('list')->get_one($tid,false);
			if($rs['user_id'] != $this->session->val('user_id')){
				return $this->_e(P_Lang('您没有权限编辑此内容'));
			}
			$vcode_act = 'edit';
		}
		$safecode = $this->get("_safecode");
		$api_code = $this->model('config')->get_one('api_code',$this->site['id']);
		//基于接口，可以忽略验证码
		if($safecode && ($api_code || $this->site['api_code'])){
			$acode = $this->site['api_code'] ? $this->site['api_code'] : $api_code;
			$this->model('apisafe')->code($acode);
			if(!$this->model('apisafe')->check()){
				$errInfo = $this->model('apisafe')->error_info();
				if(!$errInfo){
					$errInfo = P_Lang('未通过安全接口拼接');
				}
				return $this->_e($errInfo);
			}
		}else{
			if($this->model('site')->vcode($project_rs['id'],$vcode_act)){
				$code = $this->get('_chkcode');
				if(!$code){
					return $this->_e(P_Lang('验证码不能为空'));
				}
				$code = md5(strtolower($code));
				if($code != $this->session->val('vcode')){
					return $this->_e(P_Lang('验证码填写不正确'));
				}
				$this->session->unassign('vcode');
			}
		}
		if($this->session->val('user_id')){
			$user = $this->model('user')->get_one($this->session->val('user_id'),'id',false,false);
			if(!$user){
				return $this->_e(P_Lang('用户信息不存在'));
			}
			if(!$user['status']){
				return $this->_e(P_Lang('您的账号未审核'));
			}
			if($user['status'] == 2){
				return $this->_e(P_Lang('您的账号已被锁定'));
			}
		}
		//----结束
		if(!$tid){
			$array["status"] = $this->model('popedom')->val($project_rs['id'],$groupid,'post1');
		}
		$array["hidden"] = 0;
		$array["module_id"] = $project_rs["module"];
		$array["project_id"] = $project_rs["id"];
		$array["site_id"] = $project_rs["site_id"];
		
		$array['user_id'] = 0;
		if($this->session->val('user_id')){
			$array['user_id'] = $this->session->val('user_id');
		}
		if($project_rs['is_tag']){
			$array['tag'] = $this->get('tag');
			if($array["tag"]){
				$array["tag"] = preg_replace("/(\x20{2,})/"," ",$array["tag"]);
			}
		}else{
			$array['tag'] = '';
		}
		if($tid){
			$dateline = $this->get('dateline');
			if($dateline){
				$array['dateline'] = strtotime($dateline);
			}
			$get_result = $this->model('list')->save($array,$tid);
			if(!$get_result){
				return $this->_e(P_Lang('编辑失败，请联系管理员'));
			}
			$this->model('list')->list_cate_clear($tid);
			if($array["cate_id"]){
		 		$ext_cate = $this->get('ext_cate_id');
		 		if(!$ext_cate){
			 		$ext_cate = array($array["cate_id"]);
		 		}
		 		$this->model('list')->save_ext_cate($tid,$ext_cate);
	 		}
	 		$title_id = $tid;
		}else{
			$dateline = $this->get('dateline');
			if($dateline){
				$array['dateline'] = strtotime($dateline);
			}else{
				$array["dateline"] = $this->time;
			}
			$array['hidden'] = $this->get('hidden','int');
			$insert_id = $this->model('list')->save($array);
			if(!$insert_id){
				return $this->_e(P_Lang('添加失败，请联系管理'));
			}
			if($array["cate_id"]){
		 		$ext_cate = $this->get('ext_cate_id');
		 		if(!$ext_cate){
			 		$ext_cate = array($array["cate_id"]);
		 		}
		 		$this->model('list')->save_ext_cate($insert_id,$ext_cate);
	 		}
	 		$title_id = $insert_id;
		}
		//电商模块扩展
		if($project_rs['is_biz']){
			$biz = array('price'=>$this->get('price','float'));
			$biz['currency_id'] = $this->get('currency_id','int');
			if(!$biz['currency_id']){
				$biz['currency_id'] = $project_rs['currency_id'];
			}
	 		$biz['weight'] = $this->get('weight','float');
	 		$biz['volume'] = $this->get('volume','float');
	 		$biz['unit'] = $this->get('unit');
	 		$biz['id'] = $title_id;
	 		$biz['is_virtual'] = $this->get('is_virtual','int');
	 		$this->model('list')->biz_save($biz);
		}
		//Tag标签的同步
		$this->model('tag')->update_tag($array['tag'],$title_id);
		//存储扩展字段
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
			if($value["form_type"] == "password"){
				$content = $rs[$value["identifier"]] ? $rs[$value["identifier"]] : $value["content"];
				$val = ext_password_format($val,$content,$value["password_type"]);
			}
			$tmplist[$value["identifier"]] = $val;
		}
		if($tid){
			$this->model('list')->update_ext($tmplist,$project_rs['module'],$tid);
			$this->cache->delete_index($this->db->prefix.'list');
			if($project_rs['etpl_admin'] || $project_rs['etpl_user']){
				$param = 'id='.$tid.'&status=update';
				$this->model('task')->add_once('post',$param);
			}
			return $this->_e($tid,true);
		}
		$this->model('list')->save_ext($tmplist,$project_rs["module"]);
		if($project_rs['etpl_admin'] || $project_rs['etpl_user']){
			$param = 'id='.$insert_id.'&status=create';
			$this->model('task')->add_once('post',$param);
		}
		$this->cache->delete_index($this->db->prefix.'list');
		if(!$tid && $array['user_id'] && $array["status"]){
			$this->model('wealth')->add_integral($insert_id,$array['user_id'],'post',P_Lang('发布：{title}',array('title'=>$array['title'])));
		}
		return $this->_e($insert_id,true);
	}

	private function _single($project,$module)
	{
		$data = array('site_id'=>$project['site_id']);
		$tid = $this->get('tid','int');
		if($tid){
			$rs = $this->model('list')->single_one($tid,$project['module']);
			$data['id'] = $rs['id'];
		}
		$data['project_id'] = $project['id'];
		$data['cate_id'] = $project['cate'] ? $this->get('cate_id','int') : 0;
		$flist = $this->model('module')->fields_all($module['id']);
		if($flist){
			foreach($flist as $key=>$value){
				if(!$value['is_front']){
					continue;
				}
				$val = ext_value($value);
				if($value["form_type"] == "password"){
					$content = $rs[$value["identifier"]] ? $rs[$value["identifier"]] : $value["content"];
					$val = ext_password_format($val,$content,$value["password_type"]);
				}
				if($val != ''){
					$data[$value["identifier"]] = $val;
				}
			}
		}
		//保存数据
		$insert_id = $this->model("list")->single_save($data,$project['module']);
		if(!$insert_id){
			return $this->_e(P_Lang('数据保存失败，请检查'));
		}
		if(!is_numeric($insert_id) && $tid){
			$insert_id = $tid;
		}
		return $this->_e($insert_id,true);
	}

	private function _filter($title,$project_rs,$module,$tid=0)
	{
		if(!$title){
			return array('status'=>false,'info'=>P_Lang('主题不能为空'));
		}
		if($project_rs['limit_times']){
			$time = $this->time - $project_rs['limit_times'];
			$condition = "l.dateline>=".$time." ";
			if($this->session->val('user_id')){
				$condition .= " AND l.user_id='".$this->session->val('user_id')."' ";
			}
			if($tid){
				$condition .= " AND l.id !='".$tid."'";
			}
			$tmp = $this->model('list')->get_all_total($condition);
			if($tmp){
				return array('status'=>false,'info'=>P_Lang('系统限制每{times}秒只能发布一篇文章，请稍候提交…',array('times'=>$project_rs['limit_times'])));
			}
		}
		//比较相似度
		if($project_rs['limit_similar']){
			$condition = "l.status=1 ";
			if($this->session->val('user_id')){
				$condition .= " AND l.user_id='".$this->session->val('user_id')."' ";
			}
			if($tid){
				$condition .= " AND l.id !='".$tid."'";
			}
			$tmplist = $this->model('list')->get_all($condition,0,10);
			if($tmplist){
				$isok = true;
				foreach($tmplist as $key=>$value){
					$tmp = $this->lib('similar')->ssim($title,$value['title']);
					if($tmp >= $project_rs['limit_similar']){
						$isok = false;
						break;
					}
				}
				if(!$isok){
					return array('status'=>false,'info'=>P_Lang('您要发布的信息存在相似文章，不支持重复发送'));
				}
			}
		}
		return array('status'=>true);
	}

}