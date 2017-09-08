;<?php exit("<h1>Access Denied</h1>");?>

;支持 mysqli pdo_mysql，PHP大于5.3版的用户建议使用mysqli或pdo_mysql
file = "mysqli"

;数据库服务器，本地请填写localhost或127.0.0.1
host = "127.0.0.1"

;数据库服务器的端口号，默认是3306
port = "3306"

;连接数据库的账号
user = "root"

;连接数据库的密码
pass = "root"

;数据库名称
data = "phpok"

;数据表前缀，实现同一个数据库安装不同版本程序，默认使用 qinggan_
prefix = "qinggan_"

;使用通道连接（即不走网卡，Mysql在Linux下一般是/tmp/mysql.sock，建议有独立主机的用户使用）
socket = ""

;是否调试，配合系统的debug为true时，会打印出整个页面执行的SQL语句
debug = false
