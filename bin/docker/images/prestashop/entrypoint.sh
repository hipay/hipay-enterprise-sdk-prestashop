#!/bin/bash -e

COLOR_SUCCESS='\033[0;32m'
NC='\033[0m'
ENV_DEVELOPMENT="development"
ENV_STAGE="stage"
ENV_PROD="production"

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

    cp -f /tmp/conf/apache2/mpm_prefork.conf /etc/apache2/mods-available/

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}            INSTALLATION SDK PHP         ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    cd /var/www/html/modules/hipay_enterprise/ \
    && composer install --no-dev

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     INSTALLATION PRESTASHOP CONSOLE     ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    cd /var/www/html/ \
    && git clone https://github.com/nenes25/prestashop_console.git console \
    && cd console \
    && rm composer.lock \
    && composer install

    # Installation  HiPay's module
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     INSTALLATION HiPay's Module         ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    cd /var/www/html/console/ \
    && php console.php module:install hipay_enterprise

    # Configure module credentials
    # Installation  HiPay's module
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     Configuration HiPay's Module         ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    CONFIG=`php console.php configuration:get HIPAY_CONFIG`
    CONFIG=${CONFIG/'"api_username_sandbox":""'/'"api_username_sandbox":"'$HIPAY_API_USER_TEST'"'}
    CONFIG=${CONFIG/'"api_password_sandbox":""'/'"api_password_sandbox":"'$HIPAY_API_PASSWORD_TEST'"'}
    CONFIG=${CONFIG/'"api_tokenjs_username_sandbox":""'/'"api_tokenjs_username_sandbox":"'$HIPAY_TOKENJS_USERNAME_TEST'"'}
    CONFIG=${CONFIG/'"api_tokenjs_password_publickey_sandbox":""'/'"api_tokenjs_password_publickey_sandbox":"'$HIPAY_TOKENJS_PUBLICKEY_TEST'"'}
    CONFIG=${CONFIG/'"api_secret_passphrase_sandbox":""'/'"api_secret_passphrase_sandbox":"'$HIPAY_SECRET_PASSPHRASE_TEST'"'}

    if [ "$ENVIRONMENT" = "$ENV_PROD" ];then
        CONFIG=${CONFIG/'"send_url_notification":0'/'"send_url_notification":1'}
    fi

    if [ "$ENVIRONMENT" != "$ENV_DEVELOPMENT" ];then
        CONFIG=${CONFIG/'"test":"SHA1"'/'"test":"SHA512"'}
    fi

    php console.php configuration:set HIPAY_CONFIG "$CONFIG"

    if [ "$ENVIRONMENT" = "$ENV_PROD" ];then
        php console.php  configuration:set PS_SSL_ENABLED 1
    fi

    php console.php c:flush

    if [ "$ENVIRONMENT" = "$ENV_DEVELOPMENT" ];then
        # INSTALL X DEBUG
        echo '' | pecl install xdebug-2.5.0

        echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini
        echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini
        echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini
    fi   

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
