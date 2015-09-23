#!/bin/bash
# Arguments:
# - secret key for the HMAC-SHA256 hash; use empty for the simple SHA256 hash


if [ -z "$1" ]; then
	php -r "echo hash('sha256', file_get_contents('php://stdin'));"
else
	php -r "echo hash_hmac('sha256', file_get_contents('php://stdin'), '$1');"
fi
