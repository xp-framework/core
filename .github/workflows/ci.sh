#!/bin/sh

XP_RUNNERS_URL=https://baltocdn.com/xp-framework/xp-runners/distribution/downloads/e/entrypoint/xp-run-8.6.2.sh

case $1 in
  install)
    printf "\033[33;1mInstalling XP Runners\033[0m\n"
    echo $XP_RUNNERS_URL
    curl -SL $XP_RUNNERS_URL > xp-run
    echo

    printf "\033[33;1mRunning Composer\033[0m\n"
    COMPOSER_ROOT_VERSION=$(grep '^## ' ChangeLog.md | grep -v '?' | head -1 | cut -d ' ' -f 2) composer install
    echo "src/main/php/__xp.php" > composer.pth
    echo "vendor/autoload.php" >> composer.pth
    echo "!src/test/php" >> composer.pth
    echo "!src/test/resources" >> composer.pth
  ;;

  run-tests)
    sh xp-run xp.test.Runner -r Dots src/test/php
  ;;
esac