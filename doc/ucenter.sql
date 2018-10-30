/*
SQLyog Ultimate v11.24 (32 bit)
MySQL - 8.0.12 : Database - ucenter
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`ucenter` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `ucenter`;

/*Table structure for table `access_log` */

DROP TABLE IF EXISTS `access_log`;

CREATE TABLE `access_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `IP` char(15) DEFAULT NULL COMMENT '登陆IP地址',
  `long` varchar(50) DEFAULT NULL COMMENT '经度',
  `lat` varchar(50) DEFAULT NULL COMMENT '纬度',
  `area` varchar(255) DEFAULT NULL COMMENT '地区信息',
  `ctrl` varchar(50) DEFAULT NULL,
  `ac` varchar(50) DEFAULT NULL,
  `request` text COMMENT '请求参数',
  `return_info` text COMMENT '返回信息',
  `exec_time` varchar(20) DEFAULT NULL COMMENT '执行时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=808 DEFAULT CHARSET=utf8 COMMENT='记录用户登陆信息';

/*Table structure for table `api_config` */

DROP TABLE IF EXISTS `api_config`;

CREATE TABLE `api_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL COMMENT '接口名称',
  `ac` varchar(50) DEFAULT NULL COMMENT '方法名',
  `ctrl` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '控制器',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `u_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `status` int(8) DEFAULT '1' COMMENT '1、未检测；0、无效；2、通过；3、配置有误',
  `is_login` tinyint(1) DEFAULT NULL COMMENT '1需要登陆2不需要登陆',
  `module` varbinary(50) DEFAULT NULL COMMENT '模块',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

/*Table structure for table `api_para` */

DROP TABLE IF EXISTS `api_para`;

CREATE TABLE `api_para` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_config_id` int(11) DEFAULT NULL COMMENT 'api-id',
  `name` varchar(50) DEFAULT NULL COMMENT '参数名',
  `is_must` tinyint(1) DEFAULT NULL COMMENT '1必填2选填',
  `type` varchar(50) DEFAULT NULL COMMENT '参数类型',
  `a_time` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=254 DEFAULT CHARSET=utf8;

/*Table structure for table `area` */

DROP TABLE IF EXISTS `area`;

