<?php
/**
 * 报表及统计相关
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年10月19日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class report_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 会员统计数据
	 * @参数 $x X轴数据
	 * @参数 $y Y轴数据
	 * @参数 $data_mode 数据统计模式
	 * @参数 $start 开始时间，格式是：0000-00-00 00:00
	 * @参数 $stop 结束时间，格式是：0000-00-00 00:00
	 * @参数 $sqlext 扩展的SQL限制
	**/
	public function user_data($x='date',$y='',$data_mode='',$start='',$stop='',$sqlext='')
	{
		$field = array();
		$flist = $this->model('user')->fields_all("","identifier");
		$tmp_array = $this->_user_data_x($x,$flist);
		$group_by = $tmp_array['group_by'];
		$field[] = $tmp_array['field'];
		if($y && is_array($y)){
			foreach($y as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$tmp = $data_mode[$value] ? strtoupper($data_mode[$value]) : 'COUNT';
				$tmpext = $flist[$value] ? "ext" : "u";
				$field[] = $tmp."(".$tmpext.".".$value.") as y_".$value;
			}
		}
		$sql  = " SELECT ".implode(",",$field)." FROM ".$this->db->prefix."user u ";
		$sql .= " LEFT JOIN ".$this->db->prefix."user_ext ext ON(u.id=ext.id) ";
		$condition = array();
		if($start){
			$condition[] = "u.regtime>=".strtotime($start);
		}
		if($stop){
			$condition[] = "u.regtime<=".strtotime($stop);
		}
		$condition = implode(" AND ",$condition);
		if($condition){
			$sql .= " WHERE ".$condition;
			if($sqlext){
				$sql .= " AND ".$sqlext;
			}
		}else{
			if($sqlext){
				$sql .= " WHERE ".$sqlext;
			}
		}
		$sql .= " GROUP BY ".$group_by;
		$tmplist = $this->db->get_all($sql);
		if($tmp_array['x']){
			foreach($tmplist as $key=>$value){
				if($value['x'] != '' && $tmp_array['x']){
					foreach($tmp_array['x'] as $k=>$v){
						if($v['val'] == $value['x'] || (!$v['val'] && !$value['x'])){
							$value['x'] = $v['title'];
							$tmplist[$key] = $value;
							continue;
						}
					}
				}
			}
		}
		return $tmplist;
	}

	/**
	 * 订单统计数据
	 * @参数 $x X轴数据
	 * @参数 $y Y轴数据
	 * @参数 $data_mode 数据统计模式
	 * @参数 $start 开始时间，格式是：0000-00-00 00:00
	 * @参数 $stop 结束时间，格式是：0000-00-00 00:00
	 * @参数 $sqlext 扩展的SQL限制
	**/
	public function order_data($x='date',$y='',$data_mode='',$start='',$stop='',$sqlext='')
	{
		$field = array();
		$tmp_array = $this->_order_data_x($x);
		$group_by = $tmp_array['group_by'];
		$product_first = false;
		if(strpos($tmp_array['field'],'op.') !== false){
			$product_first = true;
		}
		$field[] = $tmp_array['field'];
		if($y && is_array($y)){
			foreach($y as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$tmp = $data_mode[$value] ? strtoupper($data_mode[$value]) : 'COUNT';
				$tmpext = in_array($value,array('qty','title')) ? "op" : "o";
				if($value == 'user_id'){
					$tmpext = 'DISTINCT o';
				}
				if($tmpext == 'op'){
					$product_first = true;
				}
				$field[] = $tmp."(".$tmpext.".".$value.") as y_".$value;
			}
		}
		if($product_first){
			$sql  = " SELECT ".implode(",",$field)." FROM ".$this->db->prefix."order_product op ";
			$sql .= " JOIN ".$this->db->prefix."order o ON(op.order_id=o.id) ";
		}else{
			$sql  = "SELECT ".implode(",",$field)." FROM ".$this->db->prefix."order o ";
		}
		$condition = array();
		if($tmp_array['condition']){
			$condition[] = $tmp_array['condition'];
		}
		if($start){
			$condition[] = "o.addtime>=".strtotime($start);
		}
		if($stop){
			$condition[] = "o.addtime<=".strtotime($stop);
		}
		$condition = implode(" AND ",$condition);
		if($condition){
			$sql .= " WHERE ".$condition;
			if($sqlext){
				$sql .= " AND ".$sqlext;
			}
		}else{
			if($sqlext){
				$sql .= " WHERE ".$sqlext;
			}
		}
		$sql .= " GROUP BY ".$group_by;
		return $this->db->get_all($sql);
	}

	/**
	 * 主题统计数据
	 * @参数 $x X轴数据
	 * @参数 $start 开始时间，格式是：0000-00-00 00:00
	 * @参数 $stop 结束时间，格式是：0000-00-00 00:00
	**/
	public function title_data($x='date',$start='',$stop='')
	{
		$field = array();
		$tmp_array = $this->_title_data_x($x);
		$group_by = $tmp_array['group_by'];
		$field[] = $tmp_array['field'];
		$field[] = "SUM(l.hits) as y_hits";
		$field[] = "count(l.id) as y_count";
		$field[] = "count(r.id) as y_reply";
		$sql  = "SELECT ".implode(",",$field)." FROM ".$this->db->prefix."list l ";
		$sql .= "LEFT JOIN ".$this->db->prefix."reply r ON(l.id=r.tid) ";
		$condition = array();
		if($tmp_array['condition']){
			$condition[] = $tmp_array['condition'];
		}
		if($start){
			$condition[] = "l.dateline>=".strtotime($start);
		}
		if($stop){
			$condition[] = "l.dateline<=".strtotime($stop);
		}
		$condition = implode(" AND ",$condition);
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " GROUP BY ".$group_by;
		return $this->db->get_all($sql);
	}

	/**
	 * 财富统计
	 * @参数 $x X轴数据
	 * @参数 $y 要统计的财富，数组
	 * @参数 $start 开始时间，格式是：0000-00-00 00:00
	 * @参数 $stop 结束时间，格式是：0000-00-00 00:00
	**/
	public function wealth_data($x='date',$y='',$start='',$stop='')
	{
		$tmp_array = $this->_wealth_data_x($x);
		$wlist = $this->model('wealth')->get_all();
		$tmplist = array();
		foreach($wlist as $key=>$value){
			$field = array();
			$group_by = $tmp_array['group_by'];
			$field[] = $tmp_array['field'];
			$field[] = "SUM(l.val) as y";
			$sql  = "SELECT ".implode(",",$field)." FROM ".$this->db->prefix."wealth_log l ";
			$condition = array(0=>"l.wid='".$value['id']."'",1=>'l.goal_id>0');
			if($tmp_array['condition']){
				$condition[] = $tmp_array['condition'];
			}
			if($start){
				$condition[] = "l.dateline>=".strtotime($start);
			}
			if($stop){
				$condition[] = "l.dateline<=".strtotime($stop);
			}
			$condition = implode(" AND ",$condition);
			if($condition){
				$sql .= " WHERE ".$condition;
			}
			$sql .= " GROUP BY ".$group_by;
			$tmp = $this->db->get_all($sql);
			if($tmp){
				foreach($tmp as $k=>$v){
					if($tmplist[$v['x']]){
						$tmplist[$v['x']]['y_'.$value['identifier']] = $v['y'];
					}else{
						$tmplist[$v['x']] = array('y_'.$value['identifier']=>$v['y']);
					}
				}
			}
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$tmp = array('x'=>$key);
			if($value && is_array($value)){
				foreach($value as $k=>$v){
					$tmp[$k] = $v;
				}
			}
			$rslist[] = $tmp;
		}
		return $rslist;
	}

	/**
	 * 扩展的主题统计数据
	 * @参数 $pid 项目ID
	 * @参数 $x X轴数据
	 * @参数 $y 要生成的座标
	 * @参数 $mode 模式，仅支持 count 和 sum
	 * @参数 $start 开始时间，格式是：0000-00-00 00:00
	 * @参数 $stop 结束时间，格式是：0000-00-00 00:00
	**/
	public function list_data($pid=0,$x='date',$y='',$mode='',$start='',$stop='')
	{
		$project = $this->model('project')->get_one($pid,false);
		if(!$project || !$project['module']){
			return false;
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			return false;
		}
		if($module['mtype']){
			return $this->_single_list_data($project,$x,$y,$mode,$start,$stop);
		}
		$flist = $this->model('module')->fields_all($project['module'],'identifier');
		if($x && substr($x,0,4) == 'ext_'){
			$tmp = "ext.".substr($x,4);
			$tmp_array['group_by'] = $tmp;
			$tmp_array['field'] = $tmp." as x";
			unset($tmp);
		}else{
			$tmp_array = $this->_title_data_x($x);
		}
		$field = array();
		$group_by = $tmp_array['group_by'];
		$field[] = $tmp_array['field'];
		if(!$module['mtype']){
			$field[] = "SUM(l.hits) as y_hits";
		}
		$field[] = "count(l.id) as y_count";
		if($y){
			foreach($y as $key=>$value){
				if(substr($value,0,4) != 'ext_'){
					continue;
				}
				$tmp = substr($value,4);
				$field[] = $mode == 'sum' ? "SUM(ext.".$tmp.") as y_".$value : "count(ext.".$tmp.") as y_".$value;
			}
		}
		
		$sql  = "SELECT ".implode(",",$field)." FROM ".$this->db->prefix."list l ";
		$sql .= "LEFT JOIN ".$this->db->prefix."list_".$project['module']." ext ON(l.id=ext.id) ";
		$condition = array("l.project_id='".$pid."'");
		if($tmp_array['condition']){
			$condition[] = $tmp_array['condition'];
		}
		if($start){
			$condition[] = "l.dateline>=".strtotime($start);
		}
		if($stop){
			$condition[] = "l.dateline<=".strtotime($stop);
		}
		$condition = implode(" AND ",$condition);
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " GROUP BY ".$group_by;
		return $this->db->get_all($sql);
	}

	private function _single_list_data($project,$x='',$y='',$mode='count',$start='',$stop='')
	{
		$field = array();
		$field[] = "count(id) as y_count";
		if($y){
			foreach($y as $key=>$value){
				if(substr($value,0,4) != 'ext_'){
					continue;
				}
				$tmp = substr($value,4);
				$field[] = $mode == 'sum' ? "SUM(".$tmp.") as y_".$value : "count(".$tmp.") as y_".$value;
			}
		}
		if($x && substr($x,0,4) == 'ext_'){
			$group_by = substr($x,4);
			$field[] = substr($x,4)." as x";
			unset($tmp);
		}else{
			$group_by = 'id';
			$field[] = 'id as x';
		}
		$sql  = "SELECT ".implode(",",$field)." FROM ".$this->db->prefix.$project['module']." WHERE project_id='".$project['id']."' ";
		$sql .= " GROUP BY ".$group_by;
		return $this->db->get_all($sql);
	}

	private function _wealth_data_x($x='date')
	{
		$array = array();
		switch ($x) {
			case 'week':
				$array['group_by'] = "FROM_UNIXTIME(l.dateline,'%X-%V')";
				$array['field'] = "FROM_UNIXTIME(l.dateline,'%X-%V') as x";
				break;
			case 'month':
				$array['group_by'] = "FROM_UNIXTIME(l.dateline,'%Y-%m')";
				$array['field'] = "FROM_UNIXTIME(l.dateline,'%Y-%m') as x";
				break;
			case 'year':
				$array['group_by'] = "FROM_UNIXTIME(l.dateline,'%Y')";
				$array['field'] = "FROM_UNIXTIME(l.dateline,'%Y') as x";
				break;
			case 'user':
				$array['group_by'] = "l.goal_id";
				$array['field'] = "l.goal_id as x";
				break;
			default:
				$array['group_by'] = "FROM_UNIXTIME(l.dateline,'%Y-%m-%d')";
				$array['field'] = "FROM_UNIXTIME(l.dateline,'%Y-%m-%d') as x";
				break;
		}
		return $array;
	}


	private function _title_data_x($x='date')
	{
		$array = array();
		switch ($x) {
			case 'week':
				$array['group_by'] = "FROM_UNIXTIME(l.dateline,'%X-%V')";
				$array['field'] = "FROM_UNIXTIME(l.dateline,'%X-%V') as x";
				break;
			case 'month':
				$array['group_by'] = "FROM_UNIXTIME(l.dateline,'%Y-%m')";
				$array['field'] = "FROM_UNIXTIME(l.dateline,'%Y-%m') as x";
				break;
			case 'year':
				$array['group_by'] = "FROM_UNIXTIME(l.dateline,'%Y')";
				$array['field'] = "FROM_UNIXTIME(l.dateline,'%Y') as x";
				break;
			default:
				$array['group_by'] = "FROM_UNIXTIME(l.dateline,'%Y-%m-%d')";
				$array['field'] = "FROM_UNIXTIME(l.dateline,'%Y-%m-%d') as x";
				break;
		}
		return $array;
	}

	private function _order_data_x($x='date')
	{
		$array = array();
		switch ($x) {
			case 'week':
				$array['group_by'] = "FROM_UNIXTIME(o.addtime,'%X-%V')";
				$array['field'] = "FROM_UNIXTIME(o.addtime,'%X-%V') as x";
				break;
			case 'month':
				$array['group_by'] = "FROM_UNIXTIME(o.addtime,'%Y-%m')";
				$array['field'] = "FROM_UNIXTIME(o.addtime,'%Y-%m') as x";
				break;
			case 'year':
				$array['group_by'] = "FROM_UNIXTIME(o.addtime,'%Y')";
				$array['field'] = "FROM_UNIXTIME(o.addtime,'%Y') as x";
				break;
			case 'order':
				$array['group_by'] = "o.status";
				$array['field'] = "o.status as x";
				break;
			case 'user':
				$array['group_by'] = "o.user_id";
				$array['field'] = "o.user_id as x";
				$array['condition'] = "o.user_id>0";
				break;
			case 'title':
				$array['group_by'] = "op.title";
				$array['field'] = "op.title as x";
				break;
			case 'tid':
				$array['group_by'] = "op.tid";
				$array['field'] = "op.tid as x,op.title as x_title";
				$array['condition'] = "op.tid>0";
				break;
			default:
				$array['group_by'] = "FROM_UNIXTIME(o.addtime,'%Y-%m-%d')";
				$array['field'] = "FROM_UNIXTIME(o.addtime,'%Y-%m-%d') as x";
				break;
		}
		return $array;
	}

	private function _user_data_x($x='date',$flist='')
	{
		$array = array();
		$is_ok = false;
		switch ($x) {
			case 'week':
				$array['group_by'] = "FROM_UNIXTIME(u.regtime,'%X-%V')";
				$array['field'] = "FROM_UNIXTIME(u.regtime,'%X-%V') as x";
				$is_ok = true;
				break;
			case 'month':
				$array['group_by'] = "FROM_UNIXTIME(u.regtime,'%Y-%m')";
				$array['field'] = "FROM_UNIXTIME(u.regtime,'%Y-%m') as x";
				$is_ok = true;
				break;
			case 'year':
				$array['group_by'] = "FROM_UNIXTIME(u.regtime,'%Y')";
				$array['field'] = "FROM_UNIXTIME(u.regtime,'%Y') as x";
				$is_ok = true;
				break;
			case 'group_id':
				$array['group_by'] = "u.group_id";
				$array['field'] = "u.group_id as x";
				$is_ok = true;
				break;
			default:
				$is_ok = false;
		}
		if(!$is_ok && $flist && is_array($flist) && $flist[$x]){
			$array['group_by'] = "ext.".$x;
			$array['field'] = "ext.".$x." as x";
			$tmp = array('radio','checkbox','select');
			if(in_array($flist[$x]['form_type'],$tmp)){
				$ext = $flist[$x]['ext']  ? $flist[$x]['ext'] : array();
				if(is_string($ext)){
					$ext = unserialize($ext);
				}
				
				if($ext['option_list']){
					$opt_list = explode(":",$ext['option_list']);
					$array['x']  = opt_rslist($opt_list[0],$opt_list[1]);
				}
			}
		}
		return $array;
	}
}
