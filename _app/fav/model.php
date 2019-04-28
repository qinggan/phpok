<?php
/**
 * 收藏夹
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年06月04日
**/
namespace phpok\app\model\fav;

class model extends \phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 获取数量
	 * @参数 $condition 查询条件
	**/
	public function get_count($condition='')
	{
		$sql = "SELECT count(f.id) FROM ".$this->db->prefix."fav f ";
		$sql.= "LEFT JOIN ".$this->db->prefix."user u ON(f.user_id=u.id) ";
		if($condition){
			$sql.= "WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	/**
	 * 获取列表
	 * @参数 $condition 查询条件
	 * @参数 $offset 开始标识
	 * @参数 $psize 每次查询数
	**/
	public function get_all($condition='',$offset=0,$psize=30)
	{
		$sql = "SELECT f.*,u.user,p.title project_title,p.identifier project_identifier,c.title cate_title,c.identifier cate_identifier FROM ".$this->db->prefix."fav f ";
		$sql.= "LEFT JOIN ".$this->db->prefix."user u ON(f.user_id=u.id) ";
		$sql.= "LEFT JOIN ".$this->db->prefix."list l ON(f.lid=l.id) ";
		$sql.= "LEFT JOIN ".$this->db->prefix."project p ON(l.project_id=p.id) ";
		$sql.= "LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY f.addtime DESC,f.id DESC ";
		if($psize && $psize>0){
			$sql .= " LIMIT ".intval($offset).",".$psize;
		}
		return $this->db->get_all($sql);
	}

	/**
	 * 删除收藏夹标记
	 * @参数 $id 收藏夹ID
	**/
	public function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."fav WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得主题被收藏数
	 * @参数 $id 主题ID
	**/
	public function title_fav_count($id)
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."fav WHERE lid='".$id."'";
		return $this->db->count($sql);
	}

	/**
	 * 检查主题是否已被会员收藏
	 * @参数 $id 主题ID
	 * @参数 $uid 会员ID
	 * @参数 $field 字段，默认使用 lid
	**/
	public function chk($id,$uid=0,$field='lid')
	{
		$sql = "SELECT id FROM ".$this->db->prefix."fav WHERE user_id='".$uid."' AND ".$field."='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 保存收藏
	 * @参数 $data 一维数组
	 * @参数 $id 有ID时表示更新，无ID时表示添加
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'fav',array('id'=>$id));
		}
		return $this->db->insert_array($data,'fav');
	}

	/**
	 * 删除收藏操作
	 * @参数 $id 收藏夹主表 qinggan_nav 的主键ID
	**/
	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."fav WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得一条收藏信息
	 * @参数 $id 收藏夹主表 qinggan_nav 的主键ID
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fav WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}
}