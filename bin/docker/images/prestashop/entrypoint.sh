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

# wait until MySQL is really available
maxcounter=45
counter=1
while ! mysql --protocol TCP -h $DB_SERVER -P $DB_PORT -u $DB_USER -p$DB_PASSWD -e "show databases;" >/dev/null 2>&1; do
    sleep 1
    counter=$(expr $counter + 1)
    if [ $counter -gt $maxcounter ]; then
        echo >&2 "We have been waiting for MySQL too long already; failing."
        exit 1
    fi
done

printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
printf "\n${COLOR_SUCCESS}            INSTALLATION SDK PHP         ${NC}\n"
printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
cd /var/www/html/modules/hipay_enterprise/

composer install --no-dev

cd /var/www/html

/tmp/docker_run.sh

#===================================#
#       CUSTOMS CONFIGURATIONS
#===================================#
if [ ! -f /var/www/html/prestashopConsole.phar ] || [ "$REINSTALL_CONFIG" = "1" ]; then

    if [ "$ENVIRONMENT" = "$ENV_DEVELOPMENT" ]; then
        # CONFIGURE XDEBUG
        printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
        printf "\n${COLOR_SUCCESS}            CONFIGURATION XDEBUG          ${NC}\n"
        printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

        xdebugFile=/usr/local/etc/php/conf.d/xdebug.ini
        echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" >$xdebugFile

        echo "xdebug.mode=debug" >>$xdebugFile
        echo "xdebug.idekey=PHPSTORM" >>$xdebugFile

        echo "xdebug.remote_enable=on" >>$xdebugFile
        echo "xdebug.remote_autostart=off" >>$xdebugFile
    fi

    if [[ "$PS_VERSION" != "8"* ]]; then
        printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
        printf "\n${COLOR_SUCCESS}     INSTALLATION PRESTASHOP CONSOLE     ${NC}\n"
        printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
        cd /var/www/html/ &&
            wget https://github.com/nenes25/prestashop_console/raw/master/bin/prestashopConsole.phar &&
            chmod +x prestashopConsole.phar
    fi

    # Installation  HiPay's module
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     INSTALLATION HiPay's Module         ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    if [[ "$PS_VERSION" == "1.6"* ]]; then
        mysql -h $MYSQL_HOST -D $DB_NAME -u root -p$MYSQL_ROOT_PASSWORD -e "
        DELETE FROM ps_lang WHERE language_code IN ('en-GB', 'it-IT');
        INSERT INTO ps_lang (id_lang, name, active, iso_code, language_code, date_format_lite, date_format_full, is_rtl)
        VALUES
          (2, 'English', 1, 'en', 'en-GB', 'd/m/Y', 'd/m/Y H:i:s', 0),
          (3, 'Italiano', 1, 'it', 'it-IT', 'd/m/Y', 'd/m/Y H:i:s', 0);

        COMMIT;"
    else
        mysql -h $MYSQL_HOST -D $DB_NAME -u root -p$MYSQL_ROOT_PASSWORD -e "
        DELETE FROM ps_lang WHERE locale IN ('en-GB', 'it-IT');
        INSERT INTO ps_lang (id_lang, name, active, iso_code, language_code, locale, date_format_lite, date_format_full, is_rtl)
          VALUES
            (2, 'English', 1, 'en', 'en', 'en-GB', 'd/m/Y', 'd/m/Y H:i:s', 0),
            (3, 'Italiano', 1, 'it', 'it', 'it-IT', 'd/m/Y', 'd/m/Y H:i:s', 0);

        COMMIT;"
    fi

    mysql -h $MYSQL_HOST -D $DB_NAME -u root -p$MYSQL_ROOT_PASSWORD -e "
      UPDATE ps_country SET active=1 WHERE iso_code IN ('PT', 'IT', 'NL', 'BE');

      DELETE FROM ps_module_country WHERE id_country IN (SELECT id_country FROM ps_country WHERE iso_code IN ('PT', 'IT', 'NL', 'BE'));
      INSERT INTO ps_module_country (id_module, id_shop, id_country)
        SELECT m.id_module, s.id_shop, c.id_country
        FROM ps_module m, ps_shop s, ps_country c
        WHERE m.name = 'hipay_enterprise'
        AND s.name = 'PrestaShop'
        AND c.iso_code IN ('PT', 'IT', 'NL', 'BE');

      DELETE FROM ps_country_lang WHERE id_lang = 2;
      INSERT INTO ps_country_lang (id_country, id_lang, name)
        SELECT id_country, 2 AS id_lang, name FROM ps_country_lang WHERE id_lang = 1;

      DELETE FROM ps_country_lang WHERE id_lang = 3;
      INSERT INTO ps_country_lang (id_country, id_lang, name)
        SELECT id_country, 3 AS id_lang, name FROM ps_country_lang WHERE id_lang = 1;

      COMMIT;"

    if [[ "$PS_VERSION" == "1.6"* ]]; then
        ./prestashopConsole.phar module:install hipay_enterprise
    else
        bin/console prestashop:module install hipay_enterprise
    fi

    # Configure module credentials
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     Configuration HiPay's Module         ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    if [[ "$PS_VERSION" == "8"* ]]; then
        CONFIG=$(bin/console prestashop:config get HIPAY_CONFIG)
        CONFIG=$(echo "$CONFIG" | sed -r "s/.*HIPAY_CONFIG=//")
        CONFIG=$(echo "${CONFIG//[$'\t\r\n']/}" | sed "s/  //g")
    else
        CONFIG=$(./prestashopConsole.phar configuration:get HIPAY_CONFIG)
    fi
    CONFIG=${CONFIG/'"api_username_sandbox":""'/'"api_username_sandbox":"'$HIPAY_API_USER_TEST'"'}
    CONFIG=${CONFIG/'"api_password_sandbox":""'/'"api_password_sandbox":"'$HIPAY_API_PASSWORD_TEST'"'}
    CONFIG=${CONFIG/'"api_tokenjs_username_sandbox":""'/'"api_tokenjs_username_sandbox":"'$HIPAY_TOKENJS_USERNAME_TEST'"'}
    CONFIG=${CONFIG/'"api_tokenjs_password_publickey_sandbox":""'/'"api_tokenjs_password_publickey_sandbox":"'$HIPAY_TOKENJS_PUBLICKEY_TEST'"'}
    CONFIG=${CONFIG/'"api_secret_passphrase_sandbox":""'/'"api_secret_passphrase_sandbox":"'$HIPAY_SECRET_PASSPHRASE_TEST'"'}
    CONFIG=${CONFIG/'"api_moto_username_sandbox":""'/'"api_moto_username_sandbox":"'$HIPAY_API_MOTO_USER_TEST'"'}
    CONFIG=${CONFIG/'"api_moto_password_sandbox":""'/'"api_moto_password_sandbox":"'$HIPAY_API_MOTO_PASSWORD_TEST'"'}
    CONFIG=${CONFIG/'"api_moto_secret_passphrase_sandbox":""'/'"api_moto_secret_passphrase_sandbox":"'$HIPAY_MOTO_SECRET_PASSPHRASE_TEST'"'}

    CONFIG=${CONFIG/'"api_apple_pay_username_sandbox":""'/'"api_apple_pay_username_sandbox":"'$HIPAY_API_APPLE_PAY_USER_TEST'"'}
    CONFIG=${CONFIG/'"api_apple_pay_password_sandbox":""'/'"api_apple_pay_password_sandbox":"'$HIPAY_API_APPLE_PAY_PASSWORD_TEST'"'}
    CONFIG=${CONFIG/'"api_apple_pay_passphrase_sandbox":""'/'"api_apple_pay_passphrase_sandbox":"'$HIPAY_APPLE_PAY_PASSPHRASE_TEST'"'}
    CONFIG=${CONFIG/'"api_tokenjs_apple_pay_username_sandbox":""'/'"api_tokenjs_apple_pay_username_sandbox":"'$HIPAY_TOKENJS_APPLE_PAY_USERNAME_TEST'"'}
    CONFIG=${CONFIG/'"api_tokenjs_apple_pay_password_sandbox":""'/'"api_tokenjs_apple_pay_password_sandbox":"'$HIPAY_TOKENJS_APPLE_PAY_PASSWORD_TEST'"'}

    if [ "$ENVIRONMENT" = "$ENV_PROD" ]; then
        CONFIG=${CONFIG/'"send_url_notification":0'/'"send_url_notification":1'}
    fi

    if [ "$ENVIRONMENT" != "$ENV_DEVELOPMENT" ]; then
        CONFIG=${CONFIG/'"test":"SHA1"'/'"test":"SHA512"'}
    fi

    if [[ "$PS_VERSION" == "8"* ]]; then
        bin/console prestashop:config set HIPAY_CONFIG --value "$CONFIG" -q
    else
        ./prestashopConsole.phar configuration:set HIPAY_CONFIG "$CONFIG"
    fi

    if [ "$ENVIRONMENT" = "$ENV_PROD" ]; then
        if [[ "$PS_VERSION" == "8"* ]]; then
            bin/console prestashop:config set PS_SSL_ENABLED --value 1
        else
            ./prestashopConsole.phar configuration:set PS_SSL_ENABLED 1
        fi
    fi

    if [ "$ENVIRONMENT" = "$ENV_STAGE" ]; then
        mysql -h $MYSQL_HOST -D $DB_NAME -u root -p$MYSQL_ROOT_PASSWORD <<EOF
            UPDATE ps_module SET version='0.0.0' WHERE name='hipay_enterprise';
            COMMIT;
EOF
    fi

    if [[ "$PS_VERSION" == "8"* ]]; then
        bin/console cache:clear
    else
        ./prestashopConsole.phar c:flush
    fi

    #===================================#
    #            ADD CRON
    #===================================#
    #crontab -l | { cat; echo "*/5 * * * *  php /var/www/html/modules/hipay_enterprise/cron.php > /var/log/cron.log"; } | crontab -
    #service cron start
fi

if [ "$ENVIRONMENT" = "$ENV_DEVELOPMENT" ]; then
    MOD_DROITS=775
else
    MOD_DROITS=755
fi

sleep 10

chown -R www-data:www-data /var/www/html
chmod -R $MOD_DROITS /var/www/html

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
