<?php
/***********************************************************
	Filename: plugins/phpmyadmin/admin.php
	Note	: PHPMyAdmin管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年1月31日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_phpmyadmin extends phpok_plugin
{
	function __construct()
	{
		parent::plugin();
	}

	function html_sql_index_body()
	{
		$id = $this->plugin_id();
		$html = '<div class="action"><a href="'.$this->url('plugin','exec','id='.$id.'&exec=manage').'" target="_blank">PHPMyAdmin</a></div>';
		echo '<script type="text/javascript">'."\n";
		echo '$(document).ready(function(){'."\n\t";
		echo '$(".tips").append(\''.$html.'\');'."\n";
		echo '});'."\n";
		echo '</script>'."\n";
	}

	function manage()
	{
		$rs = $this->plugin_info();
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
		echo '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
		echo '<head>'."\n";
		echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">'."\n";
		echo '<title>phpMyAdmin</title>'."\n";
		echo '</head>'."\n";
		echo '<body>'."\n";
		echo '<form name="myadmin" ID="myadmin" method="post" action="'.$rs['param']['phpmyadminurl'].'">';
		echo '<input name="mysqlhost" type="hidden" value="'.$this->db->config_db['host'].'" />';
		echo '<input name="mysqlport" type="hidden" value="'.$this->db->config_db['port'].'" />';
		echo '<input name="pma_username" type="hidden" value="'.$this->db->config_db['user'].'" />';
		echo '<input name="pma_password" type="hidden" value="'.$this->db->config_db['pass'].'" />';
		echo '<input name="server" type="hidden" value="1" />';
		echo '<input type="submit" name="Submit" value="login......" />';
		echo '</form>'."\n";
		echo '<script language="javascript">'."\n";
		echo 'document.myadmin.submit();'."\n";
		echo '</script>'."\n";
		echo '</body>'."\n";
		echo '</html>';
		exit;
	}
}
?>