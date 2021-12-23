/**
 * 批量上传
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年5月24日
**/
(function() {
	//Section 1 : 按下自定义按钮时执行的代码
	var a = {
			exec: function(editor) {
				alert("This a custome button!");
			}
		},
		//Section 2 : 创建自定义按钮、绑定方法
		b = 'images'; //注意myplug名字
	CKEDITOR.plugins.add(b, {
		init: function(editor) {
			CKEDITOR.dialog.add('myplugDialogImages', this.path + 'dialogs/images.js'); //注意myplug名字
			editor.addCommand(b, new CKEDITOR.dialogCommand('myplugDialogImages')); //注意myplug名字
			//注意myplug名字 和 图片路径
			editor.ui.addButton(b, {
				label: '图片库',
				icon: this.path + 'images/images.png',
				command: b
			});
		}
	});
})();

