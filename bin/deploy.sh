#!/bin/bash

cd ~/domains/minister-mayela.com/source
git pull
~/composer.phar install
bin/console d:m:mi --no-interaction
yarn install
./node_modules/.bin/encore production
rm -rf var/cache/*
