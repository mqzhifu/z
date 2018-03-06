/*
SQLyog Ultimate v11.24 (32 bit)
MySQL - 5.6.25-enterprise-commercial-advanced-log : Database - assistant
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`assistant` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `assistant`;

/*Table structure for table `admin_log` */

DROP TABLE IF EXISTS `admin_log`;

CREATE TABLE `admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `memo` text NOT NULL COMMENT '备注信息',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `IP` varchar(32) DEFAULT NULL COMMENT '访问者IP地址',
  `uid` int(11) NOT NULL COMMENT 'admin_uid',
  `cate` varchar(50) DEFAULT NULL COMMENT '分类',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COMMENT='管理员用户操作日志';

/*Data for the table `admin_log` */

insert  into `admin_log`(`id`,`memo`,`add_time`,`IP`,`uid`,`cate`) values (1,'登陆：2sister',1488710828,'127.0.0.1',2,'login'),(2,'登陆：2sister',1488711482,'127.0.0.1',2,'login'),(3,'登陆：2sister',1488863932,'114.112.74.239',2,'login'),(4,'登陆：2sister',1488875068,'114.112.74.239',2,'login'),(5,'登陆：2sister',1488894180,'59.108.54.37',2,'login'),(6,'登陆：2sister',1488939840,'114.112.74.239',2,'login'),(7,'登陆：2sister',1489024567,'114.112.74.239',2,'login'),(8,'登陆：2sister',1489044013,'114.252.72.16',2,'login'),(9,'登陆：2sister',1489051781,'171.105.11.51',2,'login'),(10,'登陆：2sister',1489055475,'171.105.11.51',2,'login'),(11,'登陆：2sister',1489109804,'180.142.12.33',2,'login'),(12,'登陆：2sister',1489110096,'114.112.74.239',2,'login'),(13,'登陆：2sister',1489217083,'171.105.8.255',2,'login'),(14,'登陆：2sister',1489242217,'59.108.54.34',2,'login'),(15,'登陆：2sister',1489380572,'114.112.74.239',2,'login'),(16,'登陆：2sister',1489458671,'114.112.74.239',2,'login'),(17,'登陆：2sister',1489460333,'171.105.14.86',2,'login'),(18,'登陆：2sister',1489542701,'114.112.74.239',2,'login'),(19,'登陆：2sister',1489548978,'171.105.11.43',2,'login'),(20,'登陆：2sister',1489555575,'114.112.74.239',2,'login'),(21,'登陆：2sister',1489557992,'114.112.74.239',2,'login'),(22,'登陆：2sister',1489629269,'171.105.14.34',2,'login'),(23,'登陆：2sister',1489737967,'171.105.13.193',2,'login'),(24,'登陆：2sister',1489977097,'171.105.13.143',2,'login'),(25,'登陆：2sister',1490020804,'59.108.54.36',2,'login'),(26,'登陆：2sister',1490062641,'180.142.12.213',2,'login'),(27,'登陆：2sister',1490090695,'114.112.74.239',2,'login'),(28,'登陆：2sister',1490146993,'114.112.74.239',2,'login'),(29,'登陆：2sister',1490159807,'171.105.12.3',2,'login'),(30,'登陆：2sister',1490233524,'114.112.74.239',2,'login'),(31,'登陆：2sister',1490251852,'171.105.14.165',2,'login'),(32,'登陆：2sister',1490319192,'114.112.74.239',2,'login'),(33,'登陆：2sister',1490321121,'171.105.12.190',2,'login'),(34,'登陆：2sister',1490360354,'59.108.54.34',2,'login'),(35,'登陆：2sister',1490418338,'171.105.8.12',2,'login'),(36,'登陆：2sister',1490441438,'59.108.54.36',2,'login'),(37,'登陆：2sister',1490511569,'171.105.15.218',2,'login'),(38,'登陆：2sister',1490532108,'59.108.54.36',2,'login'),(39,'登陆：2sister',1490579800,'114.112.74.239',2,'login'),(40,'登陆：2sister',1490582222,'180.142.12.223',2,'login'),(41,'登陆：2sister',1490592582,'114.112.74.239',2,'login'),(42,'登陆：2sister',1490665511,'114.112.74.239',2,'login'),(43,'登陆：2sister',1490672376,'171.105.12.215',2,'login'),(44,'登陆：2sister',1490696509,'114.112.74.239',2,'login'),(45,'登陆：2sister',1490751334,'114.112.74.239',2,'login'),(46,'登陆：admin',1490751954,'114.112.74.239',1,'login'),(47,'登陆：admin',1490752078,'114.112.74.239',1,'login'),(48,'登陆：2sister',1490761647,'171.105.10.138',2,'login'),(49,'登陆：2sister',1490771745,'171.105.10.138',2,'login'),(50,'登陆：2sister',1490840624,'171.105.14.144',2,'login'),(51,'登陆：2sister',1490932887,'114.112.74.239',2,'login'),(52,'登陆：2sister',1491459953,'171.105.13.57',2,'login');

/*Table structure for table `admin_user` */

DROP TABLE IF EXISTS `admin_user`;

CREATE TABLE `admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uname` varchar(200) DEFAULT NULL COMMENT '用户名',
  `ps` char(32) DEFAULT NULL COMMENT '密码',
  `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `is_online` tinyint(4) DEFAULT NULL COMMENT '1在线2离线',
  `max_session_num` int(11) DEFAULT '0' COMMENT '最大服务数',
  `servicing_sess_num` int(11) DEFAULT '0' COMMENT '当前会话数',
  `nickname` varchar(255) DEFAULT NULL COMMENT '昵称',
  `up_time` int(11) DEFAULT NULL,
  `wating_sess_num` int(11) DEFAULT '0' COMMENT '等待接入数',
  `close_sess_num` int(11) DEFAULT '0' COMMENT '已关闭会话',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='管理员用户';

/*Data for the table `admin_user` */

insert  into `admin_user`(`id`,`uname`,`ps`,`a_time`,`is_online`,`max_session_num`,`servicing_sess_num`,`nickname`,`up_time`,`wating_sess_num`,`close_sess_num`,`avatar`) values (1,'admin','75ae95e5e0e8f15f2de960cb626336fe',NULL,0,0,1,'客服admin',1490763373,1,0,NULL),(2,'2sister','0bbf445240f20e1ee44c690a857c8944',NULL,0,0,4,'2sister客服',1491460528,3,0,NULL),(5,'dddddd','338d811d532553557ca33be45b6bde55',1490166095,NULL,0,0,'ddddddd',1490166095,0,0,'/www/upload/admin_avatar/20170322/20170322150135_162408732058d2214fdd359.png');

/*Table structure for table `const` */

DROP TABLE IF EXISTS `const`;

CREATE TABLE `const` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text COMMENT '内容',
  `status` int(11) DEFAULT '0' COMMENT '0关闭1打开',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='常量表';

/*Data for the table `const` */

/*Table structure for table `orders` */

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(255) DEFAULT NULL,
  `customer_service` varchar(255) DEFAULT NULL COMMENT '客服',
  `product_type` int(11) DEFAULT NULL COMMENT '产品类型',
  `pay_type` int(11) DEFAULT NULL COMMENT '支付方式',
  `sequence_num` varchar(255) DEFAULT NULL COMMENT '支付平台流水号',
  `a_time` int(11) DEFAULT NULL COMMENT '下单时间',
  `pay_time` int(11) DEFAULT NULL COMMENT '支付时间',
  `code` varchar(255) DEFAULT NULL COMMENT '订单号',
  `status` tinyint(4) DEFAULT NULL COMMENT '1未处理2支付失败3支付成功4超时',
  `product_desc` varchar(255) DEFAULT NULL COMMENT '产品描述信息',
  `address` varchar(255) DEFAULT NULL COMMENT '收货地址',
  `price` decimal(10,2) DEFAULT NULL COMMENT '价格',
  `memo` varchar(255) DEFAULT NULL COMMENT '备注',
  `up_time` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='订单';

/*Data for the table `orders` */

insert  into `orders`(`id`,`openid`,`customer_service`,`product_type`,`pay_type`,`sequence_num`,`a_time`,`pay_time`,`code`,`status`,`product_desc`,`address`,`price`,`memo`,`up_time`,`admin_id`) values (1,'sdfs',NULL,2,NULL,NULL,1489039723,NULL,NULL,NULL,'dfsdfsdf','123123','1000.00','123123',1489039723,NULL),(2,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,2,NULL,NULL,1489477282,NULL,NULL,1,'鲜花','北京崇文门202号','99.99','记得要弄点水',1489477282,NULL);

