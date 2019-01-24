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

# TODO check all values are present

echo "Building client"

if [ $APP_CLEAN -eq 1 ]
then
    echo "Cleaning"
    rm -rf $APP_TMP/client
fi

mkdir -p $APP_TMP/client

echo "Getting source code"

rm -rf $APP_TMP/source
mkdir $APP_TMP/source -p

if [ -z $APP_GIT_LOCAL ]
then
    echo "Cloning repository: "$APP_GIT_URL
    cd $APP_TMP/source
    git clone $APP_GIT_URL
    git checkout tags/$APP_GIT_TAG
    rsync --filter="- .git" -rc $APP_TMP/source/client/  $APP_TMP/client/
else 
    echo "Copying local repository"
    rsync --filter="- .git" -rc $APP_GIT_LOCAL/client/  $APP_TMP/client/
fi

echo "Running Webpack"

cd $APP_TMP/client
npm install
npx webpack

echo "Fingerprinting"

sed -i 's|{{RELEASE}}|'$APP_RELEASE'|g' $APP_TMP/client/webroot/index.html 

echo "Adding footer"

sed -i "s|{{FOOTER}}|$APP_FOOTER_HTML|g" $APP_TMP/client/webroot/index.html 

echo "Synchronizing files"

rsync -rc --links --filter="- node_modules" --delete $APP_TMP/client/ root@$APP_SERVER:$APP_PATH/code/client/

echo "Client built successful"
