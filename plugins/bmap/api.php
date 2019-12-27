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
        $url = 'http://api.map.baidu.com/geocoder/v2/?address='.rawurlencode($address);
        $url .= '&output=json';
        $url .= '&ak='.$this->apikey;
//        $info = file_get_contents($url);
//        exit($info);
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


}