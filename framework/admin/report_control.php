<?php
/**
 * 报表统计，包括用户，订单，自定义模块的数据统计
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年10月17日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class report_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('report');
		$this->assign("popedom",$this->popedom);
	}

	public function site_f()
	{
		$this->config('is_ajax',true);
		$ids = $this->get('ids');
		$type = $this->get('type');
		if(!$ids || !$type){
			$this->error(P_Lang('参数不完整'));
		}
		$tmp = $this->lib('json')->encode(array('type'=>$type,'ids'=>$ids),false,true);
		$this->lib('file')->save($tmp,$this->dir_data.'json/index-report.json');
		$list = explode(",",$ids);
		$data = array();
		$vtype = $this->_vtype($type);
		foreach($list as $key=>$value){
			if($value == 'title' || $value == 'hits' || $value == 'reply'){
				$data['title'] = false;
			}
			if($value == 'order'){
				//$y = array('id'=>P_Lang('订单数量'));
				$y = array('id');
				$data['order'] = $this->model('report')->order_data($type,$y,'COUNT',$vtype['startdate'],$vtype['stopdate']);
			}
			if($value == 'price'){
				$y = array('price');
				//$y = array('price'=>P_Lang('订单价格'));
				$data['price'] = $this->model('report')->order_data($type,$y,'SUM',$vtype['startdate'],$vtype['stopdate']);
			}
			if($value == 'user'){
				$y = array('id');
				//$y = array('id'=>P_Lang('用户数'));
				$data['user'] = $this->model('report')->user_data($type,$y,'COUNT',$vtype['startdate'],$vtype['stopdate']);
			}
		}
		if(isset($data['title'])){
			$data['title'] = $this->model('report')->title_data($type,$vtype['startdate'],$vtype['stopdate']);
		}
		$rslist = array();
		$x = array();
		$y = array();
		foreach($list as $key=>$value){
			$title = $this->_title($value);
			$y[] = $title;
			$tmp = array('name'=>$title,'type'=>'line','kid'=>$key);
			$tmplist = array();
			if($value == 'title'){
				
				foreach($data['title'] as $k=>$v){
					$tmplist[$v['x']] = $v['y_count'];
					if(!in_array($v['x'],$x)){
						$x[] = $v['x'];
					}
				}
				
			}
			if($value == 'reply'){
				foreach($data['title'] as $k=>$v){
					$tmplist[$v['x']] = $v['y_reply'];
					if(!in_array($v['x'],$x)){
						$x[] = $v['x'];
					}
				}
			}
			if($value == 'hits'){
				foreach($data['title'] as $k=>$v){
					$tmplist[$v['x']] = $v['y_hits'];
					if(!in_array($v['x'],$x)){
						$x[] = $v['x'];
					}
				}
			}
			if($value == 'user'){
				foreach($data['user'] as $k=>$v){
					$tmplist[$v['x']] = $v['y_id'];
					if(!in_array($v['x'],$x)){
						$x[] = $v['x'];
					}
				}
			}
			if($value == 'order'){
				foreach($data['order'] as $k=>$v){
					$tmplist[$v['x']] = $v['y_id'];
					if(!in_array($v['x'],$x)){
						$x[] = $v['x'];
					}
				}
			}
			if($value == 'price'){
				foreach($data['price'] as $k=>$v){
					$tmplist[$v['x']] = $v['y_price'];
					if(!in_array($v['x'],$x)){
						$x[] = $v['x'];
					}
				}
			}
			$tmp['tmpdata'] = $tmplist;
			$rslist[] = $tmp;
		}
		foreach($rslist as $key=>$value){
			$tmp = array();
			foreach($x as $k=>$v){
				if($value['tmpdata'][$v]){
					$tmp[] = $value['tmpdata'][$v];
				}else{
					$tmp[] = 0;
				}
			}
			$value['data'] = $tmp;
			unset($value['tmpdata']);
			$rslist[$key] = $value;
		}
		if(count($x)<2){
			$this->error(P_Lang('数据太少，无法形成报表'));
		}
		$this->success(array('x'=>$x,'y'=>$y,'rslist'=>$rslist));
	}

	private function _title($type)
	{
		$list = array();
		$list['title'] = P_Lang('主题数量');
		$list['hits'] = P_Lang('阅读率');
		$list['reply'] = P_Lang('回复数量');
		$list['order'] = P_Lang('订单数量');
		$list['price'] = P_Lang('订单金额');
		$list['user'] = P_Lang('用户数量');
		return $list[$type];
	}

	private function _vtype($type='date')
	{
		if($type == 'year'){
			$stopdate = strtotime(date("Y-12-31 23:59:59",$this->time));
			$startdate = strtotime((date("Y",$this->time)-10).'-01-01 00:00:00');
		}elseif($type == 'quarter'){
			$stopdate = strtotime(date("Y-12-31 23:59:59",$this->time));
			$startdate = $stopdate - 2*365*24*60*60;
		}elseif($type == 'month'){
			$stopdate = strtotime(date("Y-12-31 23:59:59",$this->time));
			$startdate = $stopdate - 365*24*60*60;
		}elseif($type == 'week'){
			$stopdate =strtotime(date("Y-m-d 23:59:59",$this->time));
			$startdate = $stopdate - 180*24*60*60;
		}else{
			$stopdate = strtotime(date("Y-m-d 23:59:59",$this->time));
			$startdate = $stopdate - 30*24*60*60;
		}
		$t =  array('startdate'=>$startdate,'stopdate'=>$stopdate);
		return $t;
	}

	/**
	 * 报表统计
	 * @参数 type 统计类型，user 为用户，order 为订单，数字为要统计的项目
	 * @参数 fields 要统计的字段，多个字段用英文逗号隔开
	 * @参数 group 分组执行
	 * @参数 times 统计的时间范围，数组，包括开始时间和结束时间
	 * @参数 
	**/
	public function index_f()
	{
		$list = array('user'=>P_Lang('用户'),'order'=>P_Lang('订单'),'title'=>P_Lang('主题数'));
		$wealth_list = $this->model('wealth')->get_all();
		if($wealth_list){
			$list['wealth'] = P_Lang('财富');
		}
		$project_list = $this->model('project')->project_all($this->session->val('admin_site_id'),'id',$condition);
		if($project_list){
			foreach($project_list as $key=>$value){
				$list[$value['id']] = $value['title'];
			}
		}
		$this->assign('plist',$list);
		$condition = 'module>0';
		$type = $this->get('type');
		if($type && $list[$type]){
			$this->assign('lead_title',$list[$type]);
			$this->assign('type',$type);
		}
		$x = $this->get('x');
		$data_mode = $this->get('data_mode');
		if($data_mode && is_array($data_mode)){
			$y = array();
			foreach($data_mode as $key=>$value){
				if($value){
					$y[] = $key;
				}
			}
		}
		$startdate = $this->get('startdate');
		$stopdate = $this->get('stopdate');
		$sqlext = $this->get('sqlext');
		if($sqlext){
			$this->assign('sqlext',$sqlext);
			$sqlext = str_replace(array('&lt;','&gt;','&quot;','&apos;','&#39;'),array('<','>','"',"'","'"),$sqlext);
			//$sqlext = stripslashes($sqlext);
		}
		$this->assign('x',$x);
		$this->assign('y',$y);
		$this->assign('data_mode',$data_mode);
		$this->assign('startdate',$startdate);
		$this->assign('stopdate',$stopdate);
		if($type == 'user'){
			$xy = $this->_user_type();
			$rslist = $this->model('report')->user_data($x,$y,$data_mode,$startdate,$stopdate,$sqlext);
		}
		if($type == 'order'){
			$xy = $this->_order_type();
			$rslist = $this->model('report')->order_data($x,$y,$data_mode,$startdate,$stopdate,$sqlext);		
		}
		if($type == 'title'){
			$xy = $this->_title_type();
			$rslist = $this->model('report')->title_data($x,$startdate,$stopdate);		
		}
		if($type == 'wealth'){
			$xy = $this->_wealth_type();
			$rslist = $this->model('report')->wealth_data($x,$startdate,$stopdate);		
		}
		if($type && is_numeric($type)){
			$xy = $this->_list_type($type);
			$rslist = $this->model('report')->list_data($type,$x,$y,$data_mode,$startdate,$stopdate);		
		}
		if($rslist && $x){
			$rslist = $this->_format_rslist_x($x,$rslist);
			$this->assign('rslist',$rslist);
		}
		
		
		if($y && $xy['y']){
			$y_title = array();
			foreach($xy['y'] as $key=>$value){
				if($key && in_array($key,$y)){
					$y_title[$key] = $value;
				}
			}
			$this->assign('y_title',$y_title);
			$y2 = array('x'=>'统计');
			foreach($rslist as $key=>$value){
				$y2['y_count'] += $value['y_count'];
				foreach($y_title as $k=>$v){
					$y2['y_'.$k] += $value['y_'.$k];
				}
			}
			$this->assign('totals',$y2);
		}
		if($x && $xy['x'] && $xy['x'][$x]){
			$this->assign('x_title',$xy['x'][$x]);
		}
		$chart = $this->get('chart');
		$this->assign('chart',$chart);
		$this->addjs('js/echarts.min.js');
		$this->view('report_index');
	}

	public function ajax_type_f()
	{
		$type = $this->get('type');
		if($type == 'user'){
			$info = $this->_user_type();
			$this->success($info);
		}
		if($type == 'order'){
			$info = $this->_order_type();
			$this->success($info);
		}
		if($type == 'title'){
			$info = $this->_title_type();
			$this->success($info);
		}
		if($type == 'wealth'){
			$info = $this->_wealth_type();
			if(!$info){
				$this->error(P_Lang('未设置相应的财富信息'));
			}
			$this->success($info);
		}
		if($type && is_numeric($type)){
			$info = $this->_list_type($type);
			$this->success($info);
		}
		$this->success();
	}

	private function _wealth_type()
	{
		$ylist = array();
		$wlist = $this->model('wealth')->get_all();
		if(!$wlist){
			return false;
		}
		if($wlist){
			foreach($wlist as $key=>$value){
				$ylist[$value['identifier']] = $value['title'];
			}
		}
		$xlist = array('date'=>P_Lang('日期'),'week'=>P_Lang('周'),'month'=>P_Lang('月份'),'year'=>P_Lang('年度'),'user'=>P_Lang('用户'));
		$this->assign('xlist',$xlist);
		$this->assign('ylist',$ylist);
		return array('x'=>$xlist,'y'=>$ylist);
	}

	private function _list_type($pid)
	{
		$ylist = array('count'=>P_Lang('数量'));
		$project = $this->model('project')->get_one($pid,false);
		if(!$project || !$project['module']){
			return false;
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			return false;
		}
		$xlist = array();
		if(!$module['mtype']){
			$ylist['hits'] = P_Lang('点击');
			$ylist['title'] = $project['alias_title'] ? $project['alias_title'] : P_Lang('主题');
			$xlist = array('date'=>P_Lang('日期'),'week'=>P_Lang('周'),'month'=>P_Lang('月份'),'year'=>P_Lang('年度'));
			$xlist['title'] = $project['alias_title'] ? $project['alias_title'] : P_Lang('主题');
		}
		$zlist = false;
		$flist = $this->model('module')->fields_all($project['module']);
		if($flist){
			$forbid = array('longblob','blob','tinyblob','mediumblob');
			foreach($flist as $key=>$value){
				if(in_array($value['field_type'],$forbid)){
					continue;
				}
				$ylist['ext_'.$value['identifier']] = $value['title'];
				$xlist['ext_'.$value['identifier']] = $value['title'];
				$zlist = true;
			}
		}
		if(!$xlist || count($xlist)<1){
			return false;
		}
		$this->assign('xlist',$xlist);
		$this->assign('ylist',$ylist);
		$this->assign('zlist',$zlist);
		return array('x'=>$xlist,'y'=>$ylist,'z'=>$zlist);
	}

	private function _title_type()
	{
		$ylist = array('count'=>P_Lang('数量'),'hits'=>P_Lang('点击'),'reply'=>P_Lang('回复'));
		$xlist = array('date'=>P_Lang('日期'),'week'=>P_Lang('周'),'month'=>P_Lang('月份'),'year'=>P_Lang('年度'));
		$this->assign('xlist',$xlist);
		$this->assign('ylist',$ylist);
		return array('x'=>$xlist,'y'=>$ylist);
	}

	private function _order_type()
	{
		$ylist = array('id'=>P_Lang('订单数量'),'price'=>P_Lang('订单价格'),'user_id'=>P_Lang('用户数'));
		$ylist['qty'] = P_Lang('产品数量');
		$xlist = array('date'=>P_Lang('日期'),'week'=>P_Lang('周'),'month'=>P_Lang('月份'),'year'=>P_Lang('年度'),'order'=>P_Lang('订单状态'));
		$xlist['title'] = P_Lang('产品名称');
		$xlist['tid'] = P_Lang('产品ID');
		$xlist['user_id'] = P_Lang('用户');
		$this->assign('xlist',$xlist);
		$this->assign('ylist',$ylist);
		return array('x'=>$xlist,'y'=>$ylist);
	}

	private function _user_type()
	{
		$ylist = array('id'=>P_Lang('注册数量'));
		$xlist = array('date'=>P_Lang('日期'),'week'=>P_Lang('周'),'month'=>P_Lang('月份'),'year'=>P_Lang('年度'),'group_id'=>P_Lang('用户组'));
		$flist = $this->model('user')->fields_all('field_type NOT IN("longblob")');
		if($flist){
			foreach($flist as $key=>$value){
				$ylist[$value['identifier']] = $value['title'];
				$xlist[$value['identifier']] = $value['title'];
			}
		}
		$this->assign('xlist',$xlist);
		$this->assign('ylist',$ylist);
		return array('x'=>$xlist,'y'=>$ylist);
	}

	/**
	 * 格式化X坐标里的数据
	 * @参数 $x X的数据
	 * @参数 $rslist 数据
	**/
	private function _format_rslist_x($x='date',$rslist)
	{
		if(!$x || !$rslist){
			return false;
		}
		if($x == 'group_id'){
			$grouplist = $this->model('usergroup')->get_all('is_guest=0','id');
			foreach($rslist as $key=>$value){
				$value['x'] = $grouplist[$value['x']]['title'];
				$rslist[$key] = $value;
			}
		}
		if($x == 'order'){
			$olist = $this->model('site')->order_status_all();
			foreach($rslist as $key=>$value){
				$value['x'] = $olist[$value['x']]['title'];
				$rslist[$key] = $value;
			}
		}
		if($x == 'user'){
			$ids = array();
			foreach($rslist as $key=>$value){
				$ids[] = $value['x'];
			}
			$tmplist = $this->model('user')->simple_user_list($ids,'user');
			if($tmplist){
				foreach($rslist as $key=>$value){
					if($tmplist[$value['x']]){
						$value['x'] = $tmplist[$value['x']];
						$rslist[$key] = $value;
					}
				}
			}
		}
		if($x == 'week'){
			foreach($rslist as $key=>$value){
				$tmp = str_replace('-',P_Lang('年第'),$value['x']).P_Lang('周');
				$value['x'] = $tmp;
				$rslist[$key] = $value;
			}
		}
		if($x == 'month'){
			foreach($rslist as $key=>$value){
				$tmp = str_replace('-',P_Lang('年'),$value['x']).P_Lang('月');
				$value['x'] = $tmp;
				$rslist[$key] = $value;
			}
		}
		if($x == 'year'){
			foreach($rslist as $key=>$value){
				$tmp = $value['x'].P_Lang('年');
				$value['x'] = $tmp;
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}
}
