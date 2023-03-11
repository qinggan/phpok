phpok
=====

系统基于PHP+MySQL开发，是一套十分自由的CMS程序，支持各种自定义配置及字段扩展，包括站点全局参数，分类扩展，项目扩展及各种自建模块！

手工安装
===
1. 打包下载后，请手工在 _data 目录下创建以下文件夹： **session**，**tpl_admin**，**tpl_www**，**log**，**update**，**zip**
2. 请在根目录手动创建文件夹：**_cache**
3. 设置 **_config/db.ini.php** 文件可写，Linux属性为 666
4. 设置 **_cache**，**_data**，**res** 及子目录属性为 777
5. 运行 phpokinstall.php 进行安装

6.4 版本修正功能
===
* 修正：注册时验证码+手机号的绑定
* 修正：CKEditor复制内容上传图片
* 修正：电子商务属性问题
* 修正：购物车的一些细节
* 修正：商品属性无法删除
* 修正：微信支付异步推送及主动查询写入重复
* 完善：分类批量写入自动排序
* 完善：资源管理器
* 完善：模板代码的一些写法
* 完善：代码编辑器
* 完善：可视化模板组件功能
* 完善：字段管理（扩展字段独立表保存）
* 完善：后台内容管理
* 完善：网站首页（仅限新装用户）
* 完善：附件压缩处理
* 完善：CKEditor 粘贴图片按顺序上传（新测公众号图片，淘宝图文）
* 升级：Bootstrap 到 4.6.2 版本
* 新增：菜单增加子项目功能
* 新增：下拉触底加载下一页数据
* 新增：简单的模块可视化编辑
* 新增：执 Update，Delete, Insert, Replace 时进行安全较验

6.4.003 版本修正功能
===
* 修正：带回复信息报错
* 修正：附件是否被调用
* 修正：库存表异常问题

感谢
===
感谢您使用我们的CMS系统，这是一款优雅并且高度自定义的程序

