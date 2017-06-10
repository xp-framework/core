#!/bin/sh

case $1 in
  setup-hhvm)
    if [ -f /etc/hhvm/php.ini ] ; then
      docker pull hhvm/hhvm:latest

      echo "hhvm.php7.all = 1" > php.ini
      echo "hhvm.hack.lang.look_for_typechecker = 0" > php.ini

      cp xp-run xp-run.in
      echo "#!/bin/sh" > xp-run
      echo "docker run -v $(pwd):/opt/src -v $(pwd)/php.ini:/etc/hhvm/php.ini hhvm/hhvm:latest /opt/src/xp-run.in \$@" >> xp-run
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