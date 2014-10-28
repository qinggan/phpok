<?php
/***********************************************************
	Filename: {phpok}/api/cart_control.php
	Note	: 购物车相关信息管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月11日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cart_control extends phpok_control
{
	public $cart_id = 0;
	function __construct()
	{
		parent::control();
		$this->cart_id = $this->model('cart')->cart_id(session_id(),$_SESSION['user_id']);
	}

	//加入购物车
	function add_f()
	{
		if(!$this->cart_id) $this->json("系统异常，未获取购物车编号");
		$id = $this->get('id','int');
		$qty = $this->get('qty','int');
		if(!$qty) $qty = 1;
		if(!$id) $this->json("未指定产品ID");
		$dt = array('site_id'=>$this->site['id'],'id'=>$id,'arc_ext'=>1);
		//指定产品ID
		$rs = $this->call->phpok("_arc",$dt);
		if(!$rs) $this->json("产品信息不存在或未使用");
		//购物车产品信息
		$rslist = $this->model('cart')->get_all($this->cart_id);
		$updateid = $total = 0;
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				if($value['tid'] == $id)
				{
					$updateid = $value['id'];
					$total = $value['qty'];
				}
			}
		}
		//购物车加上
		if($updateid)
		{
			$this->model('cart')->update($updateid,($total+$qty));
		}
		else
		{
			//写入产品数量
			$array = array('cart_id'=>$this->cart_id,'tid'=>$id,'title'=>$rs['title'],'price'=>$rs['price'],'qty'=>$qty);
			$this->model('cart')->add($array);
		}
		$rslist = $this->model('cart')->get_all($this->cart_id);
		$this->json($rslist,true);
	}

	//取得购物车里的产品数量
	function total_f()
	{
		$total = $this->model('cart')->total($this->cart_id);
		$this->json($total,true);
	}

	//更新产品数量
	function qty_f()
	{
		$id = $this->get('id','int');
		if(!$id) $this->json("未指定要修改的产品ID");
		$rs = $this->model('cart')->get_one($id);
		if(!$rs) $this->json("产品不存在");
		if($rs['cart_id'] != $this->cart_id) $this->json('您没有权限执行此操作');
		$qty = $this->get('qty');
		if(!$qty) $qty = $rs['qty'];
		$this->model('cart')->update($id,$qty);
		$rs["qty"] = $qty;
		$this->json($rs,true);
	}
	//删除购物车里的产品信息
	function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id) $this->json("未指定要删除的产品");
		$rs = $this->model('cart')->get_one($id);
		if(!$rs) $this->json("产品不存在");
		if($rs['cart_id'] != $this->cart_id) $this->json('您没有权限执行此操作');
		$this->model('cart')->delete_product($id);
		$this->json('ok',true);
	}
}
?>