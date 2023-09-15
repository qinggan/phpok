;<?php exit("<h1>Access Denied</h1>");?>
;覆盖 global.ini.php 里的配置，此项仅用于 www 下使用
autoload_js = "jquery.md5.js,jquery.phpok.js,global.js,jquery.form.min.js,jquery.json.min.js,global.www.js,jquery.superslide.js,jquery.artdialog.js,jquery.cart.js,jquery.browser.js"

;是否开启安全网址，启用后，下面的 get_params 才有效
safe_homepage = false

;安全参数，不符合这些参数的，直接报 404 错误
get_params = "id,uid,phpfile,siteId,_langid,_noCache,tdsourcetag,_back,cate,ext,keywords"

;忽略控制器
reserved = "index"