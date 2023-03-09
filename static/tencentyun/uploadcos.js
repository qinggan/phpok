/**
 * 腾迅云对象存储COS
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2022年2月28日
**/
;(function($){
	var fileId = '';
	var cateId = {};
	var uploader = {};
	var region = {};
	var bucket = {};
	var multi = {};
	var gateway = {};
	var refresh = {};
	var domain = {};
	var accessKeyID = {};
	var accessKeySecret = {};
	var accessToken = {};
	var expiredTime = {};
	var startTime = {};
	var folder = {};
	var extList = {};
	var tip;
	var client;
	$.phpok_tencentcos_gateway = {
		file_id:function(identifier)
		{
			fileId = identifier;
			return fileId;
		},
		cate_id:function(val)
		{
			if(val && val != 'undefined'){
				cateId[fileId] = val;
			}
			return cateId[fileId];
		},
		account:function(val)
		{
			if(val && val != 'undefined'){
				account[fileId] = val;
			}
			return account[fileId];
		},
		region:function(val)
		{
			if(val && val != 'undefined'){
				region[fileId] = val;
			}
			return region[fileId];
		},
		bucket:function(val)
		{
			if(val && val != 'undefined'){
				bucket[fileId] = val;
			}
			return bucket[fileId];
		},
		multi:function(val)
		{
			if(val && val != 'undefined'){
				multi[fileId] = true;
			}else{
				multi[fileId] = false;
			}
			return multi[fileId];
		},
		gateway:function(val)
		{
			if(val && val != 'undefined'){
				gateway[fileId] = val;
			}
			return gateway[fileId];
		},
		refresh:function(val){
			if(val && val != 'undefined'){
				refresh[fileId] = val;
			}
			return refresh[fileId];
		},
		domain:function(val){
			if(val && val != 'undefined'){
				domain[fileId] = val;
			}
			return domain[fileId];
		},
		folder:function(val){
			if(val && val != 'undefined'){
				folder[fileId] = val;
			}
			return folder[fileId];
		},
		extList:function(val){
			if(val && val != 'undefined'){
				extList[fileId] = val;
			}
			return extList[fileId];
		},
		extCheck:function(val)
		{
			var c = extList[fileId];
			if(!c || c == 'undefined'){
				c = "jpg,gif,jpeg";
			}
			if(c.indexOf(val)>-1){
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
		act:function(obj,id)
		{
			var self = this;
			this.file_id(id);//
			uploader[id] = this.tencentCOS(id);
			for(var i=0;i<obj.files.length;i++){
				var tmpext = this.ext(obj.files[i].name);
				if(!tmpext){
					continue;
				}
				if(!this.extCheck(tmpext)){
					$.dialog.tips("附件格式 "+tmpext+" 不支持，已忽略");
					continue;
				}
				var myname = obj.files[i].name;
				var timestamp = (new Date()).valueOf();
				var tmpname = folder[id]+''+$.phpok.rand(10,'fixed')+(timestamp.toString())+"."+tmpext;
				var tmpfile = obj.files[i];
				try{
					uploader[id].sliceUploadFile({
						"Bucket": bucket[id], /* 填入您自己的存储桶，必须字段 */
						"Region": region[id],  /* 存储桶所在地域，例如ap-beijing，必须字段 */
						"Key": tmpname,  /* 存储在桶里的对象键（例如1.jpg，a/b/test.txt），必须字段 */
						"Body": tmpfile, // 上传文件对象
						"x-cos-meta-title":$.str.encode(myname),
						"SliceSize": 1024 * 100,
						"onTaskReady":function(taskId){
							self.addFileSuccess(tmpfile);
						},
						"onProgress": function(progressData) {
							self.progress(tmpfile,progressData.percent);
						}
					}, function(err, data) {
						console.log(data);
						var tmp = (data.Location).split(folder[id]);
						if(err){
							self.uploadFail(tmpfile,data);
						}else{
							self.uploadSuccess(data.Key,tmpfile);
						}
					});
				} catch(error){
					console.log(error);
				}
			}
		},
		get_sts:function(id)
		{
			var self = this;
			var url = api_url('gateway','index','id='+gateway[id]+"&file=role");
			$.phpok.json(url,function(rs){
				if(!rs || !rs.status){
					return false;
				}
				accessKeyID[id] = rs.info.credentials.tmpSecretId;
				accessKeySecret[id] = rs.info.credentials.tmpSecretKey;
				accessToken[id] = rs.info.credentials.sessionToken;
				startTime[id] = rs.info.startTime;
				expiredTime[id] = rs.info.expiredTime;
			});
		},
		progress:function (file, percent) {
			var size = this.fsize(file.size);
			var $li = $('#phpok-upfile-'+file.id),
			$percent = $li.find('.progress span');
			var width = $li.find('.progress').width();
			$percent.css( 'width', parseInt(width * percent, 10) + 'px' );
			$li.find('span.status').html(p_lang('文件大小：'+size+'，正在上传…'));
		},
		uploadSuccess:function(res,file)
		{
			console.log(res);
			$("input[type=file]").val('');
			$('#phpok-upfile-'+file.id).find('span.status').html(p_lang('上传成功'));
			$("#phpok-upfile-"+file.id).fadeOut();
			var url = api_url('gateway','index','id='+gateway[fileId]+"&file=success&cate_id="+cateId[fileId]+"&filename="+$.str.encode(res)+"&title="+$.str.encode(file.name));
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info).lock();
					$("#phpok-upfile-"+file.id).remove();
					return false;
				}
				if(multi[fileId]){
					var t = $("#"+fileId).val();
					var v = t ? t+","+rs.info.id : rs.info.id;
					$("#"+fileId).val(v);
					//执行
					var d = $.phpok.data("upload-"+fileId);
					if(d){
						d += ","+rs.info.id;
					}else{
						d = rs.info.id;
					}
					$.phpok.data("upload-"+fileId,d);
				}else{
					var t = $("#"+fileId).val();
					if(t){
						$.phpokform.upload_delete(fileId,t);
					}
					$("#"+fileId).val(rs.info.id);
					$.phpok.data("upload-"+fileId,rs.info.id);
				}
				if(refresh[fileId]){
					$.phpok.reload();
					return false;
				}
				$("#phpok-upfile-"+file.id).remove();
				$.phpokform.upload_showhtml(fileId,multi[fileId]);
			});
		},
		//文件添加成功后
		addFileSuccess:function(file)
		{
			var size = this.fsize(file.size);
			$("#"+fileId+"_progress").show().append('<div id="phpok-upfile-' + file.id + '" class="phpok-upfile-list">' +
				'<div class="title">' + file.name + ' <span class="status">'+p_lang('文件大小：'+size+'，等待上传…')+'</span></div>' +
				'<div class="progress"><span>&nbsp;</span></div>' +
				'<div class="cancel" id="phpok-upfile-cancel-'+file.id+'"></div>' +
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
		uploadFail:function(file,err)
		{
			$('#phpok-upfile-'+file.id).find('span.status').html(p_lang('上传失败'));
			$("#phpok-upfile-"+file.id).fadeOut().remove();
		},
		tencentCOS:function(id)
		{
			this.file_id(id);
			var cos = new COS({
				getAuthorization: function (options, callback) {
					callback({
						TmpSecretId: accessKeyID[id],
						TmpSecretKey: accessKeySecret[id],
						SecurityToken: accessToken[id],
						StartTime: startTime[id], // 时间戳，单位秒，如：1580000000
						ExpiredTime: expiredTime[id], // 时间戳，单位秒，如：1580000000
				  });
				}
			});
			return cos;
		}
	}
})(jQuery);