#!/bin/sh -e

COLOR_SUCCESS='\033[0;32m'
NC='\033[0m'

#===================================#
#       CALL PARENT ENTRYPOINT
#===================================#
printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
printf "\n${COLOR_SUCCESS}     INSTALLATION PRESTASHOP $DB_NAME   ${NC}\n"
printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
/tmp/docker_run.sh

#===================================#
#       CUSTOMS CONFIGURATIONS
#===================================#
if [ ! -f /var/www/html/console/console.php ];then
    

    
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}            INSTALLATION SDK PHP         ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    cd /var/www/html/modules/hipay_enterprise/ \
    && composer install --no-dev

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}             INSTALLATION SDK JS         ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    cd lib/ \
    && bower install hipay-fullservice-sdk-js

   
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     INSTALLATION PRESTASHOP CONSOLE     ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    cd /var/www/html/ \
    && git clone https://github.com/nenes25/prestashop_console.git console \
    && cd console \
    && composer install

    # Installation  HiPay's module
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     INSTALLATION HiPay's Module         ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    php console.php module:install hipay_enterprise


    chmod -R 777 /var/www/html/modules/hipay_enterprise/logs
    chmod 666 /var/www/html/modules/hipay_enterprise/logs/index.php
   
   #===================================#
    #            ADD CRON
    #===================================#
    #crontab -l | { cat; echo "*/5 * * * *  php /var/www/html/modules/hipay_enterprise/cron.php > /var/log/cron.log"; } | crontab -
    #service cron start
fi

chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

#===================================#
#       START WEBSERVER
#===================================#
printf "${COLOR_SUCCESS}                                                                           ${NC}\n"
printf "${COLOR_SUCCESS}    |======================================================================${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                      ${NC}\n"
printf "${COLOR_SUCCESS}    |               DOCKER PRESTASHOP TO HIPAY $ENVIRONMENT IS UP          ${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                      ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL FRONT       : http://$PS_DOMAIN                                ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL BACK        : http://$PS_DOMAIN/$PS_FOLDER_ADMIN               ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL MAIL CATCHER: http://localhost:1095/                           ${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                      ${NC}\n"
printf "${COLOR_SUCCESS}    |   PHP VERSION     : $PHP_VERSION                                     ${NC}\n"
printf "${COLOR_SUCCESS}    |======================================================================${NC}\n"
exec apache2-foreground