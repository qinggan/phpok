/**
 * 插件事件
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年5月12日
 **/

(function() {
	function myplugDialog(editor) {
		return {
			title: '文章列表', //窗口标题
			minWidth: 700,
			minHeight: 500,
			buttons: [
				CKEDITOR.dialog.okButton, //对话框底部的确定按钮
				CKEDITOR.dialog.cancelButton
			], //对话框底部的取消按钮
			contents: //每一个contents在对话框中都是一个tab页
				[{
				id: 'user', //contents的id
				label: 'You name',
				title: 'You name',
				elements: [{
					type: "html",
					html: "<iframe id='myiframe' width='100%' height='100%' src='"+editor.config.filebrowserArticlelist+"'></iframe>",
					style: "width:100%;height:500px;padding:0;margin:0;"
				}]
			}],
			onLoad: function() {
				//alert('onLoad');
			},
			onShow: function() {
				//alert('onShow');
			},
			onHide: function() {
				//alert('onHide');
			},
			onOk: function() {
				var iframe = document.getElementById('myiframe').contentWindow;
				var t = iframe.dialogOK();
				if(t){
					editor.insertHtml(t);
					this.commitContent();
					return true;
				}
				return false;
			},
			onCancel: function() {
				//alert('onCancel');
			},
			resizable: false
		};
	}
	CKEDITOR.dialog.add('myplugDialog', function(editor) {
		return myplugDialog(editor);
	});
})();