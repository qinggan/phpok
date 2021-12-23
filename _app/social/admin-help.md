## 个人中心模板适当位置添加服务信息
>个人主页：{url ctrl=social func=homepage/}
我的关注：{url ctrl=social func=idol/}
粉丝：{url ctrl=social func=fans/}
黑名单：{url ctrl=social func=blacklist/}

默认模板代码写法如下：
```html
<li class="menu-item has-sub">
	<a href="#" class="menu-link menu-toggle">
		<span class="menu-icon">
			<i class="fa fa-steam"></i>
		</span>
		<span class="menu-text">社交服务</span>
	</a>
	<ul class="menu-sub">
		<li class="menu-item">
			<a href="{url ctrl=social func=homepage/}" class="menu-link">
				<span class="menu-text">主页装扮</span>
			</a>
		</li>
		<li class="menu-item">
			<a href="{url ctrl=social func=idol/}" class="menu-link">
				<span class="menu-text">
					我关注的
				</span>
			</a>
		</li>
		<li class="menu-item">
			<a href="{url ctrl=social func=fans/}" class="menu-link">
				<span class="menu-text">
					我的粉丝
				</span>
			</a>
		</li>
		<li class="menu-item">
			<a href="{url ctrl=social func=blacklist/}" class="menu-link">
				<span class="menu-text">
					黑名单
				</span>
			</a>
		</li>
	</ul>
</li>
```

## 个人主页参数
直接使用变量 **{$social.变量}**，常用写法：
> {if $social.is_fans}是粉丝{else}不是粉丝{/if}
{if $social.is_idol}已关注{else}未关注{/if}
{$social.fans} 显示粉丝数
{$social.idol} 显示我的关注数

## 数据列表调用
> 仅限主题绑定用户信息，在循环中如果数据有user，可以直接使用：
{$value.user.social.idol} 用户关注数量
{$value.user.socail.fans} 用户粉丝数
当用户已登录，可以直接使用以下参数判断是否：已关注，拉黑的情况：
{if $value.user.social.is_fans}粉丝{else}路人{/if}
{if $value.user.social.is_idol}已关注{else}未关注{/if}
{if $value.user.social.is_black}黑名单{/if}
可以通过最后一个黑名单来判断是否显示文章

## 数据详细页
> 可直接通过 {$rs.user.social} 来获取相关信息
{$rs.user.social.idol} 用户关注数量
{$rs.user.socail.fans} 用户粉丝数
当用户已登录，可以直接使用以下参数判断是否：已关注，拉黑的情况：
{if $rs.user.social.is_fans}粉丝{else}路人{/if}
{if $rs.user.social.is_idol}已关注{else}未关注{/if}
{if $rs.user.social.is_black}黑名单{/if}
可以通过最后一个黑名单来判断是否显示文章

## 已登录用户可以在模板任意位置显示
> {$me.social.fans} 我的粉丝数量
{$me.social.idol} 我关注的用户
