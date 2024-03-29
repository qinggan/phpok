/**
 * 新版公共脚本
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2023年5月5日
 * @更新 2023年5月5日
**/

/**
 * 导航菜单下拉（由 Bootstrap 自带的点击改成鼠标移过就生效）
**/
function dropdownOpen() {
    var $dropdownLi = $('li.dropdown');
    $dropdownLi.mouseover(function() {
        $(this).addClass('show').find(".dropdown-menu").addClass('show');
    }).mouseout(function() {
        $(this).removeClass('show').find(".dropdown-menu").removeClass('show');
    });
}

/**
 * 搜索框
**/
function top_search(obj)
{
	var title = $(obj).find("input[name=keywords]").val();
	if(!title){
		$.dialog.tips('请输入要搜索的关键字');
		return false;
	}
	return true;
}


/**
 * 筛选器
**/
function filter_submit(url,id,val,cutype)
{
	if(!url || !id || url == 'undefined' || id == 'undefined' || !val || val == 'undefined'){
		return false;
	}
	url += (url.indexOf('?') > -1) ? '&' : '?';
	if(id == 'cate'){
		url += "cate="+val;
		$.phpok.go(url);
		return true;
	}
	if(id == 'price'){
		var t = val.split('-');
		if(t[0] && t[0] != 'undefined'){
			url += "price[min]="+t[0]+"&";
		}
		if(t[1] && t[1] != 'undefined'){
			url += "price[max]="+t[1];
		}
		$.phpok.go(url);
		return true;
	}
	var str = '';
	var is_delete = false;
	if(cutype != ''){
		$("#filter_"+id+" .active").each(function(i){
			var info = $(this).attr('data-val');
			if(info && info != val){
				if(str != ''){
					str += cutype+""+info;
				}else{
					str = info;
				}
			}
			if(info && info == val){
				is_delete = true;
			}
		});
		if(str != '' && !is_delete){
			str += cutype+""+val;
		}
		if(str == '' && !is_delete){
			str = val;
		}
		if(str != ''){
			url += "ext["+id+"]="+$.str.encode(str);
		}
	}else{
		url += "ext["+id+"]="+$.str.encode(val);
	}
	$.phpok.go(url);
}


/**
 * 基于Ajax加载内容
**/
var is_loading = false;
var is_locking = false;
function loadmore(id,func)
{
	if(is_loading || is_locking){
		return false;
	}
	var scrollTop = $(this).scrollTop();
	var scrollHeight = $(document).height() -500;
	var windowHeight = $(this).height();
	if (scrollTop + windowHeight >= scrollHeight) {
		is_loading = true;
		var url = window.location.href;
		var data = {};
		data['pageid'] = pageid+1;
		data['ajax'] = 1;
		var loading = $.dialog.tips('加载中，请稍候…',100).lock();
		$.phpok.json(url,function(rs){
			is_loading = false;
			if(!rs.status){
				is_locking = true;
				loading.content('已全部加载…').time(0.5);
				return false;
			}
			loading.close();
			is_locking = false;
			pageid = data['pageid'];
			$("#"+id).append(rs.info);
			if(func && func != 'undefined' && typeof func == 'function'){
				(func)();
			}
		},data);
	}
}



/**
 * 文档加载完成后初始化
**/
$(document).ready(function(){
	if ($("meta[name=toTop]").attr("content") == "true") {
		if ($(this).scrollTop() == 0) {
			$(".toTop").hide();
		}
		$(".toTop").click(function(event) {
			$("html,body").animate({
				scrollTop: "0px"
			}, 666)
		});
		$(window).scroll(function(event) {
			if ($(this).scrollTop() == 0) {
				$(".toTop").hide();
			}
			if ($(this).scrollTop() != 0) {
				$(".toTop").show();
			}
		});
	}
	$(document).off('click.bs.dropdown.data-api');
	dropdownOpen();//调用

	setTimeout(function(){
		$.cart.total();
	}, 500);
	

	/**
	 * 评论
	**/
	if($("#comment-post").length > 0){
	    //提交评论
	    $("#comment-post").submit(function(){
			$.comment.post($("#comment-post")[0]);
			return false;
		});
		if(typeof CKEDITOR != 'undefined'){
			CKEDITOR.on('instanceReady', function(evt) {
				evt.editor.setKeystroke(CKEDITOR.CTRL + 13, 'save');
			});
			CKEDITOR.instances['comment'].on('save', function(event) {
				window.onbeforeunload = null;
				$.comment.post($("#comment-post")[0]);
				return false;
			});
		}
		$(document).keypress(function(e){
			if(e.ctrlKey && e.which == 13 || e.which == 10) {
				$.comment.post($("#comment-post")[0]);
				return false;
			}
		});
	}
});