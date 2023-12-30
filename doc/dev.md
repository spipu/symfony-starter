# Dev

## Web Access

| Site       | Url                         | Comment                               |
|------------|-----------------------------|---------------------------------------|
| Public     | https://starter.lxd/       |                                       | 
| Admin      | https://starter.lxd/admin/ | Admin account must be used for log-in |

You can use LXD, or Docker technology.

Admin Account:

* email : `admin`
* password: `password`

## How to create the environment

How to create your environment on Linux

### LXD

```bash
# Create the dev env
./architecture/create-lxd.sh

# Remove the dev env
./architecture/remove-lxd.sh
```

### Docker

```bash
# Create the dev env
./architecture/create-docker.sh

# Start the dev env
./architecture/start-docker.sh

# Remove the dev env
./architecture/remove-docker.sh
```

If needed, you can create the file `architecture/conf/env.local.sh` and put the following content (for MacOS for example):

```bash
#!/bin/bash

# Docker Parameters
ENV_DOCKER_IP="127.0.0.1"
ENV_DOCKER_PORT_START="20000"
```

## Root user

You can use the `root` user **only** if you need to control the services of the dev environment.

```
ssh root@starter.lxd
```

## Delivery User

You can use the `delivery` user if you need to use composer or to execute the symfony console.

```
ssh delivery@starter.lxd
cd /var/www/starter/website/
composer require vendor/package
sudo -u www-data bin/console
```

**warning** always use the ̀`www-data` user to execute the symfony console

## DataBase (MySQL)

You can access to the database:

```
ssh delivery@starter.lxd
~/mysql.sh lxd
```

## Quality

The following quality tools are configured:

* deptrac
* phpqa
* phpunit

The tool **phpqa** is used to manage the following:

* phpcs
* phpmd
* phpcpd
* phploc

You **must** execute the tools from your host (not from your dev env) :

```
# DepTrac
./quality/deptrac.sh

# PhpQA
./quality/analyze.sh

# phpunit
./quality/phpunit.sh
```

For phpunit, you must install the following packages:

* php-cli
* php-common
* php-curl
* php-gd
* php-intl
* php-mbstring
* php-soap
* php-sqlite3
* php-ssh2
* php-zip
* php-xml
* firefox

You must install [composer](https://getcomposer.org/download/).

## Folders to exclude from PhpStorm

You can exclude the following folders from PhpStorm:

* ./quality/build
* ./website/var-test

## Install / Upgrade

after a pull / rebase / ... , you must update the application :

```bash
ssh delivery@starter.lxd
/var/www/starter/architecture/scripts/install.sh
```

## Front - SASS - Scss

To use it:

```bash
ssh delivery@bonus.lxc
/var/www/starter/architecture/scripts/watch-front.sh
```

## Regenerate the SSL certificate

```bash
ssh root@starter.lxd

touch /root/.rnd
mkdir -p /etc/ssl/dev-certs/
rm -f /etc/ssl/dev-certs/dev-cert.*

openssl req -new -x509 -days 1095 -nodes \
    -out /etc/ssl/dev-certs/dev-cert.crt \
    -keyout /etc/ssl/dev-certs/dev-cert.key \
    -subj "/C=FR/ST=Paris/L=Paris/O=Spipu/OU=Spipu/CN=Symfony Template/" \
    -addext "subjectAltName = DNS:starter.lxd"

chmod 600 /etc/ssl/dev-certs/*

systemctl restart apache2

rm -f /var/www/starter/architecture/conf/template/ssl/dev-cert.*
cp /etc/ssl/dev-certs/dev-cert.*  /var/www/starter/architecture/conf/template/ssl/
chown delivery.1000 /var/www/starter/architecture/conf/template/ssl/dev-cert.*
chmod 664           /var/www/starter/architecture/conf/template/ssl/dev-cert.*
```
