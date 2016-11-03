<?php
/***********************************************************
	Filename: {phpok}/model/tag.php
	Note	: TAG管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-04-16 07:53
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	//根据指定主题下的可能用到的Tag
	//id，指的是主题id
	//type，仅支持list,cate,project,site四种类型，分类是主题，分类，项目及站点全局
	//使用一层层递进，list，cate和project支持读父级的tag
	public function tag_list($id,$type="list",$site_id=0)
	{
		if(!$id){
			return false;
		}
		if($type == 'list'){
			$rslist = $this->get_record_from_stat($id);
			if($rslist){
				return $this->tag_array_html($rslist);
			}
			$sql = "SELECT parent_id,cate_id,module_id,project_id,site_id FROM ".$this->db->prefix."list WHERE id='".$id."' AND status=1";
			$rs = $this->db->get_one($sql);
			if(!$rs){
				return false;
			}
			if($rs['parent_id']){
				$rslist = $this->get_record_from_stat($rs['parent_id']);
			}
			if(!$rslist && $rs['cate_id']){
				$this->get_tag_from_cate($rslist,$rs['cate_id']);
			}
			if(!$rslist && $rs['project_id']){
				$rslist = $this->get_record_from_stat('p'.$rs['project_id']);
				if(!$rslist){
					$parent_id = $this->_parent_project($rs['project_id']);
					if($parent_id){
						$rslist = $this->get_record_from_stat('p'.$parent_id);
					}
				}
			}
		}elseif($type == 'cate'){
			$rslist = false;
			$this->get_tag_from_cate($rslist,$id);
		}elseif($type == 'project'){
			$rslist = $this->get_record_from_stat('p'.$id);
			if(!$rslist){
				$parent_id = $this->_parent_project($id);
				if($parent_id){
					$rslist = $this->get_record_from_stat('p'.$parent_id);
				}
			}
		}
		if(!$rslist){
			if(!$site_id){
				$site_id = $this->site_id;
			}
			$rslist = $this->get_global_tag($site_id);
		}
		if(!$rslist){
			return false;
		}
		return $this->tag_array_html($rslist);
	}

	private function tag_array_html($rslist)
	{
		foreach($rslist as $key=>$value)
		{
			$value['target'] = $value['target'] ? '_blank' : '_self';
			$url = $this->url('tag','','title='.rawurlencode($value['title']));
			$alt = $value['alt'] ? $value['alt'] : $value['title'];
			$rslist[$key]['html'] = '<a href="'.$url.'" title="'.$alt.'" target="'.$value['target'].'" class="tag">'.$value['title'].'</a>';
			$rslist[$key]['target'] = $value['target'];
			$rslist[$key]['url'] = $url;
			$rslist[$key]['alt'] = $alt;
			$rslist[$key]['replace_count'] = $value['replace_count'];
			$rslist[$key]['title_id'] = $value['title_id'];
			$rslist[$key]['id'] = $value['id'];
		}
		return $rslist;
	}

	private function get_global_tag($site_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tag WHERE is_global=1 AND site_id='".$site_id."' ORDER BY LENGTH(title) DESC";
		return $this->db->get_all($sql);
	}

	private function _parent_project($id)
	{
		$sql = "SELECT parent_id FROM ".$this->db->prefix."project WHERE id='".$id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs || ($rs && !$rs['parent_id'])){
			return false;
		}
		return $rs['parent_id'];
	}

	private function get_tag_from_cate(&$rslist,$id)
	{
		$rslist = $this->get_record_from_stat('c'.$id);
		if($rslist)
		{
			return $rslist;
		}
		$pcate = $this->_parent_cate($id);
		if($pcate){
			$this->get_tag_from_cate($rslist,$pcate);
		}
		return false;
	}

	private function _parent_cate($id)
	{
		$sql = "SELECT parent_id FROM ".$this->db->prefix."cate WHERE id='".$id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs || ($rs && !$rs['parent_id']))
		{
			return false;
		}
		return $rs['parent_id'];
	}

	//取得主题下的Tag记录
	private function get_record_from_stat($id)
	{
		$sql = "SELECT t.*,s.title_id FROM ".$this->db->prefix."tag_stat s ";
		$sql.= " JOIN ".$this->db->prefix."tag t ON(s.tag_id=t.id) ";
		$sql.= " WHERE s.title_id='".$id."' ORDER BY LENGTH(t.title) DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		return $rslist;
	}

	public function stat_save($tag_id,$title_id)
	{
		$sql = "REPLACE INTO ".$this->db->prefix."tag_stat(title_id,tag_id) VALUES('".$title_id."','".$tag_id."')";
		return $this->db->query($sql);
	}

	public function tag_format($tag,$content)
	{
		if(!$tag || !$content || !is_array($tag) || !is_string($content)){
			return false;
		}
		foreach($tag as $key=>$value){
			//将已存在的网址内容提取出来
			preg_match_all('/<a.*>.*<\/a>/isU',$content,$matches);
			if($matches && $matches[0]){
				$matches[0] = array_unique($matches[0]);
				foreach($matches[0] as $k=>$v){
					$string = '~/~/~'.md5($v).'~\~\~';
					$content = str_replace($v,$string,$content);
				}
			}
			//将其他HTML分离出来
			preg_match_all('/<.*>/isU',$content,$matches2);
			//将已存在title或是alt内容提取出来
			//preg_match_all('/title=["|\'](.+)["|\']/isU',$content,$matches2);
			if($matches2 && $matches2[0]){
				$matches2[0] = array_unique($matches2[0]);
				foreach($matches2[0] as $k=>$v){
					$string = '~\~\~'.md5($v).'~/~/~';
					$content = str_replace($v,$string,$content);
				}
			}
			$replace_count = $value['replace_count'] ? $value['replace_count'] : 3;
			$content = preg_replace('`'.preg_quote($value['title'],'`').'`isU',$value['html'],$content,$replace_count);
			//
			if($matches && $matches[0]){
				foreach($matches[0] as $k=>$v){
					$string = '~/~/~'.md5($v).'~\~\~';
					$content = str_replace($string,$v,$content);
				}
			}
			if($matches2 && $matches2[0]){
				foreach($matches2[0] as $k=>$v){
					$string = '~\~\~'.md5($v).'~/~/~';
					$content = str_replace($string,$v,$content);
				}
			}
		}
		return $content;
	}

	public function tag_filter($taglist,$id=0,$type='list')
	{
		if(!$taglist || !$taglist['list'] || !$taglist['tag']){
			return false;
		}
		$tag = $tag_keys = false;
		foreach($taglist['tag'] as $key=>$value){
			$tag[$value['title']] = $value;
			$tag_keys[] = $value['title'];
		}
		$list = false;
		foreach($taglist['list'] as $key=>$value){
			foreach($tag_keys as $k=>$v){
				if(stripos($value,$v) !== false){
					$list[$v] = $tag[$v];
				}
			}
		}
		if(!$list){
			return false;
		}
		if(!$id){
			return $list;
		}
		$title_id = $type == 'cate' ? 'c'.$id : ($type == 'project' ? 'p'.$id : $id);
		foreach($list as $key=>$value){
			if($value['title_id'] != $title_id){
				$this->stat_save($value['id'],$title_id);
			}
		}
		return $list;
	}
}
?>