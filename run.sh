#!/usr/bin/env bash

touch out/$CERTBOT_VALIDATION

docker run \
	--workdir /var/docker \
	--volume $(pwd):/var/docker \
	arziel/php:7.3 \
	php cli.php $CERTBOT_DOMAIN $CERTBOT_VALIDATION