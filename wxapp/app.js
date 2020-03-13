//app.js
var kunwu = require('phpok.js');
var md5 = require('md5.js');

App({
	okConfig:{
		'host':'https://wxapp.phpok.com/',
		'url':'https://wxapp.phpok.com/api.php',
		//安全码，和后台要一致
		'safecode': '71c6he!!54*$c27@',
		'site_id':1,
		//默认页签调用的参数
		'params':{
			'list':'product',
			'article':'news',
			'about':'aboutus',
			'contact':'contactus'
		},
		'wxconfig':null,
		'ctrl_id':'c',
		'func_id':'f',
		'status':false
	},

	/**
	 * 返回首页
	 * @参数 close 是否关闭所有页面
	**/
	tohome:function(close)
	{
		if(close && close != 'undefined'){
			wx.reLaunch({
				url: '../index/index'
			})
		}else{
			wx.switchTab({
				url: '../index/index'
			})
		}
	},

	/**
	 * 应用页面跳转
	 * @参数 url 要跳转的页面，留空跳转首页
	 * @参数 close 是否关闭当前页面
	**/
	tourl:function(url,close)
	{
		if(typeof url == "undefined"){
			url = '../index/index';
		}
		if(typeof url == 'boolean'){
			close = url;
			url = '../index/index';
		}
		if(url == '../index/index'){
			return this.tohome(close);
		}
		if(close && close != 'undefined'){
			wx.redirectTo({'url': url});
		}else{
			wx.navigateTo({'url': url});
		}
	},

	/**
	 * 跳转到 tabBar 页面
	 * @参数 url 
	 * @参数 
	**/
	totab:function(url,close)
	{
		if(typeof url == "undefined"){
			url = '../index/index';
		}
		if(typeof url == 'boolean'){
			close = url;
			url = '../index/index';
		}
		if(url == '../index/index'){
			return this.tohome(close);
		}
		if(close && close != 'undefined'){
			wx.reLaunch({'url': url});
		}else{
			wx.switchTab({'url': url});
		}
	},
	api_url:function(ctrl,func,ext)
	{
		var url = this.okConfig.url+"?siteId="+this.okConfig.site_id+"&"+this.okConfig.ctrl_id+"="+ctrl;
		if(func && func != 'undefined' && typeof(func) == 'string' && func.indexOf('=')<0){
			url += "&"+this.okConfig.func_id+'='+func;
		}
		if(func && func != 'undefined' && typeof(func) == 'string' && func.indexOf('=')>-1){
			url += "&"+func;
		}
		if(func && func != 'undefined' && typeof(func) == 'object'){
			for(var i in func){
				url += "&"+i+'='+func[i];
			}
		}
		if(ext && ext != 'undefined' && typeof(ext) == 'string' && ext.indexOf('=')>-1){
			url += "&"+ext;
		}
		if(ext && ext != 'undefined' && typeof(ext) == 'object'){
			for(var i in ext){
				url += "&"+i+'='+ext[i];
			}
		}
		return url;
	},
	
	api_plugin_url:function(id,exec,ext)
	{
		var extlink = 'id='+id;
		if(exec && exec != 'undefined' && typeof(exec) == 'string' && exec.indexOf('=')<0){
			extlink += "&exec="+exec;
		}else{
			extlink += '&exec=index';
		}
		if(ext && ext != 'undefined' && typeof(ext) == 'string' && func.indexOf('=')>-1){
			extlink += "&"+ext;
		}
		if(ext && ext != 'undefined' && typeof(ext) == 'object'){
			for(var i in ext){
				extlink += "&"+i+'='+ext[i];
			}
		}
		return this.api_url('plugin','exec',extlink);
	},
	
	json:function(url,obj,post_data)
	{
		if(!url){
			return false;
		}
		var header_obj = {
			'content-type':'application/json'
		}
		var session_name = kunwu.cookie.get('session_name');
		var session_val = kunwu.cookie.get('session_val');
		if(session_name && session_name != 'undefined' && session_val && session_val != 'undefined'){
			header_obj[session_name] = session_val;
		}
		var method_type = 'GET';
		if(post_data && post_data != 'undefined'){
			method_type = 'POST';
			header_obj['content-type'] = 'application/x-www-form-urlencoded';
		}else{
			post_data = {};
		}
		var tmp = [];
		var num = 0;
		var t = url.split("?");
		if (t[1]) {
			var t2 = (t[1]).split("&");
			for (var i in t2) {
				var t3 = (t2[i]).split("=");
				if ((t3[0]).indexOf('[') < 0) {
					tmp[num] = t3[0];
					num++;
				}
			}
		}
		if (post_data && typeof post_data != 'boolean') {
			for (var i in post_data) {
				tmp[num] = i;
				num++;
			}
		}
		tmp = tmp.sort();
		//---生成加密串
		var _safecode = this.okConfig.safecode;
		for (var i in tmp) {
			_safecode += "," + tmp[i];
		}
		if (url.indexOf('?') > -1) {
			url += "&_safecode=" + md5(_safecode);
		} else {
			url += "?_safecode=" + md5(_safecode);
		}
		wx.request({
			'url':url,
			'header':header_obj,
			'method':method_type,
			'data':post_data,
			'success':function(rs){
				(obj)(rs.data);
			}
		});
	},
	
	phpok:function(name,obj)
	{
		if(!name || name == 'undefined'){
			return false;
		}
		if(name && name != 'undefined' && typeof(name) == 'object'){
			name = JSON.stringify(name);
		}
		var url = this.api_url('call','index','data='+encodeURIComponent(name));
		this.json(url,obj);
	},

	clear_html:function(info)
	{
		if(!info || info == 'undefined'){
			return false;
		}
		info = info.replace(/<\/?[^>]*>/g,''); //去除HTML tag
	},
	
    //启动时操作
    onLaunch: function() {
		this.load_siteconfig();
    },
    //从后台进入前台执行动作
    onShow: function() {
        //this.load_siteconfig();
    },

    //从前台进入后台执行
    onHide: function() {
        //
    },
    onError: function(error) {
        kunwu.dialog.alert('错误：' + error);
    },

    onPageNotFound: function(obj) {
        wx.redirectTo({
            url: 'pages/error/error'
        });
    },

    getUserInfo: function(cb) {
        var that = this
        if (this.globalData.userInfo) {
            typeof cb == "function" && cb(this.globalData.userInfo)
        } else {
            wx.getUserInfo({
                withCredentials: false,
                success: function(res) {
                    that.globalData.userInfo = res.userInfo
                    typeof cb == "function" && cb(that.globalData.userInfo)
                }
            })
        }
    },

    globalData: {
        userInfo: null
    },

	load_siteconfig:function(){
		if(this.okConfig.status && this.okConfig.wxconfig){
			this.load_wxconfig();
			return true;
		}
		var that = this;
		this.json(that.okConfig.url+"?siteId="+that.okConfig.site_id+"&wxAppConfig=1", function (rs) {
			if (!rs.status) {
				kunwu.dialog.tips(rs.info);
				return false;
			}
			that.okConfig.ctrl_id = rs.info.ctrl_id;
			that.okConfig.func_id = rs.info.func_id;
			kunwu.cookie.set('session_name', rs.info.session_name);
			kunwu.cookie.set('session_val', rs.info.session_val);
			//初始化全局信息操作
			that.okConfig.wxconfig = rs.info.wxconfig;
			that.okConfig.status = true;
			that.load_wxconfig();
		});
	},

	//任意页面都要执行一次
	load_wxconfig:function()
	{
		if(!this.okConfig.wxconfig){
			wx.showTabBar();
			return false;
		}
		var config = this.okConfig.wxconfig;
		if(config.title){
			wx.setNavigationBarTitle({'title':config.title});
		}
		var params = {};
		if(config.rslist){
			for(var i in config.rslist){
				var obj = {'index':parseInt(i)};
				if(config.rslist[i].title){
					obj.text = config.rslist[i].title;
				}
				if(config.rslist[i].thumb && (config.rslist[i].thumb).substr(0,6) != 'images'){
					if((config.rslist[i].thumb).substr(0,7) == 'http://' || (config.rslist[i].thumb).substr(0,8) == 'https://'){
						obj.iconPath = config.rslist[i].thumb;
					}else{
						obj.iconPath = this.okConfig.host + config.rslist[i].thumb;
					}
				}
				if(config.rslist[i].thumb_selected && (config.rslist[i].thumb_selected).substr(0,6) != 'images'){
					if((config.rslist[i].thumb_selected).substr(0,7) == 'http://' || (config.rslist[i].thumb_selected).substr(0,8) == 'https://'){
						obj.selectedIconPath = config.rslist[i].thumb_selected;
					}else{
						obj.selectedIconPath = this.okConfig.host + config.rslist[i].thumb_selected;
					}
				}
				if(obj.text || obj.iconPath || obj.selectedIconPath){
					wx.setTabBarItem(obj);
				}
				if(i > 0 && config.rslist[i].page && config.rslist[i].param){
					if(config.rslist[i].page == 'pages/list/index'){
						this.okConfig.params.list = config.rslist[i].param;
					}
					if(config.rslist[i].page == 'pages/article/index'){
						this.okConfig.params.article = config.rslist[i].param;
					}
					if(config.rslist[i].page == 'pages/about/index'){
						this.okConfig.params.about = config.rslist[i].param;
					}
					if(config.rslist[i].page == 'pages/contact/index'){
						this.okConfig.params.contact = config.rslist[i].param;
					}
				}
			}
		}
		if(config.top_bgcolor || config.top_txtcolor){
			var font_color = config.top_txtcolor == 'black' ? '#000000' : '#ffffff';
			var obj = {'frontColor':font_color};
			if(config.top_bgcolor){
				obj.backgroundColor = config.top_bgcolor;
			}
			obj.animation = {
				'duration':400,
				'timingFunc':'easeIn'
			}
			wx.setNavigationBarColor(obj);
		}
		if(config.tab_bgcolor || config.tab_bordercolor || config.text_color || config.text_color_highlight){
			var obj = {};
			if(config.tab_bgcolor){
				obj.backgroundColor = config.tab_bgcolor;
			}
			if(config.tab_bordercolor){
				obj.borderStyle = config.tab_bordercolor;
			}
			if(config.text_color){
				obj.color = config.text_color;
			}
			if(config.text_color_highlight){
				obj.selectedColor = config.text_color_highlight;
			}
			wx.setTabBarStyle(obj);
		}
	}
});


