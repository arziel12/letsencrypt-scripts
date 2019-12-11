#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo CLEANUP


docker pull arziel/php:7.3
docker run \
	--workdir /var/docker \
	--volume $DIR:/var/docker \
	arziel/php:7.3 \
	php cleanup.php $CERTBOT_DOMAIN $CERTBOT_VALIDATION

rm out/$CERTBOT_VALIDATION