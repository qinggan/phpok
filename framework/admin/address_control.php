<?php
/**
 * 用户地址库
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年06月03日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class address_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('address');
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 删除地址库
	 * @参数 $id 要删除的地址ID
	 * @返回 json
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有删除地址库权限'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要删除的ID'));
		}
		$this->model('address')->delete($id);
		$this->model('log')->add(P_Lang('删除用户地址#{0}',$id));
		$this->success();
	}

	/**
	 * 用户地址库
	 * @参数 type 查询类型
	 * @参数 keywords 关键字
	**/
	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有此权限操作'));
		}
		$pageurl = $this->url('address');
		$type = $this->get('type');
		$keywords = $this->get('keywords');
		if(!$keywords && !is_array($keywords)){
			$type = $this->get('type');
			if($type){
				$keywords = array($type=>$keywords);
			}
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$this->_index($pageurl, $pageid, $keywords);
		$this->model('log')->add(P_Lang('访问用户地址库第 {0} 页',$pageid));
		$this->view("address_list");
	}

	/**
	 * 弹窗查看用户的地址库信息
	 * @参数 type 查询类型
	 * @参数 keywords 关键字
	**/
	public function open_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有此权限操作'));
		}
		$tpl = $this->get('tpl');
		if(!$tpl){
			$pageurl = $this->url('address','open');
			$tpl = 'address_open';
		}else{
			$pageurl = $this->url('address','open','tpl='.$tpl);
		}
		
		$keywords = $this->get('keywords');
		if($keywords && !is_array($keywords)){
			$type = $this->get('type');
			$keywords = array($type=>$keywords);
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$status = $this->_index($pageurl, $pageid, $keywords);
		if(!$status){
			$this->tip(P_Lang('该用户还没有设置地址信息'));
		}
		$types = $this->get('types');
		if(!$types){
			$types = 'shipping';
		}
		$this->assign('types',$types);
		$this->model('log')->add(P_Lang('弹窗访问查看用户地址库第 {0} 页',$pageid));
		$this->view($tpl);
	}

	public function one_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('address')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('地址信息不存在'));
		}
		$this->model('log')->add(P_Lang('查看地址信息#{0}',$id));
		$this->success($rs);
	}

	/**
	 * 保存地址库
	 * @参数 $id 为空或0时表示添加地址库，其它值表示修改地址库
	 * @参数 $user_id 地址库所属会员
	 * @参数 $fullname 姓名
	 * @参数 $country 国家
	 * @参数 $provice 省份或州
	 * @参数 $city 城市
	 * @参数 $county 县或镇或区
	 * @参数 $address 地址
	 * @参数 $zipcode 邮编
	 * @参数 $mobile 手机
	 * @参数 $tel 固定电话
	 * @参数 $email 邮箱地址
	 * @返回 json
	**/
	public function save_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有修改地址库权限'));
			}
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有添加地址库权限'));
			}
		}
		$array = array();
		$array['user_id'] = $this->get('user_id','int');
		if(!$array['user_id']){
			$this->error(P_Lang('未绑定用户'));
		}
		$array['fullname'] = $this->get('fullname');
		if(!$array['fullname']){
			$this->error(P_Lang('收件人姓名不能为空'));
		}
		$array['country'] = $this->get('country');
		$array['province'] = $this->get('province');
		$array['city'] = $this->get('city');
		$array['county'] = $this->get('county');
		$array['address'] = $this->get('address');
		if(!$array['country']){
			$this->error(P_Lang('国家不能为空'));
		}
		if(!$array['province']){
			$this->error(P_Lang('省份名称不能为空'));
		}
		if(!$array['address']){
			$this->error(P_Lang('地址信息不能为空'));
		}
		$array['zipcode'] = $this->get('zipcode');
		$array['mobile'] = $this->get('mobile');
		$array['tel'] = $this->get('tel');
		if(!$array['mobile'] && !$array['tel']){
			$this->error(P_Lang('手机号或电话，必须至少填写一个'));
		}
		$array['email'] = $this->get('email');
		$this->model('user')->address_save($array,$id);
		if($id){
			$tip = P_Lang('地址信息编辑成功');
			$this->model('log')->add(P_Lang('编辑地址信息，ID#{0}',$id));
		}else{
			$tip = P_Lang('地址信息添加成功');
			$this->model('log')->add(P_Lang('添加地址信息'));
		}
		$this->success($tip);
	}

	/**
	 * 添加或编辑地址库信息（页面）
	 * @参数 $id 为空或0时表示添加地址库，其它值表示修改地址库
	 * @返回 HTML页面
	**/
	public function set_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有修改地址库权限'));
			}
			$rs = $this->model('user')->address_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
			$this->model('log')->add(P_Lang('编辑地址 #{0}',$id));
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有添加地址库权限'));
			}
			$this->model('log')->add(P_Lang('添加地址'));
		}
		$this->view("address_set");
	}

	private function _index($pageurl='',$pageid=1, $keywords='')
	{
		$condition = "1=1";
		if($keywords && is_array($keywords)){
			$this->assign('keywords',$keywords);
			if($keywords['user']){
				$tmplist = array("u.user='".$keywords['user']."'");
				$tmplist[] = "u.email='".$keywords['user']."'";
				$tmplist[] = "u.mobile='".$keywords['user']."'";
				$condition .= " AND (".implode(" OR ",$tmplist).")";
				$pageurl .= "&keywords[user]=".rawurlencode($keywords['user']);
			}
			if($keywords['user_id']){
				$condition .= " AND a.user_id='".$keywords['user_id']."'";
				$pageurl .= "&keywords[user_id]=".rawurlencode($keywords['user_id']);
			}
			if($keywords['address']){
				$tmplist = array("a.country LIKE '%".$keywords['address']."%'");
				$tmplist[] = "a.province LIKE '%".$keywords['address']."%'";
				$tmplist[] = "a.city LIKE '%".$keywords['address']."%'";
				$tmplist[] = "a.county LIKE '%".$keywords['address']."%'";
				$tmplist[] = "a.address LIKE '%".$keywords['address']."%'";
				$condition .= " AND (".implode(" OR ",$tmplist).")";
				$pageurl .= "&keywords[address]=".rawurlencode($keywords['address']);
			}
			if($keywords['contact']){
				$tmplist = array("a.email LIKE '%".$keywords['contact']."%'");
				$tmplist[] = "a.tel LIKE '%".$keywords['contact']."%'";
				$tmplist[] = "a.mobile LIKE '%".$keywords['contact']."%'";
				$condition .= " AND (".implode(" OR ",$tmplist).")";
				$pageurl .= "&keywords[contact]=".rawurlencode($keywords['contact']);
			}
			if($keywords['fullname']){
				$condition .= " AND a.fullname='".$keywords['user_id']."'";
				$pageurl .= "&keywords[user_id]=".rawurlencode($keywords['user_id']);
			}
		}
		$this->assign('pageurl',$pageurl);
		$total = $this->model('address')->count($condition);
		if(!$total){
			return false;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('address')->get_list($condition,$offset,$psize);
		$this->assign('rslist',$rslist);
		$this->assign('total',$total);
		$this->assign('pageid',$pageid);
		$this->assign('psize',$psize);
		$this->assign('offset',$offset);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign("pagelist",$pagelist);
		return true;
	}
}
