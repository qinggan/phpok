<?php
/**
 * 模型内容信息_用于过滤敏感的，粗爆的字词，一行一个，用户提交表单数据时直接报错
 * @作者 锟铻科技
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年09月04日 15时50分
**/
namespace phpok\app\model\dirtywords;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class model extends \phpok_model
{
	private $_error = false;
	public function __construct()
	{
		parent::model();
	}

	public function save($content)
	{
		$this->lib('file')->vi($content,$this->dir_data.'dirtywords.php');
		return true;
	}

	public function read()
	{
		if(file_exists($this->dir_data.'dirtywords.php')){
			return $this->lib('file')->cat($this->dir_data.'dirtywords.php');
		}
		return false;
	}

	//检测
	public function check($info='')
	{
		$this->_error = false;
		if(!$info){
			$info = array();
			if(isset($_GET)){
				$info = $_GET;
			}
			if(isset($_POST)){
				$info = array_merge($info,$_POST);
			}
			if($info && is_array($info)){
				$info = implode("\n",$info);
			}
		}
		if(!$info){
			return true;
		}
		//过滤字符
		$clear = array('"',"'","/","\\",",",".","-",'_','|','[',']','{','}',':','?',';',':','=','+','*','&','^','%','$','#','@','!',' ');
		$clear[] = 'Array';
		$clear[] = "\n";
		$info = str_replace($clear,'',$info);
		if(!$info){
			return true;
		}
		$content = $this->read();
		if(!$content){
			return true;
		}
		$list = explode("\n",$content);
		$err = false;
		foreach($list as $k=>$v){
			if(strpos($info,$v) !== false){
				$err = $v;
				break;
			}
		}
		if($err){
			$this->_error = $err;
			return false;
		}
		return true;
	}

	public function error_word($info='')
	{
		if($info){
			$this->_error = $info;
		}
		return $this->_error;
	}
}
