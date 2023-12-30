#!/bin/bash

showMessage " > SSL DEV - Configuration"

mkdir -p $SSL_CERT_FOLDER

rm -f $SSL_CERT_PUBLIC
rm -f $SSL_CERT_PRIVATE
cp $CONFIG_FOLDER/ssl/dev-cert.crt $SSL_CERT_PUBLIC
cp $CONFIG_FOLDER/ssl/dev-cert.key $SSL_CERT_PRIVATE

chown root.root $SSL_CERT_FOLDER/*
chmod 600       $SSL_CERT_FOLDER/*
