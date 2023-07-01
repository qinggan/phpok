<?php
/**
 * 运费模板管理
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年11月27日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class freight_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('freight');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$condition = "1=1";
		$keywords = $this->get("keywords");
		$pageurl = $this->url('freight');
		if($keywords){
			$this->assign('keywords',$keywords);
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$condition .= " AND f.title LIKE '%".$keywords."%'";
		}
		$country_id = $this->get('country_id','int');
		if($country_id){
			$this->assign('country_id',$country_id);
			$pageurl .= "&country_id=".$country_id;
			$condition .= " AND f.country_id='".$country_id."'";
		}
		$total = $this->model('freight')->get_count($condition);
		$rslist = $this->model('freight')->get_all($condition,$offset,$psize);
		$this->assign('rslist',$rslist);
		$taxis = $rslist ? (count($rslist)+1) * 10 : 10;
		$this->assign('taxis',$taxis);
		$typelist = $this->model('freight')->typelist();
		$this->assign('typelist',$typelist);
		$currency_list = $this->model('currency')->get_list();
		$this->assign('currency_list',$currency_list);
		$vw = $this->model('freight')->vweight();
		$this->assign('vweight',$vw);
		$this->view('freight_index');
	}

	public function set_f()
	{
		$id = $this->get('id');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$rs = $this->model('freight')->get_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		$z_id = 0;
		//读洲，国家
		$zonelist = $this->model('worlds')->get_all("pid=0");
		$countrylist = array();
		foreach($zonelist as $key=>$value){
			$tmplist = $this->model('worlds')->get_all("pid=".$value['id']);
			if($tmplist){
				if($rs && $rs['country_id'] && !$z_id){
					foreach($tmplist as $k=>$v){
						if($v['id'] == $rs['country_id']){
							$z_id = $value['id'];
							break;
						}
					}
				}
				$countrylist[] = array("id"=>$value['id'],"name"=>$value['name'],'name_en'=>$value['name_en'],'rslist'=>$tmplist);
			}
		}
		$this->assign('z_id',$z_id);
		$this->assign('countrylist',$countrylist);
		$typelist = $this->model('freight')->typelist();
		$this->assign('typelist',$typelist);
		$currency_list = $this->model('currency')->get_list();
		$this->assign('currency_list',$currency_list);
		$this->view("freight_set");
	}

	public function save_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('名称不能为空'));
		}
		$taxis = $this->get('taxis');
		if(!$taxis){
			$taxis = 255;
		}
		$currency_id = $this->get('currency_id');
		$type = $this->get('type');
		$data = array('title'=>$title,'taxis'=>$taxis,'type'=>$type);
		$data['country_id'] = $this->get('country_id');
		if(!$data['country_id']){
			$this->error(P_Lang('未指定国家'));
		}
		$data['currency_id'] = $currency_id;
		$this->model('freight')->save($data,$id);
		$this->success();
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('freight')->delete($id);
		$this->success();
	}

	public function vweight_f()
	{
		$val = $this->get("val");
		if(!$val){
			$this->error(P_Lang('体积重不能为空'));
		}
		$this->model('freight')->vweight($val);
		$this->success();
	}

	//区域设置
	public function zone_f()
	{
		$fid = $this->get('fid');
		if(!$fid){
			$this->error(P_Lang('未指定运费模板'));
		}
		$rslist = $this->model('freight')->zone_all($fid,'id,fid,title,taxis,note');
		$this->assign('rslist',$rslist);
		$this->assign('fid',$fid);
		$rs = $this->model('freight')->get_one($fid);
		$this->assign('rs',$rs);
		$this->view('freight_zone');
	}

	public function zone_setting_f()
	{
		$fid = $this->get('fid');
		$id = $this->get('id');
		if(!$fid && !$id){
			$this->error(P_Lang('未指定ID'));
		}
		$area = $forbid = array();
		if($id){
			$rs = $this->model('freight')->zone_one($id);
			if(!$rs){
				$this->error(P_Lang('数据不存在，请检查'));
			}
			$this->assign('rs',$rs);
			if($rs['area']){
				$area = unserialize($rs['area']);
				$this->assign('area',$area);
			}
			$this->assign('id',$id);
			$fid = $rs['fid'];
		}else{
			$count = $this->model('freight')->zone_count($fid);
			$taxis = $count ? ($count+1)*10 : 10;
			$this->assign('taxis',$taxis);
		}
		$fs = $this->model('freight')->get_one($fid);
		$this->assign('fs',$fs);
		$country = $this->model('worlds')->get_one($fs['country_id']);
		$this->assign('country',$country);
		$this->assign('fid',$fid);
		//读取
		$forbid = $this->model('freight')->area_ids_used($fid,$id);
		$province = $this->model('worlds')->get_all("pid='".$country['id']."'");
		if(!$province){
			$this->error(P_Lang('国家下未配置省/州信息'));
		}
		$prolist = array();
		$is_city = false;
		foreach($province as $key=>$value){
			$citylist = $this->model('worlds')->get_all("pid='".$value['id']."'");
			if($citylist){
				$is_city = true;
				foreach($citylist as $k=>$v){
					if($v['id'] && $forbid && in_array($v['id'],$forbid)){
						unset($citylist[$k]);
						continue;
					}
				}
				//如果检测到省下面的城市都被禁用了！直接跳过省选择
				if(count($citylist)<1){
					continue;
				}
			}
			$prolist[] = array('id'=>$value['id'],'name'=>$value['name'],'name_en'=>$value['name_en'],'citylist'=>$citylist);
		}
		$this->assign('prolist',$prolist);
		$this->assign('is_city',$is_city);
		$this->assign('forbid',$forbid);
		$this->view('freight_zone_setting');
	}

	public function zone_save_f()
	{
		$fid = $this->get('fid');
		$id = $this->get('id');
		if(!$fid && !$id){
			$this->error(P_Lang('未指定ID'));
		}
		if($id){
			$rs = $this->model('freight')->zone_one($id);
			$fid = $rs['fid'];
		}
		$array = array('title'=>$this->get('title'),'note'=>$this->get('note'),'taxis'=>$this->get('taxis','int'));
		//$array['country_id'] = $country_id;
		$area = $this->get('area');
		if(!$area){
			$this->error(P_Lang('未选定所在省份/州'));
		}
		$data = array();
		foreach($area as $key=>$value){
			$tmp = $this->get('city_'.$value);
			if($tmp && is_array($tmp)){
				foreach($tmp as $k=>$v){
					$data[$value][$v] = true;
				}
			}
		}
		if($data && count($data)>0){
			$array['area'] = serialize($data);
		}
		$array['fid'] = $fid;
		$this->model('freight')->zone_save($array,$id);
		$this->success();
	}

	public function zone_sort_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$val = $this->get('val','int');
		$this->model('freight')->zone_sort($id,$val);
		$this->success();
	}

	public function zone_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('freight')->zone_delete($id);
		$this->success();
	}

	public function price_f()
	{
		$fid = $this->get('fid','int');
		if(!$fid){
			$this->error('未指定模板ID');
		}
		$typelist = array('weight'=>P_Lang('重量'),'volume'=>P_Lang('体积'),'number'=>P_Lang('数量'),'fixed'=>P_Lang('固定值'),'price'=>P_Lang('价格'));
		$rs = $this->model('freight')->get_one($fid);
		$rs['type_title'] = $typelist[$rs['type']];
		$this->assign('rs',$rs);
		$this->assign('fid',$fid);
		$zonelist = $this->model('freight')->zone_all($fid,'id,title','id');
		if(!$zonelist){
			$this->error(P_Lang('未分配省市，请先设置区域'));
		}
		$zoneids = implode(",",array_keys($zonelist));
		$condition = "zid IN(".$zoneids.")";
		$rslist = $this->model('freight')->price_all($condition);
		if($rslist){
			$pricelist = $vlist = array();
			foreach($rslist as $key=>$value){
				$pricelist['phpok'.$value['unit_val']][$value['zid']] = $value['price'];
				$vlist[] = $value['unit_val'];
			}
			$vlist = array_unique($vlist);
			$this->assign('vlist',$vlist);
			$this->assign('rslist',$pricelist);
		}
		$this->assign('zonelist',$zonelist);
		//递增进度
		$step = 1;
		if($rs['type'] == 'weight'){
			$step = '0.5';
		}elseif($rs['type'] == 'volume'){
			$step = '0.01';
		}elseif($rs['type'] == 'price'){
			$step = '10';
		}
		$this->assign('step',$step);
		$this->view('freight_price');
	}

	public function price_save_f()
	{
		$fid = $this->get('fid');
		if(!$fid){
			$this->json(P_Lang('未指定模板ID'));
		}
		$unit_val = $this->get('unit_val');
		if(!$unit_val || !is_array($unit_val)){
			$this->json(P_Lang('未设置相应的数值'));
		}
		$zonelist = $this->model('freight')->zone_all($fid,'id,title','id');
		if(!$zonelist){
			$this->json(P_Lang('未分配省市，请先设置区域'));
		}
		foreach($zonelist as $key=>$value){
			$price[$value['id']] = $this->get('price'.$value['id']);
		}
		foreach($unit_val as $key=>$value){
			if($value){
				foreach($zonelist as $k=>$v){
					$data = array('unit_val'=>$value);
					$data['zid'] = $v['id'];
					if($value == 'fixed'){
						$data['price'] = $price[$v['id']][0];
					}else{
						$data['price'] = $price[$v['id']][$key];
					}
					$this->model('freight')->price_save($data);
				}
			}
		}
		$this->json(true);
	}

	public function price_delete_f()
	{
		$val = $this->get('val');
		if(!$val){
			$this->json(P_Lang('未指定值'));
		}
		$fid = $this->get('fid','int');
		if(!$fid){
			$this->json(P_Lang('未指定模板ID'));
		}
		$this->model('freight')->price_delete($fid,$val);
		$this->json(true);
	}

	public function province_city_f()
	{
		$id = $this->get('id');
		$country_id = $this->get('country_id');
		if(!$country_id){
			$this->error(P_Lang('未指定国家ID'));
		}
		if($id){
			$rs = $this->model('freight')->zone_one($id);
			if($rs && $rs['area']){
				$area = unserialize($rs['area']);
				$this->assign('area',$area);
			}
		}
		$province = $this->model('worlds')->get_all("pid='".$country_id."'");
		if(!$province){
			$this->error(P_Lang('国家下未配置省/州信息'));
		}
		$prolist = array();
		foreach($province as $key=>$value){
			$citylist = $this->model('worlds')->get_all("pid='".$value['id']."'");
			$prolist[] = array('id'=>$value['id'],'name'=>$value['name'],'name_en'=>$value['name_en'],'citylist'=>$citylist);
		}
		$this->assign('prolist',$prolist);
		$content = $this->fetch('freight_province_city');
		$this->success($content);
	}
}