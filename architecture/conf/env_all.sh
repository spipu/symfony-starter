#!/bin/bash

# Generic Parameters
ENV_NAME="starter"
ENV_HOST="${ENV_NAME}.lxd"
ENV_SSH_PORT="22"
ENV_MODE="dev"
ENV_CODE="dev"
ENV_USER="delivery"
ENV_FOLDER="/var/www/$ENV_NAME"
WEB_FOLDER="website"

# Hosts
ENV_HOST_SUB_HOSTS=(
)
ENV_HOST_SUB_HOSTS_TXT=""

# MySQL
DB_NAME="$ENV_NAME"
DB_USER="$ENV_NAME"
DB_PASS="$ENV_NAME"

# PHP
PHP_DISPLAY_ERRORS="True"

# SSL
SSL_CERT_FOLDER="/etc/ssl/dev-certs"
SSL_CERT_PUBLIC="${SSL_CERT_FOLDER}/dev-cert.crt"
SSL_CERT_PRIVATE="${SSL_CERT_FOLDER}/dev-cert.key"
SSL_CERT_CHAIN="${SSL_CERT_FOLDER}/dev-cert.key"

# Apache
EXPORT_FOLDER="$ENV_FOLDER/$WEB_FOLDER/var/export/"

# Symfony
APP_SECRET="e95262a968c0fa9d0f540a3edbcfd448c360ab20"
APP_MAILER="smtp://127.0.0.1:1025"
APP_SESSION_HANDLER="app.session.handler"
