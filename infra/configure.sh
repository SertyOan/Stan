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

echo "Generate configuration"

mkdir -p $APP_TMP/conf
rm $APP_TMP/conf/* -rf

echo "<?php" > $APP_TMP/conf/Configuration.php
echo "define('STAN_DATABASE_HOSTNAME', '$APP_MYSQL_HOST');" >> $APP_TMP/conf/Configuration.php
echo "define('STAN_DATABASE_USER', '$APP_MYSQL_USER');" >> $APP_TMP/conf/Configuration.php
echo "define('STAN_DATABASE_PASSWORD', '$APP_MYSQL_PASSWORD');" >> $APP_TMP/conf/Configuration.php
echo "define('STAN_DATABASE_SCHEMA', '$APP_MYSQL_SCHEMA');" >> $APP_TMP/conf/Configuration.php
echo "define('STAN_FQDN', '$APP_FQDN');" >> $APP_TMP/conf/Configuration.php
echo "define('STAN_EMAIL', '$APP_EMAIL');" >> $APP_TMP/conf/Configuration.php

echo "Synchronize configuration"

scp -q $APP_TMP/conf/Configuration.php root@$APP_SERVER:$APP_PATH/etc/

echo "Configuration successful"
