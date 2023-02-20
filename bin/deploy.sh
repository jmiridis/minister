#!/bin/bash

cd ~/domains/minister-mayela.com/source
bin/console d:m:mi --no-interaction
./node_modules/.bin/encore production
rm -rf var/cache/*
