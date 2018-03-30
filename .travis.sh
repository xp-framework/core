#!/bin/sh

XP_RUNNERS_URL=https://dl.bintray.com/xp-runners/generic/xp-run-master.sh

case $1 in
  install)
    printf "\033[33;1mInstalling XP Runners\033[0m\n"
    echo $XP_RUNNERS_URL
    curl -SL $XP_RUNNERS_URL > xp-run
    echo

    case "$TRAVIS_PHP_VERSION" in
      nightly*)
        printf "\033[33;1mReplacing Nightly w/ PHP.JIT\033[0m\n"
        wd=$(pwd)
        mv xp-run xp-irun
        git clone https://github.com/mente/php-docker-jit.git
        docker build -t php:jit php-docker-jit/

        echo "#!/bin/sh" > xp-run
        echo "docker run --rm -v $wd:/opt/src -w /opt/src php:jit sh /opt/src/xp-irun \$@" >> xp-run
        chmod 755 xp-run
      ;;
    esac

    printf "\033[33;1mRunning Composer\033[0m\n"
    composer install
  ;;

  run-tests)
    echo test.xar > test.pth
    result=0
    for file in `ls -1 src/test/config/unittest/*.ini`; do
      printf "\033[33;1mTesting %s\033[0m\n" $file
      sh xp-run xp.unittest.Runner $file || result=1
      echo
    done
    exit $result
  ;;
esac