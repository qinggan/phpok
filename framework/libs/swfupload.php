<?php
/***********************************************************
	Filename: {phpok}/libs/swfupload.php
	Note	: SWFupload附件上传
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-30 21:20
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class swfupload_lib
{
	var $btn = "";
	var $html = "";
	var $show_cancel = false; //显示取消上传按钮
	var $swfupload_url;
	var $sess_id = "PHPSESSION";
	var $sess_value = "";
	# 上传文件的类型
	var $file_type = "*.*";
	var $file_desc = "文件";

	function __construct()
	{
		//
	}

	# 加载JS
	function load_js()
	{
		$this->html.= '<script type="text/javascript" src="js/swfupload/swfupload.js"></script>';
		$this->html.= '<script type="text/javascript" src="js/swfupload/swfupload.queue.js"></script>';
		$this->html.= '<script type="text/javascript" src="js/swfupload/fileprogress.js"></script>';
		$this->html.= '<script type="text/javascript" src="js/swfupload/handlers.js"></script>';
	}

	# 显示取消按钮
	function show_cancel()
	{
		$this->show_cancel = true;
	}

	# 指定上传服务端网址
	function swfupload_url($url)
	{
		$this->swfupload_url = $ur;
	}

	# 指定SESSION及session_id
	function session($session_id,$session_value)
	{
		$this->sess_id = $session_id;
		$this->sess_value = $session_value;
	}

	# 指定上传文件类型
	function set_type($types="*.*",$type_desc="文件")
	{
		$this->file_type = $types;
		$this->file_desc = $type_desc;
	}

	# id 目标存储ID
	# js 上传成功后执行的JS
	function button($id,$js="")
	{
		# 如果缺少上传程序，返回空
		if(!$this->swfupload_url) return false;
		$html  = "";
		$html .= '<table><tr>';
		$html .= '<td><div id="'.$id.'_spanButtonPlaceHolder"></div></td>';
		if($this->show_cancel)
		{
			$html .= '<td>';
			$html .= '<input id="'.$id.'_btnCancel" type="button" class="btn" value="取消上传" ';
			$html .= ' onclick="'.$id.'_swfu.cancelQueue();" disabled /></td>';
		}
		$html .= '</tr>';
		# 显示上传进程
		$html .= '<tr><td colspan="2"><div id="'.$id.'_progress"></div></td></tr>';
		$html .= '</table>';
		$html .= "\n";
		$html .= '<script type="text/javascript">';
		$html .= 'function phpok_swfupload_success_'.$id.'(file,serverData,responseReceived)'."\n";
		$html .= '{'."\n";
		$html .= '	var rs = $.parseJSON(serverData);'."\n";
		if($js)
		{
			$html .= '	'.$js.'(rs);'."\n";
		}
		else
		{
			$html .= '	$("#'.$id.'").val(rs.filename);'."\n";
		}
		$html .= '}'."\n";
		$html .= 'function phpok_swfupload_upload_complete_'.$id.'(file)'."\n";
		$html .= '{'."\n";
		$html .= '	if (this.getStats().files_queued === 0) {'."\n";
		$html .= '		document.getElementById(this.customSettings.cancelButtonId).disabled = true;'."\n";
		$html .= '		$("#'.$id.'_progress").html("");'."\n";
		$html .= '	}'."\n";
		$html .= '}'."\n";
		$html .= 'var settings_'.$id.' = {'."\n";
		$html .= 'flash_url : "js/swfupload/swfupload.swf",'."\n";
		$html.= 'upload_url: "'.$this->swfupload_url.'",'."\n";
		if($this->sess_id && $this->sess_value)
		{
			$html .= 'post_params: {"'.$this->sess_id.'" : "'.$this->sess_value.'"},'."\n";
		}
		$html .= 'file_size_limit : "200 MB",'."\n";
		$html .= 'file_types : "'.$this->file_type.'",'."\n";
		$html .= 'file_types_description : "'.$this->file_desc.'",'."\n";
		$html .= 'file_upload_limit : 2000,'."\n";
		$html .= 'file_queue_limit : 0,'."\n";
		$html .= 'button_window_mode: "transparent",'."\n";
		$html .= 'custom_settings : {'."\n";
		$html .= '	progressTarget : "'.$id.'_progress",'."\n";
		$html .= '	cancelButtonId : "'.$id.'_btnCancel"'."\n";
		$html .= '},'."\n";
		$html .= 'debug: true,'."\n";
		$html .= 'button_image_url: "images/swfupload.png",'."\n";
		$html .= 'button_placeholder_id : "'.$input_id.'_spanButtonPlaceHolder",'."\n";
		$html .= 'button_width: "92",'."\n";
		$html .= 'button_height: "23",'."\n";
		$html .= 'file_queued_handler : fileQueued,'."\n";
		$html .= 'file_queue_error_handler : fileQueueError,'."\n";
		$html .= 'file_dialog_complete_handler : fileDialogComplete,'."\n";
		$html .= 'upload_start_handler : uploadStart,'."\n";
		$html .= 'upload_progress_handler : uploadProgress,'."\n";
		$html .= 'upload_error_handler : uploadError,'."\n";
		$html .= 'upload_success_handler : upload_success_phpok_'.$input_id.','."\n";
		$html .= 'upload_complete_handler : upload_complete_phpok_'.$input_id.','."\n";
		$html .= 'queue_complete_handler : queueComplete'."\n";
		$html .= '};'."\n";
		$html .= $id.'_swfu = new SWFUpload(settings_'.$input_id.');'."\n";
		$html .= '</script>'."\n";
	}
}
?>