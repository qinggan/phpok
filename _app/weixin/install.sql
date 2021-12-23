-- 安装数据库文件，直接在这里写SQL
CREATE TABLE IF NOT EXISTS `qinggan_weixin_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `openid` varchar(255) NOT NULL COMMENT '主键',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `lastlogin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次登录时间',
  `headimg` varchar(255) NOT NULL COMMENT '头像图片',
  `nickname` varchar(255) NOT NULL COMMENT '昵称',
  `country` varchar(255) NOT NULL COMMENT '国家',
  `province` varchar(255) NOT NULL COMMENT '省份',
  `city` varchar(255) NOT NULL COMMENT '城市',
  `gender` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1男2女0未知',
  `unionid` varchar(255) NOT NULL COMMENT '有关注公众号才有此数据',
  `language` varchar(255) NOT NULL COMMENT '语言标识',
  `source` varchar(255) NOT NULL COMMENT '选择登录来源',
  `lastmessage` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次发送消息时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='微信用户' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `qinggan_weixin_message` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `to_openid` varchar(255) NOT NULL COMMENT '目标微信的OpenID',
  `from_openid` varchar(255) NOT NULL COMMENT '发送方的OpenID',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发送时间戳',
  `msg_type` varchar(50) NOT NULL COMMENT '发送类型，text文本，image图片，voice语音，shortvideo小视频，location定位，link链接，video视频，music音乐，news图文',
  `content` text NOT NULL COMMENT '内容',
  `msg_id` int(11) NOT NULL COMMENT '消息ID',
  `media_id` varchar(255) NOT NULL COMMENT '语音消息媒体id，可以调用获取临时素材接口拉取数据',
  `media_format` varchar(100) NOT NULL COMMENT '语音格式，如amr，speex等',
  `recognition` text NOT NULL COMMENT '语音识别结果',
  `thumb` varchar(255) NOT NULL COMMENT '视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据。',
  `location_x` varchar(255) NOT NULL COMMENT '地理位置纬度',
  `location_y` varchar(255) NOT NULL COMMENT '地理位置经度',
  `location_scale` varchar(255) NOT NULL COMMENT '地图缩放大小',
  `location_label` varchar(255) NOT NULL COMMENT '地理位置信息',
  `title` varchar(255) NOT NULL COMMENT '消息标题',
  `description` text NOT NULL COMMENT '消息描述',
  `url` varchar(255) NOT NULL COMMENT '消息链接',
  `vtype` enum('receive','send') NOT NULL DEFAULT 'receive' COMMENT 'receive接收，send发送',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='微信公众号消息收发操作';