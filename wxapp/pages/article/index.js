// pages/article/index.js
var app = getApp();
var kunwu = require('../../phpok.js');
Page({

	/**
	 * 页面的初始数据
	 */
	data: {
		rslist:null,
		page_rs:null,
		psize:10,
		total:0,
		pageid:1
	},

	/**
	 * 生命周期函数--监听页面加载
	 */
	onLoad: function (options) {
		app.load_wxconfig();
		this.arclist({'id':app.okConfig.params.article},true);
	},

	/**
	 * 生命周期函数--监听页面初次渲染完成
	 */
	onReady: function () {

	},

	/**
	 * 生命周期函数--监听页面显示
	 */
	onShow: function () {

	},

	/**
	 * 生命周期函数--监听页面隐藏
	 */
	onHide: function () {

	},

	/**
	 * 生命周期函数--监听页面卸载
	 */
	onUnload: function () {

	},

	/**
	 * 页面相关事件处理函数--监听用户下拉动作
	 */
	onPullDownRefresh: function () {
		var that = this;
		wx.showNavigationBarLoading();
		this.arclist({'id':app.okConfig.params.article},true,function(){
			wx.hideNavigationBarLoading();
			wx.stopPullDownRefresh();
		});
	},

	/**
	 * 页面上拉触底事件的处理函数
	 */
	onReachBottom: function () {
		//计算是否在最后一页了
		var total = parseInt(this.data.total);
		var psize = parseInt(this.data.psize);
		var pageid = parseInt(this.data.pageid);
		var next_pageid = parseInt(pageid+1);
		if((pageid*psize)>=total){
			kunwu.dialog.tips('已经到最后一页了');
			return false;
		}
		var opts = {'id':app.okConfig.params.article,'pageid':next_pageid};
		this.arclist(opts);
	},

	arclist:function(opts,first,obj)
	{
		if(!opts || !opts.id){
			opts.id = app.okConfig.params.article;
		}
		var ext = new Array();
		var m = 0;
		for(var i in opts){
			ext[m] = i+"="+opts[i];
			m++
		}
		var ext_string = ext.join('&');
		var url = app.api_url('project','index',ext_string);
		var that = this;
		app.json(url,function(rs){
			if(!rs.status){
				kunwu.dialog.alert(rs.info,function(){
					app.tohome(true);
				});
				return false;
			}
			var info = rs.info;
			if(info.rslist){
				var rslist = [],tmplist = info.rslist;
				var m = 0;
				if((!first || first == 'undefined') && that.data.rslist){
					rslist = that.data.rslist;
					m = rslist.length;
				}
				for(var i in tmplist){
					var tmp = {};
					tmp.title = tmplist[i].title;
					if(tmplist[i].thumb){
						tmp.picture = app.okConfig.host + tmplist[i].thumb.gd.thumb;
					}
					tmp.url = '../content/index?id='+tmplist[i].id;
					tmp.webview = tmplist[i].url;
					if(tmplist[i].note){
						tmp.note = app.clear_html(tmplist[i].note)
					}					
					rslist[m] = tmp;
					m++;
				}
				that.setData({
					'rslist':rslist,
					'page_rs':info.page_rs,
					'total':info.total,
					'psize':info.psize,
					'pageid':info.pageid
				});
				if(first && first != 'undefined'){
					wx.setNavigationBarTitle({
						title: info.page_rs.title
					});
				}
				if(obj && typeof(obj) == 'function'){
					(obj)();
				}
			}
		});
	},

	/**
	 * 用户点击右上角分享
	 */
	onShareAppMessage: function () {

	}
})