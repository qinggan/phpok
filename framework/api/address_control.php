<?php
/**
 * 地址库相关操作
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月04日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class address_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您还未登录，请先登录'));
		}
		$rslist = $this->model('user')->address_all($this->session->val('user_id'));
		if(!$rslist){
			$this->success();
		}
		$default = array();
		foreach($rslist as $key=>$value){
			if($value['is_default']){
				$default = $value;
				break;
			}
		}
		if(!$default){
			reset($rslist);
			$default = current($rslist);
		}
		foreach($rslist as $key=>$value){
			$value['_default'] = false;
			if($value['id'] == $default['id']){
				$value['_default'] = true; 
			}
			$rslist[$key] = $value;
		}
		$this->success($rslist);	
	}

	public function all_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您还未登录，请先登录'));
		}
		$rslist = $this->model('user')->address_all($this->session->val('user_id'));
		if(!$rslist){
			$this->error(P_Lang('会员暂无收货地址信息'));
		}
		$total = count($rslist);
		$default = $first = array();
		foreach($rslist as $key=>$value){
			if($key<1){
				$first = $value;
			}
			if($value['is_default']){
				$default = $value;
			}
		}
		if(!$default){
			$default = $first;
		}
		$array = array('total'=>$total,'rs'=>$default,'rslist'=>$rslist);
		$this->success($array);
	}

	/**
	 * 获取会员地址列表别名
	**/
	public function list_f()
	{
		$this->index_f();
	}

	/**
	 * 保存地址信息
	**/
	public function save_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您没有权限执行此操作，请先登录'));
		}
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('user')->address_one($id);
			if($rs['user_id'] != $this->session->val('user_id')){
				$this->error(P_Lang('您没有权限修改其他人联系信息'));
			}
		}
		$data = array();
		$data['fullname'] = $this->get('fullname');
		if(!$data['fullname']){
			$this->error(P_Lang('姓名不能为空'));
		}
		$data['country'] = $this->get('country');
		if(!$data['country']){
			$data['country'] = P_Lang('中国');
		}
		$pca = $this->get('pca');
		if(!$pca){
			$province = $this->get('province');
			$city = $this->get('city');
			$county = $this->get('county');
		}else{
			$tmp = explode("/",$pca);
			$province = $tmp[0];
			$city = $tmp[1];
			$county = $tmp[2];
		}
		if(!$province){
			$this->error(P_Lang('省份信息不能为空'));
		}
		$data['province'] = $province;
		$data['city'] = $city;
		$data['county'] = $county;
		$data['address'] = $this->get('address');
		if(!$data['address']){
			$this->error(P_Lang('地址信息不能为空'));
		}
		$data['mobile'] = $this->get('mobile');
		$data['tel'] = $this->get('tel');
		if(!$data['mobile'] && !$data['tel']){
			$this->error(P_Lang('手机号或固定电话至少要填写一项'));
		}
		if($data['mobile']){
			$chk = $this->lib('common')->tel_check($data['mobile']);
			if(!$chk){
				$this->error(P_Lang('手机号不符合要求'));
			}
		}
		if($data['tel']){
			$chk = $this->lib('common')->tel_check($data['tel']);
			if(!$chk){
				$this->error(P_Lang('固定电话号码不符合要求'));
			}
		}
		$data['email'] = $this->get('email');
		$data['zipcode'] = $this->get('zipcode');
		if(!$id){
			$data['user_id' ] = $this->session->val('user_id');
		}
		$this->model('user')->address_save($data,$id);
		$this->success();
	}

	/**
	 * 删除一条地址记录
	**/
	public function delete_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您没有权限执行此操作，请先登录'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指要删除的地址ID'));
		}
		$rs = $this->model('user')->address_one($id);
		if($rs['user_id'] != $this->session->val('user_id')){
			$this->error(P_Lang('您没有权限执行当前操作'));
		}
		$this->model('user')->address_delete($id);
		$this->success();
	}

	/**
	 * 设置默认地址记录
	**/
	public function default_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您没有权限执行此操作，请先登录'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指要操作的地址ID'));
		}
		$rs = $this->model('user')->address_one($id);
		if($rs['user_id'] != $this->session->val('user_id')){
			$this->error(P_Lang('您没有权限执行当前操作'));
		}
		$this->model('user')->address_default($id);
		$this->success();
	}
}
