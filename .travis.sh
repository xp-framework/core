#!/bin/sh

case $1 in
  setup-hhvm)
    if [ -f /etc/hhvm/php.ini ] ; then
      sudo apt-get install software-properties-common
      sudo apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0x5a16e7281be7a449
      sudo add-apt-repository "deb http://dl.hhvm.com/ubuntu $(lsb_release -sc) main"
      sudo apt-get update
      sudo apt-get install hhvm

      (echo "hhvm.php7.all = 1"; echo "hhvm.hack.lang.look_for_typechecker = 0") | sudo tee -a /etc/hhvm/php.ini
    fi
  ;;

  run-tests)
    result=0
    for file in `ls -1 src/test/config/unittest/*.ini`; do
      echo "---> $file"
      sh xp-run xp.unittest.Runner $file || result=1
    done
    exit $result
  ;;
esac