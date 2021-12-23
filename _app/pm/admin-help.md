### 个人中心里加上对应的管理
OK默认程序是加在 tpl/www/usercp/sidebar.html 里，代码示例：
```html
<li class="menu-item">
	<a href="{url ctrl=pm/}" class="menu-link">
		<span class="menu-icon">
			<i class="fa fa-volume-up"></i>
		</span>
		<span class="menu-text">站内消息<!-- if $pm.total --><span class="text-danger">●</span><!-- /if --></span>
	</a>
</li>
```
#### 自主添加
对应的链接是：{url ctrl=pm /}

### 模板修改
系统判断模板短信模板是顺序
- 风格目录/pm/www-index.html（可使用您自设的后缀，如.php）
应用目录/pm/tpl/www-index.html

个人不建议使用默认的应用目录，建议使用风格目录可以保证风格统一