<?php
/**
 * 后台订单相关数据库操作
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年10月04日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_model extends order_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 后台订单删除操作
	 * @参数 $id 订单ID号
	 * @返回 false 或 true
	 * @更新时间 
	**/
	public function delete($id)
	{
		$id = intval($id);
		if(!$id){
			return false;
		}
		//删除订单主表
		$sql = "DELETE FROM ".$this->db->prefix."order WHERE id=".$id;
		$this->db->query($sql);
		//删除订单地址信息
		$sql = "DELETE FROM ".$this->db->prefix."order_address WHERE order_id=".$id;
		$this->db->query($sql);
		//删除订单物流信息
		$sql = "DELETE FROM ".$this->db->prefix."order_express WHERE order_id=".$id;
		$this->db->query($sql);
		//删除订单发票信息
		$sql = "DELETE FROM ".$this->db->prefix."order_invoice WHERE order_id=".$id;
		$this->db->query($sql);
		//删除订单日志
		$sql = "DELETE FROM ".$this->db->prefix."order_log WHERE order_id=".$id;
		$this->db->query($sql);
		//删除付款信息
		$sql = "DELETE FROM ".$this->db->prefix."order_payment WHERE order_id=".$id;
		$this->db->query($sql);
		//删除订单产品信息
		$sql = "DELETE FROM ".$this->db->prefix."order_product WHERE order_id=".$id;
		$this->db->query($sql);
		return true;
	}

	//保存订单各种状态下的价格
	public function save_order_price($data)
	{
		return $this->db->insert_array($data,'order_price');
	}

	public function delete_order_price($order_id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order_price WHERE order_id='".$order_id."'";
		return $this->db->query($sql);
	}

	public function get_list($condition='',$offset=0,$psize=30)
	{
		$sql = " SELECT o.*,u.user FROM ".$this->db->prefix."order o ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user u ON(o.user_id=u.id) ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY o.id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$ids = implode(",",array_keys($rslist));
		$sql = "SELECT SUM(qty) as total,order_id FROM ".$this->db->prefix."order_product WHERE order_id IN(".$ids.") GROUP BY order_id";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$rslist[$value['order_id']]['qty'] = $value['total'];
			}
		}
		$sql = "SELECT id,order_id,title FROM ".$this->db->prefix."order_payment WHERE order_id IN(".$ids.")";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			$payments = array();
			foreach($tmplist as $key=>$value){
				$payments[$value['order_id']][] = $value['title'];
			}
			foreach($rslist as $key=>$value){
				$value['pay_title'] = '';
				if($payments[$value['id']]){
					if(count($payments[$value['id']])>2){
						$value['pay_title'] = '<span style="color:darkblue">'.P_Lang('多次付款').'</span>';
					}else{
						$value['pay_title'] = implode("/",$payments[$value['id']]);
					}
				}
				$rslist[$key] = $value;
			}
			unset($tmplist);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_payment WHERE order_id IN(".$ids.") AND dateline>0";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			$paid_list = array();
			foreach($tmplist as $key=>$value){
				$currency_id = (isset($value['currency_id']) && $value['currency_id']) ? $value['currency_id'] : $rslist[$value['order_id']]['currency_id'];
				if(!isset($paid_list[$value['order_id']])){
					$paid_list[$value['order_id']] = 0;
				}
				$tmp = price_format_val($value['price'],$currency_id,$rslist[$value['order_id']]['currency_id']);
				$paid_list[$value['order_id']] += floatval($tmp);
			}
			foreach($rslist as $key=>$value){
				$value['paid'] = $paid_list[$value['id']] ? $paid_list[$value['id']] : 0;
				$value['unpaid'] = round(($value['price'] - $value['paid']),4);
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	public function get_count($condition="")
	{
		$sql = "SELECT count(o.id) FROM ".$this->db->prefix."order o ";
		//$sql.= "LEFT JOIN ".$this->db->prefix."order_payment p ON(o.id=p.order_id) ";
		$sql.= "LEFT JOIN ".$this->db->prefix."user u ON(o.user_id=u.id) ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	public function express_save($data)
	{
		return $this->db->insert_array($data,'order_express');
	}

	public function express_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_express WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function express_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order_express WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."order_log WHERE order_express_id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 更新订单状态，仅限后台管理员有效
	 * @参数 $id 订单ID
	 * @参数 $status 订单状态
	 * @参数 $note 订单状态
	 * @返回 true
	 * @更新时间 2016年10月04日
	**/
	public function update_order_status($id,$status='',$note='')
	{
		$sql = "UPDATE ".$this->db->prefix."order SET status='".$status."',status_title='".$note."' WHERE id='".$id."'";
		$this->db->query($sql);
		if(in_array($status,array('end','stop','cancel'))){
			$sql = "UPDATE ".$this->db->prefix."order SET endtime='".$this->time."' WHERE id='".$id."'";
			$this->db->query($sql);
		}
		$param = 'id='.$id."&status=".$status;
		$this->model('task')->add_once('order',$param);
		$rs = $this->get_one($id);
		if(!$note){
			$statuslist = $this->status_list();
			$note = $statuslist[$status];
		}
		$log = P_Lang('订单（{sn}）状态变更为：{status}',array('sn'=>$rs['sn'],'status'=>$note));
		$who = P_Lang('管理员：{admin}',array('admin'=>$this->session->val('admin_account')));
		$log = array('order_id'=>$id,'addtime'=>$this->time,'who'=>$who,'note'=>$log);
		$this->log_save($log);
		return true;
	}

	/**
	 * 整理订单里的产品，仅保留有效产品
	 * @参数 $id 订单ID
	 * @参数 $order_product_ids 订单里的产品ID，多个ID用英文逗号隔开
	 * @返回 true
	**/
	public function order_product_clearup($id,$order_product_ids='')
	{
		if(!$id || !$order_product_ids){
			return false;
		}
		if(is_array($order_product_ids)){
			$order_product_ids = implode(",",$order_product_ids);
		}
		$sql = "DELETE FROM ".$this->db->prefix."order_product WHERE order_id='".$id."' AND id NOT IN(".$order_product_ids.")";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 检测订单是否需要物流，数量大于0表示需要，小于0或空或false为不需要
	 * @参数 $id 订单ID号
	 * @返回 数值或false
	**/
	public function check_need_express($id)
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."order_product WHERE order_id='".$id."' AND is_virtual=0";
		return $this->db->count($sql);
	}
}