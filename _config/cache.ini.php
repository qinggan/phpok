;<?php exit("<h1>Access Denied</h1>");?>

;缓存引挈配置
;是否启用缓存 false不使用 true使用（启用后如果系统连不上缓存，会变成false）
status = true

;是否启用调试模式
debug = false

;缓存类型，目前支持的有：default memcache redis
file = default

;缓存过期时间，单位是秒
timeout = 3600

;缓存文件目录，仅限为 default 时有效，变量
folder = "{dir_cache}"

;缓存服务器，仅在使用 memcache redis 时有效
server = "127.0.0.1"

;缓存服务器使用的端口，仅在使用 mecache redis 时有效
port = "11211"
;port = "6379"

;缓存Key前缀，防止生成的Key重复
prefix = "qinggan_"

;数据库名，仅限 redis 有效，如果为空则使用0
dbname = 0

;数据库密码，仅限 redis 有效，没有请留空
dbpass = 