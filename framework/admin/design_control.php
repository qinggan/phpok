<?php
/**
 * 页面设计器
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年05月30日
**/

class design_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 读取可能需要用到的块信息
	**/
	public function index_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定ID');
		}
		$this->assign('id',$id);
		$this->view('design_layer');
	}

	public function code_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定ID');
		}
		$this->assign('id',$id);
		$content_html = form_edit('content','','code_editor','height=500&width=750');
		$this->assign('content_html',$content_html);
		$this->view('design_code');
	}

	public function component_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$val = $this->get('val','system');
		if($val){
			$this->assign('val',$val);
		}
		$this->assign('id',$id);
		$rslist = $this->model('design')->get_all();
		$this->assign('rslist',$rslist);
		$this->view('design_component');
	}

	public function content_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error('未指ID');
		}
		$this->assign('id',$id);
		$type = $this->get('type');
		if(!$type){
			$this->error('未指定类型');
		}
		$this->assign('type',$type);
		if($type == 'editor'){
			$content_html = form_edit('content','','editor','height=350&auto_height=1');
			$this->assign('content_html',$content_html);
		}
		if($type == 'code'){
			$content_html = form_edit('content','','code_editor','height=450&width=720');
			$this->assign('content_html',$content_html);
		}
		if($type == 'image'){
			$res_id = $this->get('res_id','int');
			$content_html = form_edit('content',$res_id,'upload','');
			$this->assign('content_html',$content_html);
			$gdlist = $this->model('gd')->get_all();
			$this->assign('gdlist',$gdlist);
		}
		if($type == 'calldata'){
			$rslist = $this->model('call')->get_list('',0,9999);
			if($rslist){
				$typelist = $this->model('call')->types();
				foreach($rslist as $key => $value){
					$value['typename'] = $typelist[$value['type_id']] ? $typelist[$value['type_id']]['title'] : $value['type_id'];
					$rslist[$key] = $value;
				}
				$this->assign('rslist',$rslist);
			}
		}
		$this->view('design_content');
	}

	public function delete_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error('仅限超级管理员有权限操作');
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('design')->delete($id);
		$this->success();
	}

	public function list_f()
	{
		$rslist = $this->model('design')->get_all();
		$this->assign('rslist',$rslist);
		$this->view('design_list');
	}

	public function set_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error('仅限超级管理员有权限操作');
		}
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('design')->get_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
			$code = $rs['code'] ? $rs['code'] : $rs['id'];
			$content = $this->model('design')->content($code);
			$content_html = form_edit('content',$content,'code_editor','height=450&width=720');
		}else{
			$content_html = form_edit('content','','code_editor','height=450&width=720');
		}
		$this->assign('content_html',$content_html);
		$typelist = $this->model('design')->typelist();
		$this->assign('typelist',$typelist);

		//数据调用中心
		$datalist = $this->model('call')->get_list('',0,9999);
		if($datalist){
			$typelist = $this->model('call')->types();
			foreach($datalist as $key => $value){
				$value['typename'] = $typelist[$value['type_id']] ? $typelist[$value['type_id']]['title'] : $value['type_id'];
				$datalist[$key] = $value;
			}
			$this->assign('datalist',$datalist);
		}
		//图片
		$res_id = ($rs && $rs['ext'] && $rs['ext']['res_id']) ? $rs['ext']['res_id'] : 0;
		$content_html = form_edit('res_id',$res_id,'upload');
		$this->assign('image_content_html',$content_html);

		$this->view('design_set');
	}

	public function save_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error('仅限超级管理员有权限操作');
		}
		$id = $this->get('id','int');
		$data = array();
		$code = $this->get('code','system');
		if(!$code){
			$this->error(P_Lang('未指定编码'));
		}
		if($code == 'preview'){
			$this->error(P_Lang('不支持使用 preview 标识符'));
		}
		$chk = $this->model('design')->code_check($code,$id);
		if($chk){
			$this->error(P_Lang('代码已存在，请检查'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('未指定组件名称'));
		}
		$note = $this->get('note');
		$img = $this->get('img');
		$vtype = $this->get('vtype');
		if(!$vtype){
			$this->error(P_Lang('未指定数据源'));
		}
		$data = array('code'=>$code,'title'=>$title,'note'=>$note,'img'=>$img);
		$data['vtype'] = $vtype;
		$data['ext'] = $this->get('ext');
		$data['ext']['res_id'] = $this->get('res_id');
		$content = $this->get('content','html_js');
		$is_content_list = array('calldata','code','editor','textarea');
		if($id){
			$old = $this->model('design')->get_one($id);
			$act = $this->model('design')->save($data,$id);
			if($act && in_array($vtype,$is_content_list)){
				$this->model('design')->content($code,$content,$old['code']);
			}
			$this->success();
		}
		$id = $this->model('design')->save($data);
		if(!$id){
			$this->error(P_Lang('保存失败，请检查'));
		}
		if(in_array($vtype,$is_content_list)){
			$this->model('design')->content($code,$content);
		}
		$this->success($id);
	}

	public function style_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定层');
		}
		$this->assign('id',$id);
		$this->addjs('js/jscolor/jscolor.js');
		$inlist = $this->_in_style();
		$this->assign("inlist",$inlist);
		$outlist = $this->_out_style();
		$this->assign("outlist",$outlist);
		$this->view("design_style");
	}

	public function tpl_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error('仅限超级管理员有权限操作');
		}
		$id = $this->get('id','html');
		$rs = array();
		$glist = array();
		//读取模板记录
		$syslist = $this->model('design')->tplist($this->dir_data.'design/');
		if($syslist){
			if($id && $syslist[$id]){
				$rs = $syslist[$id];
			}
			$tmp = array('title'=>'系统','rslist'=>$syslist);
			$glist[] = $tmp;
		}
		if($this->site && $this->site['tpl_id']){
			$tpl_rs = $this->model('tpl')->get_one($this->site['tpl_id']);
			$wwwlist = $this->model('design')->tplist($this->dir_root.'tpl/'.$tpl_rs['folder'].'/design/');
			if($wwwlist){
				if($id && $wwwlist[$id]){
					$rs = $wwwlist[$id];
				}
				$tmp = array('title'=>$tpl_rs['title'],'rslist'=>$wwwlist);
				$glist[] = $tmp;
			}
			$this->assign('tpl_rs',$tpl_rs);
		}
		if($id && $rs){
			$tplfile = $this->dir_root.$rs['tplfile'].'.html';
			$content = $this->lib('file')->cat($tplfile);
			$content_html = form_edit('content',$content,'code_editor','height=420&width=720');

			$this->assign('rs',$rs);
		}else{
			$content_html = form_edit('content','','code_editor','height=420&width=720');
		}

		$this->assign('content_html',$content_html);
		$this->assign('id',$id);
		$this->assign('tplist',$glist);
		$this->view("design_tpl");
	}

	public function tpl_content_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error('仅限超级管理员有权限操作');
		}
		$id = $this->get('id','system');
		if(!$id){
			$this->error('未指定模板ID');
		}
	}

	public function tplsave_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error('仅限超级管理员有权限操作');
		}
		$id = $this->get('id','html');
		if(!$id){
			$id = $this->get('tplfile','html');
		}
		if(!$id){
			$this->error('未指定要创建的文件');
		}
		$id = str_replace("../","",$id);
		if(!$id){
			$this->error('未指定要创建的文件');
		}
		if(!preg_match("/^[a-zA-Z\_][a-z0-9A-Z\_\-\/\.]+$/u",$id)){
			$this->error('创建的文件不符合系统限制，仅支持字母a-z，数字0-9，下划线_及斜杠/');
		}
		$basename = basename($id);
		if($id == 'preview' || $id == 'preview.html'){
			$this->error('预览文件不支持操作');
		}
		$folder = $this->dir_root.$id;
		if(is_dir($folder)){
			$this->error('文件夹不允许操作');
		}
		$ext = substr($id,-5);
		$ext = strtolower($ext);
		if($ext == '.html'){
			$id = substr($id,0,-5);
		}
		$t = explode('/',$id);
		if($t[0] != '_data' && $t[0] != 'tpl'){
			$this->error('数据异常，不能写入');
		}
		$data = array();
		$data['title'] = $this->get('title');
		$data['note'] = $this->get('note');
		$data['img'] = $this->get('img');
		$content = $this->get('content','html_js');
		$file = $this->dir_root.$id.'.html';
		$this->lib('file')->vim($content,$file);
		$phpfile = $this->dir_root.$id.'.php';
		$this->lib('file')->vi($data,$phpfile,'config');
		$this->success();
	}

	public function tplfile_f()
	{
		$filename = $this->get('filename');
		if(!$filename){
			$this->error('未指定文件');
		}
		$ext = substr($filename,-5);
		$ext = strtolower($ext);
		$tplfile = $filename;
		if($ext != '.html'){
			$tplfile = $filename.'.html';
		}else{
			$tplfile = $filename;
			$filename = substr($filename,0,-5);
		}
		if(!file_exists($this->dir_root.$tplfile)){
			$this->error('模板文件不存在');
		}
		$data = $this->model('design')->tpl_info($filename);
		if(!$data){
			$this->error('文件不存在');
		}
		$this->success($data);
	}

	public function layer_top_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定ID');
		}
		$this->assign('id',$id);
		$this->addjs('js/jscolor/jscolor.js');
		$this->view('design_win');
	}

	public function layer_setting_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定ID');
		}
		$this->assign('id',$id);
		$this->addjs('js/jscolor/jscolor.js');
		$this->view('design_attr');
	}

	public function layer2_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定ID');
		}
		$this->assign('id',$id);
		$this->view('design_sub');
	}

	private function _in_style()
	{
		$dlist = array();
		$dlist['0'] = "无动画";
		$dlist['base'] = array('title'=>'基础效果');
		$dlist['base']['list'] = array();
		$dlist['base']['list']['bounce'] = "弹跳";
		$dlist['base']['list']['flash'] = "闪烁";
		$dlist['base']['list']['pulse'] = "缩放";
		$dlist['base']['list']['shake'] = "抖动";
		$dlist['base']['list']['swing'] = "悬晃";
		$dlist['base']['list']['tada'] = "波动";
		$dlist['base']['list']['wobble'] = "摇摆";
		$dlist['base']['list']['lightSpeedIn'] = "光速飞进";
		$dlist['bounce'] = array('title'=>"弹进特效");
		$dlist['bounce']['list'] = array();
		$dlist['bounce']['list']['bounceIn'] = "放大";
		$dlist['bounce']['list']['bounceInDown'] = "从上到下";
		$dlist['bounce']['list']['bounceInLeft'] = "从左到右";
		$dlist['bounce']['list']['bounceInRight'] = "从右到左";
		$dlist['bounce']['list']['bounceInUp'] = "从下到上";
		$dlist['fade'] = array('title'=>"淡入效果");
		$dlist['fade']['list'] = array();
		$dlist['fade']['list']['fadeInDown'] = "从上向下";
		$dlist['fade']['list']['fadeInLeft'] = "从左到右";
		$dlist['fade']['list']['fadeInRight'] = "从右到左";
		$dlist['fade']['list']['fadeInUp'] = "从下到上";
		$dlist['fadeBig'] = array('title'=>"快速淡入效果");
		$dlist['fadeBig']['list'] = array();
		$dlist['fadeBig']['list']['fadeInDownBig'] = "从上到下";
		$dlist['fadeBig']['list']['fadeInLeftBig'] = "从左到右";
		$dlist['fadeBig']['list']['fadeInRightBig'] = "从右到左";
		$dlist['fadeBig']['list']['fadeInUpBig'] = "从下到上";
		$dlist['flip'] = array('title'=>"翻转效果");
		$dlist['flip']['list'] = array();
		$dlist['flip']['list']['flip'] = "前后左右大翻转";
		$dlist['flip']['list']['flipInX'] = "上下小翻转";
		$dlist['flip']['list']['flipInY'] = "左右小翻转";
		$dlist['rotate'] = array('title'=>"旋转效果");
		$dlist['rotate']['list'] = array();
		$dlist['rotate']['list']['rotateIn'] = "中心顺时针";
		$dlist['rotate']['list']['rotateInDownLeft'] = "左外长半径顺时针";
		$dlist['rotate']['list']['rotateInDownRight'] = "右外长半径逆时针";
		$dlist['rotate']['list']['rotateInUpLeft'] = "左外长半径逆时针";
		$dlist['rotate']['list']['rotateInUpRight'] = "右外长半径顺时针";
		$dlist['zoom'] = array('title'=>"放大效果");
		$dlist['zoom']['list'] = array();
		$dlist['zoom']['list']['zoomIn'] = "放大渐入";
		$dlist['zoom']['list']['zoomInDown'] = "从上向下";
		$dlist['zoom']['list']['zoomInLeft'] = "从左到右";
		$dlist['zoom']['list']['zoomInRight'] = "从右到左";
		$dlist['zoom']['list']['zoomInUp'] = "从下到上";
		$dlist['slide'] = array('title'=>"滑入效果");
		$dlist['slide']['list'] = array();
		$dlist['slide']['list']['slideInDown'] = "从上到下";
		$dlist['slide']['list']['slideInLeft'] = "从左到右";
		$dlist['slide']['list']['slideInRight'] = "从右到左";
		$dlist['slide']['list']['slideInUp'] = "从下到上";
		return $dlist;
	}

	private function _out_style()
	{
		$dlist = array();
		$dlist['0'] = '无动画';
		$dlist['bounceOut'] = "常规到小消失，弹簧";
		$dlist['bounceOutDown'] = "常规到小，下方消失，弹簧";
		$dlist['bounceOutLeft'] = "常规到小，左方消失，弹簧";
		$dlist['bounceOutRight'] = "常规到小，右方消失，弹簧";
		$dlist['bounceOutUp'] = "常规到小，上方消失，弹簧";
		$dlist['fadeOut'] = "渐隐";
		$dlist['fadeOutDown'] = "渐隐，从上到下";
		$dlist['fadeOutDownBig'] = "渐隐，从上到下，滑动距离较长";
		$dlist['fadeOutLeft'] = "渐隐，从左到右";
		$dlist['fadeOutLeftBig'] = "渐隐，从左到右，滑动距离较长";
		$dlist['fadeOutRight'] = "渐隐，从右到左";
		$dlist['fadeOutRightBig'] = "渐隐，从右到左，滑动距离较长";
		$dlist['fadeOutUp'] = "渐隐，从下到上";
		$dlist['fadeOutUpBig'] = "渐隐，从下到上，滑动距离较长";
		$dlist['flipOutX'] = "中心X轴旋转，消失";
		$dlist['flipOutY'] = "中心Y轴旋转，消失";
		$dlist['rotateOut'] = "中心顺时针旋转消失";
		$dlist['rotateOutDownLeft'] = "左外长半径顺时针旋转消失";
		$dlist['rotateOutDownRight'] = "右外长半径逆时针旋转消失";
		$dlist['rotateOutUpLeft'] = "左外长半径逆时针旋转消失";
		$dlist['rotateOutUpRight'] = "右外长半径顺时针旋转消失";
		$dlist['hinge'] = "右上到左下顺时针消失";
		$dlist['zoomOut'] = "由大变小，消失";
		$dlist['zoomOutDown'] = "由大变小，从下方消失";
		$dlist['zoomOutLeft'] = "由大变小，从左方消失";
		$dlist['zoomOutRight'] = "由大变小，从右方消失";
		$dlist['zoomOutUp'] = "由大变小，从上方消失";
		$dlist['slideOutDown'] = "从上到下，平滑消失";
		$dlist['slideOutLeft'] = "从右到左，平滑消失";
		$dlist['slideOutRight'] = "从左到右，平滑消失";
		$dlist['slideOutUp'] = "从下到上，平滑消失";
		return $dlist;
	}
}
