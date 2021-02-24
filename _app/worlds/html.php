<?php
/**
 * 接入HTML节点_管理全球国家及州/省信息
 * @作者 苏相锟 <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司 / 苏相锟
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html 
 * @时间 2020年11月20日
**/
namespace phpok\app\worlds;

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class html_phpok extends \_init_node_html
{
	public function __construct()
	{
		parent::__construct();
	}

	public function www_before()
	{
		//echo '456';
	}

	public function www_after()
	{
		//echo '123';
	}

	public function admin_list_edit_after()
	{
		$p_rs = $this->tpl->val('p_rs');
		if($p_rs['is_biz']){
			$this->pri_worlds_price($p_rs);
		}		
	}

	private function pri_worlds_price($project=array())
	{
		$rs = $this->tpl->val('rs');
		if($rs && $rs['id']){
			//读取全球价格
			$pricelist = $this->model('worlds')->pricelist($rs['id'],'price');
			$this->assign('w_pricelist',$pricelist);
			//读取全球运费
			$pricelist = $this->model('worlds')->pricelist($rs['id'],'freight');
			$this->assign('f_pricelist',$pricelist);
			//读取全球消费税
			$pricelist = $this->model('worlds')->pricelist($rs['id'],'excise');
			$this->assign('x_pricelist',$pricelist);
			//读取全球价格
			$pricelist = $this->model('worlds')->pricelist($rs['id'],'tariff');
			$this->assign('g_pricelist',$pricelist);
			$notelist = $this->model('worlds')->pricelist($rs['id'],'note');
			$this->assign('g_notelist',$notelist);
		}
		$currency_id = ($rs && $rs['currency_id']) ? $rs['currency_id'] : ($project['currency_id'] ? $project['currency_id'] : 0);
		if(!$currency_id && $this->site['currency_id']){
			$currency_id = $this->site['currency_id'];
		}
		$currency_list = $this->tpl->val('currency_list');
		if(!$currency_id && $currency_list){
			$current = current($currency_list);
			$currency_id = $current['id'];
		}
		if(!$currency_id || !$currency_list){
			return false;
		}
		$currency = array();
		if($currency_list){
			foreach($currency_list as $key=>$value){
				if($value['id'] == $currency_id){
					$currency = $value;
				}
			}
		}
		$countrylist = $this->model('worlds')->group_countries();
		if($countrylist){
			foreach($countrylist as $key=>$value){
				if(!$value['rslist']){
					unset($countrylist[$key]);
					continue;
				}
				foreach($value['rslist'] as $k=>$v){
					if($v['currency_id'] && $currency_list[$v['currency_id']]){
						$v['currency_title'] = $currency_list[$v['currency_id']]['title'];
						$v['currency_code'] = $currency_list[$v['currency_id']]['code'];
					}else{
						$v['currency_title'] = $currency['title'];
						$v['currency_code'] = $currency['code'];
					}
					$value['rslist'][$k] = $v;
				}
				$countrylist[$key] = $value;
			}
		}
		$this->assign('countrylist',$countrylist);
		$this->_show("admin_list_edit");
	}
}