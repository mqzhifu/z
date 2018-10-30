/*
SQLyog Ultimate v11.24 (32 bit)
MySQL - 8.0.12 : Database - sanguo
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`sanguo` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `sanguo`;

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
) ENGINE=MyISAM AUTO_INCREMENT=905 DEFAULT CHARSET=utf8 COMMENT='记录用户登陆信息';

/*Table structure for table `admin_log` */

DROP TABLE IF EXISTS `admin_log`;

CREATE TABLE `admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_uid` int(11) DEFAULT NULL COMMENT '管理员ID',
  `a_time` int(11) DEFAULT NULL,
  `ctrl` varchar(50) DEFAULT NULL,
  `ac` varchar(50) DEFAULT NULL,
  `request` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '请求参数',
  `return` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '响应内容',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='管理员日志';

/*Table structure for table `admin_user` */

DROP TABLE IF EXISTS `admin_user`;

CREATE TABLE `admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '用户名',
  `nickname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '昵称',
  `ps` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理员';

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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;

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

/*Table structure for table `base_exp_log` */

DROP TABLE IF EXISTS `base_exp_log`;

CREATE TABLE `base_exp_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) DEFAULT NULL,
  `num` int(11) DEFAULT NULL COMMENT '血量值',
  `a_time` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `base_upgrade_log` */

DROP TABLE IF EXISTS `base_upgrade_log`;

CREATE TABLE `base_upgrade_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `ori_level` int(11) DEFAULT NULL,
  `new_level` int(11) DEFAULT NULL,
  `a_time` int(11) DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `boss_upgrade_log` */

DROP TABLE IF EXISTS `boss_upgrade_log`;

CREATE TABLE `boss_upgrade_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `ori_level` int(11) DEFAULT NULL,
  `new_level` int(11) DEFAULT NULL,
  `a_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `gift_bag_config` */

DROP TABLE IF EXISTS `gift_bag_config`;

CREATE TABLE `gift_bag_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `goldcoin` int(11) DEFAULT NULL COMMENT '金币',
  `angry` int(11) DEFAULT NULL COMMENT '愤怒',
  `sunflower` int(11) DEFAULT NULL COMMENT '向日',
  `base_exp` int(11) DEFAULT NULL COMMENT '基地血量',
  `key` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '分类',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='礼包配置';

/*Table structure for table `goldcoin_log` */

DROP TABLE IF EXISTS `goldcoin_log`;

CREATE TABLE `goldcoin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `num` int(11) DEFAULT NULL COMMENT '数量',
  `type_key` varchar(50) DEFAULT NULL COMMENT '调用者的KEY',
  `memo` varchar(255) DEFAULT NULL COMMENT '备注描述',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `opt` tinyint(1) DEFAULT NULL COMMENT '1添加2减少',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='金币日志';

/*Table structure for table `magic_log` */

DROP TABLE IF EXISTS `magic_log`;

CREATE TABLE `magic_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '分类',
  `uid` int(11) DEFAULT NULL,
  `a_time` int(11) DEFAULT NULL,
  `num` int(11) DEFAULT NULL COMMENT '数量',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='魔法值消耗日志';

/*Table structure for table `merge_tower` */

DROP TABLE IF EXISTS `merge_tower`;

CREATE TABLE `merge_tower` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a_time` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `src_tower_id` int(11) DEFAULT NULL COMMENT '原塔ID',
  `tarter_tower_id` int(11) DEFAULT NULL COMMENT '目标塔ID',
  `src_map_id` int(11) DEFAULT NULL COMMENT '原塔地图位置ID',
  `tartet_map_id` int(11) DEFAULT NULL COMMENT '目标搭地图位置ID',
  `status` tinyint(1) DEFAULT NULL COMMENT '1正常2失效',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='合并塔';

/*Table structure for table `share` */

DROP TABLE IF EXISTS `share`;

CREATE TABLE `share` (
  `id` int(11) DEFAULT NULL,
  `key` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '分类',
  `a_time` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL COMMENT '1未成功2已成功',
  `uid` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户分享日志';

/*Table structure for table `sunflower_log` */

DROP TABLE IF EXISTS `sunflower_log`;

CREATE TABLE `sunflower_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_key` varchar(50) DEFAULT NULL,
  `a_time` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='向日葵消耗日志';

/*Table structure for table `task_config` */

DROP TABLE IF EXISTS `task_config`;

CREATE TABLE `task_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '标题描述',
  `gold` int(11) DEFAULT '0' COMMENT '奖励金币',
  `point` int(11) DEFAULT '0' COMMENT '奖励积分',
  `type` tinyint(4) NOT NULL COMMENT '1日常2成长',
  `step_num` tinyint(2) DEFAULT '0' COMMENT '完成任务总共几步',
  `type_sub` tinyint(4) NOT NULL COMMENT '1固定2随机',
  `is_off` tinyint(1) DEFAULT '0' COMMENT '0正常1关闭',
  `is_random_game` tinyint(1) DEFAULT '0' COMMENT '0否1是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `task_user` */

DROP TABLE IF EXISTS `task_user`;

CREATE TABLE `task_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `step` int(11) DEFAULT NULL COMMENT '当前完成了几步',
  `done_time` int(11) DEFAULT '0' COMMENT '0未完成完成时间',
  `gold` int(11) DEFAULT NULL COMMENT '奖励金币',
  `point` int(11) DEFAULT NULL COMMENT '奖励积分',
  `status` tinyint(4) DEFAULT NULL COMMENT '0正常1刷新了',
  `reward_time` int(11) DEFAULT '0' COMMENT '0用户未领取1领取时间',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `s_time` int(11) DEFAULT NULL COMMENT '有效期开始',
  `e_time` int(11) DEFAULT NULL COMMENT '有效期结束',
  `u_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
  `task_config_type` tinyint(1) DEFAULT '0' COMMENT 'config主类型',
  `task_config_type_sub` tinyint(1) DEFAULT NULL COMMENT 'config子类型',
  `hook_info` varchar(255) DEFAULT NULL COMMENT '钩子的一些信息',
  `game_id` int(11) DEFAULT NULL COMMENT '随机游戏ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `user` */

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `openid` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '三方平台ID',
  `type` tinyint(1) DEFAULT NULL COMMENT '平台',
  `base_level` int(11) DEFAULT NULL COMMENT '基地等级',
  `base_exp` int(11) DEFAULT NULL COMMENT '基地血量',
  `goldcoin` int(11) DEFAULT NULL COMMENT '金币数',
  `magic` int(11) DEFAULT NULL COMMENT '魔法数',
  `sunflower` int(11) DEFAULT NULL COMMENT '向日葵数',
  `angry` int(11) DEFAULT NULL COMMENT '愤怒数',
  `boss_level` int(11) DEFAULT NULL COMMENT 'BOSS等级',
  `a_time` int(11) DEFAULT NULL COMMENT '注册时间',
  `wx_session_key` varchar(100) CHARACTER SET utf8 DEFAULT NULL COMMENT '微信专属',
  `province` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '省',
  `city` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '市',
  `country` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '国家',
  `gender` tinyint(1) DEFAULT NULL COMMENT '性别0未知1男2女',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `IP` char(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
