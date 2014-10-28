<?php
/*****************************************************************************************
	文件： plugins/weather/api.php
	备注： 百度天气API获取
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年7月7日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class api_weather extends phpok_plugin
{
	public $me;
	function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->tpl->assign('plugin',$this->me);
	}

	function info()
	{
		if(!$this->me['param']['weather_api'])
		{
			$this->json('未配置天气接口');
		}
		$city = $GLOBALS['app']->get('city');
		if(!$city)
		{
			$city = $this->_get_city();
			if(!$city)
			{
				$this->json('获取城市失败');
			}
		}
		$url = 'http://api.map.baidu.com/telematics/v3/weather?ak='.$this->me['param']['weather_api'];
		$url .= '&location='.rawurlencode($city);
		$url .= "&output=json";
		$info = $this->lib('html')->get_content($url);
		if(!$info)
		{
			$this->json('获取天气数据失败');
		}
		$info = $this->lib('json')->decode($info);
		if($info['status'] != 'success')
		{
			$this->json($info['message']);
		}
		$data = $info['results'][0]['weather_data'];
		if(!$data)
		{
			$this->json('没有取得相关天气信息');
		}
		$rslist = array();
		$d = array('日','一','二','三','四','五','六');
		foreach($data as $key=>$value)
		{
			$time = $this->time + ($key*24*3600);
			$tmp = array();
			$tmp['date'] = date("Y-m-d",$time);
			$tmp['week'] = '周'.$d[date('w',$time)];
			$picid = date("G",$time) > 17 ? 'nightPictureUrl' : 'dayPictureUrl';
			$tmp['picurl'] = $value[$picid];
			$tmp['weather'] = $value['weather'];
			$tmp['temperature'] = $value['temperature'];
			$rslist[$key] = $tmp;
		}
		$this->json($rslist,true);
	}

	function _get_city()
	{
		$ip = phpok_ip();
		if(!$ip || $ip == '127.0.0.1')
		{
			$this->json('本地环境不支持定位');
		}
		if(!$this->me['param']['ip_api'])
		{
			$this->json('未配置IP转地址接口');
		}
		$url = 'http://api.map.baidu.com/location/ip?ak='.$this->me['param']['ip_api'].'&ip='.rawurlencode($ip);
		$info = $this->lib('html')->get_content($url);
		if(!$info)
		{
			$this->json('自动定位出错');
		}
		$info = $this->lib('json')->decode($info);
		if($info['error'])
		{
			$this->json($info['message']);
		}
		if(!$info['address'])
		{
			$this->json('获取地址失败');
		}
		$tmp = explode('|',$info['address']);
		if(!$tmp[2] && !$tmp[1])
		{
			$this->json('没有取到相关地址信息');
		}
		return $tmp[2] ? $tmp[2] : $tmp[1];
	}
}

?>