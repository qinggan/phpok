/**
 * 插件事件
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年5月12日
 **/

(function() {
	function myplugDialogImages(editor) {
		var w = 600;
		if(window.innerWidth){
			w = window.innerWidth*0.7
		}else if(window.screen.availWidth){
			w = window.screen.availWidth*0.7;
		}else if(document.body.clientWidth){
			w = document.body.clientWidth*0.7;
		}
		var h = 400;
		if(window.innerHeight){
			h = window.innerHeight*0.7;
		}else if(window.screen.availHeight){
			h = window.screen.availHeight*0.7;
		}else if(document.body.clientHeight){
			h = document.body.clientHeight*0.7;
		}
		return {
			title: '图片库', //窗口标题
			minWidth: w,
			minHeight: h,
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
					html: "<iframe id='myiframeImages' width='100%' height='100%' src='"+editor.config.filebrowserImageBrowseUrl+"#"+Math.random()+"'></iframe>",
					style: "width:100%;height:"+h+"px;padding:0;margin:0;"
				}]
			}],
			onLoad: function() {
				//alert('onLoad');
			},
			onShow: function() {
				//document.getElementById('myiframe3').contentWindow.location.reload(true);
				//alert('onShow');
			},
			onHide: function() {
				//destroyIframe('myiframeImages');
				//document.getElementById('myiframeImages').contentWindow.location.reload(true);
				//alert('onHide');
			},
			onOk: function() {
				var iframe = document.getElementById('myiframeImages').contentWindow;
				var t = iframe.dialogOK();
				if(t){
					editor.insertHtml(t);
					this.commitContent();
					//destroyIframe('myiframeImages');
					return true;
				}
				return false;
			},
			onCancel: function() {
				//destroyIframe('myiframeImages');
				//alert('onCancel');
			},
			resizable: false
		};
	}
	CKEDITOR.dialog.add('myplugDialogImages', function(editor) {
		return myplugDialogImages(editor);
	});
})();