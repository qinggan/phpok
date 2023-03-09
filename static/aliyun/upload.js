/**
 * 阿里云点播上传
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年5月21日
**/
;(function($){
	var uploadAuth = {};
	var uploadAddress = {};
	var videoId = {};
	var fileId = '';
	var cateId = {};
	var uploader = {};
	var aliyunAccount = {};//阿里云账号
	var aliyunRegoin = {};
	var aliyunStorage = {};
	var aliyunMulti = {};
	var aliyunGateway = {};
	var aliyunVtype = {};
	var tRefresh = {};
	var Tip;
	$.phpok_aliyunvod_gateway = {
		file_id:function(identifier)
		{
			fileId = identifier;
		},
		cate_id:function(val)
		{
			cateId[fileId] = val;
		},
		account:function(val)
		{
			aliyunAccount[fileId] = val;
		},
		regoin:function(val)
		{
			aliyunRegoin[fileId] = val;
		},
		storage:function(val)
		{
			if(val && val != 'undefined'){
				aliyunStorage[fileId] = val;
			}
		},
		multi:function(val)
		{
			if(val && val != 'undefined'){
				aliyunMulti[fileId] = true;
			}else{
				aliyunMulti[fileId] = false;
			}
		},
		gateway:function(val)
		{
			if(val && val != 'undefined'){
				aliyunGateway[fileId] = val;
			}
		},
		vtype:function(val){
			if(val && val != 'undefined'){
				aliyunVtype[fileId] = val;
			}
		},
		refresh:function(val){
			if(val && val != 'undefined'){
				tRefresh[fileId] = val;
			}
		},
		act:function(obj,id)
		{
			this.file_id(id);//
			uploader[id] = this.aliyunVod(id);
			for(var i=0;i<obj.files.length;i++){
				uploader[id].addFile(obj.files[i], null, null, null, '{"Vod":{"Title":"'+obj.files[i].name+'"}}');
			}
			uploader[id].startUpload();
		},
		aliyunVod:function(id)
		{
			var obj = {
				'userId':aliyunAccount[id],
				'regoin':aliyunRegoin[id],
				'addFileSuccess':function(uploadInfo) {
					var file = uploadInfo.file;
					$("#"+id+"_progress").show().append('<div id="phpok-upfile-' + file.id + '" class="phpok-upfile-list">' +
						'<div class="title">' + file.name + ' <span class="status">'+p_lang('等待上传…')+'</span></div>' +
						'<div class="progress"><span>&nbsp;</span></div>' +
						'<div class="cancel" id="phpok-upfile-cancel-'+file.id+'"></div>' +
					'</div>' );
					var t = $(".phpok-upfile-list").length;
					if(t<1){
						t = 1;
					}
					var idx = t - 1;
					$("#phpok-upfile-"+file.id+" .cancel").click(function(){
						uploader[id].cancelFile(idx);
						$("#phpok-upfile-"+file.id).remove();
					});
				},
				// 文件上传失败
				'onUploadFailed': function (uploadInfo, code, message) {
					$('#phpok-upfile-'+uploadInfo.file.id).find('span.status').html(p_lang('上传失败'));
					$("#phpok-upfile-"+uploadInfo.file.id).fadeOut();
				},
				// 单个文件上传完成
				'onUploadSucceed': function (uploadInfo) {
					$("input[type=file]").val('');
					$('#phpok-upfile-'+uploadInfo.file.id).find('span.status').html(p_lang('上传成功'));
					$("#phpok-upfile-"+uploadInfo.file.id).fadeOut();
					var url = api_url('gateway','index','id='+aliyunGateway[fileId]+"&file=success&cate_id="+cateId[fileId]+"&video_id="+uploadInfo.videoId);
					$.phpok.json(url,function(rs){
						if(!rs.status){
							$.dialog.tips(rs.info).lock();
							return false;
						}
						if(aliyunMulti[fileId]){
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
						if(tRefresh[fileId]){
							$.phpok.reload();
							return false;
						}
						$.phpokform.upload_showhtml(fileId,aliyunMulti[fileId]);
					});
				},
				// 文件上传进度
				'onUploadProgress': function (uploadInfo, totalSize, progress) {
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
					var $li = $('#phpok-upfile-'+uploadInfo.file.id),
					$percent = $li.find('.progress span');
					var width = $li.find('.progress').width();
					$percent.css( 'width', parseInt(width * progress, 10) + 'px' );
					$li.find('span.status').html(p_lang('正在上传…'));
				},
				//全部文件上传完成
				onUploadEnd:function(){
					$("#file_"+fileId).val('');
					$("#"+fileId+"_progress").hide().html('');
				},
				// 开始上传
				'onUploadstarted': function (uploadInfo) {
					if (!uploadInfo.videoId){
						var title = uploadInfo.file.name;
						var url = api_url("gateway","index","file=vod_id");
						url += "&id="+aliyunGateway[fileId]+"&cate_id="+cateId[fileId];
						url += "&title="+$.str.encode(uploadInfo.file.name);
						if(uploadInfo.isImage){
							url += "&type=image";
						}
						$.phpok.json(url,function(rs){
							var tmpId = uploadInfo.isImage ? rs.info.ImageId : rs.info.VideoId;
							uploader[fileId].setUploadAuthAndAddress(uploadInfo, rs.info.UploadAuth, rs.info.UploadAddress,tmpId);
						})
					}else{
						var url = api_url("gateway","index","file=refresh_id");
						url += "&id="+aliyunGateway[fileId];
						url += "&vid="+uploadInfo.videoId;
						$.phpok.json(url,function(rs){
							var tmpId = uploadInfo.isImage ? rs.info.ImageId : rs.info.VideoId;
							uploader[fileId].setUploadAuthAndAddress(uploadInfo, rs.info.UploadAuth, rs.info.UploadAddress,uploadInfo.videoId);
						});
					}
				}
			};
			if(aliyunStorage[id] && aliyunStorage[id] != 'undefined'){
				obj['StorageLocation'] = aliyunStorage[id];
			}
			return new AliyunUpload.Vod(obj);
		}
	}
})(jQuery);