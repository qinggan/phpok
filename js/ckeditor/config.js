/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	config.toolbar_Basic = [
    	['Source','Preview','RemoveFormat','Image','-','Bold', 'Italic', 'Underline','Strike','-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'About']
    ];

    //
	config.removeDialogTabs = 'link:advanced';

	//读言包
	config.language = 'zh-cn';
	
	//背景颜色
	//config.uiColor = '#fff';
	
	//高度
	config.height = 300;
	
	//工具栏（基础'Basic'、全能'Full'、自定义）plugins/toolbar/plugin.js
    config.toolbar = 'Full';
    
    //工具栏是否可以被收缩
	config.toolbarCanCollapse = false;
	
	config.image_previewText = 'PHPOK企业站系统（下述称“系统”或“本系统”）是一套使用PHP语言及MySQL数据库编写的企业网站建设系统，基于LGPL协议开源授权！';
	
	config.resize_enabled = false;
	config.resize_maxHeight = 3000;
	config.toolbarStartupExpanded = true;
	
	//当提交包含有此编辑器的表单时，是否自动更新元素内的数据
	config.autoUpdateElement =true;
	
	// 设置是使用绝对目录还是相对目录，为空为相对目录
	config.baseHref = '';

	// 编辑器的z-index值
	config.baseFloatZIndex = 900;

	//设置快捷键
    config.keystrokes = [
    	[CKEDITOR.ALT + 121 /*F10*/ , 'toolbarFocus'], //获取焦点
    	[CKEDITOR.ALT + 122 /*F11*/ , 'elementsPathFocus'], //元素焦点

    	[CKEDITOR.SHIFT + 121 /*F10*/ , 'contextMenu'], //文本菜单

    	[CKEDITOR.CTRL + 90 /*Z*/ , 'undo'], //撤销
    	[CKEDITOR.CTRL + 89 /*Y*/ , 'redo'], //重做
    	[CKEDITOR.CTRL + CKEDITOR.SHIFT + 90 /*Z*/ , 'redo'], //

    	[CKEDITOR.CTRL + 76 /*L*/ , 'link'], //链接

    	[CKEDITOR.CTRL + 66 /*B*/ , 'bold'], //粗体
    	[CKEDITOR.CTRL + 73 /*I*/ , 'italic'], //斜体
    	[CKEDITOR.CTRL + 85 /*U*/ , 'underline'], //下划线

    	[CKEDITOR.ALT + 109 /*-*/ , 'toolbarCollapse']
    ];

    //设置快捷键 可能与浏览器快捷键冲突plugins/keystrokes/plugin.js.
    config.blockedKeystrokes = [
    	CKEDITOR.CTRL + 66 /*B*/ ,
    	CKEDITOR.CTRL + 73 /*I*/ ,
    	CKEDITOR.CTRL + 85 /*U*/
    ];

    //设置编辑内元素的背景色的取值
    config.colorButton_backStyle = {
    	element: 'span',
    	styles: {
    		'background-color': '#(color)'
    	}
    };

    //设置前景色的取值 
    config.colorButton_colors = '000,800000,8B4513,2F4F4F,008080,000080,4B0082,696969,B22222,A52A2A,DAA520,006400,40E0D0,0000CD,800080,808080,F00,FF8C00,FFD700,008000,0FF,00F,EE82EE,A9A9A9,FFA07A,FFA500,FFFF00,00FF00,AFEEEE,ADD8E6,DDA0DD,D3D3D3,FFF0F5,FAEBD7,FFFFE0,F0FFF0,F0FFFF,F0F8FF,E6E6FA,FFF';

    //是否在选择颜色时显示“其它颜色”选项plugins/colorbutton/plugin.js
    config.colorButton_enableMore =false;

    //区块的前景色默认值设置 plugins/colorbutton/plugin.js
    config.colorButton_foreStyle = {
        element : 'span',
       styles : { 'color' : '#(color)' }
    };

	//所需要添加的CSS文件 在此添加 可使用相对路径和网站的绝对路径
    config.contentsCss = './js/ckeditor/contents.css';

	//文字方向
    config.contentsLangDirection ='ltr'; //从左到右

    //是否拒绝本地拼写检查和提示 默认为拒绝 目前仅firefox和safari支持plugins/wysiwygarea/plugin.js.
    config.disableNativeSpellChecker =true;

    //进行表格编辑功能 如：添加行或列 目前仅firefox支持plugins/wysiwygarea/plugin.js
    config.disableNativeTableHandles =true; //默认为不开启

    //是否开启 图片和表格 的改变大小的功能config.disableObjectResizing = true;
    config.disableObjectResizing= false //默认为开启

    //是否对编辑区域进行渲染plugins/editingblock/plugin.js
    config.editingBlock = true;

    //编辑器中回车产生的标签
    config.enterMode =CKEDITOR.ENTER_P; //可选：CKEDITOR.ENTER_BR或CKEDITOR.ENTER_DIV

    //是否转换一些难以显示的字符为相应的HTML字符plugins/entities/plugin.js
    config.entities_greek = true;

    //是否转换一些拉丁字符为HTMLplugins/entities/plugin.js
    config.entities_latin = true;

    //是否转换一些特殊字符为ASCII字符 如"This is Chinese:汉语."转换为"This is Chinese: 汉语."plugins/entities/plugin.js
    config.entities_processNumerical =false;

//默认的字体名 plugins/font/plugin.js
    config.font_defaultLabel = 'Arial';

    //字体编辑时的字符集 可以添加常用的中文字符：宋体、楷体、黑体等plugins/font/plugin.js
    config.font_names = 'Arial;Times NewRoman;Verdana';

    //文字的默认式样 plugins/font/plugin.js
    config.font_style = {
        element   : 'span',
        styles  : { 'font-family' : '#(family)' },
        overrides : [ { element :'font', attributes : { 'face' : null } } ]
    };

    //字体默认大小 plugins/font/plugin.js
    config.fontSize_defaultLabel = '14px';

    //字体编辑时可选的字体大小 plugins/font/plugin.js
    config.fontSize_sizes='10/10px;11/11px;12/12px;14/14px;16/16px;18/18px;20/20px;22/22px;24/24px;26/26px;28/28px;36/36px;48/48px;72/72px';

    //设置字体大小时 使用的式样 plugins/font/plugin.js
    config.fontSize_style = {
        element   : 'span',
       styles   : { 'font-size' : '#(size)' },
        overrides : [ {element : 'font', attributes : { 'size' : null } } ]
    };

    //是否强制复制来的内容去除格式plugins/pastetext/plugin.js
    config.forcePasteAsPlainText =false//不去除

   //是否强制用“&”来代替“&amp;”plugins/htmldataprocessor/plugin.js
    config.forceSimpleAmpersand = false;

    //对address标签进行格式化 plugins/format/plugin.js
    config.format_address = { element : 'address', attributes : { class :'styledAddress' } };

    //对DIV标签自动进行格式化 plugins/format/plugin.js
    config.format_div = { element : 'div', attributes : { class :'normalDiv' } };

    //对H1标签自动进行格式化 plugins/format/plugin.js
    config.format_h1 = { element : 'h1', attributes : { class :'contentTitle1' } };

    //对H2标签自动进行格式化 plugins/format/plugin.js
    config.format_h2 = { element : 'h2', attributes : { class :'contentTitle2' } };

    //对H3标签自动进行格式化 plugins/format/plugin.js
    config.format_h1 = { element : 'h3', attributes : { class :'contentTitle3' } };

    //对H4标签自动进行格式化 plugins/format/plugin.js
    config.format_h1 = { element : 'h4', attributes : { class :'contentTitle4' } };

    //对H5标签自动进行格式化 plugins/format/plugin.js
    config.format_h1 = { element : 'h5', attributes : { class :'contentTitle5' } };

    //对H6标签自动进行格式化 plugins/format/plugin.js
    config.format_h1 = { element : 'h6', attributes : { class :'contentTitle6' } };

    //对P标签自动进行格式化 plugins/format/plugin.js
    config.format_p = { element : 'p', attributes : { class : 'normalPara' }};

    //对PRE标签自动进行格式化 plugins/format/plugin.js
    config.format_pre = { element : 'pre', attributes : { class : 'code'} };

    //用分号分隔的标签名字 在工具栏上显示plugins/format/plugin.js
    config.format_tags ='p;h1;h2;h3;h4;h5;h6;pre;address;div';

    //是否使用完整的html编辑模式如使用，其源码将包含：<html><body></body></html>等标签
    config.fullPage = false;

    //是否忽略段落中的空字符 若不忽略 则字符将以表示plugins/wysiwygarea/plugin.js
    config.ignoreEmptyParagraph = true;

    //在清除图片属性框中的链接属性时 是否同时清除两边的<a>标签plugins/image/plugin.js
    config.image_removeLinkByEmptyURL = true;

    //一组用逗号分隔的标签名称，显示在左下角的层次嵌套中plugins/menu/plugin.js.
    config.menu_groups='clipboard,form,tablecell,tablecellproperties,tablerow,tablecolumn,table,anchor,link,image,flash,checkbox,radio,textfield,hiddenfield,imagebutton,button,select,textarea';

    //显示子菜单时的延迟，单位：ms plugins/menu/plugin.js
    config.menu_subMenuDelay = 400;

    //当执行“新建”命令时，编辑器中的内容plugins/newpage/plugin.js
    config.newpage_html = '';

    //当从word里复制文字进来时，是否进行文字的格式化去除plugins/pastefromword/plugin.js
    config.pasteFromWordIgnoreFontFace = true; //默认为忽略格式

    //是否使用<h1><h2>等标签修饰或者代替从word文档中粘贴过来的内容plugins/pastefromword/plugin.js
    config.pasteFromWordKeepsStructure = false;

    //从word中粘贴内容时是否移除格式plugins/pastefromword/plugin.js
    config.pasteFromWordRemoveStyle =false;

    //当输入：shift+Enter时插入的标签
    config.shiftEnterMode = CKEDITOR.ENTER_P; //可选：CKEDITOR.ENTER_BR或CKEDITOR.ENTER_DIV
    
	//页面载入时，编辑框是否立即获得焦点plugins/editingblock/plugin.js plugins/editingblock/plugin.js.
    config.startupFocus = false;

    //载入时，以何种方式编辑 源码和所见即所得 "source"和"wysiwyg"plugins/editingblock/plugin.js.
    config.startupMode ='wysiwyg';

    //载入时，是否显示框体的边框plugins/showblocks/plugin.js
    config.startupOutlineBlocks = false;

	//撤销的记录步数
    config.undoStackSize =20;

    config.smiley_columns = 12;

    config.smiley_descriptions = [
    	'01.png','02.png','03.png','04.png','05.png','06.png','07.png','08.png','09.png','10.png','11.png','12.png','13.png','14.png','15.png','16.png','17.png','18.png','19.png','20.png','21.png','22.png','23.png','24.png','25.png','26.png','27.png','28.png','29.png','30.png','31.png','32.png','33.png','34.png','35.png','36.png','37.png','38.png','39.png','40.png','41.png','42.png','43.png','44.png','45.png','46.png','47.png','48.png','49.png','50.png','51.png','52.png','53.png','54.png','55.png','56.png','57.png','58.png','59.png','60.png','61.png','62.png','63.png','64.png'
    ];
    config.smiley_path = 'images/emotion/';
    config.smiley_images = [
        '01.png','02.png','03.png','04.png','05.png','06.png','07.png','08.png','09.png','10.png','11.png','12.png','13.png','14.png','15.png','16.png','17.png','18.png','19.png','20.png','21.png','22.png','23.png','24.png','25.png','26.png','27.png','28.png','29.png','30.png','31.png','32.png','33.png','34.png','35.png','36.png','37.png','38.png','39.png','40.png','41.png','42.png','43.png','44.png','45.png','46.png','47.png','48.png','49.png','50.png','51.png','52.png','53.png','54.png','55.png','56.png','57.png','58.png','59.png','60.png','61.png','62.png','63.png','64.png'
    ];

    config.extraAllowedContent = 'img[alt,!src,width,height,data-width,data-height]{border-style,border-width,float,height,margin,margin-bottom,margin-left,margin-right,margin-top,width}'
};
