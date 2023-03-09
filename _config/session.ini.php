;<?php exit("<h1>Access Denied</h1>");?>

;SESSION相关配置
;会话保存方式，默认是 default，支持 default，memcache，redis，redis_ini
;default 默认存储模式，需要参数 path，默认是 /tmp/，支持 {dir_data}/session/，注意必须 / 结尾
;memcache 基于 ini_set 改写的 Memcache 保存 Session，涉及到参数 host，port
;redis 将 Session 数据保存到 Redis，使用 session_set_save_handler 改写，涉及到参数 host，port，dbname，dbpass
;redis_ini 基于 ini_set 改写的 Redis 保存 Session，需要参数 path，多台服务器用逗号隔开
;	Path 写法：tcp://127.0.0.100:6379?auth=密码&database=数据库ID&timeout=1&weight=2
;	参考手册地址：//github.com/phpredis/phpredis
file = default

;会话读取参数，适用于 Cookie 被禁用的时候使用
id = PHPSESSION

;Cookie 的 生命周期，系统要求不少于600秒
timeout = 2000

;用户临时文件存储目录，默认是系统的临时目录
;如果存到Redis，请填写 tcp://IP:端口?auth=认证
;path = "{dir_data}/session/"
path = ""
;path = "tcp://127.0.0.1:6379?database=1"

;缓存限制器的名称，
;nocache 会进制客户端或者代理服务器缓存内容
;public 表示允许客户端或代理服务器缓存内容
;private 表示允许客户端缓存，但是不允许代理服务器缓存内容。 
cache_limiter = nocache

;缓存到期时间，默认为 180 分钟，设置为 0 表示使用系统默认，个人建议不低于30分钟
cache_expire = 180

;SESSION服务器
host = 127.0.0.1

;SESSION服务器对应的端口
port = 6379

;DB密码
dbpass = 

;DB名称
dbname=1

;Domain 域名限制，留空使用默认，如果要所有子域名支持，请以.开头
domain = ""

;设置为 true 表示 cookie 仅在使用 安全 链接时可用。 
secure = false

;设置为 true 表示 PHP 发送 cookie 的时候会使用 httponly 标记
httponly = true

;SESSION前缀，仅用于Memcache中使用
prefix = "sess_"