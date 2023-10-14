var list = [];
list[0] = [{
	'info':'如切如磋，如琢如磨',
	'url':'background/seasons/1.jpg'
},{
	'info':'四季轮转，云卷云舒',
	'url':'background/seasons/2.jpg'
},{
	'info':'大美中华，君之功也',
	'url':'background/seasons/3.jpg'
},{
	'info':'他山之石，可以攻玉',
	'url':'background/seasons/4.jpg'
}];
list[1] = [{
	'info':'中国，科技携手文艺',
	'url':'background/technology/1.jpg'
},{
	'info':'未来，从这里开始',
	'url':'background/technology/2.jpg'
},{
	'info':'智能 AI，引领 5G',
	'url':'background/technology/3.jpg'
},{
	'info':'他山之石，可以攻玉',
	'url':'background/technology/4.jpg'
}];

var mlist = {};
mlist['en_US'] = [];
mlist['en_US'][0] = [{
	'info':'Spring came, the earth covered with green.',
	'url':'background/seasons/1.jpg'
},{
	'info':'Seasons rotate, Clouds roll, Clouds relax',
	'url':'background/seasons/2.jpg'
},{
	'info':'The Great Beauty of China, Your Merit',
	'url':'background/seasons/3.jpg'
},{
	'info':'Meet a better version of yourself this winter.',
	'url':'background/seasons/4.jpg'
}];
mlist['en_US'][1] = [{
	'info':'China, Technology and Art',
	'url':'background/technology/1.jpg'
},{
	'info':'Future, starting from here',
	'url':'background/technology/2.jpg'
},{
	'info':'Intelligent AI, leading 5G',
	'url':'background/technology/3.jpg'
},{
	'info':'Interconnection, unlimited network',
	'url':'background/technology/4.jpg'
}];

mlist['zh_Big5'] = [];
mlist['zh_Big5'][0] = [{
	'info':'如切如磋，如琢如磨',
	'url':'background/seasons/1.jpg'
},{
	'info':'四季輪轉，雲捲雲舒',
	'url':'background/seasons/2.jpg'
},{
	'info':'大美中華，君之功也',
	'url':'background/seasons/3.jpg'
},{
	'info':'他山之石，可以攻玉',
	'url':'background/seasons/4.jpg'
}];
mlist['zh_Big5'][1] = [{
	'info':'中國，科技攜手文藝',
	'url':'background/technology/1.jpg'
},{
	'info':'未來，從這裡開始',
	'url':'background/technology/2.jpg'
},{
	'info':'智慧 AI，引領 5G',
	'url':'background/technology/3.jpg'
},{
	'info':'他山之石，可以攻玉',
	'url':'background/technology/4.jpg'
}];

var _args,_path = (function( script, i, me )
{
    var l = script.length;
	for( ; i < l; i++ ){
		me = !!document.querySelector ? script[i].src : script[i].getAttribute('src',4);
		if( me.substr(me.lastIndexOf('/')).indexOf('load.js') !== -1 ){
			break;
		}
	}
	
	me = me.split('?'); _args = me[1];
	return me[0].substr( 0, me[0].lastIndexOf('/') + 1 );
})(document.getElementsByTagName('script'),0);
if(!_args){
	_args = "host=//cdn.phpok.com/";
}
var tmp_args = decodeURIComponent(_args);
var tmp_list = tmp_args.split("&");
var host = (tmp_list[0].split("="))[1];
var langid = (tmp_list[1].split("="))[1];
if(mlist[langid] && mlist[langid] != 'undefined'){
	list = mlist[langid];
}

//生成从minNum到maxNum的随机数
function phpok_randomNum(minNum,maxNum){ 
    switch(arguments.length){ 
        case 1: 
            return parseInt(Math.random()*minNum+1,10); 
        break; 
        case 2: 
            return parseInt(Math.random()*(maxNum-minNum+1)+minNum,10); 
        break; 
            default: 
                return 0; 
            break; 
    } 
}

var max = list.length-1;
var n = phpok_randomNum(0,max);
var tmp = list[n];
var html = '<style type="text/css">'+"\n";
html += '.phpok-login-bg{position: fixed;width:100%;height:100%;top: 0;left:0;z-index:-1;display:none;}'+"\n";
html += '.phpok-login-bg .slick-list,.phpok-login-bg .slick-list .slick-track {height: 100%;}'+"\n";
html += '.phpok-login-bg .c-wrap,.phpok-login-bg .c-item .c-item {position: relative;top: 0;left:0;width: 100%;height: 100%;}'+"\n";
html += '.phpok-login-bg .c-wrap .c-item {position: relative;top: 0;left:0;height: 100%;width: 100%;transform:scale(1.1,1.1);-moz-transform:scale(1.1,1.1);-webkit-transform:scale(1.1,1.1);-o-transform:scale(1.1,1.1);-webkit-transition: all 3s;-moz-transition: all 3s;-o-transition: all 3s;transition: all 3s;background-size:cover;background-repeat:no-repeat;}'+"\n";
html += '.phpok-login-bg .c-wrap .c-textBox {position: absolute;top:50%;left:50%;padding: 0 50px;width: 100%;max-width: 1300px;font-weight: bold;font-size: 60px;line-height: 80px;text-align: left;color: #fff;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;-webkit-transform: translate(-50%,-50%);-moz-transform: translate(-50%,-50%);-o-transform: translate(-50%,-50%);transform: translate(-50%,-50%);}'+"\n";
html += '.phpok-login-bg .c-wrap .c-textBox .c-text {width: -moz-calc(100% - 420px);width: -webkit-calc(100% - 420px);width: calc(100% - 420px);text-shadow: 0 5px 5px rgba(0,0,0,.14);}'+"\n";
html += '.phpok-login-bg .c-wrap.slick-active .c-item{transform:scale(1,1);-moz-transform:scale(1,1);-webkit-transform:scale(1,1);-o-transform:scale(1,1);}'+"\n";
html += '</style>'+"\n";
html += '<div class="phpok-login-bg">'+"\n";
for(var i in tmp){
	html += '<div class="c-wrap"><div class="c-item"><img data-lazy="'+host+tmp[i].url+'" style="width:100%;height:100%;" /></div><div class="c-textBox"><p class="c-text">'+tmp[i].info+'</p></div></div>';
}
html += '</div>'+"\n";
$(document).ready(function(){
	$("body").append(html);
	var opt = {
		lazyLoad:"ondemand",
		dots: false,
		slidesToShow: 1,
		slidesToScroll: 1,
		fade:true,
		speed:1500,
		autoplay: true,
		arrows: false,
		autoplaySpeed: 3000
	}
	window.setTimeout(function(){
		$(".phpok-login-bg").show().slick(opt);
	}, 1000);
	if(langid == 'en_US'){
		$("#c-content .c-wrap").css("left","60%");
	}
});
