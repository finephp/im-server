currpath=$(cd `dirname $0`; pwd)
echo $currpath
#php /root/php/index.php rtm/swoole/worker
php ${currpath}/index.php rtm/swoole/worker
