/**
 * 阿里云点播上传
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年5月21日
**/
;(function($){
	$.phpok_aliyunoss_gateway = {
		extCheck:function(val,ext)
		{
			if(!ext || ext == 'undefined'){
				ext = "jpg,gif,jpeg";
			}
			if(ext.indexOf(val)>-1){
				return true;
			}
			return false;
		},
		ext:function(val)
		{
			if(!val || val == 'undefined'){
				return false;
			}
			var t = val.split('.');
			var count = t.length;
			if(count<2){
				return false;
			}
			var tmp = t[count-1];
			return tmp.toLowerCase();
		},
		/**
		 * 上传文件
		 * @参数 obj 当前对象
		 * @参数 id 变量名ID
		 * @参数 gateway_id 网关ID
		 * @参数 bucket_id 存储桶ID
		 * @参数 regoin_id 阿里云的 Regoin
		 * @参数 is_multiple 设置 false 表示单文件上传，设置 true 表示多文件上传
		 * @参数 is_refresh 设置 false 表示不刷新，设置为 true 表示刷新
		 * @参数 folder 目标文件夹
		 * @参数 ext 扩展名，如jpg，gif，png 等
		 * @参数 cate_id 分类ID
		**/
		act:async function(obj, id, gateway_id, bucket_id, regoin_id, is_multiple, is_refresh, folder, ext, cate_id)
		{
			if(!is_multiple || is_multiple == 'undefined'){
				is_multiple = false;
			}
			if(!is_refresh || is_refresh == 'undefined'){
				is_refresh = false;
			}
			if(!cate_id || cate_id == 'undefined'){
				cate_id = 0;
			}
			var self = this;
			var uploadAct = this.aliyunOSS(id, gateway_id, regoin_id, bucket_id);
			for(var i=0;i<obj.files.length;i++){
				var tmpext = this.ext(obj.files[i].name);
				if(!tmpext){
					$.dialog.tips('无法取得文件名，忽略跳过');
					continue;
				}
				if(!this.extCheck(tmpext,ext)){
					$.dialog.tips("附件格式 "+tmpext+" 不支持，已忽略");
					continue;
				}
				var myname = obj.files[i].name;
				var timestamp = (new Date()).valueOf();
				var tmpname = folder+''+$.phpok.rand(10,'fixed')+(timestamp.toString())+"."+tmpext;
				this.addFileSuccess(obj.files[i],id,i);
				var tmpfile = obj.files[i];
				var opts = {
					progress: function(p){
						self.progress(tmpfile,p,id,i);
					},
					partSize: 100 * 1024,
					timeout: 60000
				};
				await uploadAct.multipartUpload(tmpname,tmpfile,opts).then(function(res){
					self.uploadSuccess(res, tmpfile, gateway_id, cate_id, id, i, is_multiple, is_refresh);
				}).catch(function(res){
					self.uploadFail(tmpfile,res,id,i);
				});
			}
		},
		get_sts:function(gateway_id)
		{
			if(!gateway_id || gateway_id == 'undefined'){
				$.dialog.tips('未指定网关路由');
				return false;
			}
			var info = $.phpok.json(api_url('gateway','index','id='+gateway_id+"&file=role"));
			if(!info.status){
				$.dialog.tips(rs.info);
				return false;
			}
			return info.info;
		},
		progress:function (file, percent,id,i) {
			var name = id+'-'+i;
			var size = this.fsize(file.size);
			var $li = $('#phpok-upfile-'+name),
			$percent = $li.find('.progress span');
			var width = $li.find('.progress').width();
			$percent.css( 'width', parseInt(width * percent, 10) + 'px' );
			$li.find('span.status').html(p_lang('文件大小：'+size+'，正在上传…'));
		},
		uploadSuccess:function(res, file, gateway_id, cate_id, id, i, is_multiple, is_refresh)
		{
			if(!is_multiple || is_multiple == 'undefined'){
				is_multiple = false;
			}
			if(!is_refresh || is_refresh == 'undefined'){
				is_refresh = false;
			}
			var name = id+'-'+i;
			$("input[type=file]").val('');
			$('#phpok-upfile-'+name).find('span.status').html(p_lang('上传成功'));
			$("#phpok-upfile-"+name).fadeOut();
			var url = api_url('gateway','index','id='+gateway_id+"&file=success&cate_id="+cate_id+"&filename="+$.str.encode(res.name)+"&title="+$.str.encode(file.name));
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info).lock();
					$("#phpok-upfile-"+name).remove();
					return false;
				}
				if(is_multiple){
					var t = $("#"+id).val();
					var v = t ? t+","+rs.info.id : rs.info.id;
					$("#"+fileId).val(v);
					//执行
					var d = $.phpok.data("upload-"+id);
					if(d){
						d += ","+rs.info.id;
					}else{
						d = rs.info.id;
					}
					$.phpok.data("upload-"+id,d);
				}else{
					var t = $("#"+id).val();
					if(t){
						$.phpokform.upload_delete(id,t);
					}
					$("#"+id).val(rs.info.id);
					$.phpok.data("upload-"+id,rs.info.id);
				}
				if(is_refresh){
					$.phpok.reload();
					return false;
				}
				$("#phpok-upfile-"+name).remove();
				$.phpokform.upload_showhtml(id,is_multiple);
			});
		},
		//文件添加成功后
		addFileSuccess:function(file,id,i)
		{
			var size = this.fsize(file.size);
			var name = id+'-'+i;
			$("#"+id+"_progress").show().append('<div id="phpok-upfile-' + name + '" class="phpok-upfile-list">' +
				'<div class="title">' + file.name + ' <span class="status">'+p_lang('文件大小：'+size+'，等待上传…')+'</span></div>' +
				'<div class="progress"><span>&nbsp;</span></div>' +
				'<div class="cancel" id="phpok-upfile-cancel-'+name+'"></div>' +
			'</div>' );
		},
		fsize:function(totalSize)
		{
			var size = 0;
			totalSize = parseInt(totalSize);
			if(totalSize>= 1073741824){
				size = ((totalSize/1073741824).toFixed(2)).toString() + 'GB';
			}else if(totalSize < 1073741824 && totalSize >= 1048576){
				size = ((totalSize/1048576).toFixed(2)).toString() + 'MB';
			}else if(totalSize < 1048576 && totalSize >= 1024){
				size = ((totalSize/1024).toFixed(2)).toString() + 'KB';
			}else{
				size = (totalSize).toString() + 'B';
			}
			return size;
		},
		uploadFail:function(file,err,id,i)
		{
			var name = id+'-'+i;
			$('#phpok-upfile-'+name).find('span.status').html(p_lang('上传失败'));
			$("#phpok-upfile-"+name).fadeOut().remove();
		},
		aliyunOSS:function(id, gateway_id, regoin_id, bucket_id)
		{
			var info = this.get_sts(gateway_id);
			if(!info){
				return false;
			}
			var opts = {
				"region": regoin_id,
				"accessKeyId": info.access_id,
				"accessKeySecret": info.access_secret,
				"stsToken": info.token,
				"bucket": bucket_id,
				"refreshSTSTokenInterval":300000,
				"refreshSTSToken":async function(){
					var url = api_url('gateway','index','id='+gateway_id+"&file=role");
					var rs = await $.phpok.json(url,true);
					if(!rs || !rs.status){
						return false;
					}
					return {
						accessKeyId: rs.info.access_id,
						accessKeySecret: rs.info.access_secret,
						stsToken: rs.info.token
					}
				}
			};
			return new OSS(opts);
		}
	}
})(jQuery);