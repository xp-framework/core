#!/bin/sh

case $1 in
  install)
    case "$TRAVIS_PHP_VERSION" in
      hhvm*)
        docker pull hhvm/hhvm:latest
        docker run --rm hhvm/hhvm:latest hhvm --version

        echo "hhvm.php7.all = 1" > php.ini
        echo "hhvm.hack.lang.look_for_typechecker = 0" >> php.ini

        mv xp-run xp-run.in
        echo "#!/bin/sh" > xp-run
        echo "docker run --rm -v $(pwd):/opt/src -v $(pwd)/php.ini:/etc/hhvm/php.ini -w /opt/src hhvm/hhvm:latest /bin/sh xp-run.in \$@" >> xp-run
      ;;

      *)
        composer install
      ;;
    esac
  ;;

  run-tests)
    result=0
    for file in `ls -1 src/test/config/unittest/*.ini`; do
      echo "---> $file"
      sh xp-run -cp test.xar xp.unittest.Runner $file || result=1
    done
    exit $result
  ;;
esac