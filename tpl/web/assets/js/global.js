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
 * 文档加载完成后初始化
**/
$(document).ready(function(){
	$(document).off('click.bs.dropdown.data-api');
	dropdownOpen();//调用

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