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
		www_makefile:function(ctx,file,key,obj)
		{
			var self = this;
			var b = ctx.join(",");
			$.ajax({
				type: 'POST',
				url: self.opts.server + '/mkfile/' + file.size + '/key/' + self.URLSafeBase64Encode(key),
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
		},
		makefile:function(ctx,file,key)
		{
			var self = this;
			var b = ctx.join(",");
			$.ajax({
				type: 'POST',
				url: self.opts.server + '/mkfile/' + file.size + '/key/' + self.URLSafeBase64Encode(key),
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
		},
		www_complete:function(file,res,obj,refresh)
		{
			$("input[type=file]").val('');
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
				if(refresh && refresh != 'undefined'){
					$.phpok.reload();
					return true;
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
		upload_complete:function(file,res,refresh)
		{
			$("input[type=file]").val('');
			var self = this;
			var url = api_url('gateway','index','id='+this.opts.gateway_id+"&file=success");
			$.phpok.json(url,function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
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
				if(refresh && refresh != 'undefined'){
					$.phpok.reload();
					return true;
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
		},

		URLSafeBase64Encode:function(data) {
			console.log(data)
			var self = this;
			var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
			var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
				ac = 0,
				enc = '',
				tmp_arr = [];

			if (!data) {
				return data;
			}

			data = self.utf8_encode(data + '');

			do { // pack three octets into four hexets
				o1 = data.charCodeAt(i++);
				o2 = data.charCodeAt(i++);
				o3 = data.charCodeAt(i++);

				bits = o1 << 16 | o2 << 8 | o3;

				h1 = bits >> 18 & 0x3f;
				h2 = bits >> 12 & 0x3f;
				h3 = bits >> 6 & 0x3f;
				h4 = bits & 0x3f;

				// use hexets to index into b64, and append result to encoded string
				tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
			} while (i < data.length);

			enc = tmp_arr.join('');

			switch (data.length % 3) {
				case 1:
					enc = enc.slice(0, -2) + '==';
					break;
				case 2:
					enc = enc.slice(0, -1) + '=';
					break;
			}

			return enc.replace(/\//g, '_').replace(/\+/g, '-');
		},

		utf8_encode:function(argString) {

			if (argString === null || typeof argString === 'undefined') {
				return '';
			}

			var string = (argString + ''); // .replace(/\r\n/g, '\n').replace(/\r/g, '\n');
			var utftext = '',
				start, end, stringl = 0;

			start = end = 0;
			stringl = string.length;
			for (var n = 0; n < stringl; n++) {
				var c1 = string.charCodeAt(n);
				var enc = null;

				if (c1 < 128) {
					end++;
				} else if (c1 > 127 && c1 < 2048) {
					enc = String.fromCharCode(
						(c1 >> 6) | 192, (c1 & 63) | 128
					);
				} else if (c1 & 0xF800 ^ 0xD800 > 0) {
					enc = String.fromCharCode(
						(c1 >> 12) | 224, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
					);
				} else { // surrogate pairs
					if (c1 & 0xFC00 ^ 0xD800 > 0) {
						throw new RangeError('Unmatched trail surrogate at ' + n);
					}
					var c2 = string.charCodeAt(++n);
					if (c2 & 0xFC00 ^ 0xDC00 > 0) {
						throw new RangeError('Unmatched lead surrogate at ' + (n - 1));
					}
					c1 = ((c1 & 0x3FF) << 10) + (c2 & 0x3FF) + 0x10000;
					enc = String.fromCharCode(
						(c1 >> 18) | 240, ((c1 >> 12) & 63) | 128, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
					);
				}
				if (enc !== null) {
					if (end > start) {
						utftext += string.slice(start, end);
					}
					utftext += enc;
					start = end = n + 1;
				}
			}

			if (end > start) {
				utftext += string.slice(start, stringl);
			}

			return utftext;
		}
	}
})(jQuery);