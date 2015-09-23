#!/bin/bash

source config.cfg

DATA=`cat /dev/stdin`

if [ -z "$1" ]; then
	ACTION="beginTransaction"
else
	ACTION="$1"
fi

URL="$API$ACTION"
SIGNATURE=`echo -n "$DATA" | ./hash.sh "$SECRET"`

echo
echo "URL: $URL"
echo "SIGNATURE: $SIGNATURE"
echo

curl -H "Content-Type: application/json" -H "X-Rublon-API-Version: $VERSION" -H "X-Rublon-Signature: $SIGNATURE" -X POST -d "$DATA" "$URL"