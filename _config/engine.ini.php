;<?php exit("<h1>Access Denied</h1>");?>

[session]
;SESSION相关配置
;会话保存方式，默认是 default，支持 default，memcache
file = default

;会话读取参数，适用于 Cookie 被禁用的时候使用
id = PHPSESSION

;会话超时时间，单位是秒
timeout = 3600

;会员临时文件存储目录，默认是 _data/session/，变量 {dir_data}，{dir_cache}，{dir_root}
;path = "{dir_data}session/"
path = ""

;会员自动运行方法，经测试目前仅用于为 sql
methods = "auto_start:db"

;SESSION服务器
host = "127.0.0.1"

;SESSION服务器对应的端口
port = 11211

;Domain 域名限制，留空使用默认，如果要所有子域名支持，请以.开头
domain = ""

;SESSION前缀，仅用于Memcache中使用
prefix = "sess_"

[cache]
;缓存引挈配置
;是否启用缓存 false不使用 true使用（启用后如果系统连不上缓存，会变成false）
status = true
;是否启用调试模式
debug = false

;缓存类型，目前支持的有：default memcache redis
file = default

;缓存过期时间，单位是秒
timeout = 3600

;缓存文件目录，仅限为 default 时有效，变量 {dir_data}，{dir_cache}，{dir_root}
folder = "{dir_cache}"

;缓存服务器，仅在使用 memcache redis 时有效
server = "127.0.0.1"

;缓存服务器使用的端口，仅在使用 mecache redis 时有效
port = "6379"

;缓存Key前缀，防止生成的Key重复
prefix = "qinggan_"

