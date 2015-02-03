<?php
/***********************************************************
	Filename: phpok_tpl_helper.php
	Note	: 在PHPOK模板中常用的函数，此函数在action前才加载
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-07 20:27
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}


//该函数可实现数据自定义调用
//id，即标识串，在后台数据调用中心设置
//ext，扩展属性，可替换默认的扩展属性，支持数组及字符串，字符串格式为：cateid=1&project=test，以&分格隔
//notin，即不包含的ID，适用于读文章列表时要排除的ID
function phpok($id,$ext="")
{
	return $GLOBALS['app']->call->phpok($id,$ext);
}

function token($data)
{
	$keyid = $GLOBALS['app']->site['api_code'];
	$GLOBALS['app']->lib('token')->keyid($keyid);
	return $GLOBALS['app']->lib('token')->encode($data);
}

//同上，此函数跳过后台数据调用中心，直接获取数据信息
function phpok_load($type,$ext="")
{
	return $GLOBALS['app']->call->phpok_load($type,$ext);
}

//在模板页中直接分页传参数，完整参数有
//home=首页&prev=上一页&next=下一页&last=尾页&half=5&opt=1&add={total}/{psize}
function phpok_page($url,$total,$num=0,$psize=20,$param="")
{
	if(!$url || !$total) return false;
	if($param)
	{
		parse_str($param,$list);
		if(!$list) $list = array();
		foreach($list AS $key=>$value)
		{
			if(substr($value,0,1) == ';' && substr($value,-1) == ';') $value = "&".substr($value,1);
			if($key == 'home' && $value) $GLOBALS['app']->lib('page')->home_str($value);
			if($key == 'prev' && $value) $GLOBALS['app']->lib('page')->prev_str($value);
			if($key == 'next' && $value) $GLOBALS['app']->lib('page')->next_str($value);
			if($key == 'last' && $value) $GLOBALS['app']->lib('page')->last_str($value);
			if($key == 'half' && $value !='') $GLOBALS['app']->lib('page')->half($value);
			if($key == 'opt' && $value) $GLOBALS['app']->lib('page')->opt_str($value);
			if($key == 'add' && $value) $GLOBALS['app']->lib('page')->add_up($value);
			if($key == 'always' && $value) $GLOBALS['app']->lib('page')->always($value);
		}
	}
	if($num<1) $num = 1;
	$pagelist = $GLOBALS['app']->lib('page')->page($url,$total,$num,$psize);
	return $pagelist;
}

# 后台调用插件
function phpok_plugin()
{
	//取得全部插件
	$rslist = $GLOBALS['app']->model('plugin')->get_all(1);
	if(!$rslist)
	{
		return false;
	}
	$id = $GLOBALS['app']->app_id;
	$ctrl = $GLOBALS['app']->ctrl;
	$func = $GLOBALS['app']->func;
	
	//装载插件
	foreach($rslist AS $key=>$value)
	{
		if(is_file($GLOBALS['app']->dir_root.'plugins/'.$key.'/'.$id.'.php'))
		{
			if($value['param']) $value['param'] = unserialize($value['param']);
			include_once($GLOBALS['app']->dir_root.'plugins/'.$key.'/'.$id.'.php');
			$name = $id.'_'.$key;
			$cls = new $name();
			$func_name = $ctrl.'_'.$func;
			$mlist = get_class_methods($cls);
			if($mlist && in_array($func_name,$mlist))
			{
				echo $cls->$func_name($value);
			}
		}
	}
}

// 根据图片存储的ID，获得图片信息
function phpok_image_rs($img_id)
{
	if(!$img_id) return false;
	global $app;
	$app->model("res");
	return $app->res_model->get_one($img_id);
}

//显示评论信息
//回复数据调用
function phpok_reply($id,$psize=10,$orderby="ASC",$vouch=false)
{
	$condition = "tid='".$id."' AND parent_id='0' ";
	$sessid = $GLOBALS['app']->session->sessid();
	$uid = $_SESSION['user_id'] ? $_SESSION['user_id'] : 0;
	$condition .= " AND (status=1 OR (status=0 AND (uid=".$uid." OR session_id='".$sessid."'))) ";
	if($vouch)
	{
		$condition .= " AND vouch=1 ";
	}
	$total = $GLOBALS['app']->model('reply')->get_total($condition);
	if(!$total) return false;
	//判断当前页
	$pageid = $GLOBALS['app']->get($GLOBALS['app']->config['pageid'],'int');
	if(!$pageid) $pageid = 1;
	$order = strtoupper($orderby) == 'ASC' ? 'id ASC' : 'id DESC';
	$offset = ($pageid-1) * $psize;
	$rslist = $GLOBALS['app']->model('reply')->get_list($condition,$offset,$psize,"",$order);
	//读子主题
	$idlist = $userlist = array();
	foreach($rslist AS $key=>$value)
	{
		if($value["uid"]) $userlist[] = $value["uid"];
		$idlist[] = $value["id"];
	}
	
	//获取会员信息
	if($userlist && count($userlist)>0)
	{
		$userlist = array_unique($userlist);
		$user_idstring = implode(",",$userlist);
		$condition = "u.status='1' AND u.id IN(".$user_idstring.")";
		$tmplist = $GLOBALS['app']->model('user')->get_list($condition,0,0);
		if($tmplist)
		{
			$userlist = array();
			foreach($tmplist AS $key=>$value)
			{
				$userlist[$value["id"]] = $value;
			}
			$tmplist = "";
		}
	}
	//整理回复列表
	foreach($rslist AS $key=>$value)
	{
		if($value["uid"] && $userlist)
		{
			$value["uid"] = $userlist[$value["uid"]];
		}
		$rslist[$key] = $value;
	}
	//返回结果集
	$array = array('list'=>$rslist,'psize'=>$psize,'pageid'=>$pageid,'total'=>$total);
	return $array;
}

function phpok_ip()
{
	$cip = (isset($_SERVER['HTTP_CLIENT_IP']) AND $_SERVER['HTTP_CLIENT_IP'] != "") ? $_SERVER['HTTP_CLIENT_IP'] : FALSE;
	$rip = (isset($_SERVER['REMOTE_ADDR']) AND $_SERVER['REMOTE_ADDR'] != "") ? $_SERVER['REMOTE_ADDR'] : FALSE;
	$fip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND $_SERVER['HTTP_X_FORWARDED_FOR'] != "") ? $_SERVER['HTTP_X_FORWARDED_FOR'] : FALSE;
	$ip = "0.0.0.0";
	if($cip && $rip)
	{
		$ip = $cip;
	}
	elseif($rip)
	{
		$ip = $rip;
	}
	elseif($cip)
	{
		$ip = $cip;
	}
	elseif($fip)
	{
		$ip = $fip;
	}

	if (strstr($ip, ','))
	{
		$x = explode(',', $ip);
		$ip = end($x);
	}

	if ( ! preg_match( "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $ip))
	{
		$ip = '0.0.0.0';
	}
	return $ip;
}

//网址，驼峰写法
function phpokUrl($rs)
{
	return phpok_url($rs);
}

//网址，下划线分割字符法
function phpok_url($rs)
{
	if(!$rs) return false;
	if(is_string($rs)) parse_str($rs,$rs);
	$ctrl = $func = $id = $appid = "";
	if($rs["ctrl"]) $ctrl = $rs["ctrl"];
	if($rs["func"]) $func = $rs["func"];
	if($rs["id"]) $id = $rs["id"];
	if($rs['appid']) $appid = $rs['appid'];
	if(!$ctrl && !$id) return false;
	if(!$ctrl)
	{
		$ctrl = $id;
		$id = "";
	}
	$tmp = array();
	foreach($rs AS $key=>$value)
	{
		if(!in_array($key,array("ctrl","id","func",'appid')))
		{
			if($key == '_nocache' && $value != 'false' && $value != '0')
			{
				$tmp[] = '_noCache=0.'.$GLOBALS['app']->time.rand(1000,9999);
			}
			else
			{
				$tmp[] = $key."=".rawurlencode($value);
			}
		}
	}
	if($ctrl && $id && $id != $ctrl)
	{
		$tmp[] = "id=".rawurlencode($rs["id"]);
	}
	$string = ($tmp && count($tmp)>0) ? implode("&",$tmp) : "";
	return $GLOBALS['app']->url($ctrl,$func,$string,$appid);
}

//读取会员拥有发布的权限信息
function usercp_project()
{
	if(!$_SESSION['user_id'] || !$_SESSION['user_rs']['group_id']) return false;
	$group_rs = $GLOBALS['app']->model('usergroup')->get_one($_SESSION['user_rs']['group_id']);
	if(!$group_rs || !$group_rs['status'] || !$group_rs['post_popedom'] || $group_rs['post_popedom'] == 'none') return false;
	return $GLOBALS['app']->model('project')->plist($group_rs['post_popedom'],1);
}
//读取会员信息，如果有ID，则读取该ID数组信息
function usercp_info($field="")
{
	if(!$_SESSION['user_id'] || !$_SESSION['user_rs']['group_id']) return false;
	$group_rs = $GLOBALS['app']->model('usergroup')->get_one($_SESSION['user_rs']['group_id']);
	if(!$group_rs || !$group_rs['status']) return false;
	$condition = '';
	if($group_rs['fields'])
	{
		$lst = explode(',',$group_rs['fields']);
		$string = implode("','",$lst);
		$condition = "identifier IN('".$string."') ";
	}
	$flist = $GLOBALS['app']->model('user')->fields_all($condition,'identifier');
	if(!$flist) return false;
	$rslist = array(0=>array('val'=>$group_rs['title'],'title'=>$GLOBALS['app']->lang[$GLOBALS['app']->app_id]['user_group_name']));
	foreach($flist AS $key=>$value)
	{
		$tmp = array('title'=>$value['title'],'val'=>$_SESSION['user_rs'][$key]);
		$rslist[] = $tmp;
	}
	return $rslist;
}

//读取我上传的最新图片
function usercp_new_reslist($offset=0,$psize=10)
{
	if(!$_SESSION['user_id']) return false;
	$condition = "ext IN('jpg','gif','png','jpeg') AND user_id='".$_SESSION['user_id']."'";
	//$condition = "ext IN('jpg','gif','png','jpeg')";
	return $GLOBALS['app']->model('res')->get_list($condition,$offset,$psize);
}


function time_format($timestamp)
{
	$current_time = $GLOBALS['app']->time;
    $since = abs($current_time-$timestamp);
    if(floor($since/3600))
	{
        if(date('Y-m-d',$timestamp) == date('Y-m-d',$current_time))
		{
            $output = '今天 ';
            $output.= date('H:i',$timestamp);
        }
		else
		{
            if(date('Y',$timestamp) == date('Y',$current_time))
			{
                $output = date('m月d日 H:i',$timestamp);
            }
			else
			{
                $output = date('Y年m月d日',$timestamp);
            }
        }
    }
	else
	{
        if(($output=floor($since/60)))
		{
            $output = $output.'分钟前';
        }
		else
		{
			$output = '1分钟内';
		}
    }
    return $output;
}

//前台取得地址表单
function phpok_address($format=false)
{
	$shipping = $GLOBALS['app']->site['biz_shipping'];
	$billing = $GLOBALS['app']->site['biz_billing'];
	if(!$shipping && !$billing) return false;
	$rs = array();
	if($shipping)
	{
		$flist = $GLOBALS['app']->call->phpok('_fields',array("pid"=>$shipping,'fields_format'=>$format,'prefix'=>"s_"));
		$rs['shipping'] = $flist;
	}
	if($billing)
	{
		$flist = $GLOBALS['app']->call->phpok('_fields',array("pid"=>$billing,'fields_format'=>$format,'prefix'=>'b_'));
		$rs['billing'] = $flist;
	}
	return $rs;
}

//判断属性是否存在
function in_attr($str="",$info="")
{
	if(!$str || !$info) return false;
	$info = explode(",",$info);
	$str = explode(",",$str);
	$rs = array_intersect($str,$info);
	if($rs && count($rs)>0) return true;
	return false;
}

//读取会员信息
function phpok_user($id)
{
	return $GLOBALS['app']->model('user')->get_one($id);
}

//读取文本内容，并格式化文本内容
function phpok_txt($file,$pageid=0,$type='txt')
{
	if(!$file || !is_file($GLOBALS['app']->dir_root.$file))
	{
		return false;
	}
	$content = file_get_contents($GLOBALS['app']->dir_root.$file);
	if(!$content) return false;
	$rs = $GLOBALS['app']->model('data')->info_page($content,$pageid);
	if(!$rs) return false;
	if(is_string($rs))
	{
		$rs = $type == 'txt' ? nl2br($rs) : $rs;
		return array('content'=>$rs);
	}
	else
	{
		$rs['content'] = $type == 'txt' ? nl2br($rs['content']) : $rs;
		return $rs;
	}
}