/*Table structure for table `product` */

DROP TABLE IF EXISTS `product`;

CREATE TABLE `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `type` int(11) DEFAULT NULL COMMENT '分类',
  `a_time` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `memo` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品';

/*Data for the table `product` */

/*Table structure for table `product_type` */

DROP TABLE IF EXISTS `product_type`;

CREATE TABLE `product_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='产品类型';

/*Data for the table `product_type` */

insert  into `product_type`(`id`,`title`) values (1,'咖啡'),(2,'花'),(3,'其它');

/*Table structure for table `schedule` */

DROP TABLE IF EXISTS `schedule`;

CREATE TABLE `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `trigger_time` int(11) DEFAULT NULL COMMENT '提醒时间',
  `status` tinyint(1) DEFAULT '0' COMMENT '1未处理2已提醒3失败',
  `a_time` int(11) DEFAULT NULL,
  `up_time` int(11) DEFAULT NULL,
  `err_info` varchar(100) DEFAULT NULL COMMENT '提醒失败',
  `receive_log_id` int(11) DEFAULT NULL COMMENT '接收消息ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='日程提醒';

/*Data for the table `schedule` */

insert  into `schedule`(`id`,`openid`,`title`,`content`,`trigger_time`,`status`,`a_time`,`up_time`,`err_info`,`receive_log_id`) values (1,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','你好','PPT开会',1488861794,1,1488861794,1488861794,NULL,NULL),(2,'123123','123','3123123123',1488318600,0,1488962320,1488962320,NULL,NULL),(3,'123','','123123',1488318600,0,1488963263,1488963263,NULL,NULL),(5,'oGzvqv6BLbTJMgM0Dn40uS1troJg','111','项目上线',1489542600,0,1489476943,1489476943,NULL,NULL);

/*Table structure for table `send_all` */

DROP TABLE IF EXISTS `send_all`;

CREATE TABLE `send_all` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) DEFAULT NULL,
  `content` text,
  `gid` int(11) DEFAULT NULL,
  `wx_msg_id` varchar(100) DEFAULT NULL,
  `wx_media_id` varchar(255) DEFAULT NULL,
  `label_id` int(11) DEFAULT NULL,
  `openids` text,
  `status` tinyint(1) DEFAULT '0' COMMENT '0未处理1处理中2处理完成',
  `success_num` int(11) DEFAULT NULL,
  `fail_num` int(11) DEFAULT NULL,
  `a_time` int(11) DEFAULT NULL,
  `up_time` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `send_all` */

insert  into `send_all`(`id`,`type`,`content`,`gid`,`wx_msg_id`,`wx_media_id`,`label_id`,`openids`,`status`,`success_num`,`fail_num`,`a_time`,`up_time`,`admin_id`) values (1,'text','dfdf',NULL,NULL,'',NULL,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk,oGzvqv6BLbTJMgM0Dn40uS1troJg,oGzvqv0PpAn9cZacD1cxwVvMs8uw,oGzvqvxq22hWW6bUhHTRNMwdfrp8,oGzvqvxp5jOHqy9e1M_m7C5qMGbo,oGzvqv7sr0nRQrefY8xLCOv5rvvI,oGzvqvyMUEhSYSyxAbbGg4pfY1FY',0,NULL,NULL,1490947497,NULL,NULL),(2,'text','测试',NULL,NULL,'',NULL,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk,oGzvqv6BLbTJMgM0Dn40uS1troJg,oGzvqv0PpAn9cZacD1cxwVvMs8uw,oGzvqvxq22hWW6bUhHTRNMwdfrp8,oGzvqvxp5jOHqy9e1M_m7C5qMGbo,oGzvqv7sr0nRQrefY8xLCOv5rvvI,oGzvqvyMUEhSYSyxAbbGg4pfY1FY',0,NULL,NULL,1491460103,NULL,2);

/*Table structure for table `server` */

DROP TABLE IF EXISTS `server`;

CREATE TABLE `server` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `admin_id` varchar(255) DEFAULT NULL COMMENT '昵称',
  `is_online` varchar(25) DEFAULT '0' COMMENT '1在线2离线',
  `max_session_num` varchar(255) DEFAULT NULL COMMENT '可服务最大数',
  `servicing_sess_num` varchar(255) DEFAULT NULL COMMENT '正在服务数',
  `nickname` varchar(255) DEFAULT NULL COMMENT '昵称',
  `up_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='客服人员';

/*Data for the table `server` */

insert  into `server`(`id`,`admin_id`,`is_online`,`max_session_num`,`servicing_sess_num`,`nickname`,`up_time`) values (1,'2','1','100','2','xiaoz',NULL);

/*Table structure for table `server_session` */

DROP TABLE IF EXISTS `server_session`;

CREATE TABLE `server_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(255) DEFAULT NULL,
  `a_time` int(11) DEFAULT NULL,
  `up_time` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL COMMENT '客服ID',
  `receive_log_id` int(11) DEFAULT NULL COMMENT '接收表的主ID',
  `status` tinyint(4) DEFAULT NULL COMMENT '0未处理1未分配2等待中3进行中4结束',
  `receive_num` int(11) DEFAULT NULL COMMENT '共接收消息条数',
  `reply_num` int(11) DEFAULT NULL COMMENT '共回复消息条数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `server_session` */

insert  into `server_session`(`id`,`openid`,`a_time`,`up_time`,`admin_id`,`receive_log_id`,`status`,`receive_num`,`reply_num`) values (1,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1490752656,1490763372,1,0,3,2,4),(2,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',1490761671,1491460165,2,0,3,9,2),(3,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',1490771517,1491460186,2,0,3,5,1),(4,'oGzvqvyMUEhSYSyxAbbGg4pfY1FY',1490771859,1490772099,2,0,3,1,0),(5,'oGzvqv5DP-VAYcujo9uqxrOmePWc',1491460385,1491460528,2,0,3,1,0);

/*Table structure for table `service_task` */

DROP TABLE IF EXISTS `service_task`;

CREATE TABLE `service_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `openid` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0' COMMENT '1未处理2处理中3已处理',
  `product_type` int(11) DEFAULT NULL COMMENT '分类',
  `server_id` int(11) DEFAULT NULL COMMENT '客服ID',
  `a_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='服务队列';

/*Data for the table `service_task` */

/*Table structure for table `sess_msg` */

DROP TABLE IF EXISTS `sess_msg`;

CREATE TABLE `sess_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `content` text,
  `a_time` int(11) DEFAULT NULL,
  `openid` varchar(255) DEFAULT NULL,
  `sid` int(11) DEFAULT NULL,
  `cate` varchar(10) DEFAULT NULL COMMENT 'in:用户out客服',
  `err` varchar(100) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `wx_msg_id` varchar(100) DEFAULT NULL,
  `wx_media_id` varchar(100) DEFAULT NULL,
  `sendall_id` int(11) DEFAULT NULL,
  `msg_type` tinyint(4) DEFAULT '0' COMMENT '0普通1群发',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COMMENT='会话聊天记录';

/*Data for the table `sess_msg` */

