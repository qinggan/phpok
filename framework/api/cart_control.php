<?php
/**
 * 购物车接口请求相关
 * @package phpok\api
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年08月19日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cart_control extends phpok_control
{
	/**
	 * 购物车ID，该ID将贯穿整个购物过程
	**/
	private $cart_id = 0;

	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
		$this->cart_id = $this->model('cart')->cart_id($this->session->sessid(),$this->session->val('user_id'));
	}

	/**
	 * 加入购物车
	 * @参数 id 产品ID
	 * @参数 title 产品名称（当产品ID不存在时）
	 * @参数 qty 产品数量，仅支持数字
	 * @参数 ext 产品性属，仅有id时有效，只支持数字
	 * @参数 price 产品价格，仅当id为空时有效
	 * @参数 thumb 产品缩略图，仅当id为空时有效
	 * @返回 JSON数据
	 * @更新时间 2016年09月01日
	**/
	public function add_f()
	{
		if(!$this->cart_id){
			$this->error(P_Lang('购物车编号异常'));
		}
		$clear = $this->get('_clear');
		if($clear){
			$this->model('cart')->clear_cart($this->cart_id);
		}
		$id = $this->get('id','int');
		$qty = $this->get('qty','int');
		if(!$qty){
			$qty = 1;
		}
		$array = $id ? $this->product_from_tid($id) : $this->product_from_title();
		$rslist = $this->model('cart')->get_all($this->cart_id);
		$updateid = $total = 0;
		if(!$rslist){
			$rslist = array();
		}
		foreach($rslist as $key=>$value){
			if($id){
				if($value['tid'] == $id && $this->model('cart')->product_ext_compare($array['ext'],$value['ext'])){
					$updateid = $value['id'];
					$total = $value['qty'];
				}
			}else{
				if($value['title'] == $array['title'] && $array['price'] == $value['price'] && $this->model('cart')->product_ext_compare($array['ext'],$value['ext'])){
					$updateid = $value['id'];
					$total = $value['qty'];
				}
			}
		}
		if($updateid){
			$this->model('cart')->update($updateid,($total+$qty));
		}else{
			$array['qty'] = $qty;
			$this->model('cart')->add($array);
		}
		$rslist = $this->model('cart')->get_all($this->cart_id);
		$this->success($rslist);
	}

	private function product_from_title()
	{
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('产品名称不能为空'));
		}
		$array = array('cart_id'=>$this->cart_id);
		$array['title'] = $title;
		$array['price'] = $this->get('price','float');
		$array['tid'] = 0;
		$array['weight'] = $this->get('weight','float');
		$array['volume'] = $this->get('volume','float');
		$ext = $this->get('ext');
		if($ext && is_array($ext)){
			sort($ext);
		}
		$array['ext'] = $ext;
		$array['thumb'] = $this->get('thumb');
		$array['is_virtual'] = $this->get('is_virtual','int');
		return $array;
	}

	/**
	 * 产品数据从id传过来的值生成
	 * @参数 $id 产品ID
	 * @参数 $qty 数量
	**/
	private function product_from_tid($id,$qty=0)
	{
		$array = array('tid'=>$id,'cart_id'=>$this->cart_id);
		$rs = $this->call->phpok('_arc',array('site'=>$this->site['id'],'title_id'=>$id));		
		if(!$rs){
			$this->error(P_Lang('产品信息不存在或未启用'));
		}
		$thumb_id = $this->config['cart']['thumb_id'] ? $this->config['cart']['thumb_id'] : 'thumb';
		if($rs[$thumb_id]){
			if(is_string($rs[$thumb_id])){
				$array['thumb'] = $rs[$thumb_id];
			}
			if(is_array($rs[$thumb_id])){
				$array['thumb'] = $rs[$thumb_id]['filename'];
			}
			$gd_id = $this->config['cart']['gd_id'] ? $this->config['cart']['gd_id'] : '';
			if($gd_id && is_array($rs[$thumb_id]) && $rs[$thumb_id]['gd'] && $rs[$thumb_id]['gd'][$gd_id]){
				$array['thumb'] = $rs[$thumb_id]['gd'][$gd_id];
			}
		}
		$array['title'] = $rs['title'];
		$array['is_virtual'] = $rs['is_virtual'];
		$array['unit'] = $rs['unit'];
		$ext = $this->get('ext');
		$int = false;
		if($ext && (is_string($ext) || is_int($ext))){
			$int = true;
			$ext = explode(",",$ext);
			foreach($ext as $key=>$value){
				$value = intval($value);
				if(!$value){
					unset($ext[$key]);
					continue;
				}
				$ext[$key] = $value;
			}
		}
		if($ext && is_array($ext)){
			sort($ext);
		}
		$price = price_format_val($rs['price'],$rs['currency_id'],$this->site['currency_id']);
		$weight = $rs['weight'];
		$volume = $rs['volume'];
		if($ext && $rs['attrlist'] && $int){
			foreach($rs['attrlist'] as $key=>$value){
				foreach($value['rslist'] as $k=>$v){
					if(in_array($v['id'],$ext)){
						$price = floatval($price) + price_format_val($v['price'],$rs['currency_id'],$this->site['currency_id']);
						$weight = floatval($weight) + floatval($v['weight']);
						$volume = floatval($volume) + floatval($v['volume']);
					}
				}
			}
		}
		$array['ext'] = $ext;
		$array['price'] = $price;
		$array['weight'] = $weight;
		$array['volume'] = $volume;
		return $array;
	}

	/**
	 * 获取购物车里的产品总数
	**/
	public function total_f()
	{
		$total = $this->model('cart')->total($this->cart_id);
		$this->success($total);
	}

	public function clear_f()
	{
		$this->model('cart')->clear_cart($this->cart_id);
		$this->success();
	}

	/**
	 * 更新购物车里的产品数量
	 * @参数 id 购物车里的产品ID，即表（cart_product）的主键id，不是产品ID
	 * @参数 qty 更新购物车数量，不能小于1
	 * @返回 
	 * @更新时间 
	**/
	public function qty_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定产品ID'));
		}
		$rs = $this->model('cart')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('产品不存在'));
		}
		if($rs['cart_id'] != $this->cart_id){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$qty = $this->get('qty');
		if(!$qty){
			$qty = $rs['qty'];
		}
		$this->model('cart')->update($id,$qty);
		$rs["qty"] = $qty;
		$this->success($rs);
	}

	/**
	 * 删除购物车产品数据
	 * @参数 id 购物车里的产品ID，即表（cart_product）的主键id，不是产品ID
	 * @返回 JSON数据
	**/
	public function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定产品ID'));
		}
		$rs = $this->model('cart')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('产品不存在'));
		}
		if($rs['cart_id'] != $this->cart_id){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$this->model('cart')->delete_product($id);
		$this->success();
	}

	/**
	 * 格式化价格，带货币符号
	 * @参数 price 单个价格或多个价格
	 * @返回 返回字符串（单个价格）或数组（多个价格）
	 * @更新时间 2016年09月01日
	**/
	public function price_format_f()
	{
		$price = $this->get('price','float');
		if(is_array($price)){
			$list = array();
			foreach($price as $key=>$value){
				$list[$key] = price_format($value,$this->site['currency_id']);
			}
			$this->success($list);
		}
		$this->success(price_format($price,$this->site['currency_id']));
	}

	/**
	 * 格式化价格，无货币符号
	 * @参数 price 单个价格或多个价格
	 * @返回 返回字符串（单个价格）或数组（多个价格）
	 * @更新时间 2016年09月01日
	**/
	public function price_format_val_f()
	{
		$price = $this->get('price','float');
		if(is_array($price)){
			$list = array();
			foreach($price as $key=>$value){
				$list[$key] = price_format_val($value,$this->site['currency_id']);
			}
			$this->success($list);
		}
		$this->success(price_format_val($price,$this->site['currency_id']));
	}
}