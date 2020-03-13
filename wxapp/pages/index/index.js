//index.js
//获取应用实例
var app = getApp();
var kunwu = require("../../phpok.js");
var WxParse = require("../../wxParse/wxParse.js");

Page({
    data: {
		player_config:{
			indicatorDots: true, //显示圆点
			vertical: false, //设为true表示上下轮播，设为false则表示左右轮播
			autoplay:true, // 自动播放
			interval: 3000, // 等待时间，单位是毫秒
			duration: 800 //有效时间，单位是毫秒
		},
		banner_url: null,
		aboutus:{
			title:null
		},
		products:{
			title:null,
			identifier:null,
			rslist:null
		}
    },
    //事件处理函数
    bindViewTap: function() {
        wx.navigateTo({
            url: '../logs/logs'
        })
    },
    onLoad: function() {
		kunwu.call.set('m_picplayer');
		kunwu.call.alias('m_picplayer','picplayer');
		kunwu.call.set('aboutus');
		kunwu.call.set('new_products');
		kunwu.call.alias('new_products','products');
        var that = this;
		app.phpok(kunwu.call.get(),function(rs){
			if(!rs.status){
				kunwu.dialog.alert(rs.info);
				return false;
			}
			var info = rs.info;
			//图片播放器
			if (info.picplayer.rslist){
				var banner_url = [];
				var m = 0;
				var tmplist = info.picplayer.rslist;
				console.log(tmplist);
				for(var i in tmplist){
					banner_url[m] = app.okConfig.host + tmplist[i].picmobile.gd.auto;
					m++;
				}
				
				that.setData({
					'banner_url': banner_url
				});
			}
			//关于我们
			if(info.aboutus){
				that.setData({
					'aboutus':{
						'title': info.aboutus.title
					}
				})
				WxParse.wxParse('article', 'html', info.aboutus.note, that, 5);
			}
			if(info.products && info.products.rslist){
				var rslist = [],tmplist = info.products.rslist;
				var m = 0;
				for(var i in tmplist){
					var tmp = {};
					tmp.title = tmplist[i].title;
					tmp.picture = app.okConfig.host + tmplist[i].thumb.gd.thumb;
					tmp.url = '../content/index?id='+tmplist[i].id;
					tmp.webview = tmplist[i].url;
					rslist[m] = tmp;
					m++;					
				}
				that.setData({
					'products':{
						'title':info.products.project.title,
						'identifier':info.products.project.identifier,
						'rslist':rslist
					}
				})
			}
		});
    }
})