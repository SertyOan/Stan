#!/bin/bash

cd `dirname $0` 

./build-server.sh $1
./build-client.sh $1
