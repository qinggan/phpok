<?php
/*****************************************************************************************
	文件： {phpok}/api/express_control.php
	备注： 物流通用数据对接
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月07日 16时06分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class express_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$id = $this->get('id','int');
		$rs = $this->model('order')->express_one($id);
		if(!$rs){
			$this->json(P_Lang('数据不存在'));
		}
		if(!$rs['order_id'] || $rs['is_end'] || !$rs['express_id']){
			$this->json(true);
		}
		//读express信息
		$express = $this->model('express')->get_one($rs['express_id']);
		if(!$express){
			$this->json(true);
		}
		$rate = $express['rate'] ? $express['rate'] : 6;
		if($rs['last_query_time']){
			$time = strtotime(date("Y-m-d H:i",$rs['last_query_time']));
			$time += $rate * 3600;
			//如果未超出系统限制，不查询直接返回查询结果
			if($time >= $this->time){
				$this->json(true);
			}
		}
		$file = $this->dir_root.'gateway/express/'.$express['code'].'/index.php';
		if(!file_exists($file)){
			$this->json(true);
		}
		$info = include $file;
		if(!$info || !is_array($info)){
			$this->json(true);
		}
		if(!$info['status']){
			if(!$info['content']){
				$info['content'] = P_Lang('快递信息获取失败');
			}
			$this->json($info['content']);
		}
		//更新操作时间
		$this->model('order')->update_last_query_time($id);
		//删除旧的获取查询结果的数据
		$this->model('order')->log_delete($rs['order_id'],$rs['id'],$express['title']);
		//保存新的
		if($info['content']){
			foreach($info['content'] as $key=>$value){
				$data = array('order_id'=>$rs['order_id'],'order_express_id'=>$rs['id']);
				$data['addtime'] = strtotime($value['time']);
				$data['who'] = $express['title'];
				$data['note'] = $value['content'];
				$this->model('order')->log_save($data);
			}
		}
		if($info['is_end']){
			$this->model('order')->update_end($id);
		}
		$this->json('refresh',true);
	}
}

?>