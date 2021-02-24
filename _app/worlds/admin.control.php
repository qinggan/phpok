<?php
/**
 * 后台管理_管理全球国家及州/省信息
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年05月27日 19时51分
**/
namespace phpok\app\control\worlds;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class admin_control extends \phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('worlds');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		$parent_id = $this->get('parent_id','int');
		$condition = array();
		$is_end = false;
		$leader = array();
		$leader[] = array('title'=>P_Lang('洲/大陆'),'url'=>$this->url('worlds'));
		if($parent_id){
			$parent = $this->model('worlds')->get_one($parent_id);
			if(!$parent){
				$this->error(P_Lang('父级信息不存在，请检查'));
			}
			$this->assign('parent',$parent);
			$this->assign('parent_id',$parent_id);
			if(!$parent['pid']){
				$leadtitle = P_Lang('国家/组织');
				$leadtype = 'country';
				$leader[] = array('title'=>$parent['name'].($parent['name_en'] ? ' - '.$parent['name_en'] : ''),'url'=>$this->url('worlds'));
				$leader[] = array('title'=>$leadtitle,'url'=>$this->url('worlds','','parent_id='.$parent['id']));
				//获取洲信息
				$continent = $this->model('worlds')->get_all('pid=0');
				$this->assign('continent',$continent);
			}else{
				$p_parent = $this->model('worlds')->get_one($parent['pid']);
				if($p_parent && $p_parent['pid']){
					$leadtitle = P_Lang('城市');
					$leadtype = 'city';
					$is_end = true;
					$pp_parent = $this->model('worlds')->get_one($p_parent['pid']);
					$leader[] = array('title'=>$pp_parent['name'].($pp_parent['name_en'] ? ' - '.$pp_parent['name_en'] : ''),'url'=>$this->url('worlds','','parent_id='.$p_parent['pid']));
					$leader[] = array('title'=>$p_parent['name'].($p_parent['name_en'] ? ' - '.$p_parent['name_en'] : ''),'url'=>$this->url('worlds','','parent_id='.$parent['pid']));
					$leader[] = array('title'=>$parent['name'].($parent['name_en'] ? ' - '.$parent['name_en'] : ''),'url'=>$this->url('worlds','','parent_id='.$parent['id']));
					$leader[] = array('title'=>$leadtitle,'url'=>$this->url('worlds','','parent_id='.$parent['id']));
				}else{
					$leadtitle = P_Lang('省/州');
					$leadtype = 'province';
					$leader[] = array('title'=>$p_parent['name'].($p_parent['name_en'] ? ' - '.$p_parent['name_en'] : ''),'url'=>$this->url('worlds','','parent_id='.$parent['pid']));
					$leader[] = array('title'=>$parent['name'].($parent['name_en'] ? ' - '.$parent['name_en'] : ''),'url'=>$this->url('worlds','','parent_id='.$parent['id']));
					$leader[] = array('title'=>$leadtitle,'url'=>$this->url('worlds','','parent_id='.$parent['id']));
				}
			}
			$condition[] = "pid='".$parent_id."'";
		}else{
			$leadtype = 'continent';
			$leadtitle = P_Lang('洲/大陆');
			$condition[] = "pid=0";
		}
		$keywords = $this->get('keywords');
		if($keywords && $keywords['status']){
			$condition[] = "status='".($keywords['status'] == 1 ? 1 : 0)."'";
		}
		if($keywords && $keywords['name']){
			$condition[] = "name LIKE '%".str_replace(' ','%',$keywords['name'])."%'";
		}
		if($keywords && $keywords['name_en']){
			$condition[] = "name_en LIKE '%".str_replace(' ','%',$keywords['name_en'])."%'";
		}
		$rslist = $this->model('worlds')->get_all(implode(" AND ",$condition));
		$this->assign('is_end',$is_end);
		$this->assign('rslist',$rslist);
		$this->assign('leadtitle',$leadtitle);
		$this->assign('leadtype',$leadtype);
		$this->assign('leader',$leader);
		$this->assign('keywords',$keywords);
		//读取站点
		$sitelist = $this->model('site')->get_all_site('id');
		$this->assign('sitelist',$sitelist);
		//读取语言
		$langlist = $this->model('lang')->get_list();
		$this->assign('langlist',$langlist);
		//读取模板
		$tplist = $this->model('tpl')->get_all('id');
		$this->assign('tplist',$tplist);
		//读取货币
		$currency_list = $this->model('currency')->get_list('id');
		$this->assign('currency_list',$currency_list);
		$this->display('admin_index');
	}

	//批量迁移到对应的父级
	public function move_f()
	{
		$ids = $this->get('ids');
		$pid = $this->get('pid','int');
		if(!$ids){
			$this->error(P_Lang('未指定要删除的国家或省/州信息'));
		}
		if(!$pid){
			$this->error(P_Lang('未指定目标ID'));
		}
		$ids = explode(",",$ids);
		foreach($ids as $key=>$value){
			if(!$value || !intval($value)){
				continue;
			}
			$this->model('worlds')->update_pid($value,$pid);
		}
		$this->success();
	}

	//更新状态
	public function status_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('worlds')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('信息不存在'));
		}
		$status = $rs['status'] ? 0 : 1;
		$this->model('worlds')->update_status($id,$status);
		$this->success();
	}

	public function status_pl_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$status = $this->get('status','int');
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$this->model('worlds')->update_status($value,$status);
		}
		$this->success();
	}

	/**
	 * 删除地区信息
	 * @参数 id，多个ID用英文逗号隔开
	**/
	public function delete_f()
	{
		if(!$this->popedom['modify']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$rs = $this->model('worlds')->get_one($value);
			if(!$rs){
				continue;
			}
			$parent = $this->model('worlds')->parent_all($id);
			if($parent){
				continue;
			}
			$this->model('worlds')->del($value);
		}
		$this->success();
	}

	public function add_f()
	{
		if(!$this->popedom['add']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$pid = $this->get('pid','int');
		$type = 'continent';
		if($pid){
			$parent = $this->model('worlds')->get_one($pid);
			if(!$parent){
				$this->error(P_Lang('父级信息不存在，请检查'));
			}
			$this->assign('type','country');
			$this->assign('parent',$parent);
			if($parent && $parent['pid']){
				$type = 'province';
				$p_parent = $this->model('worlds')->get_one($parent['pid']);
				if($p_parent && $p_parent['pid']){
					$type = 'city';
				}
			}

		}
		if($type == 'country' || $type == 'province'){
			//读取站点
			$sitelist = $this->model('site')->get_all_site('id');
			$this->assign('sitelist',$sitelist);
			//读取语言
			$langlist = $this->model('lang')->get_list();
			$this->assign('langlist',$langlist);
			//读取模板
			$tplist = $this->model('tpl')->get_all('id');
			$this->assign('tplist',$tplist);
			$this->assign('overflowy',false);
			//读取货币
			$currency_list = $this->model('currency')->get_list('id');
			$this->assign('currency_list',$currency_list);
			//运费计算方式
			$freight_list = $this->model('freight')->get_all();
			$this->assign('freight_list',$freight_list);
		}
		$this->assign('type',$type);
		$this->display('admin_edit');
	}

	//编辑信息
	public function edit_f()
	{
		if(!$this->popedom['modify']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('worlds')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('信息不存在'));
		}
		$this->assign('rs',$rs);
		$type = 'continent';
		if($rs['pid']){
			$type = 'country';
			$parent = $this->model('worlds')->get_one($rs['pid']);
			if($parent && $parent['pid']){
				$type = 'province';
				$p_parent = $this->model('worlds')->get_one($parent['pid']);
				if($p_parent && $p_parent['pid']){
					$type = 'city';
				}
			}
		}
		$this->assign('type',$type);
		if($type == 'country' || $type == 'province'){
			//读取站点
			$sitelist = $this->model('site')->get_all_site('id');
			$this->assign('sitelist',$sitelist);
			
			//读取语言
			$langlist = $this->model('lang')->get_list();
			$this->assign('langlist',$langlist);
			
			//读取模板
			$tplist = $this->model('tpl')->get_all('id');
			$this->assign('tplist',$tplist);
			
			//读取货币
			$currency_list = $this->model('currency')->get_list('id');
			$this->assign('currency_list',$currency_list);

			//运费计算方式
			$freight_list = $this->model('freight')->get_all();
			$this->assign('freight_list',$freight_list);
		}
		$this->display('admin_edit');
	}

	public function save_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$rs = $this->model('worlds')->get_one($id);
			if(!$rs){
				$this->error(P_Lang('信息不存在'));
			}
			$pid = $rs['pid'];
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$pid = $this->get('pid','int');
		}
		$data = array();
		$data['name'] = $this->get('name');
		$data['name_en'] = $this->get('name_en');
		$data['pid'] = $pid;
		if(!$data['name'] && !$data['name_en']){
			$this->error(P_Lang('中文/英文名称至少要有一个填写'));
		}

		$data['excise_rate'] = $this->get('excise_rate','float');
		$data['tariff_rate'] = $this->get('tariff_rate','float');
		$data['site_id'] = $this->get('site_id','int');
		$data['tpl_id'] = $this->get('tpl_id','int');
		$data['lang_code'] = $this->get('lang_code');
		$data['currency_id'] = $this->get('currency_id','int');
		$data['code'] = $this->get('code');
		$data['code2'] = $this->get('code2');
		$data['freight_id'] = $this->get('freight_id');
		$data['status'] = $this->get('status','int');
		$data['taxis'] = $this->get('taxis','int');
		$data['note'] = $this->get('note');
		if(!$data['taxis']){
			$data['taxis'] = 255;
		}
		$this->model('worlds')->save($data,$id);
		$this->success();
	}

	public function taxis_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$taxis = $this->get('taxis','int');
		$data = array('taxis'=>$taxis);
		$this->model('worlds')->save($data,$id);
		$this->success();
	}
}
