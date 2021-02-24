;<?php exit("<h1>Access Denied</h1>");?>
;覆盖 global.ini.php 里的配置，此项仅用于 admin 下使用
autoload_js = "jquery.md5.js,jquery.phpok.js,global.js,jquery.form.min.js,jquery.json.min.js,jquery.artdialog.js,jquery.desktop.js,global.admin.js,jquery.smartmenu.js,selectpage.min.js,clipboard.min.js"

;隐藏险证码配置，设置为 TRUE 后，验证码配置按钮将不显示
hide_vcode_setting = false

;显示环境配置信息，设为 false 则隐藏
show_env = false

;扫码登录超时，单位是秒，默认是300秒（即5分钟）
;值不大于120秒时，将使用系统默认的300秒
admin_qrcode_expire_time = 300


;debug = true