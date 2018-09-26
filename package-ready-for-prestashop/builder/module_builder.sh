#!/usr/bin/env bash

version=1.0.0

function cleanAndPackage()
{
    cp -R src/hipay_enterprise hipay_enterprise
    ############################################
    #####          CLEAN CONFIG             ####
    ############################################
    if [ -f hipay_enterprise/config_fr.xml ]; then
        rm hipay_enterprise/config_fr.xml
    fi

    ############################################
    #####          CLEAN IDEA FILE           ####
    ############################################
    if [ -d hipay_enterprise/nbproject ]; then
        rm -R hipay_enterprise/nbproject
    fi

    if [ -d hipay_enterprise/.idea ]; then
        rm -R hipay_enterprise/.idea
    fi

    find hipay_enterprise/ -type d -exec cp index.php {} \;
    zip -r package-ready-for-prestashop/hipay-enterprise-sdk-prestashop-$version.zip hipay_enterprise
    rm -R hipay_enterprise
}

function show_help()
{
	cat << EOF
Usage: $me [options]

options:
    -h, --help                        Show this help
    -v, --version                     Configure version for package
EOF
}

function parse_args()
{
	while [[ $# -gt 0 ]]; do
		opt="$1"
		shift

		case "$opt" in
			-h|\?|--help)
				show_help
				exit 0
				;;
				esac
		case "$opt" in
			-v|--version)
              	version="$1"
				shift
				;;
		    esac
	done;
}

function main()
{
	parse_args "$@"
	cleanAndPackage
}

main "$@"

