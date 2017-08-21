#!/bin/sh -e
cp -R ../../src/hipay_enterprise hipay_enterprise

if [ -d hipay_enterprise/lib/bower_components/hipay-fullservice-sdk-js/example ]; then
	rm -R hipay_enterprise/lib/bower_components/hipay-fullservice-sdk-js/example
fi

if [ -d hipay_enterprise/lib/bower_components/hipay-fullservice-sdk-js/example ]; then
	rm -R hipay_enterprise/lib/bower_components/hipay-fullservice-sdk-js/images
fi

if [ -d hipay_enterprise/lib/vendor/hipay/hipay-fullservice-sdk-php/.git ]; then
	rm -R hipay_enterprise/lib/vendor/hipay/hipay-fullservice-sdk-php/.git
fi

if [ -f hipay_enterprise/config_fr.xml ]; then
	rm hipay_enterprise/config_fr.xml
fi

if [ -d hipay_enterprise/nbproject ]; then
	rm -R hipay_enterprise/nbproject
fi

if [ -d hipay_enterprise/.idea ]; then
	rm -R hipay_enterprise/.idea 
fi

find hipay_enterprise/ -type d -exec cp index.php {} \;

zip -r ../hipay_enterprise-$1.zip hipay_enterprise

rm -R hipay_enterprise
