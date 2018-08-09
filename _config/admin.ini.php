;<?php exit("<h1>Access Denied</h1>");?>
;覆盖 global.ini.php 里的配置，此项仅用于 admin 下使用
autoload_js = "jquery.md5.js,jquery.phpok.js,global.js,jquery.form.min.js,jquery.json.min.js,jquery.artdialog.js,jquery.desktop.js,global.admin.js,jquery.smartmenu.js,selectpage.min.js,clipboard.min.js"

;隐藏险证码配置，设置为 TRUE 后，验证码配置按钮将不显示
hide_vcode_setting = false


;以下是后台弹窗专用属性设置
[windows]
;是否启用 taskbar_close 关闭按钮
taskbar_close = false

;是否显示关闭按钮，两个关闭按钮至少必须有一个为 true
button_close = true

;是否允许最小化
button_min = true

;是否支持最大化操作，设为 false 后，只能手动调窗口大小
button_max = true

;是否支持刷新
button_refresh = true

;默认最大化
is_max = true

;允许移动
move = true