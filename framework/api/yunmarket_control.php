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
	public function __construct()
	{
		parent::control();
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
		$phpok_id_list = 'cloud-market';
		//云市场分类标识
		$phpok_id_cate = 'cloud-market-cate';
		///
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
			$this->error(P_Lang('数据错误，较验不通过，请联系管理员'));
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
			$this->error(P_Lang('数据错误，较验不通过，请联系管理员'));
		}
		$arc = phpok("_arc","title_id=".$id);
		if(!$arc){
			$this->error(P_Lang('没有找到相关信息'));
		}
		if($arc['project_id'] != $pid){
			$this->error(P_Lang('项目不符合系统要求'));
		}
		$cate = phpok("_cate","cateid=".$arc['cate_id']);
		$rs = array();
		$rs['id'] = $arc['id'];
		$rs['title'] = $arc['title'];
		$rs['content'] = $arc['content'];
		$rs['cate_id'] = $arc['cate_id'];
		if($cate){
			$rs['cate_title'] = $cate['title'];
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
			$this->error(P_Lang('数据错误，较验不通过，请联系管理员'));
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
		$this->success($rs);
	}
}
