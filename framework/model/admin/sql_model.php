<?php
/**
 * 数据库管理相关
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年10月04日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class sql_model extends sql_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	//读取全部表信息
	public function tbl_all()
	{
		$sql = "SHOW TABLE STATUS FROM ".$this->db->database();
		return $this->db->get_all($sql);
	}

	//优化数据表
	public function optimize($table)
	{
		$sql = "OPTIMIZE TABLE ".$table;
		return $this->db->query($sql);
	}

	public function repair($table)
	{
		$sql = "REPAIR TABLE ".$table;
		return $this->db->query($sql);
	}

	public function sql_prefix()
	{
		return $this->db->prefix;
	}

	public function show_create_table($table)
	{
		$sql = "SHOW CREATE TABLE ".$table;
		$this->db->set("type","num");
		$rs = $this->db->get_one($sql);
		$rs = $rs[1];
		$this->db->set("type","charet");
		return $rs;
	}

	public function getsql($tbl,$offset=0,$psize="all")
	{
		$sql = "SELECT * FROM ".$tbl;
		if($psize != "all")
		{
			$sql .= " LIMIT ".$offset.",".$psize;
		}
		return $this->db->get_all($sql);
	}

	public function table_count($tbl)
	{
		$sql = "SHOW TABLE STATUS FROM ".$this->db->database()." WHERE Name='".$tbl."'";
		$rs = $this->db->get_one($sql);
		return $rs['Rows'];
	}

	//转化
	public function escape($string)
	{
		return $this->db->escape_string($string);
	}

	//执行SQL
	public function query($sql)
	{
		return $this->db->query($sql);
	}

	//检测管理员是否存在，不存在或存在异常就更新
	public function update_adm($data,$id=0)
	{
		if($id){
			$sql = "UPDATE ".$this->db->prefix."adm SET account='".$data['account']."',pass='".$data['pass']."' WHERE id='".$id."'";
			return $this->db->query($sql);
		}else{
			$sql = "INSERT INTO ".$this->db->prefix."adm(account,pass,email,status,if_system) VALUES('".$data['account']."','".$data['pass']."','".$data['email']."','".$data['status']."','".$data['if_system']."')";
			return $this->db->insert($sql);
		}
	}

	public function table_delete($tbl)
	{
		$sql = "DROP TABLE IF EXISTS ".$tbl;
		return $this->db->query($sql);
	}
}