<?php
/**
 * 云市场接口端，服务端专用
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2023年4月25日
 * @更新 2023年4月25日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class yunmarket_control extends phpok_control
{
	//云市场ID，仅限此ID可以通过接口访问，防止被JS采集
	private $pid = 205;
	private $linkurl = 'https://www.phpok.com/';
	private $phpok_id_list = 'cloud-market';
	private $phpok_id_cate = 'cloud-market-cate';
	public function __construct()
	{
		parent::control();
		$this->linkurl = $this->config['url'];
	}

	/**
	 * 获取云市场
	**/
	public function index_f()
	{
		$func = $this->get('func');
		if($func && $func == 'info'){
			$this->content();
		}
		if($func && $func == 'download'){
			$this->download();
		}
		if($func && $func == 'order'){
			$this->buy();
		}
		$phpok_id_list = $this->phpok_id_list;
		//云市场分类标识
		$phpok_id_cate = $this->phpok_id_cate;
		$data = array();
		$keywords = $this->get('keywords');
		if($keywords){
			$data['keywords'] = $keywords;
		}
		$cateid = $this->get('cateid');
		if($cateid){
			$data['cateid'] = $cateid;
		}
		$offset = $this->get('offset','int');
		if($offset){
			$data['offset'] = $offset;
		}
		$psize = $this->get('psize','int');
		if($psize){
			$data['psize'] = $psize;
		}
		$domain = $this->get('domain');
		if($domain){
			$data['domain'] = $domain;
		}
		$appid = $this->get('_appid');
		if(!$appid){
			$this->error(P_Lang('未绑定APPID'));
		}
		$user = $this->model('user')->get_one($appid);
		if(!$user){
			$this->error(P_Lang('未找到相关用户信息，请检查您的APPID'));
		}
		if(!$user['status'] || $user['status'] == 2){
			$this->error(P_Lang('平台限制了您的密钥，请联系官网进行处理'));
		}
		if(!$user['keyid']){
			$this->error(P_Lang('密钥未配置，请检查'));
		}
		$sign = $this->get('_signature');
		if(!$sign){
			$this->error(P_Lang('参数不完整'));
		}
		$code = $this->model('yunmarket')->signature($data,$user['keyid']);
		if($sign != $code){
			$this->error(P_Lang('数据错误，校验不通过，请联系管理员'));
		}
		$rs = array();
		$subcate = phpok($phpok_id_cate);
		if($subcate){
			$tmplist = array();
			foreach($subcate as $key=>$value){
				$tmp = array('id'=>$value['id'],'identifier'=>$value['identifier'],'title'=>$value['title']);
				$tmplist[] = $tmp;
			}
			$rs['catelist'] = $tmplist;
		}
		$info = phpok($phpok_id_list,$data);
		if($info){
			if($info['total']){
				$rs['total'] = $info['total'];
			}
			if($info['rslist']){
				$get_buys = $this->model('yunmarket')->get_buy($user['id'],$domain);
				if(!$get_buys){
					$get_buys = array();
				}
				$tmplist = array();
				foreach($info['rslist'] as $key=>$value){
					$tmp = array('id'=>$value['id'],'cate_id'=>$value['cate_id'],'cate_title'=>$value['cate']['title']);
					$tmp['title'] = $value['title'];
					$tmp['thumb'] = '';
					$tmp['note'] = '';
					if($value['thumb']){
						$tmp['thumb'] = '//'.$this->site['domain'].'/'.$value['thumb']['gd']['thumb'];
					}
					if($value['note']){
						$tmp['note'] = $value['note'];
					}
					$tmp['version'] = $value['version'];
					$tmp['version_update'] = $value['version_update'];
					$tmp['md5'] = $value['md5'];
					$tmp['folder'] = $value['folder'];
					$tmp['price'] = price_format_val($value['price']);
					if($value['extlib'] && is_array($value['extlib'])){
						$extlist = array();
						foreach($value['extlib'] as $k=>$v){
							$extlist[] = array('id'=>$v['id'],'title'=>$v['title']);
						}
						$tmp['extlist'] = $extlist;
					}
					//判断是否已购物
					$tmp['is_buy'] = true;
					if($value['price'] && $value['price']>0 && !in_array($value['id'],$get_buys)){
						$tmp['is_buy'] = false;
					}
					$tmplist[] = $tmp;
				}
				$rs['rslist'] = $tmplist;
			}
		}
		if(!$rs || count($rs)<1){
			$this->error(P_Lang('数据获取失败'));
		}
		$rs['offset'] = $offset;
		if($psize){
			$rs['psize'] = $psize;
		}
		$this->success($rs);
	}

	private function buy()
	{
		$pid = $this->pid;
		$data = array();
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$data['id'] = $id;
		$data['func'] = 'order';
		$domain = $this->get('domain');
		if(!$domain){
			$this->error(P_Lang('数据异常，参数异常'));
		}
		$chk = $this->is_intranet($domain);
		if($chk){
			$this->error(P_Lang('内网或局域网不支持在线购买'));
		}
		$data['domain'] = $domain;
		$appid = $this->get('_appid');
		if(!$appid){
			$this->error(P_Lang('未绑定APPID'));
		}
		$sign = $this->get('_signature');
		if(!$sign){
			$this->error(P_Lang('参数不完整'));
		}
		$user = $this->model('user')->get_one($appid);
		if(!$user){
			$this->error(P_Lang('未找到相关用户信息，请检查您的APPID'));
		}
		if(!$user['status'] || $user['status'] == 2){
			$this->error(P_Lang('平台限制了您的密钥，请联系官网进行处理'));
		}
		if(!$user['keyid']){
			$this->error(P_Lang('密钥未配置，请检查'));
		}
		$code = $this->model('yunmarket')->signature($data,$user['keyid']);
		if($sign != $code){
			$this->error(P_Lang('数据错误，校验不通过，请联系管理员'));
		}
		$arc = phpok("_arc","title_id=".$id);
		//进入购买阶段
		//下定单
		$sn = $this->model('order')->create_sn($appid);
		$status_list = $this->model('order')->status_list();
		$price = price_format_val($arc['price'],$arc['currency_id'],$this->site['currency_id']);
		$main = array('sn'=>$sn);
		$main['user_id'] = $user['id'];
		$main['addtime'] = $this->time;
		$main['price'] = $price;
		$main['currency_id'] = $this->site['currency_id'];
		$main['currency_rate'] = $this->site['currency']['val'];
		$main['status'] = 'create';
		$main['status_title'] = $status_list['create'];
		$main['passwd'] = md5(str_rand(10));
		$main['email'] = $user['email'];
		$main['mobile'] = $user['mobile'];
		$tmpext = array();
		$tmpext['域名'] = $domain;
		$tmpext['软件码'] = $arc['md5'];
		$main['ext'] = serialize($tmpext);
		$order_id = $this->model('order')->save($main);
		if(!$order_id){
			$this->error(P_Lang('订单创建失败，请联系管理员'));
		}
		$tmp = array('order_id'=>$order_id,'tid'=>$arc['id']);
		$tmp['title'] = $arc['title'];
		$tmp['price'] = $price;
		$tmp['qty'] = 1;
		$tmp['is_virtual'] = 1;
		if($arc['thumb']){
			$tmp['thumb'] = $arc['thumb']['filename'];
		}
		$this->model('order')->save_product($tmp);
		$linkurl = $this->url('order','payment','sn='.$sn.'&passwd='.$main['passwd'],'www',true);
		if($linkurl){
			$this->success($linkurl);
		}
		$this->error(P_Lang('支付创建失败'));
	}

	/**
	 * 获取内容
	**/
	private function content()
	{
		$pid = $this->pid;
		$data = array();
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$data['id'] = $id;
		$data['func'] = 'info';
		$domain = $this->get('domain');
		if($domain){
			$data['domain'] = $domain;
		}
		$appid = $this->get('_appid');
		if(!$appid){
			$this->error(P_Lang('未绑定APPID'));
		}
		$sign = $this->get('_signature');
		if(!$sign){
			$this->error(P_Lang('参数不完整'));
		}
		$user = $this->model('user')->get_one($appid);
		if(!$user){
			$this->error(P_Lang('未找到相关用户信息，请检查您的APPID'));
		}
		if(!$user['status'] || $user['status'] == 2){
			$this->error(P_Lang('平台限制了您的密钥，请联系官网进行处理'));
		}
		if(!$user['keyid']){
			$this->error(P_Lang('密钥未配置，请检查'));
		}
		$code = $this->model('yunmarket')->signature($data,$user['keyid']);
		if($sign != $code){
			$this->error(P_Lang('数据错误，校验不通过，请联系管理员'));
		}
		$arc = phpok("_arc","title_id=".$id);
		if(!$arc){
			$this->error(P_Lang('没有找到相关信息'));
		}
		if($arc['project_id'] != $pid){
			$this->error(P_Lang('项目不符合系统要求'));
		}
		$content = $arc['content'];
		if($content){
			$content = $this->img2full($content);
		}
		$rs = array();
		$rs['id'] = $arc['id'];
		$rs['title'] = $arc['title'];
		$rs['content'] = $content ? $content : '';
		$rs['cate_id'] = $arc['cate_id'];
		if($arc['cate']){
			$rs['cate_title'] = $arc['cate']['title'];
		}
		$rs['thumb'] = '';
		$rs['note'] = '';
		if($arc['thumb']){
			$rs['thumb'] = '//'.$this->site['domain'].'/'.$arc['thumb']['gd']['thumb'];
		}
		if($arc['note']){
			$rs['note'] = $arc['note'];
		}
		$rs['version'] = $arc['version'];
		$rs['version_update'] = $arc['version_update'];
		$rs['md5'] = $arc['md5'];
		$rs['folder'] = $arc['folder'];
		$rs['price'] = price_format_val($arc['price']);
		if($arc['extlib']){
			$extlist = array();
			foreach($arc['extlib'] as $key=>$value){
				$extlist[] = array('id'=>$value['id'],'title'=>$value['title']);
			}
			$rs['extlist'] = $extlist;
		}
		//判断是否已购物
		$rs['is_buy'] = true;
		$get_buys = $this->model('yunmarket')->get_buy($user['id'],$domain,$rs['id']);
		if(!$get_buys){
			$get_buys = array();
		}
		if($arc['price'] && $arc['price']>0 && !in_array($arc['id'],$get_buys)){
			$rs['is_buy'] = false;
		}
		$rs['dateline'] = $arc['dateline'];
		if($arc['lastdate']){
			$rs['lastdate'] = $arc['lastdate'];
		}
		$this->success($rs);
	}

	/**
	 * 获取内容
	**/
	private function download()
	{
		$pid = $this->pid;
		$data = array();
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$data['id'] = $id;
		$data['func'] = 'download';
		$domain = $this->get('domain');
		if($domain){
			$data['domain'] = $domain;
		}
		$appid = $this->get('_appid');
		if(!$appid){
			$this->error(P_Lang('未绑定APPID'));
		}
		$sign = $this->get('_signature');
		if(!$sign){
			$this->error(P_Lang('参数不完整'));
		}
		$user = $this->model('user')->get_one($appid);
		if(!$user){
			$this->error(P_Lang('未找到相关用户信息，请检查您的APPID'));
		}
		if(!$user['status'] || $user['status'] == 2){
			$this->error(P_Lang('平台限制了您的密钥，请联系官网进行处理'));
		}
		if(!$user['keyid']){
			$this->error(P_Lang('密钥未配置，请检查'));
		}
		$code = $this->model('yunmarket')->signature($data,$user['keyid']);
		if($sign != $code){
			$this->error(P_Lang('数据错误，校验不通过，请联系管理员'));
		}
		$arc = phpok("_arc","title_id=".$id);
		if(!$arc){
			$this->error(P_Lang('没有找到相关信息'));
		}
		if($arc['project_id'] != $pid){
			$this->error(P_Lang('项目不符合系统要求'));
		}
		$rs = array();
		$rs['id'] = $arc['id'];
		$rs['version'] = $arc['version'];
		$rs['version_update'] = $arc['version_update'];
		$rs['md5'] = $arc['md5'];
		$rs['folder'] = $arc['folder'];
		//判断是否已购物
		$is_buy = true;
		$get_buys = $this->model('yunmarket')->get_buy($user['id'],$domain,$rs['id']);
		if(!$get_buys){
			$get_buys = array();
		}
		if($arc['price'] && $arc['price']>0 && !in_array($rs['id'],$get_buys)){
			$is_buy = false;
		}
		if(!$is_buy){
			$this->error(P_Lang('您还未购买，不支持下载'));
		}
		if(!$arc['soft']){
			$this->error(P_Lang('软件信息不存在，请联系管理员'));
		}
		$soft = $arc['soft']['filename'];
		if(!$soft){
			$this->error(P_Lang('软件信息异常，请联系管理员'));
		}
		if(!file_exists($this->dir_root.$soft)){
			$this->error(P_Lang('软件信息异常，请联系管理员'));
		}
		$content = file_get_contents($this->dir_root.$soft);
		if(!$content){
			$this->error(P_Lang('软件信息异常，请联系管理员'));
		}
		$rs['download'] = base64_encode($content);
		//安装相应的扩展应用
		if($arc['extlib']){
			$extlist = array();
			foreach($arc['extlib'] as $key=>$value){
				$tmp = phpok("arc","title_id=".$value['id']);
				if(!$tmp){
					continue;
				}
				$app_chk = substr($tmp['folder'],0,5);
				$plugin_chk = substr($tmp['folder'],0,8);
				if($app_chk == '_app/' || $plugin_chk == 'plugins/'){
					continue;
				}
				if(!$tmp['soft']){
					continue;
				}
				if(!file_exists($this->dir_root.$tmp['soft']['filename'])){
					continue;
				}
				$einfo = array();
				$einfo['id'] = $tmp['id'];
				$einfo['folder'] = $tmp['folder'];
				$einfo['version'] = $tmp['version'];
				$einfo['version_update'] = $tmp['version_update'];
				$einfo['md5'] = $tmp['md5'];
				$einfo['download'] = base64_encode(file_get_contents($this->dir_root.$tmp['soft']['filename']));
				$extlist[] = $einfo;
			}
			if($extlist && count($extlist)>0){
				$rs['extlist'] = $extlist;
			}
		}
		$this->success($rs);
	}

	private function img2full($content='')
	{
		if(!$content){
			return false;
		}
		$linkurl = $this->linkurl;
		preg_match_all("/<img\s*.+\s*src\s*=\s*[\"|']?\s*([^>\"'\s]+?)[\"|'| ]?.*[\/]?>/isU",$content,$matches);
		$list = $matches[1];
		if(!$list || count($list)<1){
			return $content;
		}
		$list = array_unique($list);
		$save_folder = $app->dir_root.$folder;
		foreach($list as $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			$tmp1 = substr($value,0,7);
			$tmp2 = substr($value,0,8);
			$tmp3 = substr($value,0,2);
			$tmp1 = strtolower($tmp1);
			$tmp2 = strtolower($tmp2);
			if($tmp1 == 'http://' || $tmp2 == 'https://' || $tmp3 == '//'){
				continue;
			}
			//将网址后面的@符号去掉
			$old_url = $value;
			$new_url = $linkurl.$value;
			$url_list[] = array("old_url"=>$old_url,"new_url"=>$new_url);			
		}
		foreach($url_list as $key=>$value){
			$content = str_replace($value["old_url"],$value["new_url"],$content);
		}
		return $content;
	}

	private function is_intranet($domain,$ip='')
	{
		$ip2 = gethostbyname($domain);
		if($ip2){
			$ip = $ip2;
		}
		if(!$ip){
			return true;
		}
		if($ip == '127.0.0.1' || $ip == '::1'){
			return true;
		}
		$info = explode(".",$ip);
		if($info[0] == 10){
			return true;
		}
		if($info[0] == 172 && $info[1]>=16 && $info[1] <=31){
			return true;
		}
		if($info[0] == 192 && $info[1] == 168){
			return true;
		}
		return false;
	}
}
