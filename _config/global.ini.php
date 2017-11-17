;<?php exit("<h1>Access Denied</h1>");?>

;是否调试，1/true启用，0/false禁用
debug = true

;压缩传缩，1/true启用，0/false禁用
gzip = true

;开发模式，1/true启用，0/false禁用
develop = true

;取得控制器的ID
ctrl_id = c

;取得应用方法的ID
func_id = f

;后台入口
admin_file = admin.php

;网站首页，这里一般不修改，除非首页要另外制作，才改名
www_file = index.php

;API接口
api_file = api.php

;验证码显示，1/true启用，0/false禁用
is_vcode = true

;每页显示数量
psize = 20

;分页ID
pageid = pageid

;时区调节
timezone = Asia/Shanghai

;时间调节，单位是秒
timetuning = 0

;参数Token，不在使用 session 的时候可以使用此项替代
token_id = token

;启用SQL远程执行，建议禁用，1/true启用，0/false禁用
api_remote_sql = false

;锁定时间，多次登录错误后被系统锁定的时间，单位是小时，留空或未设置则锁定2小时
lock_time = 2

;错误次数达到多少次后执行锁定登录
lock_error_count = 6

;会员登录验证，1/true启用，0/false禁用，如果要实现会员登录才能查看，请将此值设为 true 或 1
is_login = false

;公共JS加载类，多个 js 用英文逗号隔开，不考虑路径，优先读取 framework/js/ 目录，其次读取 js/ 目录
;在模板中，需要增加 {url ctrl=js /}，不然此项无效，默认加 js/ 目录下的 jquery.js 文件，可以在指定模板 js 目录中放 jquery.js 文件覆盖读取
autoload_js = "jquery.md5.js,jquery.phpok.js,global.js,jquery.form.min.js,jquery.json.min.js"

;获取域名方式，Apache 用户建议使用 SERVER_NAME，Nginx 用户建议使用 HTTP_HOST
get_domain_method = SERVER_NAME

;[www]
;网页端是否需要会员验证，1/true启用，0/false禁用
;is_login = false

;[api]
;接口是否需要会员验证，1/true启用，0/false禁用
;is_login = false


[mobile]
;启用或禁用手机端，1/true启用，0/false禁用
status = true

;自动检测是否手机端，1/true启用，0/false禁用
autocheck = true

;默认为手机版，为方便开发人员调式，设置为默认后，在网页上也会展示手机版，1/true启用，0/false禁用
default = false

;手机版自动加载的 js，配合 autoload_js 参数进行增加操作
includejs = "jquery.touchslide.js"

;手机版要去除 js，配合 autoload_js 参数进行禁用加载操作
excludejs = "jquery.superslide.js"

[pc]
;电脑端自动加载的 js，配合 autoload_js 参数进行增加操作
includejs = ""

;电脑端要去除 js，配合 autoload_js 参数进行禁用加载操作
excludejs = ""

[seo]
; SEO分割线，注意空格
line = " - "

;SEO优化模式，{title}，即传过来的标题值，{seo} 是内置的 SEO 标题，{sitename} 即是网站名称
format = "{title}-{sitename}-{seo}"

[order]
price = "product,shipping,fee,discount"

[cart]
;购物车里的图片来字系统中哪个字段
thumb_id = "thumb"
;要保存的到购物车里的图片是哪个 GD 方案，留空存原图
gd_id = ""

[fav]
;收藏夹里的图片获取，即收藏主题时，如果检测到主题有指定的图片字段，将图片存到收藏夹的缩略图中来
thumb_id = "thumb"

;收藏夹中获取的摘要从文章中哪里获取
note_id = "content"

[async]
;在PHP程序中执行异步计划任务
;如果您的空间不支持此项或要禁用，请在相应的HTML模板里写入定时计划任务请求链接，并将此处设为 false
status = false

;模式，支持 fsockopen / curl / pfsockopen / stream
type = fsockopen

[jsonp]
;远程跨域获取数据
;Get到的参数ID
getid = callback

;如果上述未设置 getid 或 内容为空，则使用默认函数
default = callback
