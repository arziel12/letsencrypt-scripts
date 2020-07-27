#!/usr/bin/env bash

#DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
#
##if [ ! -f config.neon ]; then
##	cp $DIR/config.example.neon $DIR/config.neon
##
##fi
#
##exit
#
#
#docker pull arziel/php:7.3
#
#docker run \
#	--workdir /var/docker \
#	--volume $DIR:/var/docker \
#	arziel/php:7.3 \

php cli.php run authenticate $CERTBOT_DOMAIN $CERTBOT_VALIDATION
