/**
 * 一键排版功能，可以将Br标签转成P标签，清除无内容的行信息
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年11月7日
**/

UE.registerUI('quickformat',function(editor,uiName){
    editor.registerCommand(uiName,{
        execCommand:function(){
	        content = editor.getContent();
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
			content = content.replace(/　/g,'');
			content = content.replace(/<table([^>]*?)>/g,'<table style="width:100%"$1>');
			var list = content.split('<[PHPOKBR]>');
			var tmp = '';
			for(var i in list){
				if(list[i] && UE.utils.trim(list[i]).length>0){
					if((list[i]).indexOf('</h1>') > -1){
						tmp += '<h1>'+UE.utils.trim(list[i]);
					}else if((list[i]).indexOf('</h2>') > -1){
						tmp += '<h2>'+UE.utils.trim(list[i]);
					}else if((list[i]).indexOf('</h3>') > -1){
						tmp += '<h3>'+UE.utils.trim(list[i]);
					}else if((list[i]).indexOf('</h4>') > -1){
						tmp += '<h4>'+UE.utils.trim(list[i]);
					}else if((list[i]).indexOf('</h5>') > -1){
						tmp += '<h5>'+UE.utils.trim(list[i]);
					}else if((list[i]).indexOf('</h6>') > -1){
						tmp += '<h6>'+UE.utils.trim(list[i]);
					}else if((list[i]).indexOf('<table>') > -1 || (list[i]).indexOf('<pre>') > -1){
						tmp += UE.utils.trim(list[i]);
					}else{
						tmp += '<p>'+UE.utils.trim(list[i])+'</p>';
					}
				}
			}
			editor.setContent(tmp);
			return true;
        }
    });

    //创建一个button
    var btn = new UE.ui.Button({
        //按钮的名字
        name:'一键排版',
        //提示
        title:'排版整个编辑框内容，去除多余空格，BR标签变成P标签',
        //需要添加的额外样式，指定icon图标，这里默认使用一个重复的icon
        cssRules :'background-position: -640px -40px;',
        //点击时执行的命令
        onclick:function () {
            //这里可以不用执行命令,做你自己的操作也可
           editor.execCommand(uiName);
        }
    });

    //因为你是添加button,所以需要返回这个button
    return btn;
},2);