/**
 * 内容管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2018年01月16日
**/
var autosave_handle;

;(function($){
	$.admin_list_edit = {

		/**
		 * 自动保存
		**/
		autosave:function()
		{
			window.clearTimeout(autosave_handle);
			$("#_listedit").ajaxSubmit({
				'url':get_url('auto','list'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips('数据已临时保存').position('50%','1px');
						// 每隔 5 分钟自动保存一次数据
						autosave_handle = window.setTimeout(function(){
							$.admin_list_edit.autosave();
						}, 300000);
					}
				}
			});
			return false;
		},

		/**
		 * 保存数据
		**/
		save:function()
		{
			var loading_action;
			var id = $("#id").val();
			var pcate = $("#_root_cate").val();
			var pcate_multiple = $("#_root_cate_multiple").val();
			$("#_listedit").ajaxSubmit({
				'url':get_url('list','ok'),
				'type':'post',
				'dataType':'json',
				'beforeSubmit':function(){
					loading_action = $.dialog.tips('<img src="images/loading.gif" border="0" align="absmiddle" /> '+p_lang('正在保存数据，请稍候…')).time(30).lock();
				},
				'success':function(rs){
					if(loading_action){
						loading_action.close();
					}
					if(rs.status == 'ok'){
						var url = get_url('list','action','id='+$("#pid").val());
						if(pcate>0){
							var cateid = $("#cate_id").val();
							url += "&cateid="+cateid;
						}
						if(id){
							$.dialog.alert(p_lang('内容信息修改成功'),function(){
								$.phpok.go(url);
							},'succeed');
							return true;
						}
						$.dialog.through({
							'icon':'succeed',
							'content':p_lang('内容添加操作成功，请选择继续添加或返回列表'),
							'ok':function(){$.phpok.reload();},
							'okVal':p_lang('继续添加'),
							'cancel':function(){
								$.phpok.go(url);
							},
							'cancelVal':p_lang('返回列表'),
							'lock':true
						});
						return true;

					}
					$.dialog.alert(rs.content);
					return true;
				}
			});
			return false;
		}
	}

})(jQuery);
$(document).keypress(function(e){
	//按钮CTRL+回车键执行保存
	if(e.ctrlKey && e.which == 13 || e.which == 10) {
		$('.phpok_submit_click').click();
	}

	//仅在添加主题时执行自动保存操作
	var id = $("#id").val();
	if(!id || id == '0' || id == 'undefined'){
		autosave_handle = window.setTimeout(function(){
			$.admin_list_edit.autosave();
		}, 60000);
	}

});