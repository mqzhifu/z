/*
SQLyog Ultimate v11.24 (32 bit)
MySQL - 5.6.25-enterprise-commercial-advanced-log : Database - majiang
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`majiang` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `majiang`;

/*Table structure for table `const` */

DROP TABLE IF EXISTS `const`;

CREATE TABLE `const` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text COMMENT '内容',
  `status` int(11) DEFAULT '1' COMMENT '1打开2关闭',
  `title` varchar(50) DEFAULT NULL COMMENT '常量名称',
  `key` varchar(50) DEFAULT NULL COMMENT '常量英文名',
  `a_time` int(11) DEFAULT NULL,
  `up_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='常量表';

/*Table structure for table `group` */

DROP TABLE IF EXISTS `group`;

CREATE TABLE `group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uids` varchar(255) DEFAULT NULL COMMENT '4个用户，顺序为东南西北',
  `status` tinyint(4) DEFAULT NULL COMMENT '0未处理1准备中2进程中3结束',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `u_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
  `dices` varchar(20) DEFAULT NULL COMMENT '两次打骰子数',
  `room_id` int(11) DEFAULT NULL COMMENT '房间ID',
  `current_record_num` int(11) DEFAULT NULL COMMENT '当前打到第几张牌了',
  `current_uid` int(11) DEFAULT NULL COMMENT '当前抓牌的人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `group_record` */

DROP TABLE IF EXISTS `group_record`;

CREATE TABLE `group_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0' COMMENT '0未处理1用户手里2废弃3不能抓',
  `group_id` int(11) DEFAULT NULL COMMENT 'gorup表ID',
  `change_type` int(11) DEFAULT NULL COMMENT '1吃2碰3明杠4暗杠5胡',
  `title` varchar(50) DEFAULT NULL COMMENT '名称，条万桐等',
  `no` int(11) DEFAULT NULL COMMENT '最终的顺序',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `u_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
  `change_from_uid` int(11) DEFAULT NULL COMMENT '来自哪个用户打的',
  `is_show` tinyint(1) DEFAULT '0' COMMENT '1明牌显示2不显示',
  `change_id` int(11) DEFAULT NULL COMMENT '牌的ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8;

/*Table structure for table `group_user` */

DROP TABLE IF EXISTS `group_user`;

CREATE TABLE `group_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0' COMMENT '用户ID',
  `a_time` int(11) DEFAULT '0' COMMENT '添加时间',
  `u_time` int(11) DEFAULT '0' COMMENT '最后更新时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '0未处理1准备中2进行中3结束',
  `group_id` int(11) DEFAULT '0' COMMENT '组ID',
  `dice` tinyint(1) DEFAULT '0' COMMENT '用户打的骰子数',
  `room_id` int(11) DEFAULT NULL COMMENT '房间ID',
  `timeout` int(11) DEFAULT NULL COMMENT '报名失效时间',
  `sys_control` tinyint(1) DEFAULT '0' COMMENT '0未托管1系统接管',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `record_change` */

DROP TABLE IF EXISTS `record_change`;

CREATE TABLE `record_change` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_ids` int(11) DEFAULT NULL COMMENT '记录一组牌的ID',
  `type` varchar(20) DEFAULT NULL COMMENT '吃碰胡',
  `group_id` int(11) DEFAULT NULL COMMENT '属于哪个组',
  `uid` int(11) DEFAULT NULL COMMENT '操作用户ID',
  `from_uid` int(11) DEFAULT NULL COMMENT '从哪个用户来',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `u_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `from_record_id` int(11) DEFAULT NULL COMMENT '来自哪张牌',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='吃差胡';

/*Table structure for table `record_status` */

DROP TABLE IF EXISTS `record_status`;

CREATE TABLE `record_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) DEFAULT NULL,
  `chi_uid` int(11) DEFAULT NULL,
  `peng_uid` int(11) DEFAULT NULL,
  `gang_uid` int(11) DEFAULT NULL,
  `chi_status` tinyint(1) DEFAULT '0' COMMENT '0未处理1放弃2使用',
  `peng_status` tinyint(1) DEFAULT '0' COMMENT '0未处理1放弃2使用',
  `gang_status` tinyint(1) DEFAULT '0' COMMENT '0未处理1放弃2使用',
  `a_time` int(11) DEFAULT NULL,
  `u_time` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0' COMMENT '0未处理1已结束',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='吃差碰时，3家状态';

/*Table structure for table `room` */

DROP TABLE IF EXISTS `room`;

CREATE TABLE `room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dealer_no` int(11) DEFAULT '1' COMMENT '庄家顺序,第几人做庄',
  `a_time` int(11) DEFAULT NULL,
  `u_time` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL COMMENT '0未开始1进行中2已结束',
  `ps` varchar(50) DEFAULT NULL COMMENT '密码',
  `type` tinyint(1) DEFAULT '0' COMMENT '0用户创建1系统创建',
  `uid` int(11) DEFAULT NULL COMMENT '归属用户',
  `dealer_uid` int(11) DEFAULT NULL COMMENT '当前庄家用户',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `user` */

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uname` varchar(100) NOT NULL,
  `ps` char(32) NOT NULL,
  `a_time` int(11) DEFAULT NULL,
  `u_time` int(11) DEFAULT NULL,
  `gold_coin` int(11) DEFAULT NULL,
  `phone` char(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
