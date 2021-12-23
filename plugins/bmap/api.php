<?php
/**
 * 百度地图地址编码<接口应用>
 * @package phpok\plugins
 * @作者 phpok
 * @版本 4.9.032
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年07月07日 09时51分
**/
class api_bmap extends phpok_plugin
{
	public $me;
	public $apikey;
	public $lng;
	public $lat;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
		if ($this->me && $this->me['param']){
			$this->apikey = $this->me['param']['apikey'];
		}
		$this->lng = $this->me['param']['lng'];
		$this->lat = $this->me['param']['lat'];
	}

	public function lnglat()
	{
		if (!$this->apikey){
			$this->error('您还没有设置百度秘钥');
		}
		$address = $this->get('address');
		if (!$address){
			$this->error('地址不能为空');
		}
		$ret_coordtype = $this->get('rtype');
		if(!$rtype){
			$ret_coordtype = 'bd09ll';
		}
		if(!in_array($ret_coordtype,array('gcj02ll','bd09mc','bd09ll'))){
			$ret_coordtype = 'bd09ll';
		}
		$url = 'http://api.map.baidu.com/geocoder/v2/?address='.rawurlencode($address);
		$url .= '&output=json';
		$url .= '&ak='.$this->apikey;
		if($ret_coordtype){
			$url .= "&ret_coordtype=".$ret_coordtype;
		}
		$data = $this->lib('curl')->get_json($url);
		//接口中 0 表示成功,所以可以用if判断不存在
		if ($data['status']){
			$error = $data['message'] ? '错误ID('.$data['status']."):".$data['message'] : '错误ID:'.$data['status'];
			$this->error($error);
		}
		//正确的数据
		$lng = $data['result']['location']['lng'];
		$lat = $data['result']['location']['lat'];
		$this->success(array('lng'=>$lng,'lat'=>$lat));
	}

	public function address()
	{
		if (!$this->apikey){
			$this->error('您还没有设置百度秘钥');
		}
		$lat = $this->get('lat','float');
		if(!$lat){
			$this->error('未指定纬度');
		}
		$lng = $this->get('lng','float');
		if(!$lng){
			$this->error('未指定经度');
		}
		$type = $this->get('type');
		if(!$type || !in_array($type,array('bd09ll','gcj02ll'))){
			$type = 'gcj02ll';
		}
		$url  = 'http://api.map.baidu.com/reverse_geocoding/v3/?output=json&coordtype='.$type;
		$url .= '&location='.$lat.','.$lng;
		$url .= '&ak='.$this->apikey;
		$data = $this->lib('curl')->get_json($url);
		//接口中 0 表示成功,所以可以用if判断不存在
		if ($data['status']){
			$error = $data['message'] ? '错误ID('.$data['status']."):".$data['message'] : '错误ID:'.$data['status'];
			$this->error($error);
		}
		$this->success($data['result']);
	}
}