<?php
/**
 * 接口应用_管理全球国家及州/省信息
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
class api_control extends \phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$continent = $this->model('worlds')->get_all('pid=0 AND status=1');
		if(!$continent){
			$this->error(P_Lang('未找到相应的洲信息'));
		}
		$ids = array();
		foreach($continent as $key=>$value){
			$ids[] = $value['id'];
		}
		$condition = "pid IN(".implode(",",$ids).") AND status=1";
		$tmplist = $this->model('worlds')->get_all($condition);
		if(!$tmplist){
			$this->error(P_Lang('没有找到相应的国家信息'));
		}
		foreach($tmplist as $key=>$value){
			$sublist[$value['pid']][] = $value;
		}
		$grouplist = array();
		foreach($continent as $key=>$value){
			if($sublist[$value['id']]){
				$value['rslist'] = $sublist[$value['id']];
				$grouplist[$key] = $value;
			}
		}
		$this->assign('countrylist',$grouplist);
		$html = $this->display('api_country',true);
		$this->success($html);
	}

	/**
	 * 变更国家信息
	**/
	public function change_f()
	{
		$country_id = $this->get('country_id');
		if(!$country_id){
			$this->error(P_Lang('未指定国家ID'));
		}
		$rs = $this->model('worlds')->get_one($country_id);
		if(!$rs){
			$this->error(P_Lang('国家信息不存在'));
		}
		if(!$rs['status']){
			$this->errpr(P_Lang('国家未启用，请联系客服'));
		}
		//清空购物车
		$this->model('cart')->clear_cart();
		//清空缓存（用于解决价格不同步问题）
		$this->cache->clear();
		$this->session->assign('region',$rs);
		$this->success($rs);
	}

	//计算税收
	public function tax_f()
	{
		$this->config('is_ajax',true);
		$this->session->unassign('tax');
		$id = $this->get('area_id');
		if(!$id){
			$data = array();
			$data['price'] = '0';
			$data['price_txt'] = price_format('0');
			$data['price_val'] = price_format_val('0');
			$this->success($data);
		}
		$world = $this->model('worlds')->get_one($id);
		if(!$world){
			$data = array();
			$data['price'] = '0';
			$data['price_txt'] = price_format('0');
			$data['price_val'] = price_format_val('0');
			$this->success($data);
		}
		if(!$world['tax_rate']){
			$data = array();
			$data['price'] = '0';
			$data['price_txt'] = price_format('0');
			$data['price_val'] = price_format_val('0');
			$this->success($data);
		}
		$rate = $world['tax_rate'] > 1 ? round($world['tax_rate']/100,2) : $world['tax_rate'];
		$cart_id = $this->model('cart')->cart_id($this->session->sessid(),$this->session->val('user_id'));
		$rslist = $this->model('cart')->get_all($cart_id);
		if(!$rslist){
			$data = array();
			$data['price'] = '0';
			$data['price_txt'] = price_format('0');
			$data['price_val'] = price_format_val('0');
			$this->success($data);
		}
		$totalprice = 0;
		foreach($rslist as $key=>$value){
			$totalprice += price_format_val($value['price'] * $value['qty']);
		}
		$price = $rate * $totalprice;
		$this->session->assign('tax',$price);
		$data = array();
		$data['price'] = $price;
		$data['price_txt'] = price_format($price);
		$data['price_val'] = price_format_val($price);
		$this->success($data);
	}

	public function city_f()
	{
		$langcode = $this->session->val('www_lang_id');
		if(!$langcode){
			$langcode = $this->site['lang'];
		}
		if(!$langcode || $langcode == 'default'){
			$langcode = 'cn';
		}
		$country = $this->get('country');
		if(!$country){
			$this->error(P_Lang('未指定国家'));
		}
		$province = $this->get('province');
		if(!$province){
			$this->error(P_Lang('未指定省份信息'));
		}
		$tmp = $this->model('worlds')->get_one($country,'name_en');
		if(!$tmp){
			$tmp = $this->model('worlds')->get_one($country,'name');
		}
		if(!$tmp){
			$this->error(P_Lang('国家信息不存在'));
		}
		$country_id = $tmp['id'];
		if(!$country_id){
			$this->error(P_Lang('未指定国家ID'));
		}
		$condition  = "pid='".$country_id."'";
		$condition .= " AND (name='".$province."' OR name_en='".$province."') ";
		$tmplist = $this->model('worlds')->get_all($condition);
		if(!$tmplist){
			$this->error(P_Lang('省份信息不存在'));
		}
		$province_rs = current($tmplist);
		$condition = "pid='".$province_rs['id']."'";
		$tmplist = $this->model('worlds')->get_all($condition);
		if(!$tmplist){
			$this->success();
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$value['title'] = $langcode == 'cn' ? $value['name'] : $value['name_en'];
			$rslist[] = $value;
		}
		$this->success($rslist);
	}

	public function country_f()
	{
		$langcode = $this->session->val('www_lang_id');
		if(!$langcode){
			$langcode = $this->site['lang'];
		}
		if(!$langcode || $langcode == 'default'){
			$langcode = 'cn';
		}
		$tlist = $this->_country($langcode);
		$this->success($tlist['grouplist']);
	}

	private function _country($langcode='cn')
	{
		$tmplist = $this->model('worlds')->get_all('pid=0 AND status=1');
		if(!$tmplist){
			$this->error(P_Lang('未找到相应的洲信息'));
		}
		$ids = array();
		$continent = array();
		foreach($tmplist as $key=>$value){
			$ids[] = $value['id'];
			$value['title'] = $langcode == 'cn' ? $value['name'] : $value['name_en'];
			$continent[] = $value;
		}
		$condition = "pid IN(".implode(",",$ids).") AND status=1";
		$tmplist = $this->model('worlds')->get_all($condition);
		if(!$tmplist){
			$this->error(P_Lang('没有找到相应的国家信息'));
		}
		$countrylist = array();
		foreach($tmplist as $key=>$value){
			$value['title'] = $langcode == 'cn' ? $value['name'] : $value['name_en'];
			$sublist[$value['pid']][] = $value;
			$countrylist[] = $value;
		}
		$grouplist = array();
		foreach($continent as $key=>$value){
			if($sublist[$value['id']]){
				$value['rslist'] = $sublist[$value['id']];
				$grouplist[] = $value;
			}
		}
		return array('countrylist'=>$countrylist,'grouplist'=>$grouplist);
	}

	public function glist_f()
	{
		$langcode = $this->session->val('www_lang_id');
		if(!$langcode){
			$langcode = $this->site['lang'];
		}
		if(!$langcode || $langcode == 'default'){
			$langcode = 'cn';
		}
		$tlist = $this->_country($langcode);
		$countrylist = $tlist['countrylist'];
		$data = array();
		$data = array('country'=>$tlist['grouplist']);
		//读取省份信息，中国统一为 province，国外相当于 state
		$country = $this->get('country');
		if(!$country){
			$this->success($data);
		}
		$country_id = 0;
		foreach($countrylist as $key=>$value){
			if($value['title'] == $country){
				$country_id = $value['id'];
				break;
			}
		}
		if(!$country_id){
			$this->success($data);
		}
		$condition = "pid='".$country_id."'";
		$tmplist = $this->model('worlds')->get_all($condition);
		if(!$tmplist){
			$this->success($data);
		}
		$plist = array();
		foreach($tmplist as $key=>$value){
			$value['title'] = $langcode == 'cn' ? $value['name'] : $value['name_en'];
			$plist[] = $value;
		}
		$data['province'] = $plist;
		//读取城市信息
		$province = $this->get('province');
		if(!$province){
			$this->success($data);
		}
		$province_id = 0;
		foreach($plist as $key=>$value){
			if($value['title'] == $province){
				$province_id = $value['id'];
				break;
			}
		}
		if(!$province_id){
			$this->success($data);
		}
		$condition = "pid='".$province_id."'";
		$tmplist = $this->model('worlds')->get_all($condition);
		if(!$tmplist){
			$this->success($data);
		}
		$clist = array();
		foreach($tmplist as $key=>$value){
			$value['title'] = $langcode == 'cn' ? $value['name'] : $value['name_en'];
			$clist[] = $value;
		}
		$data['city'] = $clist;
		//读取县/区信息
		$city = $this->get('city');
		if(!$city){
			$this->success($data);
		}
		$city_id = 0;
		foreach($clist as $key=>$value){
			if($value['title'] == $city){
				$city_id = $value['id'];
				break;
			}
		}
		if(!$city_id){
			$this->success($data);
		}
		$condition = "pid='".$city_id."'";
		$tmplist = $this->model('worlds')->get_all($condition);
		if(!$tmplist){
			$this->success($data);
		}
		$alist = array();
		foreach($tmplist as $key=>$value){
			$value['title'] = $langcode == 'cn' ? $value['name'] : $value['name_en'];
			$alist[] = $value;
		}
		$data['county'] = $alist;
		$this->success($data);
	}

	/**
	 * 读省份信息
	**/
	public function province_f()
	{
		$langcode = $this->session->val('www_lang_id');
		if(!$langcode){
			$langcode = $this->site['lang'];
		}
		if(!$langcode || $langcode == 'default'){
			$langcode = 'cn';
		}
		$this->config('is_ajax',true);
		$country_id = $this->get('country_id');
		if(!$country_id){
			$country = $this->get('country');
			if(!$country){
				$this->error(P_Lang('未指定国家'));
			}
			$tmp = $this->model('worlds')->get_one($country,'name_en');
			if(!$tmp){
				$tmp = $this->model('worlds')->get_one($country,'name');
			}
			if(!$tmp){
				$this->error(P_Lang('国家信息不存在'));
			}
			$country_id = $tmp['id'];
		}
		if(!$country_id){
			$this->error(P_Lang('未指定国家ID'));
		}
		$condition = "pid='".$country_id."'";
		$tmplist = $this->model('worlds')->get_all($condition);
		if(!$tmplist){
			$this->success();
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$value['title'] = $langcode == 'cn' ? $value['name'] : $value['name_en'];
			$rslist[] = $value;
		}
		$this->success($rslist);
	}
}
