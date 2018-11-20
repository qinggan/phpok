// pages/content/index.js
var app = getApp();
var kunwu = require('../../phpok.js');
var WxParse = require("../../wxParse/wxParse.js");
Page({

	/**
	 * 页面的初始数据
	 */
	data: {
		player_config:{
			indicatorDots: true, //显示圆点
			vertical: false, //设为true表示上下轮播，设为false则表示左右轮播
			autoplay:true, // 自动播放
			interval: 3000, // 等待时间，单位是毫秒
			duration: 800 //有效时间，单位是毫秒
		},
		banner_url: null,
		rs:null,
		page_rs:null
	},

	changeAttrlist(e) {
		var data = this.data.rs.attrlist;
		for(var i in data){
			var id = "attr_"+data[i].id;
			if(id == e.target.id){
				data[i].index = e.detail.value;
			}
		}
		this.data.rs.attrlist = data;
		var rs = this.data.rs;
		this.setData({
			'rs':rs
		});
	},

	/**
	 * 生命周期函数--监听页面加载
	 */
	onLoad: function (options) {
		app.load_wxconfig();
		var that = this;
		var url = app.api_url('content','index','id='+options.id);
		app.json(url,function(data){
			if(!data.status){
				kunwu.dialog.alert(data.info,function(){
					wx.navigateBack();
				});
				return false;
			}
			var rs = data.info.rs;
			var page_rs = data.info.page_rs;
			wx.setNavigationBarTitle({
				title: page_rs.title
			});
			//图片播放器
			if(rs.pictures){
				var banner_url = [];
				var m = 0;
				var tmplist = rs.pictures;
				for(var i in tmplist){
					banner_url[m] = app.okConfig.host + tmplist[i].gd.auto;
					m++;
				}
				
				that.setData({
					'banner_url': banner_url
				});
			}else{
				if(rs.thumb){
					var banner_url = [];
					banner_url[0] = app.okConfig.host + rs.thumb.gd.auto;
					that.setData({
						'banner_url': banner_url
					});
				}
			}
			if(rs.dateline){
				rs.dateline = kunwu.format.date(rs.dateline);
			}
			that.setData({
				'rs':rs,
				'page_rs':page_rs
			});
			if(rs.content){
				WxParse.wxParse('article', 'html', rs.content, that, 5);
			}
		});
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

	},

	/**
	 * 页面上拉触底事件的处理函数
	 */
	onReachBottom: function () {

	},

	/**
	 * 用户点击右上角分享
	 */
	onShareAppMessage: function () {

	}
})