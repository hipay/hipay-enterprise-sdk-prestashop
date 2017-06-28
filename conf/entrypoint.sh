#!/bin/sh -e

#===================================#
#       CALL PARENT ENTRYPOINT
#===================================#
echo "\n Execution PRESTASHOP Entrypoint \n";
/tmp/docker_run.sh

#===================================#
#       CUSTOMS CONFIGURATIONS
#===================================#

chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 777 /var/www/html/modules/hipay_enterprise/logs
chmod 666 /var/www/html/modules/hipay_enterprise/logs/index.php

if [ ! -f /var/www/html/composer.json ];then

    echo "\n Instal SDK PHP \n";
    cd /var/www/html/modules/hipay_enterprise/ \
    && composer install --no-dev

    echo "\n Instal SDK JS \n";
    cd lib/ \
    && bower install hipay-fullservice-sdk-js

    #run unit tests
    #phpunit -c 
fi


if [ ! -f /var/www/html/console/console.php ];then
    echo "\n Installation Prestashop Console \n";
    cd /var/www/html/ \
    && git clone https://github.com/nenes25/prestashop_console.git console \
    && cd console \
    && composer install

   # Installation  HiPay's module
    echo "\n Installation HiPay's module \n";
    php console.php module:install hipay_enterprise

   #===================================#
    #            ADD CRON
    #===================================#
    #crontab -l | { cat; echo "*/5 * * * *  php /var/www/html/modules/hipay_enterprise/cron.php > /var/log/cron.log"; } | crontab -
    #service cron start
fi



#===================================#
#       START WEBSERVER
#===================================#
echo "\n* Starting Apache now\n";
exec apache2-foreground