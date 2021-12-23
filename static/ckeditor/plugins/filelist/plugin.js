/**
 * 文章列表插件
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年5月12日
**/
(function() {
	//Section 1 : 按下自定义按钮时执行的代码
	var a = {
			exec: function(editor) {
				alert("This a custome button!");
			}
		},
		//Section 2 : 创建自定义按钮、绑定方法
		b = 'filelist'; //注意myplug名字
	CKEDITOR.plugins.add(b, {
		init: function(editor) {
			CKEDITOR.dialog.add('myplugDialog2', this.path + 'dialogs/article.js'); //注意myplug名字
			editor.addCommand(b, new CKEDITOR.dialogCommand('myplugDialog2')); //注意myplug名字
			//注意myplug名字 和 图片路径
			editor.ui.addButton(b, {
				label: '附件',
				icon: this.path + 'images/file.png',
				command: b
			});
		}
	});
})();

