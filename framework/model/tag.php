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

	//针对多主题的 Tag 处理
	public function list_all($ids,$site_id=0)
	{
		if($site_id){
			$this->site_id = $site_id;
		}
		if(!$ids){
			return false;
		}
		if($ids && is_array($ids)){
			$ids = implode("','",$ids);
		}
		$sql = "SELECT t.*,s.title_id FROM ".$this->db->prefix."tag_stat s ";
		$sql.= " JOIN ".$this->db->prefix."tag t ON(s.tag_id=t.id) ";
		$sql.= " WHERE s.title_id IN('".$ids."') AND site_id='".$this->site_id."' ORDER BY LENGTH(t.title) DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$list = $this->tag_array_html($rslist);
		$rslist = array();
		foreach($list as $key=>$value){
			$rslist[$value['title_id']][$value['id']] = $value;
		}
		return $rslist;
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

	public function tag_array_html($rslist)
	{
		foreach($rslist as $key=>$value){
			$value['target'] = $value['target'] ? '_blank' : '_self';
			$url = $this->url('tag','','title='.rawurlencode($value['title']),'www');
			if($value['id']){
				$url = $this->url('tag','','title='.$value['id']);
			}
			if($value['identifier']){
				$url = $this->url('tag','','title='.$value['identifier']);
			}
			if($value['url']){
				$url =  $value['url'];
			}
			$alt = $value['alt'] ? $value['alt'] : $value['title'];
			$value['html'] = '<a href="'.$url.'" title="'.$alt.'" target="'.$value['target'].'" class="tag">'.$value['title'].'</a>';
			$value['url'] = $url;
			$value['alt'] = $alt;
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function tag_html($tag='')
	{
		if(!$tag){
			return false;
		}
		if(is_string($tag)){
			$tag = str_replace(array(",","，",'、',"|","/","　"),"|",$tag);
			$tag = explode("|",$tag);
		}
		$rslist = array();
		foreach($tag as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			$tmp = array('target'=>'_blank');
			$tmp['title'] = trim($value);
			$tmp['alt'] = trim($value);
			$tmp['url'] = $this->url('tag','','title='.rawurlencode(trim($value)),'www');
			$tmp['html'] = '<a href="'.$tmp['url'].'" title="'.trim($value).'" target="'.$tmp['target'].'" class="tag">'.trim($value).'</a>';
			$rslist[] = $tmp;
		}
		return $rslist;
	}

	public function get_one($id,$field='id',$site_id=0)
	{
		if($site_id){
			$this->site_id($site_id);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."tag WHERE ".$field."='".$id."' AND site_id='".$this->site_id."'";
		return $this->db->get_one($sql);
	}

	public function get_list($condition="",$offset=0,$psize=30,$site_id=0)
	{
		if($site_id){
			$this->site_id($site_id);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."tag WHERE site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$sql.= " ORDER BY is_global DESC,id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$ids = array_keys($rslist);
		$sql = "SELECT count(title_id) as count,tag_id FROM ".$this->db->prefix."tag_stat WHERE tag_id IN(".implode(",",$ids).") GROUP BY tag_id";
		$count_list = $this->db->get_all($sql,'tag_id');
		if($count_list){
			foreach($rslist as $key=>$value){
				$rslist[$key]['count'] = $count_list[$key]['count'] ? $count_list[$key]['count'] : 0;
			}
		}
		return $rslist;
	}

	public function get_all($condition='',$offset=0,$psize=30,$orderby='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tag";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		if(!$orderby){
			$orderby = 'id DESC';
		}
		$sql .= " ORDER BY ".$orderby." ";
		if($psize && intval($psize)>0){
			$sql .= " LIMIT ".intval($offset).",".intval($psize);
		}
		return $this->db->get_all($sql);
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
		if($rslist){
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
		if(!$rslist){
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
			$replace_count = intval($value['replace_count']);
			if($replace_count){
				$content = preg_replace("/".preg_quote($value['title'],'/')."/isU",$value['html'],$content,$replace_count);
			}
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
		return array('separator'=>',','count'=>10,'psize'=>20,'urlformat'=>'');
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

	/**
	 * 增加TagID的点击率
	 * @参数 $tag_id 指定的TagID
	**/
	public function add_hits($tag_id)
	{
		$sql = "UPDATE ".$this->db->prefix."tag SET hits=hits+1 WHERE id='".intval($tag_id)."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得标签下的总数量
	 * @参数 $tag_id 指定标签ID
	**/
	public function tag_total($tag_id)
	{
		$sql = " SELECT count(title_id) FROM ".$this->db->prefix."tag_stat ";
		$sql.= " WHERE tag_id='".$tag_id."' ";
		return $this->db->count($sql);
	}

	/**
	 * 取得Tag标签下的列表
	 * @参数 $tag_id 标签ID
	 * @参数 $offset 开启页码
	 * @参数 $psize 每页取数
	**/
	public function id_list($tag_id,$offset=0,$psize=30,$condition="")
	{
		$sql = " SELECT title_id as id FROM ".$this->db->prefix."tag_stat WHERE tag_id='".$tag_id."' ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		$sql.= " ORDER BY title_id DESC LIMIT ".intval($offset).",".intval($psize);
		return $this->db->get_all($sql);
	}

	public function add_title($tag_id,$title_id)
	{
		$rs = $this->get_one($tag_id);
		if(!$rs){
			return false;
		}
		$config = $this->config();
		$separator = ($config && $config['separator']) ? $config['separator'] : ',';
		$data = array('tag_id'=>$tag_id,'title_id'=>$title_id);
		$this->db->insert_array($data,'tag_stat','replace');
		if(substr($title_id,0,1) == 'p'){
			$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id='".substr($title_id,1)."'";
			return $this->_add_tag_stat($sql,$rs,$separator,'project');
		}
		if(substr($title_id,0,1) == 'c'){
			$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id='".substr($title_id,1)."'";
			return $this->_add_tag_stat($sql,$rs,$separator,'cate');
		}
		if(is_numeric($title_id)){
			$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id='".$title_id."'";
			return $this->_add_tag_stat($sql,$rs,$separator,'list');
		}
		return true;
	}

	private function _add_tag_stat($sql,$tag,$separator=',',$type="list")
	{
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return true;
		}
		if(!$rs['tag']){
			$sql = "UPDATE ".$this->db->prefix.$type." SET tag='".$tag['title']."' WHERE id='".$rs['id']."'";
			$this->db->query($sql);
			return true;
		}
		$list = explode($separator,$rs['tag']);
		$is_add = true;
		foreach($list as $key=>$value){
			$value = trim($value);
			if($value == $tag['title']){
				$is_add = false;
				break;
			}
		}
		if(!$is_add){
			return true;
		}
		$tag = $rs['tag'].$separator.$tag['title'];
		$sql = "UPDATE ".$this->db->prefix.$type." SET tag='".$tag."' WHERE id='".$rs['id']."'";
		$this->db->query($sql);
		return true;
	}

	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'tag',array('id'=>$id));
		}
		return $this->db->insert_array($data,"tag");
	}

	/**
	 * 批量更新标签及标签统计
	 * @参数 $data 标签数据，可以是数组也可以是字符串
	 * @参数 $list_id 主题ID（项目ID，p前缀）（分类ID，c前缀）
	**/
	public function update_tag($data='',$list_id=0)
	{
		if(!$list_id){
			return false;
		}
		//没有要更新的tag标签时，删除原有记录
		if(!$data){
			return $this->stat_delete($list_id,'title_id');
		}
		$data = $this->string_to_array($data);
		$site_id = $this->_tag_site_id($list_id);
		$this->stat_delete($list_id,'title_id');
		foreach($data as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			$value = trim($value);
			$chk_rs = $this->chk_title($value);
			if($chk_rs){
				$id = $chk_rs['id'];
			}else{
				$array = array('site_id'=>$site_id,'title'=>$value,'url'=>'','target'=>'0');
				$id = $this->save($array);
			}
			if($id){
				$this->stat_save($id,$list_id);
			}
		}
		return true;
	}

	public function update_tag_title($old,$title,$tag_id)
	{
		if(!$old || !$title || !$tag_id){
			return false;
		}
		$idlist = $this->id_list($tag_id,0,999);
		if(!$idlist){
			return false;
		}
		$plist = $clist = $ilist = array();
		foreach($idlist as $key=>$value){
			if(is_numeric($value['id'])){
				$ilist[] = $value['id'];
			}
			if(substr($value['id'],0,1) == 'p'){
				$plist[] = substr($value['id'],1);
			}
			if(substr($value['id'],0,1) == 'c'){
				$clist[] = substr($value['id'],1);
			}
		}
		if($ilist){
			$sql = "UPDATE ".$this->db->prefix."list SET tag=REPLACE(tag,'".$old."','".$title."') WHERE id IN(".implode(",",$ilist).")";
			$this->db->query($sql);
		}
		if($plist){
			$sql = "UPDATE ".$this->db->prefix."project SET tag=REPLACE(tag,'".$old."','".$title."') WHERE id IN(".implode(",",$plist).")";
			$this->db->query($sql);
		}
		if($clist){
			$sql = "UPDATE ".$this->db->prefix."cate SET tag=REPLACE(tag,'".$old."','".$title."') WHERE id IN(".implode(",",$clist).")";
			$this->db->query($sql);
		}
		return true;
	}

	public function chk_title($title,$id=0)
	{
		$sql  = "SELECT id FROM ".$this->db->prefix."tag WHERE ";
		$sql .= "title='".$title."' AND site_id='".$this->site_id."'";
		if($id){
			$sql .= " AND id!='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	public function stat_delete($id,$field='tag_id')
	{
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE ".$field."='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 通过标签中的记录，获取相应的站点ID
	 * @参数 $id 标签ID
	**/
	private function _tag_site_id($id)
	{
		$sql = "";
		if(substr($id,0,1) == 'p'){
			$sql = "SELECT site_id FROM ".$this->db->prefix."project WHERE id='".substr($id,1)."'";
		}
		if(substr($id,0,1) == 'c'){
			$sql = "SELECT site_id FROM ".$this->db->prefix."cate WHERE id='".substr($id,1)."'";
		}
		if(is_numeric($id)){
			$sql = "SELECT site_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
		}
		if(!$sql){
			return $this->site_id;
		}
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs['site_id']){
			return $this->site_id;
		}
		return $rs['site_id'];
	}
	
	/**
	 * 字符串转数组
	 * @参数 $string 要转化的字符串
	 * @返回 数组
	**/
	private function string_to_array($string)
	{
		if(!$string || !trim($string)){
			return false;
		}
		if(is_array($string)){
			return $string;
		}
		$config = $this->config();
		$separator = $config['separator'] ? $config['separator'] : ',';
		return explode($separator,$string);
	}

	public function node_list($tag_id,$status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tag_node WHERE tag_id='".$tag_id."' ";
		if($status){
			$sql .= " AND status=1 ";
		}
		$sql .= " ORDER BY taxis ASC,identifier ASC,id ASC LIMIT 999";
		return $this->db->get_all($sql);
	}

	public function node_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tag_node WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}
}