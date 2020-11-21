#!/bin/sh

XP_RUNNERS_URL=https://dl.bintray.com/xp-runners/generic/xp-run-8.1.7.sh

case $1 in
  install)
    printf "\033[33;1mInstalling XP Runners\033[0m\n"
    echo $XP_RUNNERS_URL
    curl -SL $XP_RUNNERS_URL > xp-run
    echo

    printf "\033[33;1mRunning Composer\033[0m\n"
    composer install
  ;;

  run-tests)
    echo test.xar > test.pth
    for class in `grep class src/test/config/unittest/core.ini | cut -d '"' -f 2` ; do
      printf "\033[33;1mTesting %s\033[0m\n" $class
      sh xp-run xp.unittest.Runner $file || echo "Fail!"
    done
    result=0
    for file in `ls -1 src/test/config/unittest/*.ini`; do
      printf "\033[33;1mTesting %s\033[0m\n" $file
      sh xp-run xp.unittest.Runner $file || result=1
      echo
    done
    exit $result
  ;;
esac