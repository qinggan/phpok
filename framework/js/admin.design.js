/**
 * 可视化设计器
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License  https://www.phpok.com/lgpl.html
 * @时间 2021年1月5日
**/

function paddingSetting(id,obj)
{
	var list = new Array();
	list[0] = {'title':'上','name':'pt'};
	list[1] = {'title':'右','name':'pr'};
	list[2] = {'title':'下','name':'pb'};
	list[3] = {'title':'左','name':'pl'};
	var html = '<div>';
	html += '<table class="layui-table" lay-skin="nob">';
	for(var i in list){
		html += '<tr><td>'+list[i].title+'</td>';
		for(var m=0;m<6;m++){
			html += '<td><label><input type="radio" name="'+list[i].name+'" value="'+m+'" lay-ignore /> '+m+'</label></td>';
		}
		html += '</tr>';
	}
	html += '</table>';
	html += '</div>';
	$.dialog({
		"id":"classSetting",
		'title' : '内边距设置',
		'content': html,
		'follow': $(obj)[0],
		'padding':0,
		'cancel':true,
		'ok': function(){
			var pt = $("input[name=pt]:checked").val();
			var pr = $("input[name=pr]:checked").val();
			var pb = $("input[name=pb]:checked").val();
			var pl = $("input[name=pl]:checked").val();
			if(pt == pr && pt == pb && pt == pl && pt != 'undefined'){
				var input = 'p-'+pt;
			}else{
				var input = '';
				if(pt && pt != 'undefined'){
					input += ' pt-'+pt;
				}
				if(pr && pr != 'undefined'){
					input += ' pr-'+pr;
				}
				if(pb && pb != 'undefined'){
					input += ' pb-'+pb;
				}
				if(pl && pl != 'undefined'){
					input += ' pl-'+pl;
				}
				input = $.trim(input);
			}
			var old = $(id).val();
			if(old){
				old = old.replace(/p[trbl]*\-\d/g,'');
				old = $.trim(old);
				if(old){
					input = old + ' '+input;
				}
			}
			$(id).val(input);
			return true;
		}
	});
}

function marginSetting(id,obj)
{
	var list = new Array();
	list[0] = {'title':'上','name':'mt'};
	list[1] = {'title':'右','name':'mr'};
	list[2] = {'title':'下','name':'mb'};
	list[3] = {'title':'左','name':'ml'};
	var html = '<div>';
	html += '<table class="layui-table" lay-skin="nob">';
	for(var i in list){
		html += '<tr><td>'+list[i].title+'</td>';
		for(var m=0;m<6;m++){
			html += '<td><label><input type="radio" name="'+list[i].name+'" value="'+m+'" lay-ignore /> '+m+'</label></td>';
		}
		html += '</tr>';
	}
	html += '</table>';
	html += '</div>';
	$.dialog({
		"id":"classSetting",
		'title' : '外边距设置',
		'content': html,
		'follow': $(obj)[0],
		'padding':0,
		'cancel':true,
		'ok': function(){
			var mt = $("input[name=mt]:checked").val();
			var mr = $("input[name=mr]:checked").val();
			var mb = $("input[name=mb]:checked").val();
			var ml = $("input[name=ml]:checked").val();
			if(mt == mr && mt == mb && mt == ml && mt != 'undefined'){
				var input = 'm-'+mt;
			}else{
				var input = '';
				if(mt && mt != 'undefined'){
					input += ' mt-'+mt;
				}
				if(mr && mr != 'undefined'){
					input += ' mr-'+mr;
				}
				if(mb && mb != 'undefined'){
					input += ' mb-'+mb;
				}
				if(ml && ml != 'undefined'){
					input += ' ml-'+ml;
				}
				input = $.trim(input);
			}
			var old = $(id).val();
			if(old){
				old = old.replace(/m[trbl]*\-\d/g,'');
				old = $.trim(old);
				if(old){
					input = old + ' '+input;
				}
			}
			$(id).val(input);
			return true;
		}
	});
}


;(function($){
	$.admin_design = {
		save:function(obj)
		{
			$.phpok.submit(obj,get_url('design','save'),function(rs){
				if(!rs.status){
					$.dialog.tips(rs.info);
					return false;
				}
				$.dialog.tips('数据保存成功',function(){
					$.admin.close(get_url('design','list'));
				}).lock();
				return true;
			});
			return false;
		},
		delete:function(id)
		{
			$.dialog.confirm(p_lang('确定要删除ID为：{id} 的组件吗？删除后不能恢复',{'id':id}),function(){
				var url = get_url('design','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(!rs.status){
						$.dialog.tips(rs.info);
						return false;
					}
					$.dialog.tips('操作成功',function(){
						$.phpok.reload();
					}).lock();
				});
			});
		},
		padding:function(obj)
		{
			var top = obj.css("padding-top");
			var bottom = obj.css("padding-bottom");
			var right = obj.css("padding-right");
			var left = obj.css("padding-left");
			if(top == bottom && left == right && top == right){
				if(top == '0' || top == '0px' || top == '0%'){
					return false;
				}
				return top;
			}
			if(top == bottom && left == right && top != right){
				return top+' '+right;
			}
			return top+' '+right+' '+bottom+' '+left;
		},
		margin:function(obj)
		{
			var top = obj.css("margin-top");
			var bottom = obj.css("margin-bottom");
			var right = obj.css("margin-right");
			var left = obj.css("margin-left");
			if(top == bottom && left == right && top == right){
				if(top == '0' || top == '0px' || top == '0%'){
					return false;
				}
				return top;
			}
			if(top == bottom && left == right && top != right){
				return top+' '+right;
			}
			return top+' '+right+' '+bottom+' '+left;
		},
		mheight:function(obj)
		{
			var str = obj.css('min-height');
			if(!str || str == 'undefined' || str == '30px' || str == '30'){
				return false;
			}
			str = str.replace('px','');
			return str;
		},
		bg_position:function(obj)
		{
			var str = obj.css("background-position");
			console.log(str);
			if(!str){
				return false;
			}
			var list = str.split(" ");
			var bg_left = bg_right = '';
			if(list[0] == '0%'){
				bg_left = 'left';
			}
			if(list[0] == '50%'){
				bg_left = 'center';
			}
			if(list[0] == '100%'){
				bg_left = 'right';
			}
			if(list[1] == '0%'){
				bg_right = 'top';
			}
			if(list[1] == '50%'){
				bg_right = 'center';
			}
			if(list[1] == '100%'){
				bg_right = 'bottom';
			}
			return bg_left+' '+bg_right;
		}
	}
})(jQuery);