<?php
/**
 * 后台附件操作的一些信息
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年2月6日
**/

class res_model extends res_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function admin_clearlist($condition='')
	{
		$sql = "SELECT max(id) id_max,min(id) id_min,count(id) total FROM ".$this->db->prefix."res ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 检测附件是否已经被使用了，如果已被使用，直接返回true
	 * @参数 $rs 附件信息
	**/
	public function admin_check($rs)
	{
		//第一步，检测模块的扩展是否使用upfile类型
		$condition = "form_type='upload'";
		$flist = $this->model('fields')->get_all($condition);
		$is_used = false;
		if($flist){
			foreach($flist as $key=>$value){
				if(is_numeric($value['ftype'])){
					$module = $this->model('module')->get_one($value['ftype']);
					if(!$module){
						continue;
					}
					$tbl = $module['mtype'] ? $this->db->prefix.$value['ftype'] : $this->db->prefix."list_".$value['ftype'];
					$sql = "SELECT ".$value['identifier']." FROM ".$tbl." WHERE ".$value['identifier']." LIKE '%".$rs['id']."%'";
					$tmp = $this->db->get_one($sql);
					if($tmp){
						$is_used = true;
						break;
					}
					continue;
				}
				if($value['ftype'] == 'user'){
					$sql = "SELECT ".$value['identifier']." FROM ".$this->db->prefix."user_ext WHERE ".$value['identifier']." LIKE '%".$rs['id']."%'";
					$tmp = $this->db->get_one($sql);
					if($tmp){
						$is_used = true;
						break;
					}
					continue;
				}
				$sql = "SELECT content FROM ".$this->db->prefix."extc WHERE id='".$value['id']."' AND content LIKE '%".$rs['id']."%'";
				$tmp = $this->db->get_one($sql);
				if($tmp){
					$is_used = true;
					break;
				}
			}
		}
		if($is_used){
			return true;
		}
		//第二步，检测文本框中是否使用了附件
		$condition = "form_type='text'";
		$flist = $this->model('fields')->get_all($condition);
		if($flist){
			foreach($flist as $key=>$value){
				if(is_numeric($value['ftype'])){
					$module = $this->model('module')->get_one($value['ftype']);
					if(!$module){
						continue;
					}
					$tbl = $module['mtype'] ? $this->db->prefix.$value['ftype'] : $this->db->prefix."list_".$value['ftype'];
					$sql = "SELECT ".$value['identifier']." FROM ".$tbl." WHERE ".$value['identifier']."='".$rs['filename']."'";
					$tmp = $this->db->get_one($sql);
					if($tmp){
						$is_used = true;
						break;
					}
					continue;
				}
				if($value['ftype'] == 'user'){
					$sql = "SELECT ".$value['identifier']." FROM ".$this->db->prefix."user_ext WHERE ".$value['identifier']."='".$rs['filename']."'";
					$tmp = $this->db->get_one($sql);
					if($tmp){
						$is_used = true;
						break;
					}
					continue;
				}
				$sql = "SELECT content FROM ".$this->db->prefix."extc WHERE id='".$value['id']."' AND content='".$rs['filename']."'";
				$tmp = $this->db->get_one($sql);
				if($tmp){
					$is_used = true;
					break;
				}
			}
		}
		if($is_used){
			return true;
		}
		//第三步，检测编辑器中是否使用了附件
		$condition = "form_type in('editor','ckeditor','code_editor')";
		$flist = $this->model('fields')->get_all($condition);
		if($flist){
			foreach($flist as $key=>$value){
				if(is_numeric($value['ftype'])){
					$module = $this->model('module')->get_one($value['ftype']);
					if(!$module){
						continue;
					}
					$tbl = $module['mtype'] ? $this->db->prefix.$value['ftype'] : $this->db->prefix."list_".$value['ftype'];
					$sql  = "SELECT ".$value['identifier']." FROM ".$tbl." WHERE ".$value['identifier']." LIKE '%".$rs['filename']."%'";
					if($rs['gd']){
						foreach($rs['gd'] as $k=>$v){
							$sql .= " UNION ";
							$sql .= "SELECT ".$value['identifier']." FROM ".$tbl." WHERE ".$value['identifier']." LIKE '%".$v."%'";
						}
					}
					$tmp = $this->db->get_all($sql);
					if($tmp){
						$is_used = true;
						break;
					}
					continue;
				}
				if($value['ftype'] == 'user'){
					$sql  = "SELECT ".$value['identifier']." FROM ".$this->db->prefix."user_ext WHERE ".$value['identifier']." LIKE '%".$rs['filename']."%'";
					if($rs['gd']){
						foreach($rs['gd'] as $k=>$v){
							$sql .= " UNION ";
							$sql .= "SELECT ".$value['identifier']." FROM ".$this->db->prefix."user_ext WHERE ".$value['identifier']." LIKE '%".$v."%'";
						}
					}
					$tmp = $this->db->get_all($sql);
					if($tmp){
						$is_used = true;
						break;
					}
					continue;
				}
				$sql  = "SELECT content FROM ".$this->db->prefix."extc WHERE id='".$value['id']."' AND content LIKE '%".$rs['filename']."%'";
				if($rs['gd']){
					foreach($rs['gd'] as $k=>$v){
						$sql .= " UNION ";
						$sql .= "SELECT content FROM ".$this->db->prefix."extc WHERE id='".$value['id']."' AND content LIKE '%".$v."%'";
					}
				}
				$tmp = $this->db->get_all($sql);
				if($tmp){
					$is_used = true;
					break;
				}
			}
		}
		if($is_used){
			return true;
		}
		//第四步，检查会员头像是否使用了图片
		$sql = "SELECT avatar FROM ".$this->db->prefix."user WHERE avatar='".$rs['filename']."'";
		$tmp = $this->db->get_one($sql);
		if($tmp){
			return true;
		}
		//第五步，检查系统参数是否使用了图片
		if($rs['filename'] == $this->site['logo']){
			return true;
		}
		if($rs['filename'] == $this->site['favicon']){
			return true;
		}
		if($rs['filename'] == $this->site['logo_mobile']){
			return true;
		}
		//第六步，检查是否有用于支付的图标
		$sql = "SELECT id FROM ".$this->db->prefix."payment WHERE logo1='".$rs['filename']."' OR logo2='".$rs['filename']."' OR logo3='".$rs['filename']."'";
		$chk = $this->db->get_one($sql);
		if($chk){
			return true;
		}
		//第七步，检查是否有用于订单产品页中
		$sql = "SELECT id FROM ".$this->db->prefix."order_product WHERE thumb='".$rs['filename']."'";
		$chk = $this->db->get_one($sql);
		if($chk){
			return true;
		}
		//第八步，检查是否有用于产品属性
		$sql = "SELECT id FROM ".$this->db->prefix."attr_values WHERE pic='".$rs['filename']."'";
		$chk = $this->db->get_one($sql);
		if($chk){
			return true;
		}
		//第九步，检查是否有用于通知模板中
		$sql = "SELECT id FROM ".$this->db->prefix."email WHERE content LIKE '%".$rs['filename']."%'";
		if($rs['gd']){
			foreach($rs['gd'] as $k=>$v){
				$sql .= " UNION ";
				$sql .= "SELECT id FROM ".$this->db->prefix."email WHERE content LIKE '%".$v."%'";
			}
		}
		$chk = $this->db->get_all($sql);
		if($chk){
			return true;
		}
		//第十步，检查是否有用于收藏夹中
		$sql = "SELECT id FROM ".$this->db->prefix."fav WHERE thumb='".$rs['filename']."'";
		$chk = $this->db->get_one($sql);
		if($chk){
			return true;
		}
		return false;
	}

	/**
	 * 获取附件信息
	**/
	public function admin_res_info($id_start,$id_stop=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."res WHERE id>=".$id_start;
		if($id_stop){
			$sql .= " AND id<=".$id_stop;
		}
		$sql .= " ORDER BY id ASC LIMIT 1";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $this->get_one($rs['id'],true);
	}
}
