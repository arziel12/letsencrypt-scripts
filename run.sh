#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

touch $DIR/out/$CERTBOT_VALIDATION

docker run \
	--workdir /var/docker \
	--volume $DIR:/var/docker \
	arziel/php:7.3 \
	php cli.php $CERTBOT_DOMAIN $CERTBOT_VALIDATION