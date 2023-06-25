@echo off

for /f "skip=1 tokens=1-4" %%a in ('findstr /r /c:"^## " ChangeLog.md') do (
  set COMPOSER_ROOT_VERSION=%%b
  php .\xp\composer.phar install --prefer-dist
  echo src/main/php/__xp.php > composer.pth
  echo vendor/autoload.php >> composer.pth
  exit /b
)
