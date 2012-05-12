#!/bin/bash
rm -rf www/pub
rm -rf temp/cache/_Nette.*

database=app/database/data.sq3

sqlite3 $database  'DROP TABLE IF EXISTS `article`'

sqlite3 $database  'DROP TABLE IF EXISTS `page`'
sqlite3 $database  'DROP TABLE IF EXISTS `new`'
sqlite3 $database  'DROP TABLE IF EXISTS `photo`'
sqlite3 $database  'DROP TABLE IF EXISTS `album`'
sqlite3 $database  'DROP TABLE IF EXISTS `document`'
sqlite3 $database  'DROP TABLE IF EXISTS `seo`'
