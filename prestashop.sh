#!/bin/sh -e

set -e

BASE_URL="http://localhost:8087/"
URL_MAILCATCHER="http://localhost:1095/"
header="bin/tests/"
pathPreFile=${header}000*/*.js
pathLibHipay=${header}000*/*/*/*.js
pathDir=${header}0*
psVersion=${2:-"8"}
follow=${3:-""}

manageComposerForData() {
     COMPOSER_JSON_FILE="src/hipay_enterprise/composer.json"

     echo "Setting up git pre-commit hook..."

     echo "#!/bin/bash" >.git/hooks/pre-commit
     echo "COMPOSER_JSON_FILE='"$COMPOSER_JSON_FILE"'" >>.git/hooks/pre-commit
     echo "git status --porcelain -uno | grep \$COMPOSER_JSON_FILE" >>.git/hooks/pre-commit
     echo "if [ $? -eq 0 ]" >>.git/hooks/pre-commit
     echo "then" >>.git/hooks/pre-commit
     echo "    cp \$COMPOSER_JSON_FILE \$COMPOSER_JSON_FILE.bak" >>.git/hooks/pre-commit
     echo "    cat \$COMPOSER_JSON_FILE.bak | python3 -c \"import sys, json; composerObj=json.load(sys.stdin); composerObj['scripts'] = None; del composerObj['scripts']; print( json.dumps(composerObj, sort_keys=True, indent=4));\" > \$COMPOSER_JSON_FILE" >>.git/hooks/pre-commit
     echo "    git add \$COMPOSER_JSON_FILE" >>.git/hooks/pre-commit
     echo "fi" >>.git/hooks/pre-commit
     echo "exit 0" >>.git/hooks/pre-commit

     chmod 775 .git/hooks/pre-commit

     echo "Setting up git post-commit hook..."

     echo "#!/bin/bash" >.git/hooks/post-commit
     echo "COMPOSER_JSON_FILE='"$COMPOSER_JSON_FILE"'" >>.git/hooks/post-commit
     echo "if [ -f \$COMPOSER_JSON_FILE.bak ]" >>.git/hooks/post-commit
     echo "then" >>.git/hooks/post-commit
     echo "    cp \$COMPOSER_JSON_FILE.bak \$COMPOSER_JSON_FILE" >>.git/hooks/post-commit
     echo "    rm \$COMPOSER_JSON_FILE.bak" >>.git/hooks/post-commit
     echo "fi" >>.git/hooks/post-commit
     echo "exit 0" >>.git/hooks/post-commit

     chmod 775 .git/hooks/post-commit
}

manageComposerForData

#=============================================================================
#  Use this script build hipay images and run Hipay Professional's containers
#==============================================================================
if [ "$1" = '' ] || [ "$1" = '--help' ]; then
     printf "\n                                                                                         "
     printf "\n ================================================================================        "
     printf "\n                                  HiPay'S HELPER                                         "
     printf "\n                                                                                         "
     printf "\n For each commands, you may specify the prestashop version "16" or "17". Default "17"    "
     printf "\n ================================================================================        "
     printf "\n                                                                                         "
     printf "\n                                                                                         "
     printf "\n      - init      : Build images and run containers (Delete existing volumes)            "
     printf "\n      - restart   : Run all containers if they already exist                             "
     printf "\n      - up        : Up containters                                                       "
     printf "\n      - exec      : Bash prestashop.                                                     "
     printf "\n      - log       : Log prestashop.                                                      "
     printf "\n                                                                                         "
fi

if [ "$1" = 'init' ]; then
     if docker inspect hipay-enterprise-shop-ps$psVersion >/dev/null 2>&1; then
          if [ "$(docker inspect -f '{{.State.Running}}' hipay-enterprise-shop-ps$psVersion)" = 'true' ]; then
               docker exec hipay-enterprise-shop-ps$psVersion bash -c 'chmod -R 777 /var/www/html'
          fi
     fi
     sudo rm -Rf data/ web$psVersion/ src/hipay_enterprise/lib/vendor/ src/hipay_enterprise/composer.lock
     docker compose -f docker-compose.dev.yml rm -sfv prestashop$psVersion database
     docker compose -f docker-compose.dev.yml build prestashop$psVersion database

     if [ "$follow" = "-f" ]; then
          docker compose -f docker-compose.dev.yml up prestashop$psVersion database
     else
          docker compose -f docker-compose.dev.yml up -d prestashop$psVersion database
     fi
fi

if [ "$1" = 'restart' ]; then
     docker compose -f docker-compose.dev.yml stop prestashop$psVersion database
     if [ "$follow" = "-f" ]; then
          docker compose -f docker-compose.dev.yml up --build prestashop$psVersion database
     else
          docker compose -f docker-compose.dev.yml up -d --build prestashop$psVersion database
     fi
fi

if [ "$1" = 'kill' ]; then
     docker compose -f docker-compose.dev.yml rm -sfv prestashop16 prestashop17 prestashop8 database
     sudo rm -Rf data/ web16/ web17/ web8/ src/hipay_enterprise/lib/vendor/ src/hipay_enterprise/composer.lock
fi

if [ "$1" = 'exec' ]; then
     docker exec -it hipay-enterprise-shop-ps$psVersion bash
fi

if [ "$1" = 'l' ] || [ "$1" = 'log' ]; then
     docker logs -f hipay-enterprise-shop-ps$psVersion
fi

if [ "$1" = 'console' ] && [ "$2" != '' ] && [ "$3" != '' ]; then
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
     #   npm install
     cd ../../../

     if [ "$psVersion" = '8' ]; then
          BASE_URL="http://localhost:8088/"
          PRESTASHOP_VERSION=8
     elif [ "$psVersion" = '17' ]; then
          BASE_URL="http://localhost:8087/"
          PRESTASHOP_VERSION=1.7
     else
          BASE_URL="http://localhost:8086/"
          PRESTASHOP_VERSION=1.6
     fi

     casperjs test $pathPreFile ${pathDir}/[0-1]*/0202-*.js --url=$BASE_URL --ps-version=$PRESTASHOP_VERSION --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any --cookies-keep-session --web-security=false --fail-fast
fi

if [ "$1" = 'clear-smarty' ]; then
     cd web$psVersion/var/cache/dev/smarty/compile
     sudo chmod -R 775 .
     rm -r ./*
     echo "Cleared"
fi
