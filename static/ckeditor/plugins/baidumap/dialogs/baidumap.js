CKEDITOR.dialog.add("baidumap",function(editor){
	var $el = CKEDITOR.dom.element,
		$doc = CKEDITOR.document,
		lang = editor.lang.baidumap,
		plugin = CKEDITOR.plugins.baidumap;

	var dialog, win, doc;

	var iframe = CKEDITOR.dom.element.createFromHtml('<iframe frameborder="0" src="' + plugin.path + 'map.html" style="width: 558px;height: 360px;"></iframe>');
	function ready() {
		win = iframe.$.contentWindow;
		doc = iframe.getFrameDocument();//element
	}
	iframe.on("load", function() {
		if (CKEDITOR.env.ie) {
			ready();
		} else {
			setTimeout(ready, 0);
		}
	});

	return{
		title: lang.title,
		resizable : false,
		minWidth: 558,
		minHeight: 360,
		onShow:function(){
		},
		onLoad:function(){
			dialog = this;
			this.setupContent();
		},
		onOk:function(){
			var isInsertDynamicMap = dialog.getValueOf('info', 'insertDynamicMap')
			var map = win.map;
			var centerObj = map.getCenter();
			var center = centerObj.lng + ',' + centerObj.lat;
			var zoom = map.getZoom();
			var mapWidth = 558
			var mapHeight = 360;

			var url = [isInsertDynamicMap ? plugin.path + 'index.html' : 'http://api.map.baidu.com/staticimage',
				'?center=' + encodeURIComponent(center),
				'&zoom=' + encodeURIComponent(zoom),
				'&width=' + mapWidth,
				'&height=' + mapHeight,
				'&markers=' + encodeURIComponent(center),
				'&markerStyles=' + encodeURIComponent('l,A')].join('');
			if (isInsertDynamicMap) {
				var iUrl = '<iframe src="' + url + '" frameborder="0" style="width:' + (mapWidth + 2) + 'px;height:' + (mapHeight + 2) + 'px;"></iframe>';
				var html = '<img class="cke_iframe" data-cke-realelement="'+encodeURIComponent(iUrl)+'" src="data:image/gif;base64,R0lGODlhAQABAPABAP///wAAACH5BAEKAAAALAAAAAABAAEAAAICRAEAOw==" data-cke-real-element-type="iframe" data-cke-resizable="true" style="width:' + (mapWidth + 2) + 'px;height:' + (mapHeight + 2) + 'px;">';
				editor.insertHtml(html);
			} else {
				editor.insertHtml('<img src="' + url + '" alt="Map" />');
			}
		},
		contents:[
			{
				id:"info",
				name:'info',
				padding:10,
				label:lang.commonTab,
				elements:[{
					type:'vbox',
					padding:0,
					children:[
				 	{
				 		type: 'hbox',
				 		widths: [ '360px', '60px' ,"80px"],
				 		padding:10,
				 		children: [
				 			{
				    			type:'text',
				    			label:'请输入地址信息',
				      			id: 'map_address',
				      			placeholder:'请输入地址信息'
				    		},
				    		{
				    			type: 'button',
				    			label : '搜索',
				    			onClick: function() {
				    				win.search(dialog.getValueOf('info', 'map_address'));
				    			}
				    		},
				    		{
								id : 'insertDynamicMap',
								type : 'checkbox',
								labelLayout : 'horizontal',
								width: '120px',
								label : "动态地图"
							},
				 		]
				 	},
				 	{
						type: 'html',
						html: '<div></div>',
						onLoad: function() {
							CKEDITOR.document.getById(this.domId).append(iframe);
						}
					}
					]
				}]
			}
		]
	};
});



