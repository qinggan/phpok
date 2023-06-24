/**
 * 选中字符高亮
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年6月7日
**/
(function() {
	//Section 1 : 按下自定义按钮时执行的代码
	var a = {
		exec: function(editor) {
			var mySelection = editor.getSelection();
			if(!mySelection){
				return false;
			}
			var selectedText;

			//Handle for the old Internet Explorer browser
			if (mySelection.getType() == CKEDITOR.SELECTION_TEXT) {
				if (CKEDITOR.env.ie) {
					mySelection.unlock(true);
					selectedText = mySelection.getNative().createRange().text;
				} else {
					selectedText = mySelection.getNative();
				}
			}
			var plainSelectedText = selectedText.toString();
			if(plainSelectedText != ''){
				var insertedElement = editor.document.createElement('kbd');
				insertedElement.appendText(plainSelectedText);
				editor.insertElement(insertedElement);
			}
			return true
		}
	},
	b = 'kbd';
	//Section 2 : 创建自定义按钮、绑定方法
	CKEDITOR.plugins.add(b, {
		init: function(editor) {
			//注意myplug名字 和 图片路径
			editor.addCommand(b, a)
			editor.ui.addButton(b, {
				label: '文本高亮',
				icon: this.path + 'images/mark.png',
				command: b
			});
		}
	});
})();

