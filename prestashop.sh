#!/bin/sh -e

BASE_URL="http://localhost:8087/"
URL_MAILCATCHER="http://localhost:1095/"
header="bin/tests/"
pathPreFile=${header}000*/*.js
pathLibHipay=${header}000*/*/*/*.js
pathDir=${header}0*

#=============================================================================
#  Use this script build hipay images and run Hipay Professional's containers
#==============================================================================
if [ "$1" = '' ] || [ "$1" = '--help' ];then
    printf "\n                                                                                  "
    printf "\n ================================================================================ "
    printf "\n                                  HiPay'S HELPER                                 "
    printf "\n                                                                                  "
    printf "\n For each commands, you may specify the prestashop version "16" or "17"           "
    printf "\n ================================================================================ "
    printf "\n                                                                                  "
    printf "\n                                                                                  "
    printf "\n      - init      : Build images and run containers (Delete existing volumes)     "
    printf "\n      - restart   : Run all containers if they already exist                      "
    printf "\n      - up        : Up containters                                                "
    printf "\n      - exec      : Bash prestashop.                                              "
    printf "\n      - log       : Log prestashop.                                               "
    printf "\n                                                                                  "
fi

if [ "$1" = 'init' ] && [ "$2" = '' ];then
     docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose-16.yml -f docker-compose-17.yml stop
     docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose-16.yml -f docker-compose-17.yml rm -fv
     rm -Rf data/
     rm -Rf web16/
     rm -Rf web17/
     docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose-16.yml -f docker-compose-17.yml build --no-cache
     docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose-16.yml -f docker-compose-17.yml up -d
fi

if [ "$1" = 'init-production' ] && [ "$2" = '' ];then
     docker-compose -f docker-compose.yml -f docker-compose.production.yml stop
     docker-compose -f docker-compose.yml -f docker-compose.production.yml rm -fv
     docker-compose -f docker-compose.yml -f docker-compose.production.yml build --no-cache
     docker-compose -f docker-compose.yml -f docker-compose.production.yml up -d
fi

if [ "$1" = 'init-stage' ] && [ "$2" = '' ];then
     docker-compose -f docker-compose.yml -f docker-compose.stage.yml stop
     docker-compose -f docker-compose.yml -f docker-compose.stage.yml rm -fv
     docker-compose -f docker-compose.yml -f docker-compose.stage.yml build --no-cache
     docker-compose -f docker-compose.yml -f docker-compose.stage.yml up -d
fi

if [ "$1" = 'init' ] && [ "$2" != '' ];then
     docker-compose -f docker-compose.yml -f  docker-compose-"$2".yml stop
     docker-compose -f docker-compose.yml -f  docker-compose-"$2".yml rm -fv
     rm -Rf data/
     rm -Rf web16/
     rm -Rf web17/
     docker-compose -f docker-compose.yml -f  docker-compose-"$2".yml build --no-cache
     docker-compose -f docker-compose.yml -f docker-compose-"$2".yml up  -d
fi

if [ "$1" = 'restart' ];then
     docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose-16.yml -f docker-compose-17.yml stop
     docker-compose -f docker-compose.yml -f docker-compose.dev.yml -f docker-compose-16.yml -f docker-compose-17.yml up -d
fi

if [ "$1" = 'kill' ];then
     docker-compose -f docker-compose.yml -f docker-compose-16.yml -f docker-compose-17.yml stop
     docker-compose -f docker-compose.yml -f docker-compose-16.yml -f docker-compose-17.yml rm -fv
     rm -Rf data/
     rm -Rf web16/
     rm -Rf web17/
fi

if [ "$1" = 'up' ] && [ "$2" != '' ];then
     docker-compose -f docker-compose.yml -f docker-compose-"$2".yml up  -d
fi

if [ "$1" = 'exec' ] && [ "$2" != '' ];then
     docker exec -it hipay-enterprise-shop-ps"$2" bash
fi

if [ "$1" = 'log' ] && [ "$2" != '' ];then
    docker logs -f hipay-enterprise-shop-ps"$2"
fi

if [ "$1" = 'console' ] && [ "$2" != '' ] && [ "$3" != '' ];then
     docker exec -it hipay-enterprise-shop-ps"$2" bash php console/console.php "$3"
fi

if [ "$1" = 'udpate-lib' ]; then
   cd bin/tests/000_lib
   bower install hipay-casperjs-lib#develop --allow-root
fi

if [ "$1" = 'test' ]; then
   #setBackendCredentials
   #setPaypalCredentials

   rm -rf bin/tests/errors/*
   printf "Errors from previous tests cleared !\n\n"

   if [ "$(ls -A ~/.local/share/Ofi\ Labs/PhantomJS/)" ]; then
       rm -rf ~/.local/share/Ofi\ Labs/PhantomJS/*
       printf "Cache cleared !\n\n"
   else
       printf "Pas de cache à effacer !\n\n"
   fi

   cd bin/tests/000_lib
   bower install hipay-casperjs-lib#develop --allow-root
   cd ../../../;

   if [ "$2" = '17' ]; then
    BASE_URL="http://localhost:8087/"
    PRESTASHOP_VERSION=1.7
   else
    BASE_URL="http://localhost:8086/"
    PRESTASHOP_VERSION=1.6
   fi

   casperjs test $pathLibHipay $pathPreFile ${pathDir}/[0-1]*/[0-9][4-9][0-9][0-9]-*.js --url=$BASE_URL --ps-version=$PRESTASHOP_VERSION --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any
fi
