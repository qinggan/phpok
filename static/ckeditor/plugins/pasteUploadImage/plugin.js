(function() {
	var CKEDITOR_NAME = 'pasteUploadImage';
	CKEDITOR.plugins.add(CKEDITOR_NAME, {
		init: function(editor) {
			var config = editor.config;
			var remoteDomain = config.imgToLocalRemoteDomain ? config.imgToLocalRemoteDomain : '*';
			var ignoreDomain = config.imgToLocalIgnoreDomain ? config.imgToLocalIgnoreDomain : 'localhost,127.0.0.1,::1';
			var imgUpload = config.imgToLocalUpload;
			var notSupportText = '浏览器不支持'
			if (!imgUpload) {
				CKEDITOR.error('您必须配置参数 imgToLocalUpload，全局参数是：config.imgToLocalUpload');
				return;
			}
			var path = this.path;
			editor.on('paste', function(event) {
				var dataTransfer = event.data.dataTransfer;
				var filesCount = dataTransfer.getFilesCount();
				var oldUrl = event.data.dataValue;
				var urls = uniq(oldUrl.match(/(?<=img.*?[\s]src=")[^"]+(?=")/gi));
				if(urls && urls.length>0){
					var tmplist = new Array();
					for(var i=0;i<urls.length;i++){
						var obj = parseUrl(urls[i]);
						var tmp = (urls[i]).substr(0,11);
						if(tmp == 'data:image/'){
							tmplist.push(urls[i]);
							continue;
						}
						if(obj.protocol != 'https' && obj.protocol != 'http'){
							continue;
						}
						if(ignoreDomain.indexOf(obj.host)>-1){
							continue;
						}
						if(remoteDomain != '*' && remoteDomain.indexOf(obj.host) < 0){
							continue;
						}
						tmplist.push(urls[i]);
						continue;
					}
					if(tmplist && tmplist.length>0){
						tips_start("检测到需要上传的文件数："+(tmplist.length).toString()+'，请稍候…');
						start_loadData(tmplist,0);
					}
				}
			});

			function start_loadData(urls,i)
			{
				var obj = parseUrl(urls[i]);
				var tmp = (urls[i]).substr(0,11);
				var txt = ' 总文件数 '+(urls.length).toString()+'，当前第 '+(i+1)+' 条正在上传，请稍候…';
				tips_update(txt);
				if(tmp == 'data:image/'){
					uploadImageBase64(urls,i);
					return true;
				}
				uploadImageUrl(urls,i);
				return true;
			}

			function uploadImageBase64(urls,i)
			{
				var formData = new FormData();
				formData.append('data',urls[i]);
				var option = {
					url:imgUpload,
					data:formData
				}
				ajaxPost(option).then(function(rs) {
					image = null;
					if(rs.status && rs.info){
						updateEditorVal(urls[i], rs.info);
						//上传图片成功
						goto_next(urls,i,true);
						return;
					}
					var tip_info = rs.info ? rs.info : '上传失败';
					tips_update(tip_info);
					goto_next(urls,i,false);
				}).catch(function() {
					//上传图片失败
					goto_next(urls,i, false);
				});
			}

			function getBase64Image(img) {
				var canvas = document.createElement("canvas");
				canvas.width = img.width;
				canvas.height = img.height;
				var ctx = canvas.getContext("2d");
				ctx.drawImage(img, 0, 0, img.width, img.height);
				var dataURL = canvas.toDataURL("image/png");
				return dataURL
			}

			//傻了，漏掉了跨域问题
			function uploadImageUrl(urls,i) {
				var oldUrl = urls[i];
				var formData = new FormData();
				formData.append('url',oldUrl);
				var option = {
					url:imgUpload,
					data:formData
				}
				ajaxPost(option).then(function(rs) {
					image = null;
					if(rs.status && rs.info){
						updateEditorVal(urls[i], rs.info);
						goto_next(urls,i,true);
						return;
					}
					var tip_info = rs.info ? rs.info : '上传失败';
					tips_update(tip_info);
					goto_next(urls,i,false);
				}).catch(function() {
					goto_next(urls,i, false);
				});
			}

			function parseUrl(url) {
				var urlObj = {
					protocol: /^(.+)\:\/\//,
					host: /\:\/\/(.+?)[\?\#\s\/]/,
					path: /\w(\/.*?)[\?\#\s]/,
					query: /\?(.+?)[\#\/\s]/,
					hash: /\#(\w+)\s$/
				}
				url += ' ';

				function formatQuery(str) {
					return str.split('&').reduce((a, b) => {
						let arr = b.split('=')
						a[arr[0]] = arr[1]
						return a
					}, {})
				}
				for (var key in urlObj) {
					let pattern = urlObj[key]
					urlObj[key] = key === 'query' ? (pattern.exec(url) && formatQuery(pattern.exec(url)[1])) : (pattern.exec(url) && pattern.exec(url)[1])
				}
				return urlObj
            }

			function ajaxPost(option) {
				var timeout = 10000;
				var xhr = new XMLHttpRequest();
				var p = new Promise(function(resolve, reject) {
					option = option || {};
					xhr.open('post', option.url);
					xhr.send(option.data);
					xhr.onreadystatechange = function() {
						if (xhr.readyState === 4 && xhr.status == 200) {
							var text = xhr.responseText || '{}';
							var data = JSON.parse(text);
							resolve(data);
							xhr = null;
						} else if (xhr.readyState === 4 && xhr.status !== 200) {
							reject();
							xhr = null;
						}
					}
				});
				var t = new Promise(function(resolve) {
					var t = setTimeout(function() {
						if (xhr) {
							xhr && xhr.abort();
							resolve('request time out');
							clearTimeout(t);
						}
					}, timeout);
				});
				return Promise.race([p, t]);
			}

			function tips_start(txt,color)
			{
				if(!txt || txt == 'undefined'){
					txt = '正在执行操作，请稍候…';
				}
				if(!color || color == 'undefined'){
					color = 'green';
				}
				var html =
					'<div class="modal-editor-upload" style="margin-bottom: 5px;padding-bottom: 5px;">' +
					'<img style="width:32px;height:32px;vertical-align: middle;" src="'+path + 'images/upload.png" />' +
					'<label style="color:'+color+';"> '+txt+' </label>' +
					'</div>';
				var wrapper = document.querySelector('.modal-editor-upload-wrapper');
				if (!wrapper) {
					var wrapper = document.createElement('div');
					wrapper.className = 'modal-editor-upload-wrapper';
					wrapper.style.cssText = 'width:auto;background-color:#fdfdfd;position:fixed;right: 30px;top: 100px;'
					wrapper.innerHTML = html;
					var edi = document.getElementById('cke_' + editor.name);
					edi.appendChild(wrapper);
					edi.style.position = 'relative';
				} else {
					wrapper.innerHTML += html;
				}
			}

			function tips_update(txt,color)
			{
				if(!txt || txt == 'undefined'){
					txt = '正在执行操作，请稍候…';
				}
				if(!color || color == 'undefined'){
					color = 'green';
				}
				var selector = 'div.modal-editor-upload';
				var content = document.querySelector(selector);
				var label = content.querySelector('label');
				label.innerHTML = txt;
				label.style.color = color;
			}

			function goto_next(urls,i , result) {
				var txt = '';
				var color = ''
				if (result === 'request time out') {
					txt = ' 总文件数 '+(urls.length).toString()+'，当前第 '+(i+1)+' 条上传超时，';
					color = 'red';
				} else if (result === 'notupload'){
					txt = ' 总文件数 '+(urls.length).toString()+'，当前第 '+(i+1)+' 条上传忽略，';
					color = 'darkgreen';
				} else if (result === true) {
					txt = ' 总文件数 '+(urls.length).toString()+'，当前第 '+(i+1)+' 条上传成功，';
					color = 'green';
				} else {
					txt = ' 总文件数 '+(urls.length).toString()+'，当前第 '+(i+1)+' 条上传失败，';
					color = 'red';
				}
				var next_i = i+1;
				if(urls[next_i] && urls[next_i] != 'undefined'){
					txt += '即将上传下一条文件，请稍候…';
					tips_update(txt,color);
					setTimeout(function(){
						start_loadData(urls,next_i);
					}, 500);
					return true;
				}
				txt += '文件已全部操作完成，请稍候…';
				tips_update(txt,color);
				//0.5秒后关闭提示框
				setTimeout(function(){
					var c = document.querySelector('div.modal-editor-upload');
					document.querySelector('.modal-editor-upload-wrapper').removeChild(c);
				}, 500);
			}

			// 更新
			function updateEditorVal(oldUrl, newUrl, isCreateImage) {
				var data = editor.getData();
				var bookmarks = editor.getSelection().createBookmarks2();//当前光标
				if (isCreateImage) {
					if (!oldUrl) {
						data = data + '<p><img src="' + newUrl + '"/></p>';
					} else {
						data = data.replace(oldUrl, '<img src="' + newUrl + '"/>');
					}
				} else {
					data = replaceAll(data, oldUrl, newUrl);
				}
				editor.setData(data);
				editor.getSelection().selectBookmarks(bookmarks);
			}

			function uniq(arr) {
				arr = arr || [];
				var list = [];
				for (var i = 0; i < arr.length; i++) {
					if (list.indexOf(arr[i]) < 0) {
						list.push(arr[i]);
					}
				}
				return list;
			}

			function escapeRegExp(str) {
				return str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
			}

			function replaceAll(str, find, replace) {
				return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
			}
		}
	});
})();