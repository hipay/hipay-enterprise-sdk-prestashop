#!/bin/sh -e

#=============================================================================
#  Use this script build hipay images and run Hipay Professional's containers
#
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
    sudo docker-compose -f docker-compose.yml -f docker-compose-16.yml -f docker-compose-17.yml stop
    sudo docker-compose -f docker-compose.yml -f docker-compose-16.yml -f docker-compose-17.yml rm -fv
    sudo rm -Rf data/
    sudo rm -Rf web16/
    sudo rm -Rf web17/
    sudo docker-compose -f docker-compose.yml -f docker-compose-16.yml -f docker-compose-17.yml build --no-cache
    sudo docker-compose -f docker-compose.yml -f docker-compose-16.yml -f docker-compose-17.yml up -d
fi

if [ "$1" = 'init' ] && [ "$2" != '' ];then
    sudo docker-compose -f docker-compose.yml -f  docker-compose-"$2".yml stop
    sudo docker-compose -f docker-compose.yml -f  docker-compose-"$2".yml rm -fv
    sudo rm -Rf data/
    sudo rm -Rf web16/
    sudo rm -Rf web17/
    sudo docker-compose -f docker-compose.yml -f  docker-compose-"$2".yml build --no-cache
    sudo docker-compose -f docker-compose.yml -f docker-compose-"$2".yml up  -d
fi

if [ "$1" = 'restart' ];then
    sudo docker-compose -f docker-compose.yml -f docker-compose-16.yml -f docker-compose-17.yml stop
    sudo docker-compose -f docker-compose.yml -f docker-compose-16.yml -f docker-compose-17.yml up -d
fi

if [ "$1" = 'kill' ];then
    sudo docker-compose -f docker-compose.yml -f docker-compose-16.yml -f docker-compose-17.yml stop
    sudo docker-compose -f docker-compose.yml -f docker-compose-16.yml -f docker-compose-17.yml rm -fv
    sudo rm -Rf data/
    sudo rm -Rf web16/
    sudo rm -Rf web17/
fi

if [ "$1" = 'up' ] && [ "$2" != '' ];then
    sudo docker-compose -f docker-compose.yml -f docker-compose-"$2".yml up  -d
fi

if [ "$1" = 'exec' ] && [ "$2" != '' ];then
    sudo docker exec -it hipay-enterprise-shop-ps"$2" bash
fi

if [ "$1" = 'log' ] && [ "$2" != '' ];then
    docker logs -f hipay-enterprise-shop-ps"$2"
fi

if [ "$1" = 'console' ] && [ "$2" != '' ] && [ "$3" != '' ];then
    sudo docker exec -it hipay-enterprise-shop-ps"$2" bash php console/console.php "$3"
fi
