;<?php exit("<h1>Access Denied</h1>");?>

;is_vcode = true

; API 接口生成的图片如果异常，可在这里关闭 gzip 模式
gzip = false

[token]
;用户 Token 标识，仅在 API 接口使用
;启用此项需要后台开始密钥
id = userToken

; Token 有效时间，单位为秒，默认为 7200 秒（即2小时）
time = 7200

;用于刷新 token 认证，以实现永久登录
refresh_id = refreshToken

;用于刷新的token信息，单位是天，默认为 30 天
refresh_day = 30