#!/bin/sh

XP_RUNNERS_URL=https://dl.bintray.com/xp-runners/generic/xp-run-master.sh

wrap() {
  local image="$1"
  local cmd="$2"
  local target="$3"
  local wrapper=$(basename $target).in
  local wd=$(pwd)

  mv $target $wrapper
  echo "#!/bin/sh" > $target
  echo "docker run --rm -v $wd:/opt/src -v $wd/php.ini:/etc/hhvm/php.ini -w /opt/src $image $cmd $wrapper \$@" >> $target
  chmod 755 $target
}

replace_hhvm_with() {
  local version="$1"

  printf "\033[33;1mReplacing HHVM\033[0m\n"
  docker pull hhvm/hhvm:$version
  docker run --rm hhvm/hhvm:$version hhvm --version
  echo

  echo "hhvm.php7.all = 1" > php.ini
  echo "hhvm.hack.lang.look_for_typechecker = 0" >> php.ini
  wrap hhvm/hhvm:$version "hhvm --php" /home/travis/.phpenv/versions/hhvm/bin/composer
  wrap hhvm/hhvm:$version "sh" xp-run
}

case $1 in
  install)
    printf "\033[33;1mInstalling XP Runners\033[0m\n"
    echo $XP_RUNNERS_URL
    curl -SL $XP_RUNNERS_URL > xp-run
    echo

    # Run HHVM inside Docker as the versions provided by Travis-CI are too old
    case "$TRAVIS_PHP_VERSION" in
      hhvm-nightly*)
        replace_hhvm_with "latest"
      ;;

      hhvm*)
        replace_hhvm_with "3.20.1"
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