insert  into `sess_msg`(`id`,`type`,`content`,`a_time`,`openid`,`sid`,`cate`,`err`,`admin_id`,`wx_msg_id`,`wx_media_id`,`sendall_id`,`msg_type`) values (1,'text','你好',1490752656,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'in','0',NULL,'','',NULL,0),(2,'text','真的是这样么？',1490752769,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'in','0',NULL,'','',NULL,0),(3,'text','你好',1490761671,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',2,'in','0',NULL,'','',NULL,0),(4,'text','是的呀',1490763406,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'out','0',NULL,NULL,NULL,NULL,0),(5,'image','/www/upload/send_wx_pic/20170329/20170329130134_23184361258db3faec8bd4.jpg',1490763697,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'out','0',NULL,NULL,'IdJ-gcMtglpDmoqVnpBpR7Lm4X2exhl1xxdQJ0pbzyvGJ6ThMYoFqW5ZsPydvkvO',NULL,0),(6,'text','行不行',1490765860,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'out','0',NULL,NULL,NULL,NULL,0),(7,'text','不行不行就是不行',1490765985,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'out','0',NULL,NULL,NULL,NULL,0),(8,'text','对手呀',1490766261,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'out','0',NULL,NULL,NULL,NULL,0),(9,'image','/www/upload/send_wx_pic/20170329/20170329134425_99705698358db49b997854.jpg',1490766268,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'out','0',NULL,NULL,'4N5GyWxtOSzXlZq0bTP6Z5AMjQVZ31ENYmuDRl9YSOeIgrnHA4wfQ8Q_o3DJ5YX_',NULL,0),(10,'image','/www/upload/send_wx_pic/20170329/20170329135445_77863507058db4c25f0205.jpg',1490766888,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'out','0',NULL,NULL,'XqCYv98Cd6du75m0flRfCwlGs_UxVnlgE8K6SisjMK9BV3b-bljmTNCYpQEs82fi',NULL,0),(11,'image','/www/upload/send_wx_pic/20170329/20170329135812_144925987058db4cf41780d.jpg',1490767095,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'out','0',NULL,NULL,'BhCtdd675NqFE4VnZnbvUg3Ra8AgW0v-SlX5wewtSqlwp8WKnDLagds8lpv5Ffkr',NULL,0),(12,'image','/www/upload/send_wx_pic/20170329/20170329135953_108851559758db4d599dbd7.png',1490767197,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'out','0',NULL,NULL,'Hk-w8C6ly2UhH-6x3Sz_EOQt3-DCPu75eljuOsvOI5OeW0B2IxksCSr_nTK-vDdx',NULL,0),(13,'image','/www/upload/send_wx_pic/20170329/20170329140132_206531195458db4dbc58a55.jpg',1490767295,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'out','0',NULL,NULL,'nEPwNbVBMkfpCdsm5RN3h3WF7pgSMh2KPmOOAidEa_GyRWL2qYwdqxVL70i0wfoo',NULL,0),(14,'image','/www/upload/send_wx_pic/20170329/20170329140242_17312519858db4e02697b2.jpg',1490767366,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'out','0',NULL,NULL,'EfSH2CIfQnpt9bYs44z9SCqxM270DqF4ZL0cDCIh5s-mkGnF77lPzA9ze4OvgAj9',NULL,0),(15,'image','/www/upload/send_wx_pic/20170329/20170329140305_187985823558db4e197eb3a.png',1490767390,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1,'out','0',NULL,NULL,'0lWnUNAY9VVmlN1cJFJnXEt2XDHgCBc175vlECcIPvi6ipgnXhUQ4ghSUj_-KYa2',NULL,0),(16,'text','11',1490771333,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',2,'in','0',2,'','',NULL,0),(17,'voice','/www/upload/voice/20170329/149077139088730.mp3',1490771390,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',2,'in','0',2,'6402814361991951849','iIeOKSkRdnQbyy8nqitogI9CqhghJDes9TxZzExAENpwluNfNJhv5jcNTk35CHPD',NULL,0),(18,'voice','/www/upload/voice/20170329/149077151671631.mp3',1490771517,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',3,'in','0',NULL,'6402814907452798543','ZvwOtRWQ_PHj39l77KsFvCcbxJk2BBAV-W2_yJwTepFabUwoN5RncSVPOHXEhqxi',NULL,0),(19,'text','khjk',1490771562,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',2,'out','0',NULL,NULL,NULL,NULL,0),(20,'text','2301321',1490771573,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',2,'in','0',2,'','',NULL,0),(21,'text','645453',1490771582,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',2,'in','0',2,'','',NULL,0),(22,'text','87747',1490771585,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',2,'in','0',2,'','',NULL,0),(23,'text','123',1490771715,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',3,'in','0',2,'','',NULL,0),(24,'text','1',1490771859,'oGzvqvyMUEhSYSyxAbbGg4pfY1FY',4,'in','0',NULL,'','',NULL,0),(25,'text','你好',1490840661,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',2,'in','0',2,'','',NULL,0),(26,'text','有什么可以帮助',1490840675,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',2,'out','0',NULL,NULL,NULL,NULL,0),(27,'text','我无聊',1490840683,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',2,'in','0',2,'','',NULL,0),(28,'text','您好',1490933332,'111',3,'out','1',NULL,NULL,NULL,NULL,0),(29,'text','你好',1491460035,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',3,'in','0',2,'','',NULL,0),(30,'text','1',1491460060,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',3,'out',NULL,NULL,NULL,NULL,NULL,0),(31,'text','123',1491460150,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',3,'in','0',2,'','',NULL,0),(32,'text','1',1491460165,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',2,'in','0',2,'','',NULL,0),(33,'text','到',1491460186,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',3,'in','0',2,'','',NULL,0),(34,'text','我',1491460385,'oGzvqv5DP-VAYcujo9uqxrOmePWc',5,'in','0',NULL,'','',NULL,0);

/*Table structure for table `user_label` */

DROP TABLE IF EXISTS `user_label`;

CREATE TABLE `user_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户标签';

/*Data for the table `user_label` */

/*Table structure for table `wx_app_bind` */

DROP TABLE IF EXISTS `wx_app_bind`;

CREATE TABLE `wx_app_bind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) DEFAULT NULL COMMENT '自填写MD5值',
  `skey` varchar(255) DEFAULT NULL COMMENT '微信随机生成',
  `appid` varchar(255) DEFAULT NULL COMMENT '微信端提供',
  `appsecret` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `is_secret` tinyint(4) DEFAULT NULL COMMENT '0未加密通信1加密',
  `wx_login_uname` varchar(255) DEFAULT NULL COMMENT '公众平台用户名',
  `wx_id` varchar(255) DEFAULT NULL COMMENT '搜索的ID号，微信平台自行设置',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='微信公众号列表';

/*Data for the table `wx_app_bind` */

insert  into `wx_app_bind`(`id`,`token`,`skey`,`appid`,`appsecret`,`title`,`is_secret`,`wx_login_uname`,`wx_id`) values (1,'1f35fc1bf9025fc3369b6d0f9390d2a9','XYOQro39YWZAqqGghZcIAd929QblSQ5gK8zPUmFMs2a','wx3a4c81892e8d15ba','2fa712e3fcd2558e8b691099a1ad5701','我是马仔',NULL,'2636326530@qq.com',NULL);

/*Table structure for table `wx_keyword_reply` */

DROP TABLE IF EXISTS `wx_keyword_reply`;

CREATE TABLE `wx_keyword_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keywords` varchar(255) DEFAULT NULL COMMENT '关键字',
  `search` varchar(255) DEFAULT NULL COMMENT '1精确2模糊',
  `reply_type` int(11) DEFAULT NULL COMMENT '1普通文字2多图文3图片',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='关键词命中-自动回复';

/*Data for the table `wx_keyword_reply` */

/*Table structure for table `wx_location` */

DROP TABLE IF EXISTS `wx_location`;

CREATE TABLE `wx_location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(100) DEFAULT NULL,
  `x` varchar(100) DEFAULT NULL,
  `y` varchar(100) DEFAULT NULL,
  `scale` varchar(10) DEFAULT NULL COMMENT '绽放比例',
  `label` varchar(255) DEFAULT NULL COMMENT '地图信息',
  `a_time` int(11) DEFAULT NULL,
  `Precision` varchar(100) DEFAULT NULL COMMENT '精确度',
  `up_time` int(11) DEFAULT NULL,
  `area_info` varchar(255) DEFAULT NULL COMMENT '经纬度换算成城市',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='用户位置';

/*Data for the table `wx_location` */

insert  into `wx_location`(`id`,`openid`,`x`,`y`,`scale`,`label`,`a_time`,`Precision`,`up_time`,`area_info`) values (6,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','39.956779','116.442810',NULL,NULL,1490260986,'65.000000',1490608854,'北京市朝阳区西坝河南路'),(7,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','39.956772','116.442772',NULL,NULL,1490263471,'65.000000',1490263668,'北京市朝阳区西坝河南路'),(8,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','24.306583','109.405754',NULL,NULL,1490266055,'40.000000',1490593016,'广西壮族自治区柳州市柳南区红光路13号'),(9,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','24.306219','109.405785',NULL,NULL,1490266506,'184.000000',1490355561,'广西壮族自治区柳州市柳南区红光路55-56号'),(10,'oGzvqvxq22hWW6bUhHTRNMwdfrp8','24.343199','109.422478',NULL,NULL,1490270564,'65.000000',1490653292,'广西壮族自治区柳州市柳北区滨江东路');

/*Table structure for table `wx_receive_log` */

DROP TABLE IF EXISTS `wx_receive_log`;

CREATE TABLE `wx_receive_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `type` varchar(50) DEFAULT NULL,
  `a_time` int(11) DEFAULT NULL,
  `openid` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `event_key` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=357 DEFAULT CHARSET=utf8 COMMENT='接收日志';

/*Data for the table `wx_receive_log` */

