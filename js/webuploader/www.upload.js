/**
 * 附件类型上传操作类
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @网站 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @日期 2017年03月22日
**/
;(function($){
	$.www_upload = function(options){
		var self = this;
		var defaults = {
			'id':'upload',
			'swf':'js/webuploader/uploader.swf',
			'server':'index.php',
			'pick':'#picker',
			'resize': false,
			'multiple':false,
			'disableGlobalDnd':true,
			'fileVal':'upfile',
			'sendAsBinary':false,
			'duplicate':false,
			'chunked':true,
			'chunkSize':102400,
			'threads':3,
			'auto':false,
			'accept':{'title':p_lang('图片(*.jpg, *.gif, *.png)'),'extensions':'jpg,png,gif'}
		};
    var opts = $.extend({},defaults,options);
    this.opts = opts;
		if(!this.opts.pick.innerHTML){
			this.opts.pick.innerHTML = p_lang('选择本地文件');
		}
		this.id = "#"+opts.id;
		uploader = WebUploader.create(opts);
		this.uploader = uploader;
		this.upload_state = 'ready';
		uploader.on('beforeFileQueued',function(file){
			var extlist = (self.opts.accept.extensions).split(",");
			if($.inArray((file.ext).toLowerCase(),extlist) < 0){
				$.dialog.alert(p_lang('附件类型不支持{filext}格式',' <span class="red">'+file.ext+'</span> '));
				return false;
			}
		});
		this.option = function(k,val){
			uploader.option(k,val);
		}
		uploader.on('fileQueued',function(file){
			$(self.id+"_progress").append('<div id="phpok-upfile-' + file.id + '" class="phpok-upfile-list">' +
				'<div class="title">' + file.name + ' <span class="status">'+p_lang('等待上传…')+'</span></div>' +
				'<div class="progress"><span>&nbsp;</span></div>' +
				'<div class="cancel" id="phpok-upfile-cancel-'+file.id+'"></div>' +
			'</div>' );
			$("#phpok-upfile-"+file.id+" .cancel").click(function(){
				uploader.removeFile(file,true);
				$("#phpok-upfile-"+file.id).remove();
			});
			//执行自定义的方法
			if(self.opts.file_queued && self.opts.file_queued != 'undefined'){
				(self.opts.file_queued)(file);
				return true;
			}
		});
		uploader.on('uploadStart',function(file){
			//执行自定义的方法
			if(self.opts.upload_start && self.opts.upload_start != 'undefined'){
				(self.opts.upload_start)(file);
				return true;
			}
		});
		uploader.on('uploadProgress',function(file,percent){
			if(self.opts.loading && self.opts.loading != 'undefined'){
				(self.opts.loading)(file,percent);
			}
			var $li = $('#phpok-upfile-'+file.id),
			$percent = $li.find('.progress span');
			var width = $li.find('.progress').width();
			$percent.css( 'width', parseInt(width * percent, 10) + 'px' );
			$li.find('span.status').html(p_lang('正在上传…'));
			self.upload_state = 'running';
		});
		uploader.on('uploadBeforeSend',function(block,data,headers){
			data.cateid = self.opts.cateid;
			if($(self.id+"_cateid").val()){
				data.cateid = $(self.id+"_cateid").val();
			}
			//执行自定义的方法
			if(self.opts.before_send && self.opts.before_send != 'undefined'){
				(self.opts.before_send)(block,data,headers);
				return true;
			}
		});
		uploader.on('uploadAccept',function(block,ret){
	        //执行自定义的方法
			if(self.opts.upload_accept && self.opts.upload_accept != 'undefined'){
				(self.opts.upload_accept)(block,ret);
				return true;
			}
		});
		uploader.on('uploadSuccess',function(file,data){
			//执行自定义的方法
			if(self.opts.success && self.opts.success != 'undefined'){
				(self.opts.success)(file,data);
				return true;
			}
			//上传成功后，清除表单项
			$("input[type=file]").val('');
			if(!data.status && data._raw){
				var lst = data._raw.split('{"status"');
				var info = lst[0];
				var html = lst[1];
				if(info.indexOf('$HTTP_RAW_POST_DATA') > -1){
					$.dialog.tips('建议更新您的PHP.INI环境，设置：always_populate_raw_post_data = -1');
					data = $.parseJSON('{"status"'+html);
				}else{
					$.dialog.alert(data._raw);
					return false;
				}
			}
			if(data.status != 'ok'){
				$.dialog.alert(p_lang('上传错误')+' <span style="color:red">'+data.content+'</span>');
				return false;
			}
			$('#phpok-upfile-'+file.id).find('span.status').html(p_lang('上传成功'));
			var tmp = $.phpok.data('upload-'+self.opts.id);
			if(self.opts.multiple == 'true'){
				var val = $(self.id).val();
				if(val){
					val += ","+data.content.id;
				}else{
					val = data.content.id;
				}
				$(self.id).val(val);
				if(tmp){
					tmp += ','+data.content.id;
				}else{
					tmp = data.content.id;
				}
			}else{
				if(tmp){
					$.phpokform.upload_remote_delete(self.opts.id,tmp);
				}
				tmp = data.content.id;
				$(self.id).val(data.content.id);
			}
			$.phpok.data('upload-'+self.opts.id,tmp);
			self.showhtml();
		});
		uploader.on('uploadError',function(file,reason){
			$('#phpok-upfile-'+file.id).find('span.status').html(p_lang('上传错误：')+'<span style="color:red">'+reason+'</span> ');
		});
		uploader.on('uploadFinished',function(){
			self.upload_state = 'ready';
			if(self.opts.upload_finished && self.opts.upload_finished != 'undefined'){
				(self.opts.upload_finished)();
				return true;
			}
		});
		//上传完成，无论失败与否，3秒后删除
		uploader.on('uploadComplete',function(file){
			$("#phpok-upfile-"+file.id).fadeOut();
		});
		uploader.on('error',function(handle){
			var tip = '';
			if(handle == 'Q_EXCEED_NUM_LIMIT'){
				tip = p_lang('要添加的文件数量超出系统限制');
			}
			if(handle == 'Q_EXCEED_SIZE_LIMIT'){
				tip = p_lang('要添加的文件总大小超出系统限制');
			}
			if(handle == 'Q_TYPE_DENIED'){
				tip = p_lang('文件类型不符合要求');
			}
			if(handle == 'F_DUPLICATE'){
				tip = p_lang('文件重复');
			}
			if(handle =='F_EXCEED_SIZE'){
				tip = p_lang('上传文件超过系统限制');
			}
			if(!tip){
				tip = handle;
			}
			$.dialog.alert('<span style="color:red">'+tip+'</span>');
			return false;
		});
		this.showhtml = function(){
			var id = $(this.id).val();
			if(!id){
				$(self.id).val('');
				$(self.id+"_list").html('').fadeOut();
				return false;
			}
			var url = get_url('upload','thumbshow','id='+$.str.encode(id));
			$.phpok.json(url,function(rs){
				if(rs.status != 'ok'){
					$(self.id).val('');
					$(self.id+"_list").html('').fadeOut();
					return true;
				}
				var html = '';
				var index_i = 1;
				for(var i in rs.content){
					html += self.html(rs.content[i],index_i);
					index_i++;
				}
				$(self.id+"_list").html(html).show();
				if(!html){
					$(self.id+"_list").html('').fadeOut();
				}
				return true;
			});
		};
		this.html = function(rs,i){
			var html = '<div class="'+opts.id+'_thumb" name="_elist">';
			html += '<div style="text-align:center;"><img src="'+rs.ico+'" width="100" height="100" /></div>';
			html += '<div class="file-action" style="text-align:center;"><div class="button-group">';
			html += '	<input type="button" value="预览" class="phpok-btn" onclick="obj_'+opts.id+'.preview(\''+rs.id+'\')" />';
			html += '	<input type="button" value="删除" class="phpok-btn" onclick="obj_'+opts.id+'.del(\''+rs.id+'\')" /></div>';
			html += '</div></div>';
			html += '</div>';
			return html;
		};
		this.update = function(id){
			$.dialog.open(get_url('upload','editopen','id='+id),{
				'title':'编辑附件信息',
				'width':'700px',
				'height':'400px',
				'lock':true,
				'okVal':'提交',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe还没加载完毕呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'cancelVal':'取消修改',
				'cancel':function(){}
			});
		};
		this.preview = function(id){
			$.dialog.open(get_url('upload','preview','id='+id),{
				'title':'预览附件信息',
				'width':'700px',
				'height':'400px',
				'lock':true,
				'okVal':'关闭',
				'ok':function(){}
			});
		};
		this.del = function(id){
			var content = $(self.id).val();
			if(!content || content == "undefined"){
				return true;
			}
			//删除单个附件
			if(content == id){
				$(self.id).val("");
				$(self.id+"_list").fadeOut(function(){
					$(this).html('');
				});
				//远程删除数据
				self.remote_delete(id);
				return true;
			}
			var list = content.split(",");
			var newlist = new Array();
			var new_i = 0;
			for(var i=0;i<list.length;i++){
				if(list[i] != id){
					newlist[new_i] = list[i];
					new_i++;
				}
			}
			content = newlist.join(",");
			$(self.id).val(content);
			//远程删除数据
			self.remote_delete(id);
			self.showhtml();
		};
		this.remote_delete = function(id){
			var tmp = $.dialog.data('upload-'+opts.id);
			if(!tmp || tmp == 'undefined'){
				return true;
			}
			var delete_status = false;
			if(tmp != id){
				var list = tmp.split(',');
				var newlist = new Array();
				var new_i = 0;
				for(var i=0;i<list.length;i++){
					if(list[i] != id){
						newlist[new_i] = list[i];
						new_i++;
					}else{
						delete_status = true;
					}
				}
				content = newlist.join(",");
				$.dialog.data('upload-'+opts.id,content);
			} else {
				delete_status = true;
				$.dialog.data('upload-'+opts.id,'');
			}
			if(delete_status){
				var url = get_url('upload','delete','id='+id);
				$.phpok.json(url,function(){
					return true;
				});
			}
		};
		//排序
		this.sort = function(type){
			var t = [];
			$("#"+opts.id+"_list .taxis").each(function(i){
				if(type == 'title'){
					var val = $(this).attr('title');
				}else{
					var val = $(this).val();
				}
				var data = $(this).attr("data");
				t.push({"id":val,"data":data});
			});
			t = t.sort(function(a,b){return parseInt(a['id'])>parseInt(b['id']) ? 1 : -1});
			var list = new Array();
			for(var i in t){
				list[i] = t[i]['data'];
			}
			var val = list.join(",");
			$(this.id).val(val);
			this.showhtml();
		};
		$(this.id+"_submit").click(function(){
			if($(this).hasClass('disabled')){
				$.dialog.alert(p_lang('正在上传中，已锁定'));
				return false;
			}
			var f = $(self.id+"_progress .phpok-upfile-list").length;
			if(f<1){
				$.dialog.alert(p_lang('请选择要上传的文件'));
				return false;
			}
			if(self.upload_state == 'ready' || self.upload_state == 'paused'){
				uploader.upload();
			}else{
				uploader.stop();
			}
		});
	};
})(jQuery);
