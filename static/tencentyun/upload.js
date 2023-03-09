/**
 * 腾迅云点播
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
	var tMulti = {};
	var tGateway = {};
	var tVtype = {};
	var tExt = {};
	var tRefresh = {};
	var Tip;
	$.phpok_tencentvod_gateway = {
		file_id:function(identifier)
		{
			fileId = identifier;
		},
		cate_id:function(val)
		{
			cateId[fileId] = val;
		},
		multi:function(val)
		{
			if(val && val != 'undefined'){
				tMulti[fileId] = true;
			}else{
				tMulti[fileId] = false;
			}
		},
		gateway:function(val)
		{
			if(val && val != 'undefined'){
				tGateway[fileId] = val;
			}
		},
		vtype:function(val){
			if(val && val != 'undefined'){
				tVtype[fileId] = val;
			}
		},
		ext:function(val){
			if(val && val != 'undefined'){
				tExt[fileId] = val;
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
			var tmp = title.split('.');
			var ext = tmp[(tmp.length-1)];
			if(!ext){
				$.dialog.alert('未找到附件类型');
				return false;
			}
			ext = ext.toLowerCase();
			var elist = tExt[id].split(',');
			var ext_check = false;
			for(var i in elist){
				if(elist[i].toLowerCase() == ext){
					ext_check = true;
					break;
				}
			}
			if(!ext_check){
				$.dialog.alert('附件不支持 <span style="color:red">'+ext+'</span> 格式上传');
				return false;
			}
			this.file_id(id);//
			var url = api_url('gateway','index','id='+tGateway[id]+'&file=signature');
			const tcVod = new TcVod.default({
				getSignature: function(){
					var info = $.phpok.json(url);
					if(!info){
						$.dialog.alert('签名获取异常');
						return false;
					}
					if(!info.status){
						$.dialog.alert(info.info);
						return false;
					}
					return info.info;
				}
			});

			const uploader = tcVod.upload({
				mediaFile: obj.files[0], // 媒体文件（视频或音频或图片），类型为 File
			});
			uploader.on('media_progress', function(info) {
				var size = 0;
				totalSize = parseInt(info.total);
				if(totalSize>= 1073741824){
					size = ((totalSize/1073741824).toFixed(2)).toString() + 'GB';
				}else if(totalSize < 1073741824 && totalSize >= 1048576){
					size = ((totalSize/1048576).toFixed(2)).toString() + 'MB';
				}else if(totalSize < 1048576 && totalSize >= 1024){
					size = ((totalSize/1024).toFixed(2)).toString() + 'KB';
				}else{
					size = (totalSize).toString() + 'B';
				}
				$("#"+id+"_progress").show().html('正在上传：'+title+'，文件大小：'+size+'，已上传：'+ Math.ceil(info.percent * 100)+'%');
			});
			uploader.done().then(function (doneResult) {
				Tip = $.dialog.tips(p_lang('视频已成功上传到腾迅云VOD平台，请稍候…'),100).lock();
				$("#file_"+id).val('');
				console.log(doneResult);
				$.phpok.json(api_url('gateway','index'),function(rs){
					if(!rs.status){
						Tip.content(rs.info).time(2);
						return false;
					}
					Tip.content('附件上传成功').time(1.5);
					if(tMulti[id]){
						var t = $("#"+id).val();
						var v = t ? t+","+rs.info.id : rs.info.id;
						$("#"+id).val(v);
						//执行
						var d = $.phpok.data("upload-"+id);
						if(d){
							d += ","+rs.info.id;
						}else{
							d = rs.info;
						}
						$.phpok.data("upload-"+fileId,d);
					}else{
						var t = $("#"+id).val();
						if(t){
							$.phpokform.upload_delete(id,t);
						}
						$("#"+id).val(rs.info.id);
						$.phpok.data("upload-"+id,rs.info.id);
					}
					if(tRefresh[id]){
						$.phpok.reload();
						return false;
					}
					$.phpokform.upload_showhtml(id,tMulti[id]);
				},{
					'id':tGateway[id],
					'file':'success',
					'cate_id':cateId[id],
					'file_id':doneResult.fileId
				});
			}).catch(function (err) {
				console.log(err);
				$.dialog.alert('上传文件：'+title+' 失败<br/>错误代码：'+err.Code+"<br/>提示内容："+err.Message);
				$("#file_"+id).val('');
				return true;
			})

		}
	}
})(jQuery);