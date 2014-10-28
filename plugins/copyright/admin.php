<?php
/***********************************************************
	Filename: plugins/copyright/admin.php
	Note	: 后台管理授权页
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年1月31日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_copyright extends phpok_plugin
{
	function __construct()
	{
		parent::plugin();
	}

	function manage()
	{
		$rs = $this->plugin_info();
		$pageurl = $this->url('plugin','exec','id=copyright&exec=manage');
		$condition = '';
		$keywords = $this->get('keywords');
		if($keywords)
		{
			$condition .= " domain LIKE '%".$keywords."%' ";
			$condition .= " OR code LIKE '%".$keywords."%' ";
			$condition .= " OR fullname LIKE '%".$keywords."%' ";
			$condition .= " OR email LIKE '%".$keywords."%' ";
			$condition .= " OR phone LIKE '%".$keywords."%' ";
			$condition .= " OR im LIKE '%".$keywords."%' ";
			$pageurl .= '&keywords='.rawurlencode($keywords);
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid) $pageid = 1;
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		//$psize = 10;
		$offset = ($pageid-1) * $psize;

		//取得数量
		$sql = "SELECT count(id) FROM ".$this->db->prefix."copyright ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$total = $this->db->count($sql);
		if($total>0)
		{
			$sql = "SELECT * FROM ".$this->db->prefix."copyright ";
			if($condition)
			{
				$sql .= " WHERE ".$condition;
			}
			$sql.= " ORDER BY regdate DESC, id DESC LIMIT ".$offset.",".$psize;
			$rslist = $this->db->get_all($sql);
			$this->assign('rslist',$rslist);
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=[total]/[psize]&always=1");
			$this->assign('pagelist',$pagelist);
		}
		echo $this->plugin_tpl('manage.html');
	}

	//
	function set()
	{
		$tid = $this->get('tid','int');
		if($tid)
		{
			$sql = "SELECT * FROM ".$this->db->prefix."copyright WHERE id='".$tid."'";
			$rs = $this->db->get_one($sql);
			$this->assign('rs',$rs);
		}
		echo $this->plugin_tpl('set.html');
	}

	function save()
	{
		$tid = $this->get('tid','int');
		$error_url = $this->url('plugin','exec','id=copyright&exec=set');
		if($tid)
		{
			$error_url .= "&tid=".$tid;
		}
		$domain = $this->get('domain');
		if(!$domain) error('域名不能为空',$error_url,'error');
		$chk_rs = $this->domain_check($domain,$tid);
		if($chk_rs) error('域名已经被使用了，请检查',$error_url,'error');
		
		$array = array();
		$array['domain'] = $domain;
		$array['code'] = $this->get('code');
		if(!$array['code']) error('注册码不能为空',$error_url,'error');
		$array['code'] = strtoupper($array['code']);
		$date = $this->get('regdate');
		if(!$date) $date = date("Y-m-d",$this->time);
		$array['regdate'] = strtotime($date);
		$array['status'] = 1;
		$array['fullname'] = $this->get('fullname');
		$array['note'] = $this->get('note');
		$array['version'] = $this->get('version');
		$array['email'] = $this->get('email');
		$array['phone'] = $this->get('phone');
		$array['im'] = $this->get('im');
		$array['type'] = $this->get('type');
		if($tid)
		{
			$this->db->update_array($array,'copyright',array('id'=>$tid));
			$info = '修改域名授权信息成功';
		}
		else
		{
			$this->db->insert_array($array,'copyright');
			$info = '添加域名：'.$domain.' 授权成功';
		}
		$okurl = $this->url('plugin','exec','id=copyright&exec=manage');
		error($info,$okurl,'ok');
	}

	//删除授权
	function delete()
	{
		$tid = $this->get('tid');
		if(!$tid) $this->json('未指定授权的ID');
		$sql = "DELETE FROM ".$this->db->prefix."copyright WHERE id='".$tid."'";
		$this->db->query($sql);
		$this->json('删除成功',true);
	}

	//域名检测
	function domain_check($domain,$tid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."copyright WHERE domain='".$domain."'";
		if($tid)
		{
			$sql .= " AND id!='".$tid."'";
		}
		return $this->db->get_one($sql);
	}

	//更新状态后执行信息
	function ap_system_status_after()
	{
		$rs = $this->plugin_info();
		$menu_rs = $this->model('sysmenu')->get_one($rs['param']['sysmenu_id']);
		$this->model('plugin')->update_status($rs['id'],$menu_rs['status']);
	}

	function status()
	{
		$id = $this->get('tid','int');
		if(!$id) $this->json('未指定TID');
		$sql = "SELECT * FROM ".$this->db->prefix."copyright WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		$status = $rs['status'] ? 0 : 1;
		$sql = "UPDATE ".$this->db->prefix."copyright SET status='".$status."' WHERE id='".$id."'";
		$this->db->query($sql);
		$this->json(true,true);
	}

	//生成授权文件
	function license()
	{
		$tid = $this->get('tid','int');
		if(!$tid) error('未指定ID信息',$this->url('plugin','exec','id=copyright&exec=manage'),'error');
		$sql = "SELECT * FROM ".$this->db->prefix."copyright WHERE id='".$tid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			error("没有相关授权信息",$this->url('plugin','exec','id=copyright&exec=manage'),'error');
		}
		$key = md5($rs["code"]."-".$rs["domain"]."-".date("Ymd",$rs["regdate"]));
		$content = '<?php'."\n";
		$content.= "/*****************************************************************************************\n";
		$content.= "	文件： license.php\n";
		$content.= "	说明： PHPOK-VIP 许可证书\n";
		$content.= "	版本： PHPOK".VERSION."\n";
		$content.= "	作者： phpok.com<admin@phpok.com>\n";
		$content.= "	更新： ".date("Y-m-d H:i",$rs["regdate"])."\n";
		$content.= "*****************************************************************************************/\n";
		$content.= 'if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}'."\n";
		//授权类型
		$content.= 'define("LICENSE","'.$rs["type"].'");'."\n";
		//授权时间
		$content.= 'define("LICENSE_DATE","'.date("Y-m-d",$rs["regdate"]).'");'."\n";
		//授权域名
		$content.= 'define("LICENSE_SITE","'.$rs["domain"].'");'."\n";
		//授权码
		$content.= 'define("LICENSE_CODE","'.strtoupper($rs['code']).'");'."\n";
		//授权人信息
		$content.= 'define("LICENSE_NAME","'.$rs["fullname"].'");'."\n";
		//是否显示版权
		$content.= 'define("LICENSE_POWERED",false);'."\n";
		$content.= '?>'."\n";
		ob_end_clean();
		header("Date: ".gmdate("D, d M Y H:i:s", $this->system_time)." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", $this->system_time)." GMT");
		header("Content-Encoding: none");
		header("Content-Disposition: attachment; filename=".rawurlencode("license.php"));
		header("Content-Length: ".strlen($content));
		header("Accept-Ranges: bytes");
		echo $content;
		flush();
		ob_flush();
	}
}
?>