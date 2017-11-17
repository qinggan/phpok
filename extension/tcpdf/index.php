<?php
/**
 * PDF生成操作类
 * @package phpok\extension\tcpdf
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年02月27日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tcpdf_lib
{
	/**
	 * 页面打印方向，P表示纵向，L表示横向
	**/
	private $page_orientation = 'P';

	/**
	 * 页面单位，默认是 mm
	**/
	private $page_unit = 'mm';


	/**
	 * 页面格式化，默认使用A4纸
	**/
	private $page_format = 'A4';

	/**
	 * PDF制作人
	**/
	private $page_author = 'phpok.com';

	/**
	 * PDF页头
	**/
	private $page_title = 'PHPOK';

	/**
	 * 间距设置
	**/
	private $margin_left = 5;
	private $margin_top = 10;
	private $margin_right = 5;
	private $margin_bottom = 10;

	private $psize = 10;
	
	public function __construct()
	{
		require_once(ROOT.'extension/tcpdf/config/tcpdf_config.php');
		require_once(ROOT.'extension/tcpdf/tcpdf.php');
	}

	public function orientation($val='')
	{
		if($val && ($val == 'P' || $val == 'L')){
			$this->page_orientation = $val;
		}
		return $this->page_orientation;
	}

	public function unit($val='')
	{
		if($val){
			$this->page_unit = $val;
		}
		return $this->page_unit;
	}

	public function page_format($val='')
	{
		if($val){
			$this->page_format = $val;
		}
		return $this->page_format;
	}

	public function author($val='')
	{
		if($val){
			$this->page_author = $val;
		}
		return $this->page_author;
	}

	public function title($val='')
	{
		if($val){
			$this->page_title = $val;
		}
		return $this->page_title;
	}

	/**
	 * 设置PDF间距
	 * @参数 $left 左间距
	 * @参数 $top 上间距
	 * @参数 $right 右间距
	 * @参数 $bottom 页脚间距
	 * @返回 true
	**/
	public function set_margin($left=5,$top=10,$right=5,$bottom=10)
	{
		$this->margin_left = $left;
		$this->margin_top = $top;
		$this->margin_right = $right;
		$this->margin_bottom = $bottom;
		return true;
	}

	public function psize($psize='')
	{
		if($psize){
			$this->psize = $psize;
		}
		return $this->psize;
	}

	public function create($html='',$file='',$download=false)
	{
		if(!$html){
			return false;
		}
		if(is_array($html)){
			return $this->create_array($html,$file,$download);
		}
		$PDF = new TCPDF($this->page_orientation,$this->page_unit, $this->page_format, true, 'UTF-8', false);
		$PDF->SetCreator('TCPDF');
		$PDF->SetAuthor($this->page_author);
		$PDF->SetTitle($this->page_title);

		// set default header data
		//$PDF->SetHeaderData($this->page_logo, $this->page_logo_width, $this->page_title, $this->page_author, array(0,64,255), array(0,64,128));
		//$PDF->setFooterData(array(0,64,0), array(0,64,128));

		// set header and footer fonts
		$PDF->setPrintHeader(false);
		$PDF->setPrintFooter(false);

		// set default monospaced font
		$PDF->SetDefaultMonospacedFont('courier');
		
		// set margins
		$PDF->SetMargins($this->margin_left, $this->margin_top, $this->margin_right);
		$PDF->SetHeaderMargin(0);
		$PDF->SetFooterMargin(0);

		// set auto page breaks
		$PDF->SetAutoPageBreak(true, $this->margin_bottom);

		// set image scale factor
		$PDF->setImageScale(1.25);
		$PDF->setFontSubsetting(true);
		$PDF->SetFont('cid0cs', '', 12,true);
		$PDF->AddPage();
		// set text shadow effect
		//$PDF->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
		$PDF->writeHTML($html, true, false, true, false, '');
		$type = $download ? 'D' :'F';
		$PDF->Output($file,$type);
	}

	public function create_array($list='',$file='',$download=false)
	{
		$PDF = new TCPDF($this->page_orientation,$this->page_unit, $this->page_format, true, 'UTF-8', false);
		$PDF->SetCreator('TCPDF');
		$PDF->SetAuthor($this->page_author);
		$PDF->SetTitle($this->page_title);
		$PDF->setPrintHeader(false);
		$PDF->setPrintFooter(false);
		$PDF->SetDefaultMonospacedFont('courier');
		$PDF->SetMargins($this->margin_left, $this->margin_top, $this->margin_right);
		$PDF->SetHeaderMargin(0);
		$PDF->SetFooterMargin(0);
		$PDF->setImageScale(1);
		$PDF->setFontSubsetting(true);
		$PDF->SetFont('cid0cs', '', 12,true);
		$list = array_chunk($list,$this->psize);
		foreach($list as $key=>$value){
			$html = '';
			foreach($value as $k=>$v){
				$html .= $v;
			}
			$PDF->AddPage();
			$PDF->writeHTML($html, true, false, true, false, '');
		}
		$type = $download ? 'D' :'F';
		$PDF->Output($file,$type);
	}
}
