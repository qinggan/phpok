<?php
/**
 * 接入节点_管理全球国家及州/省信息
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年05月27日 19时51分
**/
namespace phpok\app\worlds;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class nodes_phpok extends \_init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	//解决不同国家不同价格
	public function PHPOK_arclist()
	{
		$rslist = $this->data('rslist');
		$pid = $this->data('pid');
		if(!$rslist || !is_array($rslist) || !$pid || !is_numeric($pid)){
			return false;
		}
		$ids = array_keys($rslist);
		$region = $this->session->val('region');
		if(!$region){
			return false;
		}
		$pricelist = $this->model('worlds')->price_all($ids);
		if(!$pricelist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if(!$value['apps']){
				$value['apps'] = array();
			}
			if(isset($pricelist[$value['id']])){
				$value['apps']['worlds'] = $pricelist[$value['id']];
				if(isset($pricelist[$value['id']]['price']) && isset($pricelist[$value['id']]['price'][$region['id']])){
					$prices = $pricelist[$value['id']]['price'][$region['id']];
					if($prices && isset($prices['price']) && $prices['price']){
						$value['price'] = $prices['price'];
					}
					if($prices && isset($prices['currency_id']) && $prices['currency_id']){
						$value['currency_id'] = $prices['currency_id'];
					}
				}
			}
			$rslist[$key] = $value;
		}
		$this->data('rslist',$rslist);
		return true;
	}

	public function PHPOK_arc()
	{
		$arc = $this->data('arc');
		if(!$arc){
			return false;
		}
		if(!$arc['price']){
			return false;
		}
		$region = $this->session->val('region');
		if(!$region){
			return false;
		}
		$pricelist = $this->model('worlds')->price_all($arc['id']);
		if(!$pricelist || !$pricelist[$arc['id']]){
			return false;
		}
		if(!$arc['apps']){
			$arc['apps'] = array();
		}
		$arc['apps']['worlds'] = $pricelist[$arc['id']];
		if(isset($pricelist[$arc['id']]['price']) && $pricelist[$arc['id']]['price']){
			if(isset($pricelist[$arc['id']]['price'][$region['id']]) && $pricelist[$arc['id']]['price'][$region['id']]){
				$price = $pricelist[$arc['id']]['price'][$region['id']];
				if($price && $price['val']){
					$arc['price'] = $price['val'];
				}
				if($price && $price['currency_id']){
					$arc['currency_id'] = $price['currency_id'];
				}
			}
		}
		$this->data('arc',$arc);
		return true;
	}

	public function PHPOK_project()
	{
		//这里开始编写PHP代码
	}

	public function PHPOK_catelist()
	{
		//这里开始编写PHP代码
	}

	public function PHPOK_cate()
	{
		//这里开始编写PHP代码
	}

	public function api_cart_index_after($data)
	{
		if(!is_array($data)){
			return false;
		}
		$rslist = $data['rslist'];
		$freeship = 0;
		foreach($rslist as $key=>$value){
			if(!$value['tid']){
				continue;
			}
			$arc = phpok('_arc','title_id='.$value['tid']);
		}
		$data['price'] = '1000';
		$this->success($data);
	}

	public function www_cart_review_after()
	{
		$this->www_cart_checkout_after();
	}

	//系统自动生成的节点
	public function www_cart_checkout_after()
	{
		$country_id = 7;
		if($this->session->val('region')){
			$country_id = $this->session->val('region.id');
		}
		$pricelist = $this->tpl->val('pricelist');
		if(!$pricelist){
			return false;
		}
		$rslist = $this->tpl->val('rslist');
		if(!$rslist){
			return false;
		}
		$this->data('rslist',$rslist);
		$this->data('pricelist',$pricelist);
		$pricelist = $this->system_pricelist();
		$total_price = 0;
		foreach($pricelist as $key=>$value){
			$total_price += $value['price_val'];
		}
		$this->assign('pricelist',$pricelist);
		$price = price_format($total_price,$this->site['currency_id']);
		$this->assign('price',$price);
		$this->assign('price_val',$total_price);
	}


	//后台删除文章主题触发事件
	public function system_admin_title_delete($id)
	{
		$this->model('worlds')->price_delete($id);
		return true;
	}

	public function system_admin_title_success($id,$project)
	{
		if(!$project['is_biz']){
			return false;
		}
		$price = $this->get('_price');
		$freight = $this->get('_freight');
		$excise = $this->get("_excise");
		$tariff = $this->get("_tariff");
		$note = $this->get('_note');
		if($price && is_array($price)){
			$this->model('worlds')->price_save($id,$price,'price');
		}else{
			$this->model('worlds')->price_delete($id,'price');
		}
		if($freight && is_array($freight)){
			$this->model('worlds')->price_save($id,$freight,'freight');
		}else{
			$this->model('worlds')->price_delete($id,'freight');
		}
		if($excise && is_array($excise)){
			$this->model('worlds')->price_save($id,$excise,'excise');
		}else{
			$this->model('worlds')->price_delete($id,'excise');
		}
		if($tariff && is_array($tariff)){
			$this->model('worlds')->price_save($id,$tariff,'tariff');
		}else{
			$this->model('worlds')->price_delete($id,'tariff');
		}
		if($note && is_array($note)){
			$this->model('worlds')->price_save($id,$note,'note');
		}else{
			$this->model('worlds')->price_delete($id,'note');
		}
		return true;
	}

	public function system_cart_product_id()
	{
		$region = $this->session->val('region');
		if(!$region){
			return false;
		}
		$rs = $this->data('product_rs');
		if(!$rs || !$rs['apps'] || !$rs['apps']['worlds']){
			return false;
		}
		$cart_rs = $this->data('cart_rs');
		if(!$cart_rs){
			return false;
		}
		$w = $rs['apps']['worlds'];
		if(!$w['note'] || !$w['note'][$region['id']]){
			return false;
		}
		$note = false;
		if($w['excise'] && $w['excise'][$region['id']] && $w['excise'][$region['id']]['val'] == '-1'){
			$note = true;
		}
		if($w['tariff'] && $w['tariff'][$region['id']] && $w['tariff'][$region['id']]['val'] == '-1'){
			$note = true;
		}
		if($note){
			$cart_rs['note'] = $w['note'][$region['id']]['val'];
			$this->data('cart_rs',$cart_rs);
			return true;
		}
		return false;
	}

	/**
	 * 系统加载站点信息
	**/
	public function system_init_site()
	{
		$site_rs = $this->data("site_rs");
		$region = $this->session->val('region');
		if(!$region){
			return false;
		}
		if($region['site_id'] && $region['site_id'] != $site_rs['id']){
			$site_rs = $this->model('site')->site_info($region['site_id']);
		}
		//检测
		if($region['currency_id']){
			$site_rs['currency_id'] = $region['currency_id'];
		}
		if($region['tpl_id']){
			$site_rs['tpl_id'] = $region['tpl_id'];
		}
		if($region['lang_code']){
			$site_rs['lang'] = $region['lang_code'];
		}
		if($region['freight_id']){
			$site_rs['biz_freight'] = $region['freight_id'];
		}
		//消费税比例
		if($region['excise_rate']){
			$site_rs['excise_rate'] = $region['excise_rate'];
		}
		//关税比例
		if($region['tariff_rate']){
			$site_rs['tariff_rate'] = $region['tariff_rate'];
		}
		$this->data("site_rs",$site_rs);
		return true;
	}

	/**
	 * 计算价格参数
	**/
	public function system_pricelist()
	{
		$address = $this->data('address');
		if($address){
			$this->undata('address');
		}
		$rslist = $this->data('rslist');
		if($rslist){
			$this->undata('rslist');
		}
		if(!$rslist){
			return false;
		}
		$pricelist = $this->data('pricelist');
		if($pricelist){
			$this->undata('pricelist');
		}
		if(!$pricelist){
			return false;
		}
		if($address && is_numeric($address['country'])){
			$country_id = $address['country'];
			$worlds = $this->model('worlds')->get_one($country_id);
		}else{
			$country_id = 0;
			if($address && is_array($address) && $address['country']){
				$tmp_c = "name='".$address['country']."' OR name_en='".$address['country']."'";
				$tmplist = $this->model('worlds')->get_all($tmp_c);
				if($tmplist){
					$worlds = current($tmplist);
					$country_id = $worlds['id'];
				}
			}
			if(!$country_id && $this->session->val('region')){
				$country_id = $this->session->val('region.id');
			}
			if(!$country_id){
				$country_id = 7;
			}
			if($country_id && !$worlds){
				$worlds = $this->model('worlds')->get_one($country_id);
			}
		}
		$excise_rate = $worlds['excise_rate']; //消费税计算
		$tariff_rate = $worlds['tariff_rate']; //关税计算
		
		//计算运费，消费税，关税，产品价格
		$shipping = $excise = $tariff = $price = 0;
		foreach($rslist as $key=>$value){
			$price += $value['price'] * $value['qty'];
			if(!$value['tid']){
				continue;
			}
			$arc = phpok('_arc','title_id='.$value['tid']);
			if(!$arc['apps'] || !$arc['apps']['worlds']){
				continue;
			}
			$project = $this->model('project')->get_one($arc['project_id'],false);
			$currency_id = $arc['currency_id'];
			if(!$currency_id){
				$currency_id = $project['currency_id'];
			}
			if(!$currency_id){
				$currency_id = $this->site['currency_id'];
			}
			$obj = $arc['apps']['worlds'];
			if(!$arc['is_virtual']){
				//检测运费
				if($obj['freight'] && isset($obj['freight'][$country_id])){
					$tmp = $obj['freight'][$country_id];
					if(!$tmp['currency_id']){
						$tmp['currency_id'] = $this->site['currency_id'];
					}
					$shipping += price_format_val($tmp['price'],$tmp['currency_id'],$this->site['currency_id']);
				}else{
					//计算运费
					$freight_id = $worlds['freight_id'];
					if(!$freight_id && $project && $project['freight']){
						$freight_id = $project['freight'];
					}
					if(!$freight_id){
						$freight_id = $this->site['biz_freight'];
					}
					if($freight_id){
						$freight_rs = $this->model('freight')->get_one($freight_id);
						$tmp_freight = $this->model('cart')->freight_price($freight_id,$address['province'],$address['city']);
						if($tmp_freight){
							$tmp_currency_id = $freight_rs['currency_id'] ? $freight_rs['currency_id'] : $this->site['currency_id'];
							$shipping += price_format_val($tmp_freight,$tmp_currency_id,$this->site['currency_id']);
						}
					}
				}
			}
			//计算消费税
			if($obj['excise'] && isset($obj['excise'][$country_id])){
				$tmp = $obj['excise'][$country_id];
				if(!$tmp['currency_id']){
					$tmp['currency_id'] = $this->site['currency_id'];
				}
				$excise += price_format_val($tmp['price'],$tmp['currency_id'],$this->site['currency_id']);
			}else{
				$tmp = price_format_val($value['price']*$value['qty']*$excise_rate,$currency_id,$this->site['currency_id']);
				if($address && is_array($address) && $address['province']){
					$tmp_c = "(name='".$address['province']."' OR name_en='".$address['province']."') AND pid='".$country_id."'";
					$tmplist = $this->model('worlds')->get_all($tmp_c);
					if($tmplist){
						$tmp_rs = current($tmplist);
						if($tmp_rs['excise_rate']){
							$tmp = price_format_val($value['price']*$value['qty']*$tmp_rs['excise_rate'],$currency_id,$this->site['currency_id']);
						}
					}
				}
				$excise += $tmp;
			}
			//检测关税
			if($obj['tariff'] && isset($obj['tariff'][$country_id])){
				$tmp = $obj['tariff'][$country_id];
				if(!$tmp['currency_id']){
					$tmp['currency_id'] = $this->site['currency_id'];
				}
				$tariff += price_format_val($tmp['price'],$tmp['currency_id'],$this->site['currency_id']);
			}else{
				$tmp = price_format_val($value['price']*$value['qty']*$tariff_rate,$currency_id,$this->site['currency_id']);
				if($address && is_array($address) && $address['province']){
					$tmp_c = "(name='".$address['province']."' OR name_en='".$address['province']."') AND pid='".$country_id."'";
					$tmplist = $this->model('worlds')->get_all($tmp_c);
					if($tmplist){
						$tmp_rs = current($tmplist);
						if($tmp_rs['tariff_rate']){
							$tmp = price_format_val($value['price']*$value['qty']*$tmp_rs['tariff_rate'],$currency_id,$this->site['currency_id']);
						}
					}
				}
				$tariff += $tmp;
			}
		}
		$discount = 0;
		foreach($pricelist as $key=>$value){
			if($value['identifier'] == 'product'){
				$value['price'] = price_format($price,$this->site['currency_id']);
				$value['price_val'] = $price;
			}
			if($value['identifier'] == 'shipping' && $shipping > $value['price_val']){
				$value['price'] = price_format($shipping,$this->site['currency_id']);
				$value['price_val'] = $shipping;
			}
			if($value['identifier'] == 'excise'){
				$value['price'] = price_format($excise,$this->site['currency_id']);
				$value['price_val'] = price_format_val($excise,$this->site['currency_id']);
			}
			if($value['identifier'] == 'tariff'){
				$value['price'] = price_format($tariff,$this->site['currency_id']);
				$value['price_val'] = price_format_val($tariff,$this->site['currency_id']);
			}
			$pricelist[$key] = $value;
		}
		$this->data('pricelist',$pricelist);
		return $pricelist;
	}
	
}
