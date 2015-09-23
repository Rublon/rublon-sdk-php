#!/bin/bash
# Arguments:
# - access token

source config.cfg

DATA="{\"systemToken\":\"$SYSTEM_TOKEN\",\"accessToken\":\"$1\"}"

echo -n "$DATA" | ./api.sh credentials
