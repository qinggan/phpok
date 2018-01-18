<?php
/**
 * 评论信息维护
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年04月28日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class reply_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得全部回复
	 * @参数 $condition 查询条件
	 * @参数 $offset 起始值
	 * @参数 $psize 每页查询数
	**/
	public function get_all($condition="",$offset=0,$psize=30)
	{
		$sql = "SELECT l.* FROM ".$this->db->prefix."list l ";
		if($condition){
			$sql.= " WHERE ".$condition;
		}
		$sql .= " ORDER BY l.replydate DESC,l.id DESC ";
		if($psize && $psize>0){
			$offset = intval($offset);
			$sql.= " LIMIT ".$offset.",".$psize;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		$idlist = array_keys($rslist);
		$reply_list = $this->total_status(implode(",",$idlist));
		if($reply_list){
			foreach($reply_list AS $key=>$value){
				$rslist[$key]["checked"] = $value["checked"];
				$rslist[$key]["uncheck"] = $value["uncheck"];
			}
		}
		return $rslist;
	}

	/**
	 * 回复数
	 * @参数 $condition 查询条件
	**/
	public function get_all_total($condition="")
	{
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l ";
		if($condition){
			$sql.= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 统计回复中的已审核主题信息，未审核信息
	 * @参数 $id 主题ID，多个ID用英文逗号隔开
	**/
	public function total_status($id)
	{
		$list = array();
		$sql = "SELECT tid,count(id) total FROM ".$this->db->prefix."reply WHERE status=1 AND tid IN(".$id.") GROUP BY tid";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist AS $key=>$value){
				$list[$value["tid"]]["checked"] = $value["total"];
				$list[$value["tid"]]["uncheck"] = 0;
			}
		}
		$sql = "SELECT tid,count(id) total FROM ".$this->db->prefix."reply WHERE status=0 AND tid IN(".$id.") GROUP BY tid";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist AS $key=>$value){
				if(!$list[$value["tid"]]){
					$list[$value["tid"]]["checked"] = 0;
				}
				$list[$value["tid"]]["uncheck"] = $value["total"];
			}
		}
		return $list;
	}

	/**
	 * 获取回复列表
	 * @参数 $condition 查询条件
	 * @参数 $offset 开始位置
	 * @参数 $psize 每页查询数
	 * @参数 $pri 主键
	 * @参数 $orderby 排序
	**/
	public function get_list($condition="",$offset=0,$psize=30,$pri="",$orderby="")
	{
		if(!$orderby){
			$orderby = 'addtime ASC,id DESC';
		}
		$sql = "SELECT * FROM ".$this->db->prefix."reply WHERE ".$condition." ORDER BY ".$orderby;
		if($psize && intval($psize)){
			$offset = intval($offset);
			$sql .= " LIMIT ".$offset.",".$psize;
		}
		return $this->db->get_all($sql,$pri);
	}

	/**
	 * 查询数量
	 * @参数 $condition 条件
	**/
	public function get_total($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."reply";
		if($condition){
			$sql .= " WHERE ".$condition;
		} 
		return $this->db->count($sql);
	}

	/**
	 * 保存回复数据
	 * @参数 $data 数组，要保存的数据
	 * @参数 $id 回复ID，不为空时表示更新
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data) || count($data) < 1){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"reply",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"reply");
		}
	}

	/**
	 * 删除回复
	 * @参数 $id 回复ID
	**/
	public function delete($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->get_one($id);
		if($rs){
			$sql = "DELETE FROM ".$this->db->prefix."reply WHERE id='".$id."' OR parent_id='".$id."'";
			$this->db->query($sql);
			$sql = "SELECT id,addtime FROM ".$this->db->prefix."reply WHERE tid='".$rs['tid']."' ORDER BY id DESC LIMIT 1";
			$tmp = $this->db->get_one($sql);
			$sql = "UPDATE ".$this->db->prefix."list SET replydate=0 WHERE id='".$rs['tid']."'";
			if($tmp){
				$sql = "UPDATE ".$this->db->prefix."list SET replydate='".$tmp['addtime']."' WHERE id='".$rs['tid']."'";
			}
			$this->db->query($sql);
		}
		return true;
	}

	/**
	 * 取得一条回复信息
	 * @参数 $id 回复ID
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."reply WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 回复统计
	 * @参数 $ids 主题ID，多个主题用英文逗号隔开，也支持多个主题的数组
	**/
	public function comment_stat($ids)
	{
		if(!$ids){
			return false;
		}
		if(is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "SELECT count(tid) as total,tid FROM ".$this->db->prefix."reply WHERE tid IN(".$ids.") GROUP BY tid";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['tid']] = $value['total'];
		}
		return $rslist;
	}

	/**
	 * 取得主题属性信息，如绑定的项目ID，如分页页码等
	 * @参数 int $id 主题ID或主题标识
	 */
	public function get_title_info($id)
	{
		$sql = "SELECT l.id,l.project_id,p.psize,p.comment_status FROM ".$this->db->prefix."list l ";
		$sql.= "LEFT JOIN ".$this->db->prefix."project p ON(l.project_id=p.id) WHERE ";
		if(is_numeric($id)){
			$sql.= "l.id='".$id."'";
		}else{
			$sql.= "l.identifier='".$id."' AND l.site_id='".$this->site_id."'";
		}
		$sql.= " AND l.status=1 AND p.status=1";
		return $this->db->get_one($sql);
	}
}