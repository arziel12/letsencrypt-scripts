#!/usr/bin/env bash


echo CLEANUP

env | grep CERTBOT

docker run \
	--workdir /var/docker \
	--volume $(pwd):/var/docker \
	arziel/php:7.3 \
	php cleanup.php $CERTBOT_DOMAIN $CERTBOT_VALIDATION

rm out/$CERTBOT_VALIDATION