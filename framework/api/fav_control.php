<?php
/*****************************************************************************************
	文件： {phpok}/api/fav_control.php
	备注： 收藏夹相关操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年11月20日 11时06分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class fav_control extends phpok_control
{
	private $uid;
	private $is_client;
	public function __construct()
	{
		parent::control();
		$token = $this->get('token');
		if($token){
			$info = $this->lib('token')->decode($token);
			if(!$info || !$info['user_id']){
				$this->json(P_Lang('您还没有登录，请先登录或注册'));
			}
			$this->uid = $info['user_id'];
			$this->is_client = true;
		}else{
			if(!$_SESSION['user_id']){
				$this->json(P_Lang('您还没有登录，请先登录或注册'));
			}
			$this->uid = $_SESSION['user_id'];
		}
	}

	public function add_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定主题ID'));
		}
		$chk = $this->model('fav')->chk($id,$this->uid,'lid');
		if(!$chk){
			$this->json(P_Lang('该主题已经收藏过，不用再收藏'));
		}
		$rs = $this->call->phpok('_arc','title_id='.$id);
		if(!$rs){
			$this->json(P_Lang('内容不存在'));
		}
		$data = array('user_id'=>$this->uid);
		//读取缩略图
		$type = ($this->config['fav'] && $this->config['fav']['thumb_id']) ? $this->config['fav']['thumb_id'] : 'thumb';
		if($rs[$type]){
			if(is_array($rs[$type])){
				$data['thumb'] = $rs[$type]['filename'];
			}else{
				$data['thumb'] = $rs[$type];
			}
		}
		$data['title'] = $rs['title'];
		//读取摘要
		$type = ($this->config['fav'] && $this->config['fav']['note_id']) ? $this->config['fav']['note_id'] : 'content';
		if($rs[$type]){
			$data['note'] = $this->lib('string')->cut($rs[$type],80,'…',false);
		}
		$data['addtime'] = $this->time;
		$data['lid'] = $id;
		$this->model('fav')->save($data);
		$this->json(true);
	}

	public function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('fav')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('数据不存在'));
		}
		if($rs['user_id'] != $this->uid){
			$this->json(P_Lang('您没有权限删除'));
		}
		$this->model('fav')->delete($id);
		$this->json(true);
	}
}

?>