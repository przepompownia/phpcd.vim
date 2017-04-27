#!/bin/bash

composer --working-dir=fixtures dump-autoload -ao -vvv

messageFile=/tmp/phpcd-tests-vim-messages.log

vimExecuable=nvim

$vimExecuable -u vimrc -U NONE -N \
	--cmd set\ rtp+=../.. \
	--cmd 'source ../../plugin/phpcd.vim' \
	--cmd "let g:phpcd_test_vim_message_log='${messageFile}'" \
	-S runner.vim                              \
	test_*.vim

cat $messageFile

grep -q "0 errors, 0 failures" $messageFile
status=$?
rm -f $messageFile
exit $status
