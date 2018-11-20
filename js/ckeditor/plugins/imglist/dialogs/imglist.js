/**
 * 图库JS
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年10月12日
**/
CKEDITOR.dialog.add('imglist', function(editor){
    var escape = function(value){
        return value;
    };
    return {
        title: '附件',
        resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
        minWidth: 300,
        minHeight: 80,
        contents: [{
            id: 'cb',
            name: 'cb',
            label: 'cb',
            title: 'cb',
            elements: [{
                type: 'text',
                label: '请输入日期控件名称',
                id: 'lang',
                required: true,
            },{
                type:'html',
                html:'<span>说明：日历控件选择的日期、时间将回填到该输入框中。</span>'
            }]
        }],
        onOk: function(){
            lang = this.getValueOf('cb', 'lang');
            editor.insertHtml("<p>" + lang + "</p>");
        },
        onLoad: function(){
        }
    };
});