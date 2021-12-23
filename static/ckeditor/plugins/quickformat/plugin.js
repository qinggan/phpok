/**
 * 清理事件
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
			var content = editor.getData();
			if(!content){
				return false;
			}
	        content = content.replace(/<\/p>/ig,'<[PHPOKBR]>');
			content = content.replace(/<p>/ig,'');
			content = content.replace(/<p\t+[^>]*?>/ig,'');
			content = content.replace(/<h([1-6]{1})[^>]*?>/ig,'');
			content = content.replace(/<\/h([1-6]{1})>/ig,'</h\\1><[PHPOKBR]>');
			content = content.replace(/<br[^>]*?>/ig,'<[PHPOKBR]>');
			content = content.replace(/&nbsp;/g,' ');
			content = content.replace(/width=[\'|\"]{0,1}[0-9a-z\%\-]+[\'|\"]{0,1}/ig,'');
			content = content.replace(/<th([^>]*?)>(.*?)<\[PHPOKBR\]>(.*?)<\/th>/ig,"<th$1>$2$3</th>");
			content = content.replace(/<td([^>]*?)>(.*?)<\[PHPOKBR\]>(.*?)<\/td>/ig,"<td$1>$2$3</td>");
			content = content.replace(/<li([^>]*?)>(.*?)<\[PHPOKBR\]>(.*?)<\/li>/ig,"<li$1>$2$3</li>");
			content = content.replace(/<img([^>]*?)src=[\'|\"](.*?)[\'|\"]([^>]*?)>/ig,'<img src="$2" />'); //清理图片样式
			content = content.replace(/　/g,'');
			content = content.replace(/<table([^>]*?)>/g,'<table style="width:100%"$1>');
			var list = content.split('<[PHPOKBR]>');
			var tmp = '';
			for(var i in list){
				if(list[i] && list[i].length>0){
					if((list[i]).indexOf('</h1>') > -1){
						tmp += '<h1>'+list[i];
					}else if((list[i]).indexOf('</h2>') > -1){
						tmp += '<h2>'+list[i];
					}else if((list[i]).indexOf('</h3>') > -1){
						tmp += '<h3>'+list[i];
					}else if((list[i]).indexOf('</h4>') > -1){
						tmp += '<h4>'+list[i];
					}else if((list[i]).indexOf('</h5>') > -1){
						tmp += '<h5>'+list[i];
					}else if((list[i]).indexOf('</h6>') > -1){
						tmp += '<h6>'+list[i];
					}else if((list[i]).indexOf('<table>') > -1 || (list[i]).indexOf('<pre>') > -1){
						tmp += list[i];
					}else{
						tmp += '<p>'+list[i]+'</p>';
					}
				}
			}
			editor.setData(tmp);
			return true
		}
	},
	b = 'quickformat';
	//Section 2 : 创建自定义按钮、绑定方法
	CKEDITOR.plugins.add(b, {
		init: function(editor) {
			//注意myplug名字 和 图片路径
			editor.addCommand(b, a)
			editor.ui.addButton(b, {
				label: '段落格式化',
				icon: this.path + 'images/p.png',
				command: b
			});
		}
	});
})();

