#!/usr/bin/env bash

junit-viewer --results=bin/tests/result.xml --save=bin/tests/report$1-$2.html --minify=false --contracted
if [ -d bin/tests/errors/ ]; then mkdir $CIRCLE_ARTIFACTS/screenshots/$1-$2; cp bin/tests/errors/* $CIRCLE_ARTIFACTS/screenshots/$1-$2; rm -rf bin/tests/errors/; fi
