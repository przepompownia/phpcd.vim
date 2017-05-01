#!/bin/bash

cd $(dirname $0)
. ../app/vimCommand.sh

composer --working-dir=../fixtures dump-autoload -ao -vvv

messageFile=/tmp/phpcd-tests-vim-messages.log

vimExecuables=(`which nvim`)

for vimExecuable in "${vimExecuables[@]}"; do
	printf "Test on %s:\n" $vimExecuable
	vimWithPHPCDOnly "$vimExecuable" \
		--cmd "let g:phpcd_test_vim_message_log='${messageFile}'" \
		-S ../app/runner.vim \
		../tests/test_*.vim

	cat $messageFile

	grep -q "0 errors, 0 failures" $messageFile
	status=$?
	rm -f $messageFile
done

exit $status
