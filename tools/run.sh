#!/usr/bin/env bash

# Local web server address
ADDR=localhost:8080

# Open command
UNAMESTR=`uname`
if [[ "$(expr substr ${UNAMESTR} 1 6)" == "CYGWIN" ]] ; then
	OPEN='cygstart'
else
	if [[ "${UNAMESTR}" == "Linux" ]] ; then
		OPEN='xdg-open'
	else
	    OPEN='open'
	fi
fi

cd $(dirname $0)/.. && php -S $ADDR router.php &

$OPEN http://$ADDR

wait
