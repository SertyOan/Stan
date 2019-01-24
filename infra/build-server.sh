#!/bin/bash

cd `dirname $0` 

if [ $# -ne 1 ]
then
    echo "Usage: `basename $0` [config-file]"
    exit 1
else
    APP_CONFIG_FILE=$1
fi

if [ -f $APP_CONFIG_FILE ]
then
    source $APP_CONFIG_FILE
else
    echo "Configuration file not found"
    exit 1
fi

echo "Building server"

if [ $APP_CLEAN -eq 1 ]
then
    echo "Cleaning"
    rm -rf $APP_TMP/server
fi

mkdir -p $APP_TMP/server

echo "Getting source code"

rm -rf $APP_TMP/source
mkdir $APP_TMP/source -p

if [ -z $APP_GIT_LOCAL ]
then
    echo "Cloning repository: "$APP_GIT_URL
    cd $APP_TMP/source
    git clone $APP_GIT_URL
    git checkout tags/$APP_GIT_TAG
    rsync --filter="- .git" -rc $APP_TMP/source/server/  $APP_TMP/server/
else 
    echo "Copying local repository"
    rsync --filter="- .git" -rc $APP_GIT_LOCAL/server/  $APP_TMP/server/
fi

echo "Running Composer"

cd $APP_TMP/server/php
composer install

rsync -rc --links --delete $APP_TMP/server/ root@$APP_SERVER:$APP_PATH/code/server/

echo "Server built successful"
