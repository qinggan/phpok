/**
 * 七牛上传组件中涉及到的JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月19日
**/
;(function($){
	$.phpok_qiniu_gateway = {
		config:function(opts)
		{
			var self = this;
			var defaults = {
				'server':'index.php',
				'token':false,
				'gateway_id':0,
				'cate_id':0,
				'multiple':false,
				'fid':'thumb',
				'link':''
			};
			this.opts = $.extend({},defaults,opts);
		},
		www_makefile:function(ctx,file,hash,obj)
		{
			var self = this;
			var b = ctx.join(",");
			if(hash){
				$.ajax({
					type: 'POST',
					url: self.opts.server + '/mkfile/' + file.size,
					data: b,
					contentType: "text/plain",
					contentLength: b.length,
					beforeSend: function (XMLHttpRequest) {
						XMLHttpRequest.setRequestHeader("Authorization", 'UpToken ' + self.opts.token);
					},
					success: function(res){
						self.www_complete(file, res,obj);
					}
				});
			} else {
				$.ajax({
					type: 'POST',
					url: self.opts.server + '/mkfile/' + file.size + '/key/' + $.str.encode(file.name),
					data: b,
					contentType: "text/plain",
					contentLength: b.length,
					beforeSend: function (XMLHttpRequest) {
						XMLHttpRequest.setRequestHeader("Authorization", 'UpToken ' + self.opts.token);
					},
					success: function(res){
						self.www_complete(file, res,obj);
					}
				});
			}
		},
		makefile:function(ctx,file,hash)
		{
			var self = this;
			var b = ctx.join(",");
			if(hash){
				$.ajax({
					type: 'POST',
					url: self.opts.server + '/mkfile/' + file.size,
					data: b,
					contentType: "text/plain",
					contentLength: b.length,
					beforeSend: function (XMLHttpRequest) {
						XMLHttpRequest.setRequestHeader("Authorization", 'UpToken ' + self.opts.token);
					},
					success: function(res){
						self.upload_complete(file, res);
					}
				});
			} else {
				$.ajax({
					type: 'POST',
					url: self.opts.server + '/mkfile/' + file.size + '/key/' + $.str.encode(file.name),
					data: b,
					contentType: "text/plain",
					contentLength: b.length,
					beforeSend: function (XMLHttpRequest) {
						XMLHttpRequest.setRequestHeader("Authorization", 'UpToken ' + self.opts.token);
					},
					success: function(res){
						self.upload_complete(file, res);
					}
				});
			}
		},
		www_complete:function(file,res,obj)
		{
			var self = this;
			var url = api_url('gateway','index','id='+this.opts.gateway_id+"&file=success");
			$.phpok.json(url,function(rs){
				if(!rs.status){
					//登记失败，删除远程
					$.dialog.alert(rs.info);
					return false;
				}
				var tmp = $.phpok.data('upload-'+self.opts.fid);
				if(self.opts.multiple){
					var val = $('#'+self.opts.fid).val();
					if(val){
						val += ","+rs.info.id;
					}else{
						val = rs.info.id;
					}
					$('#'+self.opts.fid).val(val);
					if(tmp){
						tmp += ','+rs.info.id;
					}else{
						tmp = rs.info.id;
					}
				}else{
					if(tmp){
						$.phpokform.upload_remote_delete(self.opts.fid,tmp);
					}
					tmp = rs.info.id;
					$('#'+self.opts.fid).val(rs.info.id);
				}
				$.phpok.data('upload-'+self.opts.fid,tmp);
				obj.showhtml();
			},{
				'ext':file.ext,
				'filename':this.opts.link+"/"+res.key,
				'hash':res.hash,
				'name':res.key,
				'title':file.name,
				'cate_id':this.opts.cate_id
			});
		},
		upload_complete:function(file,res)
		{
			var self = this;
			var url = api_url('gateway','index','id='+this.opts.gateway_id+"&file=success");
			$.phpok.json(url,function(rs){
				if(!rs.status){
					//登记失败，删除远程
					$.dialog.alert(rs.info);
					return false;
				}
				var tmp = $.phpok.data('upload-'+self.opts.fid);
				if(self.opts.multiple){
					var val = $('#'+self.opts.fid).val();
					if(val){
						val += ","+rs.info.id;
					}else{
						val = rs.info.id;
					}
					$('#'+self.opts.fid).val(val);
					if(tmp){
						tmp += ','+rs.info.id;
					}else{
						tmp = rs.info.id;
					}
				}else{
					if(tmp){
						$.phpokform.upload_remote_delete(self.opts.fid,tmp);
					}
					tmp = rs.info.id;
					$('#'+self.opts.fid).val(rs.info.id);
				}
				$.phpok.data('upload-'+self.opts.fid,tmp);
				$.phpokform.upload_showhtml(self.opts.fid,self.opts.multiple);
			},{
				'ext':file.ext,
				'filename':this.opts.link+"/"+res.key,
				'hash':res.hash,
				'name':res.key,
				'title':file.name,
				'cate_id':this.opts.cate_id
			});
		}
	}
})(jQuery);