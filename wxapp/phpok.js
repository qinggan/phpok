/**
 * 小程序中用的通用JS，封装在此
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @日期 2017年04月18日
**/
var app = getApp();

var data = {
	set:function(name,val='')
	{
		if(!name || name == 'undefined'){
			return false;
		}
		if(val != '' && val != 'undefined'){
			wx.setStorageSync(name,val);
			return val;
		}
		return wx.getStorageSync(name);
	},
	get:function(name)
	{
		return wx.getStorageSync(name);
	},
	del:function(name)
	{
		return wx.removeStorageSync(name);
	}
}

var call = {
	data:{},
	name:null,
	set:function(name,param_id,param_val)
	{
		if(!name || name == 'undefined'){
			return false;
		}
		var tmp = {}
		if(param_id && param_id != 'undefined' && (!param_val || param_val == 'undefined')){
			tmp[this.name] = {};
			tmp[this.name][name] = param_id;
			this.name = name;
		}
		if((!param_id || param_id == 'undefined') && (!param_val || param_val == 'undefined')){
			tmp[name] = {};
			this.name = name;
		}
		if (param_id && param_id != 'undefined' && param_val && param_val != 'undefined'){
			if(!tmp[name] || tmp[name] == 'undefined'){
				tmp[name] = {};
			}
			tmp[name][param_id] = param_val;
			this.name = name;
		}
		var all = this.extend(this.data,tmp,true);
		this.data = all;
	},
	alias:function(name,new_name)
	{
		var tmp = {};
		tmp[name] = {'_alias':new_name};
		var all = this.extend(this.data, tmp, true);
		this.data = all;
	},
	extend: function (des, src, override)
	{
		if (src instanceof Array) {
			for (var i = 0, len = src.length; i < len; i++)
				extend(des, src[i], override);
		}
		for (var i in src) {
			if (override || !(i in des)) {
				des[i] = src[i];
			}
		}
		return des;
	},
	del:function(name)
	{
		if(this.data[name]){
			delete this.data[name];
		}
	},
	get:function()
	{
		var is_val = false;
		for(var i in this.data){
			if(this.data[i] && this.data[i] != 'undefined'){
				is_val = true;
			}
		}
		if(is_val){
			return this.data;
		}
		return false;
	},
	reset:function()
	{
		this.data = {};
	}
}


var dialog = {
	tips:function(info,obj)
	{
		wx.showToast({
            'title':info,
            'icon': 'none',
            'mask':true,
            'success':function(){
	            if(obj && obj != 'undefined' && typeof obj == 'function'){
		            (obj)();
	            }
            }
        });
	},
	alert:function(info,obj)
	{
		wx.showModal({
			title: '友情提示',
			content: info,
			showCancel: false,
			confirmColor: '#007aff',
			success: function() {
				if(obj && obj != 'undefined' && typeof obj == 'function'){
		            (obj)();
	            }
			}
		});
	},
	confirm:function(info,ok_func,cancel_func)
	{
		wx.showModal({
			title: '友情提示',
			content: info,
			success(res) {
				if(res.confirm){
					if(ok_func && typeof(ok_func) == 'function'){
						ok_func();
					}
				}else if(res.cancel){
					if(cancel_func && typeof(cancel_func) == 'function'){
						cancel_func();
					}
				}
			}
		})
	},
	select:function(list,success_func,err_func)
	{
		wx.showActionSheet({
			itemList:list,
			success(res) {
				if(success_func && typeof(success_func) == 'function'){
					success_func(res.tapIndex);
				}
			},
			fail(res) {
				if(err_func && typeof(err_func) == 'function'){
					err_func();
				}
			}
		})
	},
	loading:function(info,time,success_func,err_func)
	{
		var title = info;
		var load_time = time;
		var ok_func = success_func;
		var cancel_func = err_func;
		if(this.check_number(info)){
			title = '加载中';
			load_time = parseInt(info);
			ok_func = time;
			cancel_func = success_func;
		}

		wx.showLoading({
			'title':title,
			'mask':true,
			'success':function(){
				if(ok_func && ok_func != 'undefined' && typeof(ok_func) == 'function'){
					ok_func();
				}
			},
			'fail':function(){
				if(cancel_func && cancel_func != 'undefined' && typeof(cancel_func) == 'function'){
					cancel_func();
				}
			}
		});
		var mytime = (load_time && load_time != 'undefined') ? (parseInt(load_time) * 1000) : 2000;
		window.setTimeout(function(){
			wx.hideLoading();
		}, mytime);
	},
	check_number:function(val)
	{
		if(val == "" || val == null){
			return false;
		}
		if(!isNaN(val)){
			return true;
		}
		return false;
	}
}

var format = {
	date:function(dateline)
	{
		var rs = '';
		var chk = new Date();
		var now = new Date(dateline * 1000);
		var chkyear = chk.getFullYear();
		var year = now.getFullYear();
		var month = now.getMonth() + 1;
		if(chkyear != year){
			rs = year+'-';
		}
		if(rs && rs != ''){
			rs += this.add_zero(month) + '-';
		}else{
			var chkmonth = chk.getMonth()+1;
			if(month != chkmonth){
				rs += this.add_zero(month) + '-';
			}
		}
		var date = now.getDate();
		var is_end = false;
		if(rs && rs != ''){
			rs += this.add_zero(date) + ' ';
		}else{
			var chkdate = chk.getDate();
			if(chkdate>date){
				rs = (parseInt(chkdate-date)).toString()+"天前";
				is_end = true;
			}
		}
		if(is_end){
			return rs;
		}
		var hour = now.getHours();
		var minute = now.getMinutes();
		if(rs && rs != ''){
			rs += " "+this.add_zero(hour) + ':'+this.add_zero(minute);
			return rs;
		}
		var chkhour = chk.getHours();
		if(chkhour>hour){
			rs = (parseInt(chkhour-hour)).toString() + '小时前';
			return rs;
		}
		var chkminute = chk.getMinutes();
		if(chkminute>minute){
			rs = (parseInt(chkminute-minute)).toString() + '分钟前';
			return rs;
		}
		return '1分钟前';
	},
	add_zero:function(val,type)
	{
		if(parseInt(val)<10){
			return '0'+val.toString();
		}
		return val.toString();
	},
	price:function(val)
	{
		return parseFloat(val).toFixed(2);
	}
}
module.exports = {
	cookie:cookie,
	call:call,
	dialog:dialog,
	format:format
}
