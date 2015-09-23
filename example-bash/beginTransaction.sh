#!/bin/bash
# Arguments:
# - user ID (optional)
# - user email (optional)

source config.cfg

if [ ! -z "$1" ]; then
	USER_ID="$1"
fi
if [ ! -z "$2" ]; then
	USER_EMAIL="$2"
fi

USER_EMAIL_HASH=`echo -n "$USER_EMAIL" | ./hash.sh`
DATA="{\"systemToken\":\"$SYSTEM_TOKEN\",\"callbackUrl\":\"$CALLBACK_URL\",\"userId\":\"$USER_ID\",\"userEmail\":\"$USER_EMAIL\",\"userEmailHash\":\"$USER_EMAIL_HASH\"}"

RESULT=`echo -n "$DATA" | ./api.sh beginTransaction`
echo "$RESULT"

# Parse web URI:
echo -n "$RESULT" | tail -n 1 | php -r "echo json_decode(file_get_contents('php://stdin'))->result->webURI;"
echo