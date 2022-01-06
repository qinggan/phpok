### 在个人中心适当位置添加代码
默认模板是在 tpl/www/usercp/sidebar.html 里添加
```html
<li class="menu-item">
	<a href="{url ctrl=fav/}" class="menu-link">
		<span class="menu-icon">
			<i class="fa fa-star"></i>
		</span>
		<span class="menu-text">我的收藏</span>
	</a>
</li>
```
个人中心的链接是：{url ctrl=fav/}
