#!/bin/bash

# Go into the project folder
cd "$( dirname "${BASH_SOURCE[0]}" )"
cd ../website/

# Create the build folder
LOG_FOLDER="../quality/build/"
mkdir -p $LOG_FOLDER

# Cleanup
rm -rf ./var/log
rm -rf ./var/cache
rm -rf ./var-test/log
rm -rf ./var-test/cache
rm -rf ./var-test/screenshot
rm -rf ./var-test/test.sqlite

# Modify the /etc/hosts file
sudo sh -c "echo \"# PHPUNIT-STARTER
127.0.0.1 starter.lxd # PHPUNIT-STARTER\" >> /etc/hosts"

# Create temporary Key Pair
APP_ENCRYPTOR_KEY_PAIR=$(php -r "echo sodium_bin2base64(sodium_crypto_box_keypair(), SODIUM_BASE64_VARIANT_ORIGINAL);")
export APP_ENCRYPTOR_KEY_PAIR
APP_ENV="dev"
export APP_ENV

# Install the good driver
./vendor/bin/bdi detect drivers

# Increase max open file limit
ulimit -n 8192

# Tests - PhpUnit --no-coverage
if [[ "$1" == "--coverage" ]]; then
  echo "PHPUnit - With Coverage"
  ./bin/phpunit -c ./.phpunit.xml $2
else
  echo "PHPUnit - Without Coverage"
  ./bin/phpunit -c ./.phpunit.xml --no-coverage $1
fi

# Clean bad cache
rm -rf ./var/log
rm -rf ./var/cache

# Clean temporary Key Pair
APP_ENCRYPTOR_KEY_PAIR=""
export APP_ENCRYPTOR_KEY_PAIR

APP_ENV=""
export APP_ENV

# Revert the /etc/hosts file
sudo sed -i "/\# PHPUNIT-STARTER/d" /etc/hosts

# Output
if [[ "$1" == "--coverage" ]]; then
  firefox "${LOG_FOLDER}coverage/index.html"
fi
