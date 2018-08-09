<?php
/**
 * 标签管理器
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年04月21日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 根据指定主题下的可能用到的标签，其中 $type 为主题/分类/项目时，本身读不到标签时会尝试读取系统设置的分类/项目/站点里的标签
	 * @参数 $id 指主题ID或是项目ID或是分类ID或是站点ID
	 * @参数 $type 仅支持：list（主题），cate（分类），project（项目），site（全局）
	 * @参数 $site_id 站点ID
	 * @返回 格式化后的标签数组
	**/
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

	public function tag_quick($count=10)
	{
		$sql = "SELECT title FROM ".$this->db->prefix."tag WHERE site_id='".$this->site_id."' ORDER BY id DESC LIMIT ".$count;
		return $this->db->get_all($sql);
	}

	private function tag_array_html($rslist)
	{
		foreach($rslist as $key=>$value)
		{
			$value['target'] = $value['target'] ? '_blank' : '_self';
			//$url = $this->url('tag','','title='.rawurlencode($value['title']));
			$url = $value['url'] ? $value['url'] : $this->url('tag','','title='.rawurlencode($value['title']));
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
		$id = $this->cache->id(get_class(),'get_global_tag',$site_id);
		$check = $this->cache->get($id,true);
		if($check){
			return $this->cache->get($id);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."tag WHERE is_global=1 AND site_id='".$site_id."' ORDER BY LENGTH(title) DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$this->cache->save($id,false);
			return false;
		}
		$this->cache->save($id,$rslist);
		return $rslist;
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

	/**
	 * 读取标签配置信息
	 * @参数 $data 要保存的标签数据，必须是数组。为空或非数组时，表示读标签配置信息
	**/
	public function config($data='')
	{
		if($data && is_array($data)){
			$this->lib('xml')->save($data,$this->dir_data.'xml/tag_config_'.$this->site_id.'.xml');
			return true;
		}
		if(file_exists($this->dir_data.'xml/tag_config_'.$this->site_id.'.xml')){
			return $this->lib('xml')->read($this->dir_data.'xml/tag_config_'.$this->site_id.'.xml');
		}
		if(file_exists($this->dir_data.'xml/tag_config.xml')){
			return $this->lib('xml')->read($this->dir_data.'xml/tag_config.xml');
		}
		return array('separator'=>',','count'=>10);
	}

	/**
	 * 取得指定主题、项目，分类下的标签
	 * @参数 $id 主题ID或是项目ID（p前缀）或是分类ID（c前缀）
	 * @返回 合并后的标签字符串
	**/
	public function get_tags($id)
	{
		$sql = "SELECT t.title FROM ".$this->db->prefix."tag_stat s ";
		$sql.= " JOIN ".$this->db->prefix."tag t ON(s.tag_id=t.id) ";
		$sql.= " WHERE s.title_id='".$id."'";
		$rs = $this->db->get_all($sql);
		if(!$rs){
			return false;
		}
		$list = array();
		foreach($rs as $key=>$value){
			$list[] = $value['title'];
		}
		$config = $this->config();
		$separator = $config['separator'] ? $config['separator'] : ',';
		return implode($separator,$list);
	}
}