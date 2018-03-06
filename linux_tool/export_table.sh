#!/bin/sh
echo $#
if [ 1 != $# ];then
	echo ' para num null...'
	exit
fi
echo $1
#设置用户名和密码
v_user="root"
v_password="redcollar"
v_host='127.0.0.1'
 
#mysql安装全路径
MysqlDir=/usr/local/mysql/bin
 
#备份数据库
database="rctailor_test"
 
#设置备份路径，创建备份文件夹
Full_Backup=/root
 
#开始备份,记录备份开始时间
echo '=========='$(date +"%Y-%m-%d %H:%M:%S")'=========='"备份开始"
 
$MysqlDir/mysqldump -h$v_host -u$v_user -p$v_password --single-transaction --flush-logs  --databases $database --table $1 >$Full_Backup/table_$(date +%Y%m%d)_$(date +%H)_$1.sql
 
#压缩备份文件
#gzip $Full_Backup/$(date +%Y%m%d)/full_backup.sql
 
echo '=========='$(date +"%Y-%m-%d %H:%M:%S")'=========='"备份完成"
