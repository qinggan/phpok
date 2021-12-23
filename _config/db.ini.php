;<?php exit("<h1>Access Denied</h1>");?>

; 支持 mysqli pdo_mysql sqlite，PHP大于5.3版的用户建议使用mysqli或pdo_mysql
file = "mysqli"

; 数据库服务器，本地请填写localhost或127.0.0.1
; 当使用 http 时，这里填写服务器的 IP，可用于解决无法认别域名问题
;host = "127.0.0.1"

; 数据库服务器的端口号，默认是3306
; 使用 http 模式此项无效
port = "3306"

; 连接数据库的账号
; 使用 http 模式时，这里是认证账号，不需要请留空
user = "root"

; 连接数据库的密码
; 使用 http 模式时的基本验证，不需要请留空
pass = "root"

; 数据库名称
; 使用 sqlite 或 pdo_sqlite 时，请填写数据库相对地址，要确保文件存在
; 使用 mysqli 或 pdo_mysql 时，请填写数据库名称
data = "phpok5"

; 数据表前缀，实现同一个数据库安装不同版本程序，默认使用 qinggan_
; 使用 http 模式时，注意生成的 SQL 文件会补上前缀
prefix = "qinggan_"

; 使用通道连接（即不走网卡，Mysql在Linux下一般是/tmp/mysql.sock，建议有独立主机的用户使用）
socket = ""

;语言编码，仅支持 utf8 和 utf8mb4，默认是 utf8
charset = "utf8"

; 是否调试，配合系统的debug为true时，会打印出整个页面执行的SQL语句
debug = false

; 即时缓存，适应用大量的小查询（重复查询，一般不用开启）
; 注意，此项仅在使用了 memcache 或 redis 缓存时才有效，如果使用文件缓存，速度反而会下降
cache = false

;慢查询记录
slow = false

;慢查询时间，单位是秒，支持小数点，如0.05
slow_time = 0.02