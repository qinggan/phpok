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
			var title = obj.files[0].name;
			if(!title){
				$.dialog.alert(p_lang('请选择要上传的文件'))
				return false;
			}
			this.file_id(id);//
			if(aliyunVtype[id] == 'video'){
				uploader[id] = this.aliyunVod(id);
				uploader[id].addFile(obj.files[0], null, null, null, '{"Vod":{"Title":"'+title+'"}}');
				var url = api_url('gateway','index','id='+aliyunGateway[id]+'&file=video_id&cate_id='+cateId[id]+"&title="+$.str.encode(title));
				$.phpok.json(url,function(rs){
					uploadAuth[id] = rs.info.UploadAuth;
					uploadAddress[id] = rs.info.UploadAddress;
					videoId[id] = rs.info.VideoId;
					uploader[id].startUpload();
				});
			}else{
				uploader[id] = this.aliyunVod(id);
				uploader[id].addFile(obj.files[0], null, null, null, '{"Vod":{"Title":"'+title+'"}}');
				var url = api_url('gateway','index','id='+aliyunGateway[id]+'&file=image_id&cate_id='+cateId[id]+"&title="+$.str.encode(title));
				$.phpok.json(url,function(rs){
					uploadAuth[id] = rs.info.UploadAuth;
					uploadAddress[id] = rs.info.UploadAddress;
					videoId[id] = rs.info.ImageId;
					uploader[id].startUpload();
				});
			}
		},
		aliyunVod:function(id)
		{
			var obj = {
				'userId':aliyunAccount[id],
				'regoin':aliyunRegoin[id],
				// 文件上传失败
				'onUploadFailed': function (uploadInfo, code, message) {
					$.dialog.alert('上传文件：'+uploadInfo.file.name+' 失败<br/>错误代码：'+code+"<br/>提示内容："+message);
					$("#file_"+fileId).val('');
				},
				// 文件上传完成
				'onUploadSucceed': function (uploadInfo) {
					$("input[type=file]").val('');
					Tip = $.dialog.tips(p_lang('视频已成功上传到阿里云平台，请稍候…'),100).lock();
					//保存数据到Form表单中，存储一条记录到本地
					var url = api_url('gateway','index','id='+aliyunGateway[fileId]+"&file=success&cate_id="+cateId[fileId]+"&video_id="+videoId[fileId]);
					$.phpok.json(url,function(rs){
						if(!rs.status){
							Tip.content(rs.info).time(2);
							$("#file_"+fileId).val('');
							return false;
						}
						Tip.content('附件上传成功').time(1.5);
						if(aliyunMulti[fileId]){
							var t = $("#"+fileId).val();
							var v = t ? t+","+rs.info.id : rs.info.id;
							$("#"+fileId).val(v);
							//执行
							var d = $.phpok.data("upload-"+fileId);
							if(d){
								d += ","+rs.info.id;
							}else{
								d = rs.info;
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
					//去除客户端file
					$("#file_"+fileId).val('');
					$("#"+fileId+"_progress").hide().html('');
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
					$("#"+fileId+"_progress").show().html('正在上传：'+uploadInfo.file.name+'，文件大小：'+size+'，已上传：'+ Math.ceil(progress * 100)+'%');
				},
				// 开始上传
				'onUploadstarted': function (uploadInfo) {
					uploader[fileId].setUploadAuthAndAddress(uploadInfo, uploadAuth[fileId], uploadAddress[fileId],videoId[fileId]);
					$("#"+fileId+"_progress").show().html('正在开始上传文件：'+uploadInfo.file.name+'，请稍候…');
				}
			};
			if(aliyunStorage[id] && aliyunStorage[id] != 'undefined'){
				obj['StorageLocation'] = aliyunStorage[id];
			}
			return new AliyunUpload.Vod(obj);
		}
	}
})(jQuery);