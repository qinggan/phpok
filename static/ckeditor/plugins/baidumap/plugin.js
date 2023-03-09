CKEDITOR.plugins.baidumap = {
	requires: ['dialog'],
	lang : 'en,zh-cn,zh', 
    init:function(editor) { 
		var b="baidumap";
		var c=editor.addCommand(b,new CKEDITOR.dialogCommand(b));
		c.modes={wysiwyg:1,source:0};
		c.canUndo=false;

	
		editor.ui.addButton && editor.ui.addButton("BaiduMap",{
			label:editor.lang.baidumap.title,
			command:b,
			icon:this.path+"images/map.png"
		});

		CKEDITOR.dialog.add(b,this.path+"dialogs/baidumap.js");
	}
}
CKEDITOR.plugins.add('baidumap', CKEDITOR.plugins.baidumap);
