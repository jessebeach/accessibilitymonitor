#/bin/bash
mkdir /tmp/quaildata
export FILENAME=`/usr/bin/php -f /opt/siteinspector/createhash.php $1`
echo $1 > /tmp/quaildata/$FILENAME
/opt/phantomjs-1.9.2-linux-x86_64/bin/phantomjs --ignore-ssl-errors=yes phantomquail.js $1 >> /tmp/quaildata/$FILENAME
/usr/bin/php -f /opt/siteinspector/parseData.php /tmp/quaildata/$FILENAME
