<?php
/**
 * 设计器中涉及到的Mode操作
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html 
 * @时间 2021年1月7日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class design_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."design ORDER BY code ASC,id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if(!$value['ext']){
				$value['ext'] = array();
			}else{
				$value['ext'] = unserialize($value['ext']);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function get_one($id,$type='id')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."design WHERE ".$type."='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		return $rs;
	}

	public function save($data,$id=0)
	{
		if($data['ext'] && is_array($data['ext'])){
			$data['ext'] = serialize($data['ext']);
		}
		if($id){
			return $this->db->update($data,'design',array('id'=>$id));
		}
		return $this->db->insert($data,'design');
	}

	public function delete($id=0)
	{
		if(!$id){
			return false;
		}
		$rs = $this->get_one($id);
		if(!$rs){
			return false;
		}
		$code = $rs['code'] ? $rs['code'] : $rs['id'];
		if(file_exists($this->dir_data.'design/'.$code.'.html')){
			$this->lib('file')->rm($this->dir_data.'design/'.$code.'.html');
		}
		$sql = "DELETE FROM ".$this->db->prefix."design WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function typelist()
	{
		$type = array();
		$type['editor'] = '内容编辑器';
		$type['code'] = '代码编辑器';
		$type['textarea'] = '文本区';
		$type['image'] = '图片附件';
		$type['video'] = '视频链接';
		$type['iframe'] = 'Iframe 框架';
		$type['calldata'] = '数据调用';
		return $type;
	}

	public function tplist($basedir='')
	{
		$syslist = array();
		$this->lib('file')->deep_ls($basedir,$syslist);
		if(count($syslist)<1){
			return false;
		}
		$tplist = array();
		$length = strlen($this->dir_root);
		foreach($syslist as $key=>$value){
			if(is_dir($value)){
				continue;
			}
			//预览模板跳过
			if(basename($value) == 'preview.html'){
				continue;
			}
			$ext = substr($value,-5);
			$ext = strtolower($ext);
			if($ext != '.html'){
				continue;
			}
			$chk = substr($value,0,-5);
			$tplfile = substr($value,$length,-5);
			$einfo = $chk.'.php';
			$config = array();
			if(file_exists($einfo)){
				include($einfo);
			}
			if(!$config['title']){
				$config['title'] = $tplfile;
			}
			$data = array('tplfile'=>$tplfile,"title"=>$config['title'],"note"=>$config['note']);
			if($config['img'] && file_exists($this->dir_root.$config['img'])){
				$data['img'] = $config['img'];
			}else{
				$img = $chk.'.jpg';
				if($img && file_exists($img)){
					$data['img'] = $tplfile.'.jpg';
				}else{
					$img = $chk.'.png';
					if($img && file_exists($img)){
						$data['img'] = $tplfile.'.png';
					}
				}
			}
			$tplist[$tplfile] = $data;
		}
		return $tplist;
	}

	/**
	 * 保存代码内容
	 * @参数 $id 模板ID
	 * @参数 $content 保存的内容
	 * @参数 $delcode 要删除的文件
	**/
	public function content($id,$content='',$delcode='')
	{
		if($content && $id && $delcode && $delcode != $id){
			$this->lib('file')->rm($this->dir_data.'design/'.$delcode.'.html');
		}
		if($content && $id){
			$this->lib('file')->vim($content,$this->dir_data.'design/'.$id.'.html');
			return true;
		}
		return $this->lib('file')->cat($this->dir_data.'design/'.$id.'.html');
	}

	public function code_check($code,$id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."design WHERE code='".$code."'";
		if($id){
			$sql .= " AND id!='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	public function tpl_info($id='')
	{
		if(!$id){
			return false;
		}
		$ext = substr($id,-5);
		$ext = strtolower($ext);
		if($ext == '.html'){
			$id = substr($id,0,-5);
		}
		$tplfile = $this->dir_root.$id.'.html';
		$einfo = $this->dir_root.$id.'.php';
		$config = array();
		if(file_exists($einfo)){
			include($einfo);
		}
		if(!$config['title']){
			$config['title'] = $tplfile;
		}
		$data = array('tplfile'=>$tplfile,"title"=>$config['title'],"note"=>$config['note']);
		if($config['img'] && file_exists($this->dir_root.$config['img'])){
			$data['img'] = $config['img'];
		}else{
			$img = $this->dir_root.$id.'.jpg';
			if($img && file_exists($img)){
				$data['img'] = $id.'.jpg';
			}else{
				$img = $this->dir_root.$id.'.png';
				if($img && file_exists($img)){
					$data['img'] = $id.'.png';
				}
			}
		}
		return $data;
	}
}
