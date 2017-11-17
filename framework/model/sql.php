<?php
/**
 * 数据库操作基础类
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年10月04日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class sql_model_base extends phpok_model
{
	protected $tbl_key;
	public function __construct()
	{
		parent::model();
		$this->tbl_key = array('PRI'=>P_Lang('主键'),'MUL'=>P_Lang('索引'),'UNI'=>P_Lang('唯一'));
	}

	public function table_info($table='')
	{
		if(!$table){
			return false;
		}
		$tmplist = $this->db->list_fields_more($table,false);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$comment = array();
			if($value['extra'] && $value['extra'] == 'auto_increment'){
				$comment[] = P_Lang('自动增量');
				unset($value['extra']);
			}
			if($value['key'] && $this->tbl_key[$value['key']]){
				$comment[] = $this->tbl_key[$value['key']];
				unset($value['key']);
			}
			if($value['comment']){
				$comment[] = $value['comment'];
				unset($value['comment']);
			}
			$value['note'] = implode(" / ",$comment);
			$rslist[$key] = $value;
		}
		return $rslist;
	}
}