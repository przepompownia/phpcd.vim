#!/bin/bash

set -e

cd $(dirname $0)

. bash-utils.sh

cd ../..
projectPath=$PWD
autoloadPath="${projectPath}/vendor/autoload.php"

handler=PHPCD
php ./php/main.php "$PWD" "$handler" "$(printf '{
	"autoload_path": "%s",
	"completion_match_type":
	"head_or_subsequence_of_last_part",
	"messenger": "msgpack"
}' "$autoloadPath")" &

sleep 1

killTask $! $handler

echo $handler works
