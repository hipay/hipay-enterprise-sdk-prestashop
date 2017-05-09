#!/bin/sh -e

#===================================#
#       CALL PARENT ENTRYPOINT
#===================================#
echo "\n Execution PRESTASHOP Entrypoint \n";
/tmp/docker_run.sh

#===================================#
#       CUSTOMS CONFIGURATIONS
#===================================#
if [ ! -f /var/www/html/console/console.php ];then
    echo "\n Installation Prestashop Console \n";
    cd /var/www/html/ \
    && git clone https://github.com/nenes25/prestashop_console.git console \
    && cd console \
    && composer install

   # Installation  HiPay's module
    echo "\n Installation HiPay's module \n";
  #  php console.php module:install hipay_enterprise

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
