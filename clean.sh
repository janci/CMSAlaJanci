#!/bin/bash
rm -rf www/pub
rm -rf temp/cache/_Nette.*

database=app/database/data.sq3

git checkout $database
chmod 777 $database
