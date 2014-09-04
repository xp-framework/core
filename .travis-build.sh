#!/bin/sh

REL=$1

cd src/main/php
echo $REL > VERSION
USE_XP=../../.. ../../../xar cvf ../../../xp-rt-$REL.xar . VERSION
cd ../../..

cat glue.json | sed -e "s/.version.*:.*,/\"version\" : \"$REL\",/g" > tmp ; mv tmp glue.json
ls -al xp-rt-$REL.xar
cat glue.json
