/**
 * @license Copyright (c) 2003-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	//工具条全部内容
	config.toolbar = [
		{ name: 'document', items: [ 'Source', '-', 'Save', 'NewPage', 'ExportPdf', 'Preview', 'Print', '-', 'Templates' ] },
		{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
		{ name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
		{ name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
		'/',
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat','quickformat' ,'kbd'] },
		{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
		{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
		{ name: 'insert', items: [ 'Image','images','Html5video','Html5audio', 'BaiduMap' ,'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe','Syntaxhighlight','arclist','filelist' ] },
		'/',
		{ name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
		{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
		{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
		{ name: 'about', items: [ 'About' ] }
	];

	//去除系统自带的 exportpdf
	config.removePlugins = 'easyimage, cloudservices, exportpdf';

	//纯文本粘贴，建议禁用
	config.forcePasteAsPlainText = false;

	//禁止隐藏工具条
	config.toolbarCanCollapse = false;
	//工具条对应的组信息
	config.toolbarGroups = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'basicstyles', groups: [ 'cleanup', 'basicstyles' ] },
		{ name: 'paragraph', groups: [ 'align', 'list', 'indent', 'blocks', 'bidi', 'paragraph' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'about', groups: [ 'about' ] }
	];

	config.removeButtons = 'ExportPdf,Print,NewPage,Save,Templates,SelectAll,Scayt,Form,Radio,Checkbox,TextField,Textarea,Select,Button,ImageButton,HiddenField,CreateDiv,Language,Find,Replace,Flash,BidiLtr,BidiRtl';

	//自定义表情包
	config.smiley_path = "images/emotion/";
	config.smiley_images = ["01.png","02.png","03.png","04.png","05.png","06.png","07.png","08.png","09.png","10.png","11.png","12.png","13.png","14.png","15.png","16.png","17.png","18.png","19.png","20.png","21.png","22.png","23.png","24.png","25.png","26.png","27.png","28.png","29.png","30.png","31.png","32.png","33.png","34.png","35.png","36.png","37.png","38.png","39.png","40.png","41.png","42.png","43.png","44.png","45.png","46.png","47.png","48.png","49.png","50.png","51.png","52.png","53.png","54.png","55.png","56.png","57.png","58.png","59.png","60.png","61.png","62.png","63.png","64.png"];
	//表情包释译，即鼠标移过去的提示，请注意顺序
	config.smiley_descriptions = [];

	//是否自动设置
	config.resize_enabled = false;

	config.coreStyles_bold = { element : 'b'};

	//常用字体
	config.font_names =
		'微软雅黑/微软雅黑,Microsoft YaHei,华文细黑,Arial,Helvetica,Sans-serif;' +
		'黑体/黑体,SimHei,Tahoma,Lucida Family, Sans-serif;' +
		'宋体/宋体,SimSun,Sans-serif;' +
		'Arial/arial,helvetica,sans-serif;' +
		'Arial Black/arial black,avant garde;' +
		'Comic Sans Ms/comic sans ms;' +
		'Times New Roman/times new roman;';

	//字号设置
	config.fontSize_sizes = '12/12px;14/14px;16/16px;18/18px;20/20px;24/24px;36/36px;48/48px;';

	//扩展插件
	config.extraPlugins = "html5video,html5audio,autogrow,syntaxhighlight,baidumap,fixed,arclist,filelist,images,quickformat,pasteUploadImage,kbd,tableresize";
	
	config.image_previewText = "专注企业 互联网+ 发展，将互联网思维植入传统企业，帮 助企业搭建属于自己的互联网经营平台，引领商业新生态 的变革，帮助企业做大做强。我们为每个客户提供专业化 和定制化的互联网+方案，坚持以客户需求为导向，竭力 为客户带来最大的商业价值。相信我们是您值得信赖的合作伙伴！";
	config.image_prefillDimensions  = false;

	//远程本地化参数
	config.imgToLocalRemoteDomain = '*';
	//忽略的图片本地化的域名
	config.imgToLocalIgnoreDomain = 'localhost';
	//加载远程图片
	config.imgToLocalUpload = '';

	config.allowedContent=true;

	//弹出窗口默认宽度
	config.openMinWidth = 800;

	//自动高度
	config.autoGrow_onStartup = true;
	config.autoGrow_bottomSpace = 30;
	config.autoGrow_minHeight = 200;
	//config.autoGrow_maxHeight = 1600;
};
