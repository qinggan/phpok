<?php
/**
 * 管理评论
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年07月29日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class reply_model extends reply_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 获取指定主题下的评论数统计，返回统计数组，为空返回false
	 * @param mixed $ids 主题ID，字符串或数组
	 * @date 2016年02月14日
	 */
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
			$rslist[$value['tid']] = array('total'=>$value['total'],'uncheck'=>0);
		}
		$sql = "SELECT count(tid) as total,tid FROM ".$this->db->prefix."reply WHERE tid IN(".$ids.") AND status=0 GROUP BY tid";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return $rslist;
		}
		foreach($tmplist as $key=>$value){
			$rslist[$value['tid']]['uncheck'] = $value['total'];
		}
		return $rslist;
	}

	/**
	 * 取得回复统计
	 * @参数 $condition 查询条件
	 * @参数 $offset 起始值
	 * @参数 $psize 每页查询数
	**/
	public function get_all($condition="",$offset=0,$psize=30)
	{
		$sql  = "SELECT r.*,l.title l_title,l.id list_id,l.project_id l_pid,p.title p_title,c.title c_title,o.sn o_title,o.id order_id FROM ".$this->db->prefix."reply r ";
		$sql .= "LEFT JOIN ".$this->db->prefix."list l ON(r.vtype='title' AND r.tid=l.id) ";
		$sql .= "LEFT JOIN ".$this->db->prefix."project p ON(r.vtype='project' AND r.tid=p.id) ";
		$sql .= "LEFT JOIN ".$this->db->prefix."cate c ON(r.vtype='cate' AND r.tid=c.id) ";
		$sql .= "LEFT JOIN ".$this->db->prefix."order o ON(r.vtype='order' AND r.tid=o.id) ";
		if($condition){
			$sql .= "WHERE ".$condition." ";
		}
		$sql .= "ORDER BY r.id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$pictures = '';
		foreach($rslist as $key=>$value){
			if(!$value['pictures']){
				continue;
			}
			if($pictures){
				$pictures .= ",";
			}
			$pictures .= $value['pictures'];
		}
		if(!$pictures){
			return $rslist;
		}
		$idlist = explode(",",$pictures);
		$idlist = array_unique($idlist);
		$pictures = implode(",",$idlist);
		$sql = "SELECT id,ico,filename,ext";
	}

	public function get_all_total($condition='')
	{
		$sql = "SELECT count(r.id) FROM ".$this->db->prefix."reply r ";
		$sql .= "LEFT JOIN ".$this->db->prefix."list l ON(r.vtype='title' AND r.tid=l.id) ";
		$sql .= "LEFT JOIN ".$this->db->prefix."project p ON(r.vtype='project' AND r.tid=p.id) ";
		$sql .= "LEFT JOIN ".$this->db->prefix."cate c ON(r.vtype='cate' AND r.tid=c.id) ";
		$sql .= "LEFT JOIN ".$this->db->prefix."order o ON(r.vtype='order' AND r.tid=o.id) ";
		if($condition){
			$sql .= "WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	/**
	 * 评论类型
	**/
	public function types()
	{
		$list = array('title'=>P_Lang('主题'),'cate'=>P_Lang('分类'),'project'=>P_Lang('分类'),'order'=>P_Lang('订单'));
		return $list;
	}

}