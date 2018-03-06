#!/bin/sh
if [ ! -n "$1" ] ;then
echo "$1 para not 1.." 
exit
fi

www_dir="/home/www/rctailor/trunk"
target=$www_dir/$1
echo $target

if [ ! -f "$target" ] ;then
echo "file not exist"
exit
fi

rsync -avz --progress $target rsync@118.244.192.100::www/$1