insert  into `wx_receive_log`(`id`,`content`,`type`,`a_time`,`openid`,`event`,`event_key`) values (1,'抱歉，没搜索到数据','0',1488861794,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(2,'抱歉，没搜索到数据','text',1488861841,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(3,'抱歉，没搜索到数据','text',1488863029,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(4,NULL,NULL,1488863074,NULL,NULL,NULL),(5,'不行','text',1488863247,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(6,'','event',1488871743,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(7,'','event',1488871809,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(8,'','event',1488871952,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(9,'','event',1488872531,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(10,'','event',1488872551,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(11,'','event',1488872569,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(12,'','event',1488872836,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(13,'','event',1488879622,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(14,'','event',1488879626,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(15,'','event',1488880568,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(16,'','event',1488880632,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(17,'','event',1488880762,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',NULL,NULL),(18,'','event',1488894320,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(19,'','event',1488977927,'oGzvqv0dgOrmvXVUbxeWPClVEOUU','qualification_verify_success',''),(20,'','event',1489048169,'oGzvqv6BLbTJMgM0Dn40uS1troJg','subscribe',''),(21,'','event',1489048181,'oGzvqv6BLbTJMgM0Dn40uS1troJg','VIEW','http://www.baidu.com'),(22,'','event',1489048183,'oGzvqv6BLbTJMgM0Dn40uS1troJg','VIEW','http://www.baidu.com'),(23,'','event',1489124003,'oGzvqv0dgOrmvXVUbxeWPClVEOUU','naming_verify_success',''),(24,'','event',1489242658,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(25,'','event',1489242770,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(26,'','event',1489243442,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(27,'','event',1489397004,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(28,'','event',1489397138,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(29,'','event',1489397160,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(30,'','event',1489397238,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(31,'','event',1489397342,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(32,'','event',1489397934,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(33,'','event',1489397959,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(34,'','event',1489398039,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(35,'','event',1489398754,'oGzvqv6BLbTJMgM0Dn40uS1troJg','CLICK','schedule'),(36,'','event',1489399524,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','unsubscribe',''),(37,'','event',1489399789,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','subscribe',''),(38,'','text',1489459494,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(39,'','text',1489459499,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(40,'','text',1489463547,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(41,'','text',1489472295,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(42,'','text',1489472334,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(43,'','text',1489472434,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(44,'','text',1489472528,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(45,'你号','text',1489472586,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(46,'你号','text',1489472600,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(47,'好吧，可以了','text',1489473511,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(48,'好吧','text',1489569009,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(49,'','event',1489569601,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(50,'','event',1489569643,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(51,'','event',1489569678,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(52,'','event',1489569803,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','subscribe',''),(53,'','event',1489569809,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','CLICK','schedule'),(54,'','event',1489569872,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','CLICK','schedule'),(55,'','event',1489569930,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(56,'','event',1489569958,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(57,'','event',1489570011,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(58,'','event',1489570097,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(59,'','event',1489570143,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(60,'在的亲','text',1489571822,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(61,'你好','text',1490090885,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(62,'','event',1490094440,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(63,'好搭','text',1490096442,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(64,'真的','text',1490096507,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(65,'想不想','text',1490097819,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(66,'好吧你赢了','text',1490097837,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(67,'嗯，在的~','text',1490172323,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(68,'需要的~亲','text',1490172461,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(69,'行','text',1490256972,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(70,'','event',1490256983,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(71,'','event',1490257018,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(72,'','event',1490257083,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(73,'','event',1490257288,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(74,'',NULL,1490257335,NULL,'',''),(75,'','event',1490257354,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(76,'','event',1490257966,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','subscribe',''),(77,'','event',1490257969,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(78,'你好','text',1490257975,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(79,'','event',1490257976,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(80,'','event',1490258034,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(81,'','event',1490258039,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(82,'','event',1490258040,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','schedule'),(83,'','event',1490258055,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','coffee'),(84,'','event',1490258185,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(85,'对的','text',1490258189,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(86,'','event',1490258298,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(87,'','event',1490258492,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(88,'','event',1490258519,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(89,'','event',1490258538,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(90,'','event',1490258562,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(91,'','event',1490258619,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(92,'','event',1490258719,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(93,'','event',1490258908,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(94,'','event',1490258993,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(95,'','event',1490259055,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(96,'你好','text',1490259178,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(97,'你好','text',1490259232,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(98,'你家婆','text',1490259376,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(99,'','event',1490259380,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(100,'','event',1490259388,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(101,'','event',1490259996,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(102,'','event',1490260013,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(103,'','event',1490260986,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(104,'','event',1490261008,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(105,'','event',1490262314,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(106,'','event',1490262320,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(107,'','event',1490262343,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(108,'','event',1490262601,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(109,'','event',1490262631,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(110,'','event',1490262692,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(111,'','event',1490262833,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(112,'','event',1490263209,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','CLICK','schedule'),(113,'你好','text',1490263300,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','',''),(114,'','event',1490263331,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','CLICK','schedule'),(115,'','event',1490263367,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','CLICK','flower'),(116,'','event',1490263378,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','VIEW','http://www.baidu.com'),(117,'','event',1490263387,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','CLICK','schedule'),(118,'','event',1490263399,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','CLICK','coffee'),(119,'','event',1490263410,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','VIEW','http://www.baidu.com'),(120,'','event',1490263427,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','VIEW','http://www.baidu.com'),(121,'','event',1490263431,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','CLICK','schedule'),(122,'','event',1490263471,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','LOCATION',''),(123,'/:X-)/:X-)','text',1490263574,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','',''),(124,'','event',1490263607,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','LOCATION',''),(125,'','event',1490263668,'oGzvqv0PpAn9cZacD1cxwVvMs8uw','LOCATION',''),(126,'','event',1490266055,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(127,'','event',1490266055,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','VIEW','http://www.baidu.com'),(128,'','event',1490266064,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','schedule'),(129,'','event',1490266064,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(130,'','event',1490266071,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','coffee'),(131,'','event',1490266102,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','flower'),(132,'','event',1490266250,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(133,'你好','text',1490266273,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(134,'','event',1490266284,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(135,'','event',1490266342,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(136,'','event',1490266483,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','CLICK','schedule'),(137,'','event',1490266490,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','VIEW','http://www.baidu.com'),(138,'','event',1490266499,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','VIEW','http://www.baidu.com'),(139,'','event',1490266506,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','LOCATION',''),(140,'','event',1490266516,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','CLICK','schedule'),(141,'','event',1490269192,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','LOCATION',''),(142,'','event',1490270559,'oGzvqvxq22hWW6bUhHTRNMwdfrp8','subscribe',''),(143,'','event',1490270564,'oGzvqvxq22hWW6bUhHTRNMwdfrp8','LOCATION',''),(144,'','event',1490270569,'oGzvqvxq22hWW6bUhHTRNMwdfrp8','CLICK','coffee'),(145,'','event',1490270574,'oGzvqvxq22hWW6bUhHTRNMwdfrp8','VIEW','http://www.baidu.com'),(146,'','event',1490270577,'oGzvqvxq22hWW6bUhHTRNMwdfrp8','CLICK','schedule'),(147,'','event',1490271464,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','LOCATION',''),(148,'','event',1490275224,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(149,'','event',1490321312,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(150,'你好','text',1490321316,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(151,'','event',1490321326,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','unsubscribe',''),(152,'','event',1490321538,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','subscribe',''),(153,'','event',1490321540,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(154,'','event',1490321541,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','schedule'),(155,'你好','text',1490321559,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(156,'你好','text',1490321572,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(157,'','event',1490321681,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(158,'','event',1490321681,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','VIEW','http://www.baidu.com'),(159,'','event',1490321685,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','coffee'),(160,'','event',1490321690,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','schedule'),(161,'为什么','text',1490321700,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(162,'','event',1490321734,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(163,'','event',1490321791,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(164,'','event',1490321800,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','flower'),(165,'','event',1490325899,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(166,'不知道','text',1490325921,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(167,'','event',1490326047,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(168,'','event',1490326071,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(169,'','event',1490328983,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(170,'测试','text',1490328989,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(171,'123','text',1490328999,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(172,'你好','text',1490329005,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(173,'/::|','text',1490329013,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(174,'','event',1490329158,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(175,'真有意思','text',1490329167,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(176,'你好','text',1490329426,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(177,'我可以做什么','text',1490330188,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(178,'','event',1490355299,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','schedule'),(179,'你好','text',1490355319,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(180,'有什么可以服务','text',1490355376,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(181,'222222','text',1490355385,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(182,'444444','text',1490355407,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(183,'3333','text',1490355420,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(184,'2222','text',1490355421,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(185,'1111','text',1490355422,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(186,'','event',1490355519,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','LOCATION',''),(187,'','event',1490355520,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','VIEW','http://www.baidu.com'),(188,'','event',1490355561,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','LOCATION',''),(189,'你好','text',1490355583,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(190,'','event',1490355640,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','unsubscribe',''),(191,'','event',1490355691,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','subscribe',''),(192,'你好','text',1490355713,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(193,'','event',1490355753,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','VIEW','http://www.baidu.com'),(194,'','event',1490355766,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','CLICK','schedule'),(195,'我想买啤酒','text',1490355774,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(196,'111','text',1490355789,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(197,'2222','text',1490355795,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(198,'3333','text',1490355803,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(199,'nihao ','text',1490356714,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(200,'','event',1490357558,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(201,'','voice',1490357562,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(202,'','image',1490357633,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(203,'???','text',1490358278,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(204,'444','text',1490364262,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(205,'可以帮忙么','text',1490418364,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(206,'','image',1490442624,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(207,'','image',1490442685,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(208,'','image',1490442770,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(209,'','event',1490442793,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(210,'','event',1490442794,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','VIEW','http://www.baidu.com'),(211,'','event',1490442797,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(212,'','image',1490442809,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(213,'','image',1490442881,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(214,'','image',1490442893,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(215,'','image',1490442913,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(216,'','image',1490443312,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(217,'','voice',1490443634,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(218,'','voice',1490444203,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(219,'','voice',1490444301,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(220,'','voice',1490445190,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(221,'','voice',1490445225,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(222,'','voice',1490445534,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(223,'','voice',1490446690,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(224,'','voice',1490446752,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(225,'','event',1490446813,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(226,'','voice',1490446903,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(227,'','voice',1490447569,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(228,'','voice',1490447589,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(229,'','voice',1490447864,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(230,'','voice',1490453464,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(231,'','voice',1490453629,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(232,'','voice',1490453708,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(233,'','voice',1490453943,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(234,'','image',1490454291,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(235,'','image',1490454567,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(236,'','voice',1490454613,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(237,'','image',1490461569,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(238,'','image',1490461788,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(239,'','image',1490461840,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(240,'','image',1490462055,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(241,'你好','text',1490511610,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(242,'','event',1490530840,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(243,'','event',1490530840,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','schedule'),(244,'','event',1490530846,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','coffee'),(245,'','event',1490530851,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','flower'),(246,'','event',1490530853,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','VIEW','http://www.baidu.com'),(247,'','event',1490530859,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(248,'','image',1490531381,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(249,'行不行啊','text',1490541354,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(250,'真的呀 ？','text',1490541361,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(251,'','event',1490575756,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(252,'1','text',1490575767,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(253,'','event',1490575782,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','VIEW','http://www.baidu.com'),(254,'','event',1490575788,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(255,'','event',1490582302,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(256,'','event',1490582316,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(257,'','image',1490582317,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(258,'11','text',1490582496,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(259,'11','text',1490582501,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(260,'11','text',1490582503,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(261,'','event',1490582540,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(262,'','event',1490582625,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(263,'11','text',1490582676,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(264,'111','text',1490582678,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(265,'11','text',1490582681,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(266,'1','text',1490582806,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(267,'2','text',1490582812,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(268,'3','text',1490582840,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(269,'4','text',1490582840,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(270,'你好','text',1490588455,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(271,'行不行','text',1490588481,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(272,'对的','text',1490588535,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(273,'','event',1490588623,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(274,'好的','text',1490588626,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(275,'是的','text',1490588688,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(276,'对哒','text',1490588893,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(277,'球球啊','text',1490588940,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(278,'哎无语了我就','text',1490588961,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(279,'','event',1490592896,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(280,'','event',1490593016,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(281,'','event',1490603012,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(282,'/::D','text',1490607207,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(283,'/::D','text',1490608284,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(284,'','text',1490608330,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(285,'[红包]','text',1490608354,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(286,'/::)','text',1490608384,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(287,'/:!!!','text',1490608393,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(288,'/:8*','text',1490608403,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(289,'/:showlove','text',1490608408,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(290,'','text',1490608415,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(291,'[皱眉]','text',1490608428,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(292,'【收到不支持的消息类型，暂无法显示】','text',1490608479,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(293,'','event',1490608854,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','LOCATION',''),(294,'','text',1490608881,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(295,'','event',1490653292,'oGzvqvxq22hWW6bUhHTRNMwdfrp8','LOCATION',''),(296,'','event',1490653296,'oGzvqvxq22hWW6bUhHTRNMwdfrp8','CLICK','schedule'),(297,'','event',1490653297,'oGzvqvxq22hWW6bUhHTRNMwdfrp8','VIEW','http://www.baidu.com'),(298,'','event',1490653304,'oGzvqvxq22hWW6bUhHTRNMwdfrp8','CLICK','coffee'),(299,'？','text',1490672419,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(300,'','event',1490672437,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','CLICK','schedule'),(301,'？','text',1490672475,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(302,'？','text',1490672480,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(303,'？','text',1490672826,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(304,'？','text',1490672833,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(305,'1','text',1490672834,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(306,'2','text',1490672834,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(307,'3','text',1490672835,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(308,'4','text',1490672836,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(309,'5','text',1490672836,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(310,'6','text',1490672837,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(311,'7','text',1490672842,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(312,'8','text',1490672842,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(313,'9','text',1490672843,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(314,'10','text',1490672844,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(315,'11','text',1490672852,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(316,'2','text',1490672853,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(317,'123','text',1490672855,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(318,'你好','text',1490696342,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(319,'你好','text',1490696436,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(320,'你好','text',1490696486,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(321,'你好','text',1490696587,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(322,'行不行','text',1490696595,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(323,'你好','text',1490696656,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(324,'你好','text',1490696747,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(325,'','event',1490750567,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(326,'','event',1490750569,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(327,'','event',1490750572,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','coffee'),(328,'','event',1490750574,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','flower'),(329,'赶紧啊~','text',1490751352,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(330,'','event',1490752623,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','CLICK','schedule'),(331,'你好','text',1490752656,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(332,'真的是这样么？','text',1490752769,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk','',''),(333,'你好','text',1490761671,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(334,'11','text',1490771333,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(335,'','event',1490771385,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(336,'','voice',1490771389,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(337,'','event',1490771421,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','LOCATION',''),(338,'','voice',1490771516,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(339,'2301321','text',1490771573,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(340,'645453','text',1490771582,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(341,'87747','text',1490771585,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(342,'123','text',1490771715,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(343,'','event',1490771847,'oGzvqvyMUEhSYSyxAbbGg4pfY1FY','subscribe',''),(344,'','event',1490771858,'oGzvqvyMUEhSYSyxAbbGg4pfY1FY','LOCATION',''),(345,'1','text',1490771859,'oGzvqvyMUEhSYSyxAbbGg4pfY1FY','',''),(346,'你好','text',1490840661,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(347,'我无聊','text',1490840683,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(348,'你好','text',1491460035,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(349,'123','text',1491460150,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(350,'1','text',1491460165,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo','',''),(351,'到','text',1491460186,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','',''),(352,'','event',1491460351,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','CLICK','schedule'),(353,'','event',1491460358,'oGzvqv7sr0nRQrefY8xLCOv5rvvI','VIEW','http://www.baidu.com'),(354,'','event',1491460375,'oGzvqv5DP-VAYcujo9uqxrOmePWc','subscribe',''),(355,'我','text',1491460385,'oGzvqv5DP-VAYcujo9uqxrOmePWc','',''),(356,'','event',1491460409,'oGzvqv5DP-VAYcujo9uqxrOmePWc','unsubscribe','');

/*Table structure for table `wx_reply_log` */

DROP TABLE IF EXISTS `wx_reply_log`;

CREATE TABLE `wx_reply_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `type` varchar(50) DEFAULT NULL,
  `a_time` int(11) DEFAULT NULL,
  `openid` varchar(255) DEFAULT NULL,
  `receive_log_id` int(11) DEFAULT NULL COMMENT '外键，接收表ID',
  `kewyord_reply_id` int(11) DEFAULT NULL COMMENT '外键，关键字表ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8 COMMENT='回复日志';

/*Data for the table `wx_reply_log` */

insert  into `wx_reply_log`(`id`,`content`,`type`,`a_time`,`openid`,`receive_log_id`,`kewyord_reply_id`) values (1,'抱歉，没搜索到数据','0',1488863075,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',4,NULL),(2,'抱歉，没搜索到数据','text',1488863247,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',5,NULL),(3,'302','text',1489243442,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',26,NULL),(4,'302','text',1489397004,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',27,NULL),(5,'302','text',1489397138,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',28,NULL),(6,'302','text',1489397160,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',29,NULL),(7,'302','text',1489397342,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',31,NULL),(8,'会话已创建，但客服均在忙，等待接入中，请等待','text',1489397934,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',32,NULL),(9,'客服：,等待接入中或者未分配....','text',1489398040,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',34,NULL),(10,'会话已创建，但客服均在忙，等待接入中，请等待','text',1489398754,'oGzvqv6BLbTJMgM0Dn40uS1troJg',35,NULL),(11,'你好','text',1489459494,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',38,NULL),(12,'不行啊','text',1489459499,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',39,NULL),(13,'好达','text',1489463547,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',40,NULL),(14,'客服系统正在服务中....','text',1489472434,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',43,NULL),(15,'客服系统正在服务中....','text',1489472601,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',46,NULL),(16,'客服系统正在服务中....','text',1489473511,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',47,NULL),(17,'客服系统正在服务中....','text',1489569009,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',48,NULL),(18,'会话已创建，但客服均未上线，请等待','text',1489569678,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',51,NULL),(19,'会话已创建，但客服均在忙，等待接入中，请等待','text',1489569809,'oGzvqv0PpAn9cZacD1cxwVvMs8uw',53,NULL),(20,'会话已创建，但客服均在忙，等待接入中，请等待','text',1489569872,'oGzvqv0PpAn9cZacD1cxwVvMs8uw',54,NULL),(21,'会话已创建，但客服均在忙，等待接入中，请等待','text',1489570143,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',59,NULL),(22,'客服系统正在服务中....','text',1489571822,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',60,NULL),(23,'客服系统正在服务中....','text',1490090885,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',61,NULL),(24,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490094440,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',62,NULL),(25,'客服系统正在服务中....','text',1490096442,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',63,NULL),(26,'客服系统正在服务中....','text',1490096507,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',64,NULL),(27,'客服系统正在服务中....','text',1490097819,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',65,NULL),(28,'客服系统正在服务中....','text',1490097837,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',66,NULL),(29,'客服系统正在服务中....','text',1490172323,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',67,NULL),(30,'客服系统正在服务中....','text',1490172461,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',68,NULL),(31,'客服系统正在服务中....','text',1490256972,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',69,NULL),(32,'客服系统正在服务中....','text',1490258189,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',85,NULL),(33,'客服系统正在服务中....','text',1490259178,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',96,NULL),(34,'客服系统正在服务中....','text',1490259232,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',97,NULL),(35,'客服系统正在服务中....','text',1490259376,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',98,NULL),(36,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490263209,'oGzvqv0PpAn9cZacD1cxwVvMs8uw',112,NULL),(37,'客服系统正在服务中....','text',1490263300,'oGzvqv0PpAn9cZacD1cxwVvMs8uw',113,NULL),(38,'客服：,正在服务中....','text',1490263331,'oGzvqv0PpAn9cZacD1cxwVvMs8uw',114,NULL),(39,'客服：,正在服务中....','text',1490263387,'oGzvqv0PpAn9cZacD1cxwVvMs8uw',117,NULL),(40,'客服：,正在服务中....','text',1490263431,'oGzvqv0PpAn9cZacD1cxwVvMs8uw',121,NULL),(41,'客服系统正在服务中....','text',1490263574,'oGzvqv0PpAn9cZacD1cxwVvMs8uw',123,NULL),(42,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490266064,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',128,NULL),(43,'客服系统正在服务中....','text',1490266273,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',133,NULL),(44,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490266483,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',136,NULL),(45,'客服：,等待接入中或者未分配....','text',1490266516,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',140,NULL),(46,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490270577,'oGzvqvxq22hWW6bUhHTRNMwdfrp8',146,NULL),(47,'客服系统正在服务中....','text',1490321316,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',150,NULL),(48,'客服：,等待接入中或者未分配....','text',1490321541,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',154,NULL),(49,'客服系统正在服务中....','text',1490321559,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',155,NULL),(50,'客服系统正在服务中....','text',1490321572,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',156,NULL),(51,'客服：,等待接入中或者未分配....','text',1490321690,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',160,NULL),(52,'客服系统正在服务中....','text',1490321700,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',161,NULL),(53,'客服系统正在服务中....','text',1490325921,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',166,NULL),(54,'测试','text',1490328989,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',170,NULL),(55,'123','text',1490328999,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',171,NULL),(56,'你好','text',1490329005,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',172,NULL),(57,'/::|','text',1490329013,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',173,NULL),(58,'真有意思','text',1490329167,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',175,NULL),(59,'你好','text',1490329426,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',176,NULL),(60,'我可以做什么','text',1490330188,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',177,NULL),(61,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490355299,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',178,NULL),(62,'客服系统正在服务中....','text',1490355319,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',179,NULL),(63,'客服系统正在服务中....','text',1490355376,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',180,NULL),(64,'客服系统正在服务中....','text',1490355385,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',181,NULL),(65,'客服系统正在服务中....','text',1490355407,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',182,NULL),(66,'客服系统正在服务中....','text',1490355420,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',183,NULL),(67,'客服系统正在服务中....','text',1490355421,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',184,NULL),(68,'客服系统正在服务中....','text',1490355422,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',185,NULL),(69,'你好','text',1490355583,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',189,NULL),(70,'你好','text',1490355713,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',192,NULL),(71,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490355766,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',194,NULL),(72,'客服系统正在服务中....','text',1490355774,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',195,NULL),(73,'客服系统正在服务中....','text',1490355789,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',196,NULL),(74,'客服系统正在服务中....','text',1490355795,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',197,NULL),(75,'客服系统正在服务中....','text',1490355803,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',198,NULL),(76,'客服系统正在服务中....','text',1490356714,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',199,NULL),(77,'???','text',1490358278,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',203,NULL),(78,'444','text',1490364262,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',204,NULL),(79,'可以帮忙么','text',1490418364,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',205,NULL),(80,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490442797,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',211,NULL),(81,'客服系统正在服务中....','text',1490442809,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',212,NULL),(82,'客服系统正在服务中....','text',1490443313,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',216,NULL),(83,'客服系统正在服务中....','text',1490446752,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',224,NULL),(84,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490446813,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',225,NULL),(85,'客服系统正在服务中....','text',1490446903,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',226,NULL),(86,'客服系统正在服务中....','text',1490447569,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',227,NULL),(87,'客服系统正在服务中....','text',1490447590,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',228,NULL),(88,'客服系统正在服务中....','text',1490447864,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',229,NULL),(89,'客服系统正在服务中....','text',1490453944,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',233,NULL),(90,'客服系统正在服务中....','text',1490454291,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',234,NULL),(91,'客服系统正在服务中....','text',1490454567,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',235,NULL),(92,'客服系统正在服务中....','text',1490454614,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',236,NULL),(93,'客服系统正在服务中....','text',1490462055,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',240,NULL),(94,'你好','text',1490511610,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',241,NULL),(95,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490530840,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',243,NULL),(96,'客服系统正在服务中....','text',1490531381,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',248,NULL),(97,'客服系统正在服务中....','text',1490541354,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',249,NULL),(98,'客服系统正在服务中....','text',1490541361,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',250,NULL),(99,'客服系统正在服务中....','text',1490575767,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',252,NULL),(100,'客服系统正在服务中....','text',1490582317,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',257,NULL),(101,'客服系统正在服务中....','text',1490582496,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',258,NULL),(102,'客服系统正在服务中....','text',1490582501,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',259,NULL),(103,'客服系统正在服务中....','text',1490582503,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',260,NULL),(104,'客服系统正在服务中....','text',1490582676,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',263,NULL),(105,'客服系统正在服务中....','text',1490582678,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',264,NULL),(106,'客服系统正在服务中....','text',1490582681,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',265,NULL),(107,'客服系统正在服务中....','text',1490582806,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',266,NULL),(108,'客服系统正在服务中....','text',1490582812,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',267,NULL),(109,'客服系统正在服务中....','text',1490582840,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',268,NULL),(110,'客服系统正在服务中....','text',1490582840,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',269,NULL),(111,'客服系统正在服务中....','text',1490588455,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',270,NULL),(112,'客服系统正在服务中....','text',1490588481,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',271,NULL),(113,'客服系统正在服务中....','text',1490588535,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',272,NULL),(114,'客服系统正在服务中....','text',1490588626,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',274,NULL),(115,'客服系统正在服务中....','text',1490588688,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',275,NULL),(116,'客服系统正在服务中....','text',1490588893,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',276,NULL),(117,'客服系统正在服务中....','text',1490588940,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',277,NULL),(118,'客服系统正在服务中....','text',1490588961,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',278,NULL),(119,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490603012,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',281,NULL),(120,'客服系统正在服务中....','text',1490607207,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',282,NULL),(121,'客服系统正在服务中....','text',1490608284,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',283,NULL),(122,'客服系统正在服务中....','text',1490608330,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',284,NULL),(123,'客服系统正在服务中....','text',1490608354,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',285,NULL),(124,'客服系统正在服务中....','text',1490608384,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',286,NULL),(125,'客服系统正在服务中....','text',1490608393,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',287,NULL),(126,'客服系统正在服务中....','text',1490608403,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',288,NULL),(127,'客服系统正在服务中....','text',1490608408,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',289,NULL),(128,'客服系统正在服务中....','text',1490608415,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',290,NULL),(129,'客服系统正在服务中....','text',1490608428,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',291,NULL),(130,'客服系统正在服务中....','text',1490608479,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',292,NULL),(131,'客服系统正在服务中....','text',1490608881,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',294,NULL),(132,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490653296,'oGzvqvxq22hWW6bUhHTRNMwdfrp8',296,NULL),(133,'？','text',1490672419,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',299,NULL),(134,'会话已创建，但客服均在忙，等待接入中，请等待','text',1490672437,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',300,NULL),(135,'客服系统正在服务中....','text',1490672475,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',301,NULL),(136,'客服系统正在服务中....','text',1490672480,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',302,NULL),(137,'客服系统正在服务中....','text',1490672826,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',303,NULL),(138,'客服系统正在服务中....','text',1490672833,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',304,NULL),(139,'客服系统正在服务中....','text',1490672834,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',305,NULL),(140,'客服系统正在服务中....','text',1490672834,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',306,NULL),(141,'客服系统正在服务中....','text',1490672835,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',307,NULL),(142,'客服系统正在服务中....','text',1490672836,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',308,NULL),(143,'客服系统正在服务中....','text',1490672836,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',309,NULL),(144,'客服系统正在服务中....','text',1490672837,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',310,NULL),(145,'客服系统正在服务中....','text',1490672842,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',311,NULL),(146,'客服系统正在服务中....','text',1490672842,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',312,NULL),(147,'客服系统正在服务中....','text',1490672843,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',313,NULL),(148,'客服系统正在服务中....','text',1490672844,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',314,NULL),(149,'客服系统正在服务中....','text',1490672852,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',315,NULL),(150,'客服系统正在服务中....','text',1490672853,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',316,NULL),(151,'客服系统正在服务中....','text',1490672855,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',317,NULL),(152,'客服：,正在忙~等待接入中....','text',1490696487,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',320,NULL),(153,'客服：,正在忙~等待接入中....','text',1490696595,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',322,NULL),(154,'客服：客服admin,正在忙~等待接入中....','text',1490696747,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',324,NULL),(155,'感谢您点击菜单','text',1490750567,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',325,NULL),(156,'感谢您点击菜单','text',1490750569,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',326,NULL),(157,'感谢您点击菜单','text',1490750573,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',327,NULL),(158,'感谢您点击菜单','text',1490750574,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',328,NULL),(159,'客服：,正在忙~等待接入中....','text',1490751352,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',329,NULL),(160,'感谢您点击菜单','text',1490752623,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',330,NULL),(161,'会话已创建，但客服均未上线，请等待','text',1490752656,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',331,NULL),(162,'会话已建立，但是所有客服均在忙，未分配客服,请耐心等待....','text',1490752769,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',332,NULL),(163,'会话已创建，但客服均未上线，请等待','text',1490761671,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',333,NULL),(164,'会话已建立，但是所有客服均在忙，未分配客服,请耐心等待....','text',1490771333,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',334,NULL),(165,'客服：2sister客服,正在服务中....','text',1490771390,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',336,NULL),(166,'会话已创建，但客服均未上线，请等待','text',1490771517,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',338,NULL),(167,'客服：2sister客服,正在服务中....','text',1490771573,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',339,NULL),(168,'客服：2sister客服,正在服务中....','text',1490771582,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',340,NULL),(169,'客服：2sister客服,正在服务中....','text',1490771585,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',341,NULL),(170,'客服：客服2,正在服务中....','text',1490771715,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',342,NULL),(171,'会话已创建，但客服均未上线，请等待','text',1490771859,'oGzvqvyMUEhSYSyxAbbGg4pfY1FY',345,NULL),(172,'客服：2sister客服,正在服务中....','text',1490840661,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',346,NULL),(173,'客服：2sister客服,正在服务中....','text',1490840683,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',347,NULL),(174,'客服：客服2,正在服务中....','text',1491460035,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',348,NULL),(175,'客服：客服2,正在服务中....','text',1491460150,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',349,NULL),(176,'客服：2sister客服,正在服务中....','text',1491460165,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',350,NULL),(177,'客服：客服2,正在服务中....','text',1491460186,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',351,NULL),(178,'感谢您点击菜单','text',1491460351,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',352,NULL),(179,'会话已创建，但客服均未上线，请等待','text',1491460385,'oGzvqv5DP-VAYcujo9uqxrOmePWc',355,NULL);

/*Table structure for table `wx_user` */

DROP TABLE IF EXISTS `wx_user`;

CREATE TABLE `wx_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `openid` varchar(255) DEFAULT NULL,
  `a_time` int(10) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0' COMMENT '1关注中2取消关注',
  `headimgurl` varchar(255) DEFAULT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `up_time` int(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='关注用户';

/*Data for the table `wx_user` */

insert  into `wx_user`(`id`,`openid`,`a_time`,`status`,`headimgurl`,`nickname`,`country`,`province`,`city`,`sex`,`up_time`,`phone`) values (1,'oGzvqv6TMc1vG7VTDMnqFtPb6NXk',1488872837,1,'http://wx.qlogo.cn/mmopen/Q3auHgzwzM5v8pfDiaIibPalFstz2TRGpmKGHyJSLzdU9T6XXFfLkztLTfOVnWjial5ayO2OuLqzakkuXpO7j8bVg/0','小z','中国','河北','廊坊','1',1489399790,NULL),(2,'oGzvqv6BLbTJMgM0Dn40uS1troJg',1489048169,1,'http://wx.qlogo.cn/mmopen/52uRY46mGBNZwzMe9yu7Pl8GBJmmXTs5Lf7XIMjRsu80jVBXuqv9OfIUVBNsUDJKuF2wkw6k08a1l4rwWXVWTIiavyqWh4rCT/0','艾未来你好','罗马尼亚','','','1',1489048169,NULL),(3,'oGzvqv0PpAn9cZacD1cxwVvMs8uw',1489569804,1,'http://wx.qlogo.cn/mmopen/52uRY46mGBNZwzMe9yu7PqqWrvicoIqdMTZmxZ5MxicZXxtb9YERDmvwTgVHG0A76okKPgpzyGf2glm0dnkYCBMGHpqstibqp2P/0','赵洁','冰岛','','','2',1489569804,NULL),(4,'oGzvqvxq22hWW6bUhHTRNMwdfrp8',1490270560,1,'http://wx.qlogo.cn/mmopen/bJIEq4PJqB0hdBXkqZf0t9WrTKA8IKC0AkFtRrXbEbQwJEUWiaUVCAq3zzticA3LdrwJ3eYONmV1m3AMqBLqUB5Q/0','','中国','广西','柳州','1',1490270560,NULL),(5,'oGzvqvxp5jOHqy9e1M_m7C5qMGbo',1490321538,1,'http://wx.qlogo.cn/mmopen/bJIEq4PJqB0Qv3ibEsEgcNS3xzD09N20coMyfYgcZROoSA1mI5XoFcibwB9kicKPY7lYBMw7Tg0qVYiasBq6gObvPtjqYCaticjlL/0','韦炟 可欣网络','中国','广西','柳州','1',1490321538,NULL),(6,'oGzvqv7sr0nRQrefY8xLCOv5rvvI',1490355691,1,'http://wx.qlogo.cn/mmopen/PiajxSqBRaEIxNTgCibrPDmyBPAI1v5G8SX6LEvkKjDDoMicAmEqYOWKkAXPtoGQ8qq3pHic4YP7txZpXJrZUwCkUg/0','A桂百佳品～叶昌','中国','广西','柳州','1',1490355691,NULL),(7,'oGzvqvyMUEhSYSyxAbbGg4pfY1FY',1490771847,1,'http://wx.qlogo.cn/mmopen/bJIEq4PJqB1Rn87ajX77PElIH90S2h5xkbJnj7gK6qdeEnqAicS3h9neoo3wkHrB1SZftW5BnwwIScNbqshLd3icPzMHlB37Tx/0','桂百佳品','中国','','','2',1490771847,NULL),(8,'oGzvqv5DP-VAYcujo9uqxrOmePWc',1491460376,2,'http://wx.qlogo.cn/mmopen/52uRY46mGBNZwzMe9yu7Prwx0xGLNGc34Y7UsY89gbz1zLKiaXSiarsTe5ExfUic2FHCWGicODUG0IVvrGmBOel7dsv8C6FH3DW2/0','钱找我','中国','广西','柳州','1',1491460409,NULL);






/*====================================================================================================================================================*/
CREATE TABLE `msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `send_uid` int(11) DEFAULT NULL COMMENT '发送者UID',
  `to_uid` int(11) DEFAULT NULL COMMENT '收信者UID',
  `to_gid` int(11) NOT NULL DEFAULT '0' COMMENT '目标组',
  `to_tid` int(11) NOT NULL DEFAULT '0' COMMENT '目标标签id',
  `is_read` tinyint(1) DEFAULT '0' COMMENT '0未读1已读',
  `send_del` tinyint(1) DEFAULT '0' COMMENT '发送方已删除：0未删1已删',
  `title` varchar(255) DEFAULT NULL,
  `content_id` text,
  `type` int(11) DEFAULT NULL COMMENT '1用户之间私信，2商家群发，3系统私信（管家），4用户组，5系统群发，6系统部分发，7指定标签发送，8系统通知（用户）',
  `add_time` int(11) DEFAULT NULL,
  `to_del` tinyint(1) DEFAULT '0' COMMENT '接收方已删除:0未删1已删',
  `platform_key` varchar(50) DEFAULT '' COMMENT '平台',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=143811 DEFAULT CHARSET=utf8 COMMENT='站内信';


CREATE TABLE `msg_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `type` tinyint(1) DEFAULT NULL COMMENT '1用户之间私信，2商家群发，3系统私信（管家），4用户组，5系统群发，6系统部分发，7指定标签发送，8系统通知（用户）',
  `dynamic_func` varchar(100) DEFAULT NULL COMMENT '需要替换变量的方法名',
  `title` varchar(100) DEFAULT NULL COMMENT '标题',
  `send_uid` int(11) DEFAULT NULL COMMENT '商户ID或者管理员ID',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '0未删除1已删除',
  `to_uid` text COMMENT '接收者们的UIDS',
  `to_gid` int(11) NOT NULL DEFAULT '0' COMMENT '目标组id',
  `add_time` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0' COMMENT '0未处理1处理中2完成',
  `img` int(11) DEFAULT '0' COMMENT '图片ID',
  `link` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='站内信-群发';

/*Table structure for table `msg_text` */

DROP TABLE IF EXISTS `msg_text`;

CREATE TABLE `msg_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `type` tinyint(1) DEFAULT NULL COMMENT '1用户之间私信，2商家群发，3系统私信（管家），4用户组，5系统群发，6系统部分发，7指定标签发送，8系统通知（用户）',
  `uid` int(11) DEFAULT NULL COMMENT '发送者UID',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '0未删除1已删除',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=133287 DEFAULT CHARSET=utf8 COMMENT='站内信-内容';




CREATE TABLE `examine_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oid` int(11) DEFAULT NULL COMMENT '单id',
  `examine_type` int(11) DEFAULT NULL COMMENT '审批类型id 关联examine_type表',
  `type_key` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `product_type` int(11) DEFAULT NULL COMMENT '产品类型ID',
  `step` int(11) DEFAULT '1' COMMENT '第几步',
  `approver` int(11) DEFAULT NULL COMMENT '审批人ID',
  `group_id` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '用户所在组',
  `examine_status` int(11) DEFAULT NULL COMMENT '当前审核状态 1:审核通过 2：审核未通过',
  `degree` int(11) DEFAULT '1' COMMENT '次数 用于标记审批流第几次被触发',
  `remark` text CHARACTER SET utf8 COMMENT '备注 批注或驳回理由',
  `param` text CHARACTER SET utf8 COMMENT '参数组 建议使用JSON格式',
  `addtime` int(11) DEFAULT NULL,
  `is_jump` int(11) DEFAULT '0' COMMENT '是否跳过 0：否 1：是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36612 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

/*Table structure for table `examine_people` */

DROP TABLE IF EXISTS `examine_people`;

CREATE TABLE `examine_people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) DEFAULT NULL COMMENT '审批流ID',
  `ext_id` int(11) DEFAULT NULL COMMENT '后台用户ID或组ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=717 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

/*Table structure for table `examine_people_snapshot` */

DROP TABLE IF EXISTS `examine_people_snapshot`;

CREATE TABLE `examine_people_snapshot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_snapshot_id` int(11) DEFAULT NULL COMMENT '审批流快照ID 对应examine_process_snapshot表',
  `ext_id` int(11) DEFAULT NULL COMMENT '后台用户ID或组ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54084 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

/*Table structure for table `examine_process` */

DROP TABLE IF EXISTS `examine_process`;

CREATE TABLE `examine_process` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `examine_type` int(11) DEFAULT NULL COMMENT '审批类型id 关联examine_type表',
  `step` int(11) DEFAULT '1' COMMENT '第几步',
  `approver_type` int(11) DEFAULT '0' COMMENT '审批人类型 0：个人 1：组',
  `product_type` int(11) DEFAULT NULL COMMENT '产品类型ID',
  `before_hook` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '前置钩子',
  `later_hook` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '后置钩子',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=346 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

/*Table structure for table `examine_process_improve` */

DROP TABLE IF EXISTS `examine_process_improve`;

CREATE TABLE `examine_process_improve` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(255) NOT NULL COMMENT '节点名称',
  `group_id` int(11) DEFAULT NULL COMMENT '组ID 对应admin_group表',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '节点层级',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父节点ID',
  `parent_path` varchar(255) NOT NULL DEFAULT '0' COMMENT '节点 父级路径(当前节点以上的父级)',
  `child_path` varchar(255) NOT NULL DEFAULT '0' COMMENT '节点 子级路径(最高级父级以下的所有子级  只有最高级父级存)',
  `examine_type` int(11) DEFAULT NULL COMMENT '审批类型',
  `before_hook` varchar(255) DEFAULT NULL COMMENT '前置钩子',
  `later_hook` varchar(255) DEFAULT NULL COMMENT '后置钩子',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=333 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*Table structure for table `examine_process_snapshot` */

DROP TABLE IF EXISTS `examine_process_snapshot`;

CREATE TABLE `examine_process_snapshot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oid` int(11) DEFAULT NULL COMMENT '单ID',
  `examine_type` int(11) DEFAULT NULL COMMENT '审批类型id 关联examine_type表',
  `type_key` varchar(255) DEFAULT NULL COMMENT 'key 对应examine_type中的type_key',
  `step` int(11) DEFAULT '1' COMMENT '第几步',
  `product_type` int(11) DEFAULT NULL COMMENT '产品类型ID',
  `approver_type` int(11) DEFAULT '1' COMMENT '审批人类型 0：个人 1：组',
  `before_hook` varchar(255) DEFAULT NULL COMMENT '前置钩子',
  `later_hook` varchar(255) DEFAULT NULL COMMENT '后置钩子',
  `degree` int(11) DEFAULT '1' COMMENT '次数 用于标记审批流第几次被触发',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41031001 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

/*Table structure for table `examine_result` */

DROP TABLE IF EXISTS `examine_result`;

CREATE TABLE `examine_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oid` int(11) DEFAULT NULL COMMENT '单ID',
  `applicant` int(11) DEFAULT NULL COMMENT '申请人',
  `examine_type` int(11) DEFAULT NULL COMMENT '审批类型id 关联examine_type表',
  `type_key` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'key 对应examine_type中的type_key',
  `product_type` int(11) DEFAULT NULL COMMENT '产品类型ID',
  `step` int(11) DEFAULT '1' COMMENT '第几步',
  `degree` int(11) DEFAULT '1' COMMENT '次数 用于标记审批流第几次被触发',
  `status` int(11) DEFAULT '0' COMMENT '审核是否完成 1：审核完成 0：审核未完成',
  `final_status` int(11) DEFAULT '0' COMMENT '最终审批状态 0：未审批完毕 1：审批通过 2：审批不通过',
  `version` int(11) DEFAULT '1' COMMENT '版本信息 此字段为锁信息',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11618 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

/*Table structure for table `examine_type` */

DROP TABLE IF EXISTS `examine_type`;

CREATE TABLE `examine_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_key` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '标识名称',
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '类型名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;





