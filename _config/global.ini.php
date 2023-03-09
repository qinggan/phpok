;<?php exit("<h1>Access Denied</h1>");?>

;是否调试，1/true启用，0/false禁用
debug = false

;压缩传缩，1/true启用，0/false禁用
gzip = true

;开发模式，1/true启用，0/false禁用
;设置为 false 后，后台如果需要自定义字段，需要到管理员那里临时打开
;当 debug 为 false 时，此项即为 false
develop = false

;设置为true，系统管理员可以切换开发者模式，设置为false时，无法切换模式
develop_status = true

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

;启用SQL远程执行，建议禁用，1/true启用，0/false禁用
api_remote_sql = false

;锁定时间，多次登录错误后被系统锁定的时间，单位是小时，留空或未设置则锁定2小时
lock_time = 2

;错误次数达到多少次后执行锁定登录
lock_error_count = 6

;用户登录验证，1/true启用，0/false禁用，如果要实现用户登录才能查看，请将此值设为 true 或 1
is_login = false

;公共JS加载类，多个 js 用英文逗号隔开，不考虑路径，优先读取 framework/js/ 目录，其次读取 js/ 目录
;在模板中，需要增加 {url ctrl=js /}，不然此项无效，默认加 js/ 目录下的 jquery.js 文件，可以在指定模板 js 目录中放 jquery.js 文件覆盖读取
autoload_js = "jquery.md5.js,jquery.phpok.js,global.js,jquery.form.min.js,jquery.json.min.js,jquery.browser.js"

;获取域名方式，Apache 用户建议使用 SERVER_NAME，Nginx 用户建议使用 HTTP_HOST
get_domain_method = HTTP_HOST

;是否多语言选择
multiple_language = false

;是否启用 opcache，仅限 debug 为 false 时有效
opcache = true

;是否强制启用 HTTPS，默认不强制，如果您使用 nginx+apache 多模式组合，可能检测 https 失败，可以在这里设置强制
force_https = 0

;防止被 Iframe 嵌套，开启此项目
;DENY 表示页面不允被嵌套
;SAMEORIGIN 表示页面只能被本站页面嵌入到iframe或者frame中
;ALLOW-FROM uri 表示页面允许frame或frame加载，其中uri 是指具体的网站域名
iframe_options = *

;跨域请求数据，设为 false 表示禁止跨域数据请求，设为 true 表示允许跨域
cross_domain = true

;允许跨域的域名，为 * 号表示允许所有，单独域名：www.phpok.com 写法
cross_domain_origin = *


;完整地址（即通过平台生成的网址是否带上域名）
;设置为 0 或 false 表示系统默认为相对网址，但是参数如果传 true 过来，还是显示完整地址
;设置为 1 或 true 表示系统强制为绝对地址（带有域名）
is_baseurl = false

;完整地址，为空使用系统生成
baseurl = ""

;是否开启API认证
;设置为 0 或 false 表示不启用 AUTHORIZATION 认证
;设置为 1 或 true 表示前台及API（除少数接口）大部分都要认在 HTTP 头里增加 AUTHORIZATION 认证
api_auth = false


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
line = "_"

;是否继承上一级SEO，设为true会一层层递归进来
;企业站用户（数据量少，建议设为 true），数据量稍多点的（超过1000条运营数据时，建议设为 false，以防止被百度认识垃圾站）
;不清楚请设置 false
inherit = false

;仅限详细页有效，且必须启用 inherit 设为 true 时有效
;  1、设为 false 详细页的标题在自身未设置SEO标题时，仅使用【主题 + 分类 + 项目】；
;  2、设为 true  详细页的标题在自身未设置SEO标题时，使用【主题 + 分类 + 项目 + SEO标题】，请慎用，可能会造成标题超长
;个人建议设为 false
in_title = false

[order]
;价格选项
;product 产品价格
;shipping 运费
;fee 手续费
;discount 打扰优惠
;discount-shipping 运费打折
;excise 税费
;tariff 关税
;refund 退款操作
price = "product,shipping,discount-shipping,fee,discount,excise,tariff,refund"

;地址，国内用户一般只需要使用shipping地址，即收货地址
;海外用户还会用到billing地址，即账单地址(正常情况默认账单地址和收货地址是一样的)
address = "shipping"

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

[jsonp]
;远程跨域获取数据
;Get到的参数ID
getid = callback

;如果上述未设置 getid 或 内容为空，则使用默认函数
default = callback

[cdn]
;是否开启 CDN 静态资源库
status = true

;CDN服务器地址
server = "cdn.phpok.com"

;IP地址，用于检测CDN时响应过长造成
ip = ""

;是否启用https
https = true

;多久检测CDN连接情况，单位是秒
time = 3600

;连接失败，指向目录地址
folder = "static/cdn"

[log]
;日志状态，设为1或true时表示开启，设为0或false时，表示关闭
status = false

;记录级别，目前仅支持 1 和 0
;1 表示所有的都记录
;0 仅记录报错信息
type = 0

;禁止记录的控制器，多个控制器用英文逗号隔开
forbid = "index,log"