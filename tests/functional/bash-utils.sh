#!/bin/bash

killTask()
{
	local pid=$1
	local taskName=${2:-unnamedTask}

	kill $1

	if (($? != 0))
	then
		printf "Cannot kill what already dead: %s %s\n" $pid $taskName >&2

		return 1
	else
		return 0
	fi
}
