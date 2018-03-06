create database assistant charset=utf8;

USE `assistant`;

CREATE TABLE `admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `memo` text NOT NULL COMMENT '备注信息',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `IP` varchar(32) DEFAULT NULL COMMENT '访问者IP地址',
  `uid` int(11) NOT NULL COMMENT 'admin_uid',
  `cate` varchar(50) DEFAULT NULL COMMENT '分类',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='管理员用户操作日志';

CREATE TABLE `admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uname` varchar(200) DEFAULT NULL COMMENT '用户名',
  `ps` char(32) DEFAULT NULL COMMENT '密码',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='管理员用户';

insert  into `admin_user`(`id`,`uname`,`ps`,`add_time`) values (1,'admin','e10adc3949ba59abbe56e057f20f883e',NULL),(2,'2sister','0bbf445240f20e1ee44c690a857c8944',NULL);

CREATE TABLE `const` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text COMMENT '内容',
  `status` int(11) DEFAULT '0' COMMENT '0关闭1打开',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='常量表';