CREATE TABLE `area` (
  `id` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `deep` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `pinyin_prefix` varchar(100) DEFAULT NULL,
  `pinyin` varchar(100) DEFAULT NULL,
  `ext_id` varchar(20) DEFAULT NULL,
  `ext_name` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `black_word` */

DROP TABLE IF EXISTS `black_word`;

CREATE TABLE `black_word` (
  `id` int(11) NOT NULL COMMENT 'ID',
  `name` char(100) DEFAULT NULL COMMENT '名称',
  `type` tinyint(1) DEFAULT NULL COMMENT '类型',
  `sub_type` tinyint(1) DEFAULT NULL COMMENT '二级类型'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='敏感词、过滤替换';

/*Table structure for table `bulletin` */

DROP TABLE IF EXISTS `bulletin`;

CREATE TABLE `bulletin` (
  `id` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `a_time` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `company_app` */

DROP TABLE IF EXISTS `company_app`;

CREATE TABLE `company_app` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL COMMENT '公司名称',
  `callback_url` varchar(255) DEFAULT NULL COMMENT '回调地址',
  `key` varchar(100) DEFAULT NULL COMMENT '加密KEY',
  `from_url` varchar(255) DEFAULT NULL COMMENT '请求来源URL',
  `icon` varchar(255) DEFAULT NULL COMMENT '公司ICON',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `power` text COMMENT '权限'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `company_app_user` */

DROP TABLE IF EXISTS `company_app_user`;

CREATE TABLE `company_app_user` (
  `id` int(11) NOT NULL COMMENT '自增ID',
  `uid` varchar(255) DEFAULT NULL COMMENT '同USER表ID一样',
  `openid` varchar(255) DEFAULT NULL COMMENT '开放ID',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `appid` int(11) DEFAULT NULL COMMENT '公司ID',
  `u_time` int(11) DEFAULT NULL COMMENT '最后更新时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `email_log` */

DROP TABLE IF EXISTS `email_log`;

CREATE TABLE `email_log` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL COMMENT '发送邮箱',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '接受方uid',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `content` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT '0' COMMENT '状态 0：待发送；1：发送成功；2：发送失败；3：处理成功',
  `a_time` int(11) DEFAULT '0' COMMENT '发送时间',
  `u_time` int(11) DEFAULT '0' COMMENT '修改时间，发送成功或者失败时间',
  `json_str` text COMMENT '存储redis的数据',
  `type` int(20) NOT NULL COMMENT '1修改密码和邮件 2预警警告',
  `rule_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `email_rule` */

DROP TABLE IF EXISTS `email_rule`;

CREATE TABLE `email_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `type` tinyint(4) DEFAULT NULL COMMENT '1正常2报警',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `period` int(11) DEFAULT NULL COMMENT '周期时间，秒',
  `period_times` int(11) DEFAULT NULL COMMENT '周期时间内，发送次数',
  `day_times` int(11) DEFAULT NULL COMMENT '1天发几次',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `id_validate` */

DROP TABLE IF EXISTS `id_validate`;

CREATE TABLE `id_validate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no` varchar(20) DEFAULT NULL COMMENT '身份证号',
  `photo_top` int(11) DEFAULT NULL COMMENT '身份证正面照片',
  `photo_back` int(11) DEFAULT NULL COMMENT '背面照片',
  `photo_self` int(11) DEFAULT NULL COMMENT '本人手持身份证照片',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `status` tinyint(1) DEFAULT NULL COMMENT '审核状态0未处理1通过2拒绝',
  `u_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='实名验证';

/*Table structure for table `login` */

DROP TABLE IF EXISTS `login`;

CREATE TABLE `login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `u_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
  `IP` char(15) DEFAULT NULL COMMENT '登陆IP地址',
  `long` varchar(50) DEFAULT NULL COMMENT '经度',
  `lat` varchar(50) DEFAULT NULL COMMENT '纬度',
  `login_type` tinyint(1) DEFAULT NULL COMMENT '1手机号2微信3QQ4微博5其它6facebook7google',
  `random` varchar(50) DEFAULT NULL COMMENT '登陆随机码',
  `area` varchar(255) DEFAULT NULL COMMENT '地区信息',
  `token` varchar(50) DEFAULT NULL COMMENT '可以解出UID',
  `country` varchar(50) DEFAULT NULL COMMENT '国家',
  `channel` varchar(50) DEFAULT NULL COMMENT '带宽类型',
  `province` varchar(50) DEFAULT NULL COMMENT '省',
  `city` varchar(50) DEFAULT NULL COMMENT '市',
  `type` tinyint(1) DEFAULT NULL COMMENT '1登陆2登出',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='记录用户登陆信息';

/*Table structure for table `skill` */

DROP TABLE IF EXISTS `skill`;

CREATE TABLE `skill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

/*Table structure for table `sms_log` */

DROP TABLE IF EXISTS `sms_log`;

CREATE TABLE `sms_log` (
  `id` int(11) NOT NULL,
  `rule_id` int(11) DEFAULT NULL COMMENT '外键ID',
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `content` varchar(255) DEFAULT NULL COMMENT '短信内容',
  `status` tinyint(4) DEFAULT NULL COMMENT '0等待发送1成功2失败',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `IP` char(15) DEFAULT NULL COMMENT 'IP地址',
  `errinfo` varchar(255) DEFAULT NULL COMMENT '发送失败原因',
  `cellphone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '手机号',
  `type` tinyint(1) DEFAULT NULL COMMENT '1正常2预警'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信发送日志';

/*Table structure for table `sms_rule` */

DROP TABLE IF EXISTS `sms_rule`;

CREATE TABLE `sms_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL COMMENT '标题',
  `content` varchar(255) DEFAULT NULL COMMENT '内容',
  `period_times` int(11) DEFAULT NULL COMMENT '周期时间内，发送次数',
  `day_times` int(11) DEFAULT NULL COMMENT '一天最多发送次数',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `type` tinyint(1) DEFAULT NULL COMMENT '1正常2报警',
  `period` int(11) DEFAULT NULL COMMENT '周期时间，秒',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='短信发送规则限制';

/*Table structure for table `university` */

DROP TABLE IF EXISTS `university`;

CREATE TABLE `university` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no` varchar(20) DEFAULT NULL COMMENT '学校标识码',
  `dep` varchar(100) DEFAULT NULL COMMENT '主管部门',
  `type` varchar(50) DEFAULT NULL COMMENT '民办/中外合',
  `city` varchar(100) DEFAULT NULL COMMENT '所在城市',
  `name` varchar(255) DEFAULT NULL COMMENT '大学名称',
  `province` varchar(50) DEFAULT NULL COMMENT '所在省',
  `level` varchar(50) DEFAULT NULL COMMENT '专/本',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2632 DEFAULT CHARSET=utf8;

/*Table structure for table `user` */

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '用户名',
  `ps` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'md5密码',
  `avatar` int(11) DEFAULT NULL COMMENT '头像',
  `cellphone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '手机号',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `sex` tinyint(1) NOT NULL COMMENT '性别0未知1男2女',
  `birthday` int(11) DEFAULT NULL COMMENT '出生日期',
  `id_card_no` varchar(20) DEFAULT NULL COMMENT '身份证号',
  `country` varchar(20) DEFAULT NULL COMMENT '国家',
  `province` varchar(50) DEFAULT NULL COMMENT '省',
  `city` varchar(50) DEFAULT NULL COMMENT '城市',
  `tags` text COMMENT '标签-暂不用',
  `school` varchar(100) DEFAULT NULL COMMENT '毕业大学',
  `education` tinyint(4) DEFAULT NULL COMMENT '0未知1博士2硕士3研究生4本科5专科6高中',
  `skill` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '技能',
  `real_name` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `u_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
  `type` tinyint(1) DEFAULT NULL COMMENT '注册方式:1手机2微信3QQ4邮箱5用户名',
  `channel` tinyint(4) DEFAULT NULL COMMENT '渠道',
  `thrid_uid` varchar(100) DEFAULT NULL COMMENT '第3方UID',
  `point` int(11) DEFAULT NULL COMMENT '积分',
  `IP` char(32) DEFAULT NULL COMMENT '注册IP',
  `addr` varchar(255) DEFAULT NULL COMMENT '详细地址',
  `company` varchar(255) DEFAULT NULL COMMENT '公司',
  `telphone` varchar(50) DEFAULT NULL COMMENT '座机',
  `fax` varchar(50) DEFAULT NULL COMMENT '传真',
  PRIMARY KEY (`id`,`sex`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `user_black` */

DROP TABLE IF EXISTS `user_black`;

CREATE TABLE `user_black` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `mome` varchar(255) DEFAULT NULL COMMENT '备注',
  `type` tinyint(1) DEFAULT NULL COMMENT '1访问次数频繁',
  `status` tinyint(1) DEFAULT NULL COMMENT '1正常2禁止访问',
  `a_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `verifier_code` */

DROP TABLE IF EXISTS `verifier_code`;

CREATE TABLE `verifier_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(6) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL COMMENT '1正常2已验证3失效',
  `a_time` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '1手机2邮箱',
  `addr` varbinary(20) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `expire_time` int(11) DEFAULT NULL,
  `cate` tinyint(1) DEFAULT NULL COMMENT '1手机回密码2邮箱找回密码3绑定邮箱4绑定手机',
  `rule_id` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
