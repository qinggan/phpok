/**
 * 微信公众号信息设置
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年11月16日
**/
function clear_user_data()
{
	$.dialog.confirm('确定要清理数据吗？请仅在测试时使用',function(){
		var url = get_url('wxappconfig','clear');
		$.phpok.json(url,function(rs){
			if(rs.status){
				$.dialog.alert('数据清理完成',true,'succeed');
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		});
	});
	
}
$(document).ready(function(){
	$("#post_save").submit(function(){
		$(this).ajaxSubmit({
			'url':get_url('wxappconfig','save'),
			'type':'post',
			'dataType':'json',
			'success':function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('参数保存成功操作成功')).lock();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			}
		});
		return false;
	});
});