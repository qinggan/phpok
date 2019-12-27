<?php
/**
 * 会员地址库
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
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
	 * 弹窗查看会员的地址库信息
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
		$status = $this->_index($pageurl,$keywords);
		if(!$status){
			$this->tip(P_Lang('该会员还没有设置地址信息'));
		}
		$types = $this->get('types');
		if(!$types){
			$types = 'shipping';
		}
		$this->assign('types',$types);
		$this->view($tpl);
	}

	/**
	 * 会员地址库
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
		$this->_index($pageurl,$keywords);
		$this->view("address_list");
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
		$this->success($rs);
	}

	private function _index($pageurl='',$keywords='')
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
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
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
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有添加地址库权限'));
			}
		}
		$this->view("address_set");
	}

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
			$this->error(P_Lang('未绑定会员'));
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
		$tip = $id ? P_Lang('地址信息编辑成功') : P_Lang('地址信息添加成功');
		$this->success($tip);
	}

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
		$this->success();
	}
}
