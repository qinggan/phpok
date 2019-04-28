<?php
/**
 * 用户控制面板
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年12月04日
**/

class usercp_control extends phpok_control
{
	public $group_rs;
	public $user_rs;
	private $user;
	public function __construct()
	{
		parent::control();
		$user_id = $this->session->val('user_id');
		if(!$user_id){
			$errurl = $this->url('login','',$this->url('usercp'));
			$this->error(P_Lang('未登录会员不能执行此操作'),$errurl);
		}
		$this->user = $this->model('user')->get_one($user_id);
		$this->group_rs = $this->model('usergroup')->group_rs($user_id);
		if(!$this->group_rs){
			$this->error(P_Lang('您的账号有异常：无法获取相应的会员组信息，请联系管理员'));
		}
	}

	//会员个人中心
	public function index_f()
	{
		$user = $this->model('user')->get_one($this->session->val('user_id'));
		$this->assign('rs',$user);
		$this->assign('user',$user);

		//读取最新下单信息
		$condition = "user_id='".$this->session->val('user_id')."'";
		$rslist = $this->model('order')->get_list($condition,0,10);
		$this->assign('rslist',$rslist);
		//读取会员上传的最新附件
		$reslist = $this->model('res')->get_list($condition,0,10);
		$this->assign('reslist',$reslist);
		//
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp';
		}
		$this->view($tplfile);
	}

	//修改个人资料
	public function info_f()
	{
		$rs = $this->model('user')->get_one($this->session->val('user_id'));
		$group_rs = $this->group_rs;
		//读取扩展属性
		$condition = 'is_front=1';
		if($group_rs['fields']){
			$tmp = explode(",",$group_rs['fields']);
			$condition .= " AND identifier IN('".(implode("','",$tmp))."')";
		}
		$ext_list = $this->model('user')->fields_all($condition,"id");
		if($ext_list){
			$tmp_f = $group_rs['fields'] ? explode(",",$group_rs['fields']) : 'all';
			$extlist = array();
			foreach($ext_list as $key=>$value){
				if($value["ext"]){
					$ext = unserialize($value["ext"]);
					foreach($ext AS $k=>$v){
						$value[$k] = $v;
					}
				}
				$idlist[] = strtolower($value["identifier"]);
				if($rs[$value["identifier"]]){
					$value["content"] = $rs[$value["identifier"]];
				}
				if($tmp_f == 'all' || (is_array($tmp_f) && in_array($value['identifier'],$tmp_f))){
					$extlist[] = $this->lib('form')->format($value);
				}
			}
			$this->assign("extlist",$extlist);
		}
		$this->assign("rs",$rs);
		$this->assign("group_rs",$group_rs);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_info';
		}
		$this->view($tplfile);
	}

	//修改密码
	public function passwd_f()
	{
		$rs = $this->model('user')->get_one($this->session->val('user_id'));
		$this->assign('rs',$rs);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_passwd';
		}
		$this->view($tplfile);
	}

	//修改邮箱
	public function email_f()
	{
		$this->assign('rs',$this->user);
		//判断后台是否配置好第三方网关
		$sendemail = $this->model('gateway')->get_default('email') ? true : false;
		$this->assign('sendemail',$sendemail);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_email';
		}
		$this->view($tplfile);
	}

	//修改手机
	public function mobile_f()
	{
		$this->assign('rs',$this->user);
		$sendsms = $this->model('gateway')->get_default('sms') ? true : false;
		$this->assign('sendsms',$sendsms);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_mobile';
		}
		$this->view($tplfile);
	}

	//发票管理
	public function invoice_f()
	{
		$rslist = $this->model('user')->invoice($_SESSION['user_id']);
		if($rslist){
			$this->assign('rslist',$rslist);
			$this->assign('total',count($rslist));
		}
		$this->view("usercp_invoice");
	}

	public function invoice_setting_f()
	{
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('user')->invoice_one($id);
			if(!$rs || $rs['user_id'] != $_SESSION['user_id']){
				$this->error(P_Lang('发票信息不存在或您没有权限修改此发票设置'));
			}
			$this->assign('id',$id);
			$this->assign('rs',$rs);
		}
		$this->view("usercp_invoice_setting");
	}

	//获取项目列表
	public function list_f()
	{
		//$this->cache->close();
		$id = $this->get("id");
		if(!$id){
			error(P_Lang('未指定项目'),$this->url('usercp'),'notice',10);
		}
		$this->assign('id',$id);
		$pid = $this->model('id')->project_id($id,$this->site['id']);
		if(!$pid){
			error(P_Lang('项目信息不存在'),$this->url('usercp'),'error');
		}
		if(!$this->model('popedom')->check($pid,$this->group_rs['id'],'post')){
			error(P_Lang('您没有这个权限功能，请联系网站管理员'),$this->url('usercp'),'error');
		}
		$project_rs = $this->model('project')->get_one($pid);
		if(!$project_rs || !$project_rs['status']){
			error(P_Lang('项目不存在或未启用'),$this->url('usercp'),'error');
		}
		$tplfile = 'usercp_'.$id;
		$tplfile.= $project_rs['module'] ? '_list' : '_page';
		//非列表项目直接指定
		$this->assign("page_rs",$project_rs);
		if(!$project_rs['module']){
			$this->view($tplfile);
			exit;
		}
		$dt = array('pid'=>$project_rs['id'],'user_id'=>$_SESSION['user_id']);
		if($project_rs['cate']){
			$cate = $this->get('cate');
			$cateid = $this->get('cateid','int');
			if($cate){
				$dt['cate'] = $cate;
			}
			if($cateid){
				$dt['cateid'] = $cateid;
			}
		}
		//读取符合要求的内容
		$pageurl = $this->url('usercp','list','id='.$id);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid) $pageid = 1;
		$psize = $project_rs['psize'] ? $project_rs['psize'] : $this->config['psize'];
		if(!$psize){
			$psize = 20;
		}
		$offset = ($pageid-1) * $psize;
		$tpl = $this->get('tpl');
		if($tpl){
			$pageurl .= "&tpl=".rawurlencode($tpl);
			$tplfile = $tpl;
		}
		$dt['psize'] = $psize;
		$dt['offset'] = $offset;
		$keywords = $this->get('keywords');
		if($keywords){
			$dt['keywords'] = $keywords;
			$pageurl .= "&keywords=".$keywords;
			$this->assign("keywords",$keywords);
		}
		$dt['not_status'] = true;
		$dt['is_usercp'] = true;
		$status = $this->get('status');
		if($status){
			if($status == 1){
				$dt['sqlext'] = "l.status=1";
			}else{
				$dt['sqlext'] = "l.status=0";
			}
		}
		
		$dt['is_list'] = true;
		$dt['cache'] = false;
		$ext = $this->get('ext');
		if($ext && is_array($ext)){
			foreach($ext AS $key=>$value){
				if($key && $value){
					$dt['e_'.$key] = $value;
					$pageurl .= "&ext[".$key."]=".rawurlencode($value);
				}
			}
			$this->assign('ext',$ext);
		}
		$list = $this->call->phpok('_arclist',$dt);
		if($list['total']){
			$this->assign("pageid",$pageid);
			$this->assign("psize",$psize);
			$this->assign("pageurl",$pageurl);
			$this->assign("total",$list['total']);
			$this->assign("rslist",$list['rslist']);
		}
		if(!$this->tpl->check_exists($tplfile)){
			$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
			if(!$tplfile){
				$tplfile = 'usercp_list';
			}
		}
		$this->view($tplfile);
	}

	//收货地址管理
	public function address_f()
	{
		$rslist = $this->model('user')->address_all($this->session->val('user_id'));
		if($rslist){
			$this->assign('rslist',$rslist);
			$this->assign('total',count($rslist));
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_address';
		}
		$this->view($tplfile);
	}

	/**
	 * 添加或是修改地址信息
	**/
	public function address_setting_f()
	{
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('user')->address_one($id);
			if(!$rs || $rs['user_id'] != $_SESSION['user_id']){
				$this->error(P_Lang('地址信息不存在或您没有权限修改此地址'));
			}
			$this->assign('id',$id);
			$this->assign('rs',$rs);
		}else{
			$rs = array();
		}
		$info = form_edit('pca',array('p'=>$rs['province'],'c'=>$rs['city'],'a'=>$rs['county']),'pca');
		$this->assign('pca_rs',$info);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,'address2');
		if(!$tplfile){
			$tplfile = 'usercp_address_setting';
		}
		$this->view($tplfile);
	}

	public function avatar_f()
	{
		$this->assign('rs',$this->user);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_avatar';
		}
		$this->view($tplfile);
	}

	public function avatar_cut_f()
	{
		$id = $this->get('thumb_id','int');
		$x1 = $this->get("x1");
		$y1 = $this->get("y1");
		$x2 = $this->get("x2");
		$y2 = $this->get("y2");
		$w = $this->get("w");
		$h = $this->get("h");
		$rs = $this->model('res')->get_one($id);
		$new = $rs["folder"]."_tmp_".$id."_.".$rs["ext"];
		if($rs['attr']['width'] > 500){
			$beis = round($rs['attr']['width']/500,2);
			$w = round($w * $beis);
			$h = round($h * $beis);
			$x1 = round($x1 * $beis);
			$y1 = round($y1 * $beis);
			$x2 = round($x2 * $beis);
			$y2 = round($y2 * $beis);
		}
		$cropped = $this->create_img($new,$this->dir_root.$rs["filename"],$w,$h,$x1,$y1,1);
		$this->lib('file')->mv($this->dir_root.$new,$this->dir_root.$rs['filename']);
		$this->model('user')->update_avatar($rs['filename'],$_SESSION['user_id']);
		$this->json(true);
	}

	private function create_img($thumb_image_name, $image, $width, $height, $x1, $y1,$scale=1)
	{
		list($imagewidth, $imageheight, $imageType) = getimagesize($image);
		$imageType = image_type_to_mime_type($imageType);
		switch($imageType) {
			case "image/gif":
				$source=imagecreatefromgif($image);
				break;
			case "image/pjpeg":
				$source=imagecreatefromjpeg($image);
				break;
			case "image/jpeg":
				$source=imagecreatefromjpeg($image);
				break;
			case "image/jpg":
				$source=imagecreatefromjpeg($image);
				break;
			case "image/png":
				$source=imagecreatefrompng($image);
				break;
			case "image/x-png":
				$source=imagecreatefrompng($image);
				break;
		}
		$nWidth = ceil($width * $scale);
		$nHeight = ceil($height * $scale);
		$newImage = imagecreatetruecolor($nWidth,$nHeight);
		imagecopyresampled($newImage,$source,0,0,$x1,$y1,$nWidth,$nHeight,$width,$height);
		switch($imageType) {
			case "image/gif":
				imagegif($newImage,$thumb_image_name);
				break;
			case "image/pjpeg":
				imagejpeg($newImage,$thumb_image_name,100);
				break;
			case "image/jpeg":
				imagejpeg($newImage,$thumb_image_name,100);
				break;
			case "image/jpg":
				imagejpeg($newImage,$thumb_image_name,100);
				break;
			case "image/png":
				imagepng($newImage,$thumb_image_name);
				break;
			case "image/x-png":
				imagepng($newImage,$thumb_image_name);
				break;
		}
		return $thumb_image_name;
	}

	/**
	 * 查看会员的推广链及推广统计
	**/
	public function introducer_f()
	{
		$vlink = $this->url("index","link","uid=".$this->session->val('user_id'));
		$this->assign('vlink',$vlink);
		//取得推荐人列表
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$monthlist = $this->model('user')->stat_relation($this->session->val('user_id'));
		if($monthlist){
			$this->assign('monthlist',$monthlist);
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$month = $this->get('month');
		$pageurl = $this->url('usercp','introducer');
		$condition = '';
		if($month && strlen($month) == 6 && is_numeric($month)){
			$condition = "FROM_UNIXTIME(dateline,'%Y%m')='".$month."'";
			$this->assign('month',$month);
			$pageurl .= "&month=".$month;
		}
		$total = $this->model('user')->count_relation($this->session->val('user_id'),$condition);
		if($total && $total>0){
			$rslist = $this->model('user')->list_relation($this->session->val('user_id'),$offset,$psize,$condition);
			$this->assign('psize',$psize);
			$this->assign('offset',$offset);
			$this->assign('pageid',$pageid);
			$this->assign('total',$total);
			$this->assign('pageurl',$pageurl);
			$this->assign('rslist',$rslist);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_introducer';
		}
		$this->view($tplfile);
	}

	public function wealth_f()
	{
		$rslist = $this->model('wealth')->get_all(1);
		if(!$rslist){
			$this->error(P_Lang('系统没有启用任何财富功能，请联系管理员'));
		}
		$wealth = $this->user['wealth'];
		foreach($rslist as $key=>$value){
			$value['val'] = $wealth[$value['identifier']]['val'];
			$rslist[$key] = $value;
		}
		$this->assign('rslist',$rslist);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_wealth';
		}
		$this->view($tplfile);
	}
}