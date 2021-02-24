<?php
/**
 * 易宠接口<后台应用>
 * @作者 苏相锟
 * @版本 5.7
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年01月11日 08时59分
**/
class admin_epetbar extends phpok_plugin
{
	public $me;
	private $epet;
	private $brand_pid = 14;//品牌店
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
		if($this->me && $this->me['param']){
			$this->epet = $this->me['param'];
		}
	}

	/**
	 * 易宠产品管理
	**/
	public function elist()
	{
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1) * $psize;
		$condition = "1=1";
		$keywords = $this->get('keywords');
		$pageurl = $this->url('plugin','exec','_phpokid=epetbar&exec=elist');
		if($keywords){
			$condition .= " AND goods_title LIKE '%".$keywords."%'";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign('keywords',$keywords);
		}
		//查询数量
		$sql = "SELECT count(id) FROM ".$this->db->prefix."plugins_epet WHERE ".$condition;
		$total = $this->db->count($sql);
		if($total>0){
			//读取列表数据
			$sql = "SELECT * FROM ".$this->db->prefix."plugins_epet WHERE ".$condition." ORDER BY createtime DESC,id DESC LIMIT ".$offset.",".$psize;
			$rslist = $this->db->get_all($sql);
			if($rslist){
				$ids = array();
				foreach($rslist as $key=>$value){
					$value['is_sell'] = false;
					if($value['tid']){
						$ids[] = $value['tid'];
					}
					$rslist[$key] = $value;
				}
				if($ids && count($ids)>0){
					$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id IN(".implode(",",$ids).")";
					$tmplist = $this->db->get_all($sql,'id');
					if($tmplist){
						foreach($rslist as $key=>$value){
							if($value['tid'] && $tmplist[$value['tid']] && !$tmplist[$value['tid']]['hidden']){
								$value['is_sell'] = true;
							}
							$rslist[$key] = $value;
						}
					}
				}
			}
			$string = P_Lang("home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=数量：(total)/(psize)，页码：(num)/(total_page)&always=1");
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('total',$total);
			$this->assign('pageid',$pageid);
			$this->assign('psize',$psize);
			$this->assign('pageurl',$pageurl);
			$this->assign("pagelist",$pagelist);
			$this->assign("rslist",$rslist);
		}
		$this->_view('admin_epet_list.html');
	}

	public function del()
	{
		$ids = $this->get('ids');
		if(!$ids){
			$this->error('未指定要删除的产品');
		}
		$sql = "DELETE FROM ".$this->db->prefix."plugins_epet WHERE id IN(".$ids.")";
		$this->db->query($sql);
		$this->success();
	}

	public function up_market()
	{
		$ids = $this->get('ids');
		if(!$ids){
			$this->error('未指定要操作的产品');
		}
		$pid = $this->epet['pid'];
		if(!$pid){
			$this->error('未指定商城');
		}
		$project = $this->model('project')->get_one($pid);
		$brand_project = $this->model('project')->get_one($this->brand_pid);
		$sql = "SELECT * FROM ".$this->db->prefix."plugins_epet WHERE id IN(".$ids.")";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$this->error('产品信息不存在');
		}
		$res_cate = $this->model('rescate')->get_default();
		
		foreach($rslist as $key=>$value){
			if($value['tid'] && $value['stock'] && $value['sku_status']){
				$sql = "UPDATE ".$this->db->prefix."list_".$project['module']." SET stock='".$value['stock']."' WHERE id='".$value['tid']."'";
				$this->db->query($sql);
				$sql = "UPDATE ".$this->db->prefix."list SET hidden=0 WHERE id='".$value['tid']."'";
				$this->db->query($sql);
				continue;
			}
			if($value['tid'] || !$value['stock'] || !$value['sku_status'] || !$value['sale_price']){
				continue;
			}
			$tmpdata = array();
			$tmpdata['title'] = $value['goods_title'];
			$tmpdata['status'] = 0;
			if($value['main_picture']){
				$tmp_id = $this->pic2id($value['main_picture'],$res_cate);
				if($tmp_id){
					$tmpdata['thumb'] = $tmp_id;
				}
			}
			if($value['normal_picture_list']){
				$tmp = explode("\n",$value['normal_picture_list']);
				$tmp_ids = array();
				foreach($tmp as $k=>$v){
					$tmp_id = $this->pic2id($v,$res_cate);
					if($tmp_id){
						$tmp_ids[] = $tmp_id;
					}
				}
				if($tmp_ids && count($tmp_ids)>0){
					$tmpdata['pictures'] = implode(",",$tmp_ids);
				}
			}
			$tmpdata['stock'] = $value['stock'];
			$sql = "SELECT * FROM ".$this->db->prefix."list WHERE title='".$value['brand_name']."' AND project_id='".$this->brand_pid."'";
			$chk = $this->db->get_one($sql);
			if($chk){
				$tmpdata['brand'] = $chk['id'];
			}else{
				$brands = array();
				$brands['title'] = $value['brand_name'];
				$brands['status'] = 1;
				$brand_id = phpok_post_save($brands,$this->brand_pid);
				if($brand_id){
					$tmpdata['brand'] = $brand_id;
				}
			}
			$tmpdata['market_price'] = round($value['sale_price'] * 1.2);//取整
			$tmpdata['epet_id'] = $value['goods_gid'];
			$tmpdata['video'] = $value['video_picture'];
			$content = $value['phone_describe_text'] ? $value['phone_describe_text'] : $value['pc_describe_text'];
			if($content){
				$content = str_replace("http://","https://",$content);
				//$tmpdata['content'] = phpok_img_local($value['pc_describe_text']);
				$tmpdata['content'] = $content;
			}
			$tmpdata['price'] = $value['sale_price'];
			if($value['weight']){
				$tmpdata['weight'] = round($value['weight']/1000,2);
			}
			$tmpdata['supplier'] = 760;
			$tid = phpok_post_save($tmpdata,$project['id']);
			$sql = "UPDATE ".$this->db->prefix."plugins_epet SET tid='".$tid."' WHERE id='".$value['id']."'";
			$this->db->query($sql);
		}
		$this->success();
	}

	private function pic2id($file,$cate_rs,$is_array=false)
	{
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/"){
			$folder .= date($cate_rs["folder"],$this->time);
		}
		if(substr($folder,-1) != '/'){
			$folder .= '/';
		}
		$id = md5($file);
		$extlist = explode(".",$file);
		$ext = end($extlist);
		if($ext == 'jfif'){
			$ext = 'jpg';
		}
		$content = $this->lib('curl')->get_content($file);
		$this->lib('file')->save_pic($content,$this->dir_root.$folder.$id.'.'.$ext);
		$array = array();
		$array["cate_id"] = $cate_rs['id'];
		$array["folder"] = $folder;
		$array["name"] = $id.'.'.$ext;
		$array["ext"] = $ext;
		$array["filename"] = $folder.$id.'.'.$ext;
		$array["addtime"] = $this->time;
		$array['title'] = basename($file);
		$img_ext = getimagesize($this->dir_root.$folder.$id.'.'.$ext);
		if($img_ext && $img_ext[0] && $img_ext[1]){
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		$insert_id = $this->model('res')->save($array);
		$this->model('res')->gd_update($insert_id);
		if($is_array){
			$rs = $this->model('res')->get_one($insert_id);
			return $rs;
		}
		return $insert_id;
	}

	public function down_market()
	{
		$ids = $this->get('ids');
		if(!$ids){
			$this->error('未指定要操作的产品');
		}
		$pid = $this->epet['pid'];
		if(!$pid){
			$this->error('未指定商城');
		}
		$project = $this->model('project')->get_one($pid);
		$sql = "SELECT * FROM ".$this->db->prefix."plugins_epet WHERE id IN(".$ids.")";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$this->error('产品信息不存在');
		}
		foreach($rslist as $key=>$value){
			if($value['tid']){
				$sql = "UPDATE ".$this->db->prefix."list SET hidden=1 WHERE id='".$value['tid']."'";
				$this->db->query($sql);
			}
		}
		$this->success();
	}

	public function update_pro()
	{
		$ids = $this->get('ids');
		if(!$ids){
			$this->error('未指定要操作的产品');
		}
		$pid = $this->epet['pid'];
		if(!$pid){
			$this->error('未指定商城');
		}
		$project = $this->model('project')->get_one($pid);
		$sql = "SELECT * FROM ".$this->db->prefix."plugins_epet WHERE id IN(".$ids.")";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$this->error('产品信息不存在');
		}
		$goods = array();
		$tlist = array();
		foreach($rslist as $key=>$value){
			$goods[] = $value['goods_gid'];
			if($value['tid']){
				$tlist[$value['goods_gid']] = $value['tid'];//
			}
		}
		$this->lib("epet")->app_id($this->epet['app_id']);
		$this->lib("epet")->app_secret($this->epet['app_secret']);
		$this->lib("epet")->session_key($this->epet['session_key']);
		$this->lib("epet")->url($this->epet['url']);
		$data = $this->lib('epet')->product_ids($goods);
		if(!$data || !is_array($data)){
			$this->error('获取数据失败');
		}
		if($data['status_code'] != '200'){
			$this->error($data['message']);
		}
		if(!$data['data']){
			$this->error('内容为空');
		}
		foreach($data['data'] as $key=>$value){
			$tmpdata = $value;
			$tmpdata['synctime'] = $this->time;
			unset($tmpdata['barcode'],$tmpdata['goods_gid']);
			if($value['barcode']){
				$tmpdata['barcode'] = implode(",",$value['barcode']);
			}
			$this->db->update($tmpdata,"plugins_epet",array('goods_gid'=>$value['goods_gid']));
		}
		//检查产品库存
		$data = $this->lib('epet')->product_stock($goods);
		if($data && $data['data']){
			foreach($data['data'] as $key=>$value){
				$tmpdata = array();
				$tmpdata['stock'] = $value['stock_num'];
				$tmpdata['wid'] = $value['wid'];
				$tmpdata['warehouse_name'] = $value['warehouse_name'];
				$this->db->update($tmpdata,'plugins_epet',array('goods_gid'=>$value['goods_gid']));
				//如果有记录
				if($tlist[$value['goods_gid']]){
					$tid = $tlist[$value['goods_gid']];
					$tmpdata = array("stock"=>$value['stock_num']);
					$this->db->update($tmpdata,'list_'.$project['module'],array('id'=>$tid));
					if(!$value['stock_num']){
						$sql = "UPDATE ".$this->db->prefix."list SET hidden=1 WHERE id='".$tmp['id']."'";
						$this->db->query($sql);
					}
				}
			}
		}
		//更新产品明细
		$data = $this->lib('epet')->product_info($goods);
		if($data && $data['data']){
			foreach($data['data'] as $key=>$value){
				$tmpdata = array();
				$tmpdata['main_picture'] = $value['main_picture'];
				$tmpdata['video'] = $value['video'];
				$tmpdata['video_picture'] = $value['video_picture'];
				$tmpdata['normal_picture_list'] = $value['normal_picture_list'] ? implode("\n",$value['normal_picture_list']) : '';
				$tmpdata['pc_describe_text'] = $value['pc_describe_text'] ? addslashes($value['pc_describe_text']) : '';
				$tmpdata['phone_describe_text'] = $value['phone_describe_text'] ? addslashes($value['phone_describe_text']) : '';
				$this->db->update($tmpdata,'plugins_epet',array('goods_gid'=>$value['goods_gid']));
			}
		}
		$this->success();
	}
	
	public function importdata()
	{
		$pageid = $this->get('pageid','int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$this->lib("epet")->app_id($this->epet['app_id']);
		$this->lib("epet")->app_secret($this->epet['app_secret']);
		$this->lib("epet")->session_key($this->epet['session_key']);
		$this->lib("epet")->url($this->epet['url']);
		$data = $this->lib('epet')->product_list($pageid,$psize);
		if(!$data || !is_array($data)){
			$this->error('获取数据失败');
		}
		if($data['status_code'] != '200'){
			$this->error($data['message']);
		}
		if(!$data['data']){
			$this->error('内容为空');
		}
		$meta = $data['meta']['pagination'];
		$total = $meta['total'];
		$total_pages = $meta['total_pages'];
		$count = $psize *  ($pageid-1);
		$list = $data['data'];
		$ids = array();
		$tlist = array();
		foreach($list as $key=>$value){
			$ids[] = $value['goods_gid'];
			$sql = "SELECT * FROM ".$this->db->prefix."plugins_epet WHERE goods_gid='".$value['goods_gid']."'";
			$tmp = $this->db->get_one($sql);
			$tmpdata = $value;
			$tmpdata['synctime'] = $this->time;
			unset($tmpdata['barcode']);
			if($value['barcode']){
				$tmpdata['barcode'] = implode(",",$value['barcode']);
			}
			if($tmp){
				if($tmp['tid']){
					$tlist[$value['goods_gid']] = $tmp['tid'];
				}
				$this->db->update($tmpdata,"plugins_epet",array('id'=>$tmp['id']));
			}else{
				$this->db->insert($tmpdata,'plugins_epet');
			}
			$count++;
		}
		//检查产品库存
		$data = $this->lib('epet')->product_stock($ids);
		if($data && $data['data']){
			foreach($data['data'] as $key=>$value){
				$sql  = " UPDATE ".$this->db->prefix."plugins_epet SET ";
				$sql .= " stock='".$value['stock_num']."',wid='".$value['wid']."',warehouse_name='".$value['warehouse_name']."' ";
				$sql .= " WHERE goods_gid='".$value['goods_gid']."'";
				$this->db->query($sql);
				//如果有记录
				if($tlist[$value['goods_gid']]){
					//更新库存
					$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id='".$tlist[$value['goods_gid']]."'";
					$tmp = $this->db->get_one($sql);
					if($tmp){
						$sql = "UPDATE ".$this->db->prefix."list_".$tmp['module_id']." SET stock='".$value['stock_num']."' WHERE id='".$tmp['id']."'";
						$this->db->query($sql);
						//如果库存变为0，自动隐藏产品信息（即下架产品信息）
						if(!$value['stock_num']){
							$sql = "UPDATE ".$this->db->prefix."list SET hidden=1 WHERE id='".$tmp['id']."'";
							$this->db->query($sql);
						}
					}
				}
			}
		}
		//更新产品明细
		$data = $this->lib('epet')->product_info($ids);
		if($data && $data['data']){
			foreach($data['data'] as $key=>$value){
				$sql  = " UPDATE ".$this->db->prefix."plugins_epet SET ";
				$sql .= " main_picture='".$value['main_picture']."',video='".$value['video']."',video_picture='".$value['video_picture']."' ";
				if($value['normal_picture_list']){
					$sql .= ",normal_picture_list='".implode("\n",$value['normal_picture_list'])."' ";
				}
				if($value['pc_describe_text']){
					$text = addslashes($value['pc_describe_text']);
					$sql .= ",pc_describe_text='".$text."' ";
				}
				if($value['phone_describe_text']){
					$text = addslashes($value['phone_describe_text']);
					$sql .= ",phone_describe_text='".$text."' ";
				}
				$sql .= " WHERE goods_gid='".$value['goods_gid']."'";
				$this->db->query($sql);
			}
		}
		$nextpage = $pageid+1;
		if($nextpage>$total_pages){
			$this->success('数据已全部更新，请手动关闭窗口');
		}
		$url = $this->url('plugin','exec','_phpokid=epetbar&exec=importdata&pageid='.$nextpage);
		$this->success('正在导入数据，共有：'.$total.'，当前已导入'.$count.'，请稍候…',$url);
	}
}