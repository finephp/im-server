currpath=$(cd `dirname $0`; pwd)
echo $currpath
php ${currpath}/index.php daemon/redis/worker start
