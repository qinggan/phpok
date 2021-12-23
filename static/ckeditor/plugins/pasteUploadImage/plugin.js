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
			editor.on('paste', function(event) {
				var dataTransfer = event.data.dataTransfer;
				var filesCount = dataTransfer.getFilesCount();
				var oldUrl = event.data.dataValue;
				var urls = uniq(oldUrl.match(/(?<=img.*?[\s]src=")[^"]+(?=")/gi));
				if(urls && urls.length>0){
					for(var i =0;i<urls.length;i++){
						var obj = parseUrl(urls[i]);
						if(obj.protocol != 'https' && obj.protocol != 'http'){
							continue;
						}
						if(ignoreDomain.indexOf(obj.host)>-1){
							continue;
						}
						if(remoteDomain != '*' && remoteDomain.indexOf(obj.host) < 0){
							continue;
						}
						modal(urls[i]);
						uploadImageUrl(urls[i]);
					}
				}
			});

			function uploadImageUrl(oldUrl) {
				var formData = new FormData();
				formData.append('url',oldUrl);
				var option = {
					url:imgUpload,
					data:formData
				}
				ajaxPost(option).then(function(rs) {
					image = null;
					if(rs.status && rs.info){
						updateEditorVal(oldUrl, rs.info);
						updateModal(oldUrl,true);
						return;
					}
				}).catch(function() {
					updateModal(oldUrl, false);
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

			function modal(filename) {
				var html =
					'<div class="modal-editor-upload" filename="{{filename}}" style="margin-bottom: 5px;border-bottom: 1px solid #ddd;padding-bottom: 5px;">' +
					'<img style="width:40px;height:40px;vertical-align: middle;" src="{{filename}}"/>' +
					'<label style="color:green;"> uploading...</label>' +
					'</div>';
				html = html.replace(/\{\{(.+?)\}\}/g, filename);
				var wrapper = document.querySelector('.modal-editor-upload-wrapper');
				if (!wrapper) {
					var wrapper = document.createElement('div');
					wrapper.className = 'modal-editor-upload-wrapper';
					wrapper.style.cssText = 'width:200px;background-color:#fdfdfd;position:absolute;right: 30px;top: 100px;'
					wrapper.innerHTML = html;
					var edi = document.getElementById('cke_' + editor.name);
					edi.appendChild(wrapper);
					edi.style.position = 'relative';
				} else {
					wrapper.innerHTML += html;
				}
			}

			function updateModal(filename, result) {
				filename = filename.replace(/&amp;/g, '&');
				var selector = 'div.modal-editor-upload[filename="' + filename + '"]';
				var content = document.querySelector(selector);
				var label = content.querySelector('label');
				if (result === 'request time out') {
					label.innerHTML = ' ' + result;
					label.style.color = 'red';
				} else if (result === true) {
					label.innerHTML = ' success';
					label.style.color = 'green';
				} else {
					label.innerHTML = ' failure';
					label.style.color = 'red';
				}
				var time = result === true ? 3000 : 10000;
				var t = setTimeout(function() {
					var c = document.querySelector(selector);
					document.querySelector('.modal-editor-upload-wrapper').removeChild(c);
					clearTimeout(t);
				}, time);
			}

			// 更新
			function updateEditorVal(oldUrl, newUrl, isCreateImage) {
				var data = editor.getData();
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