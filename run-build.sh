rm -rf build
mkdir build
git checkout master
git pull
git archive -o ./build/php.tar master
#mkdir build/php
#tar xf ./build/php.tar -C ./build/php
cp Dockerfile ./build/
cd build
docker build --no-cache=false -f Dockerfile -t wangtr/im_realtime .
cd ..
rm -rf build
