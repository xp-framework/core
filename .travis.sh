#!/bin/sh

case $1 in
  install)
    if [ -f /etc/hhvm/php.ini ] ; then
      docker pull hhvm/hhvm:latest
      docker run --rm hhvm/hhvm:latest hhvm --version

      echo "hhvm.php7.all = 1" > php.ini
      echo "hhvm.hack.lang.look_for_typechecker = 0" >> php.ini

      mv xp-run xp-run.in
      echo "#!/bin/sh" > xp-run
      echo "docker run --rm -v $(pwd):/opt/src -v $(pwd)/php.ini:/etc/hhvm/php.ini hhvm/hhvm:latest /opt/src/xp-run.in \$@" >> xp-run
    else
      composer install
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