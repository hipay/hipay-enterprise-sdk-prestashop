#!/bin/bash

set -e

if [ "$URL_MAILCATCHER" = "" ];then
    URL_MAILCATCHER="http://smtp:1080/"
fi

cd bin/tests/000_lib
bower install hipay-casperjs-lib#develop --allow-root
cd ../../../;

header="bin/tests/"

   if [ "$2" = 'ps17' ]; then
    PRESTASHOP_VERSION=1.7
   else
    PRESTASHOP_VERSION=1.6
   fi

echo "BASE URL for tests: $BASE_URL"

if [ "$1" = "0" ];then
    echo "Execute part $1 of casperjs test"
    casperjs test ${header}000*/*/*/*.js ${header}000*/000[0-9]*.js ${header}0[0-1][0-9]*/[0-1]*/[0-9][0-3][0-9][0-9]-*.js --url=$3 --url-mailcatcher=$URL_MAILCATCHER --ps-version=$PRESTASHOP_VERSION --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any
fi

if [ "$1" = "1" ];then
    echo "Execute part $1 of casperjs test"
    casperjs test ${header}000*/*/*/*.js ${header}000*/000[0-9]*.js ${header}0[0-1][0-9]*/[0-1]*/[0-9][4-9][0-9][0-9]-*.js --url=$3 --url-mailcatcher=$URL_MAILCATCHER --ps-version=$PRESTASHOP_VERSION --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any
fi

if [ "$1" = "2" ];then
    echo "Execute part $1 of casperjs test"
    casperjs test ${header}000*/*/*/*.js ${header}000*/000[0-9]*.js ${header}0[0-1][0-9]*/[0-1]*/[0-9][1][0-9][0-9]-*.js --url=$3 --url-mailcatcher=$URL_MAILCATCHER --ps-version=$PRESTASHOP_VERSION --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any
fi
