<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/express_model.php
	备注： 物流后台相关操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月07日 13时52分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class express_model extends express_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	//获取本站系统中存储的所有支付引挈
	public function code_all()
	{
		//读取目录下的
		$handle = opendir($this->dir_root.'gateway/express');
		$list = array();
		while(false !== ($myfile = readdir($handle))){
			if(substr($myfile,0,1) != '.' && is_dir($this->dir_root.'gateway/express/'.$myfile))
			{
				$list[$myfile] = array('id'=>$myfile,'dir'=>$this->dir_root.'gateway/express/'.$myfile);
				$tmpfile = $this->dir_root.'gateway/express/'.$myfile.'/config.xml';
				if(file_exists($tmpfile)){
					$tmp = $this->lib('xml')->read($tmpfile);
				}else{
					$tmp = array('title'=>$myfile,'code'=>'');
				}
				$list[$myfile]['title'] = $tmp['title'];
				$list[$myfile]['note'] = $tmp['note'];
				$list[$myfile]['content'] = $tmp['content'];
				$list[$myfile]['code'] = $tmp['code'];
			}
		}
		closedir($handle);
		return $list;
	}

	public function code_one($id)
	{
		$rs = array('id'=>$id,'dir'=>$this->dir_root.'gateway/express/'.$id);
		$xmlfile = $this->dir_root.'gateway/express/'.$id.'/config.xml';
		if(file_exists($xmlfile)){
			$tmp = $this->lib('xml')->read($xmlfile);
		}else{
			$tmp = array('title'=>$myfile,'code'=>'');
		}
		$rs['code'] = $tmp['code'];
		$rs['title'] = $tmp['title'];
		return $rs;		
	}

	public function save($data,$id=0)
	{
		if($id){
			return $this->db->update_array($data,'express',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'express');
		}
	}

	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."express WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}