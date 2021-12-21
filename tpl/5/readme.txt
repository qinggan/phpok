index-old.html
是之前旧版代码编写模式

购物车页说明：

在国内，大部分电商平台是：购物车，结算（生成订单），付款，完成！

但在海外平台，电商基本上是这样子的：
	购物车 
		> 结算页（主要填写收货地址及账号地址）cart_check 
			> 确认结算信息，同时填写优惠券 cart_review 
				> 生成订单，提交支付（信用卡支付也在这一步进行信息填写）order_payment
					> 订单完成

相比之前，中文版的结算页就只有一页包含了所有！

目前模板里没有针对 cart_review 和 cart_confirm 进行编写，您可以参与 cart_check 编辑（参数及值都有 post 传过去）

开发人员可以查看

	framework/www/cart_control.php
	framework/www/order_control.php
	framework/www/payment_control.php

进行分析要求