<?php
/*****************************************************************************************
	文件： {phpok}/admin/freight_control.php
	备注： 运费模板管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月08日 03时16分
*****************************************************************************************/
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
		$rslist = $this->model('freight')->get_all();
		$this->assign('rslist',$rslist);
		$taxis = $rslist ? (count($rslist)+1) * 10 : 10;
		$this->assign('taxis',$taxis);
		$typelist = array('weight'=>P_Lang('重量'),'volume'=>P_Lang('体积'),'number'=>P_Lang('数量'),'fixed'=>P_Lang('固定值'));
		$this->assign('typelist',$typelist);
		$currency_list = $this->model('currency')->get_list();
		$this->assign('currency_list',$currency_list);
		$this->view('freight_index');
	}

	public function save_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
		}else{
			if(!$this->popedom['add']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
		}
		$title = $this->get('title');
		if(!$title){
			$this->json(P_Lang('名称不能为空'));
		}
		$taxis = $this->get('taxis');
		if(!$taxis){
			$taxis = 255;
		}
		$currency_id = $this->get('currency_id');
		$type = $this->get('type');
		$this->model('freight')->save(array('title'=>$title,'taxis'=>$taxis,'type'=>$type,'currency_id'=>$currency_id),$id);
		$this->json(true);
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('freight')->delete($id);
		$this->json(true);
	}

	//区域设置
	public function zone_f()
	{
		$fid = $this->get('fid');
		if(!$fid){
			error(P_Lang('未指定运费模板'),'','error');
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
			error(P_Lang('未指定ID'),$this->url('freight'),'error');
		}
		$area = $forbid = array();
		if($id){
			$rs = $this->model('freight')->zone_one($id);
			if(!$rs){
				error('数据不存在，请检查');
			}
			if($rs['area']){
				$area = unserialize($rs['area']);
			}
			$this->assign('rs',$rs);
			$this->assign('id',$id);
			$fid = $rs['fid'];
		}else{
			$count = $this->model('freight')->zone_count($fid);
			$taxis = $count ? ($count+1)*10 : 10;
			$this->assign('taxis',$taxis);
		}
		$fs = $this->model('freight')->get_one($fid);
		$this->assign('fs',$fs);
		$all = $this->model('freight')->zone_all($fid,'*');
		if($all){
			foreach($all as $key=>$value){
				if($id && $value['id'] == $id){
					continue;
				}
				$tmp = $value['area'] ? unserialize($value['area']) : array();
				foreach($tmp as $k=>$v){
					if($v && is_array($v)){
						foreach($v as $kk=>$vv){
							$forbid[$k][$kk] = true;
						}
					}
				}
			}
		}
		$this->assign('area',$area);
		$this->assign('fid',$fid);
		//读取当前省市表信息
		$prolist = $this->lib('xml')->read($this->dir_root.'data/xml/provinces.xml');
		$citylist = $this->lib('xml')->read($this->dir_root.'data/xml/cities.xml');
		if(!$prolist && !$citylist){
			error(P_Lang('数据异常，省市表信息不存在'),'','error');
		}
		$province = array();
		foreach($prolist['province'] as $key=>$value){
			$province[$value['attr']['id']] = $value['val'];
		}
		unset($prolist);
		foreach($citylist['city'] as $key=>$value){
			$prolist[$province[$value['attr']['pid']]][$value['val']] = true;
		}
		foreach($prolist as $key=>$value){
			if($value){
				foreach($value as $k=>$v){
					if($forbid[$key][$k]){
						unset($prolist[$key][$k]);
					}
				}
			}
		}
		foreach($prolist as $key=>$value){
			if(!$value){
				unset($prolist[$key]);
			}
		}
		if(!$prolist || count($prolist)<1){
			error('所有省市地址已分配完成，请点编辑进行调节',$this->url('freight','zone','fid='.$fid));
		}
		$this->assign('prolist',$prolist);
		$this->view('freight_zone_setting');
	}

	public function zone_save_f()
	{
		$fid = $this->get('fid');
		$id = $this->get('id');
		if(!$fid && !$id){
			error(P_Lang('未指定ID'),$this->url('freight'),'error');
		}
		if($id){
			$rs = $this->model('freight')->zone_one($id);
			$fid = $rs['fid'];
		}
		$array = array('title'=>$this->get('title'),'note'=>$this->get('note'),'taxis'=>$this->get('taxis','int'));
		$area = $this->get('area');
		if(!$area){
			error(P_Lang('未选定所在省份'),$this->url('freight','zone_setting','fid='.$fid.'&id='.$id),'error');
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
		error(P_Lang('区域信息操作成功'),$this->url('freight','zone','fid='.$fid),'ok');
	}

	public function zone_sort_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$val = $this->get('val','int');
		$this->model('freight')->zone_sort($id,$val);
		$this->json(true);
	}

	public function zone_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('freight')->zone_delete($id);
		$this->json(true);
	}

	public function price_f()
	{
		$fid = $this->get('fid','int');
		if(!$fid){
			$this->error('未指定模板ID');
		}
		$typelist = array('weight'=>P_Lang('重量'),'volume'=>P_Lang('体积'),'number'=>P_Lang('数量'),'fixed'=>P_Lang('固定值'));
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
}

?>