try
	set cpo&vim
	let s:save_cpo = &cpo

	if ! exists('g:phpcd_server_options')
		let g:phpcd_server_options = {}
	endif

	if ! exists('g:phpcd_php_cli_executable')
		let g:phpcd_php_cli_executable = 'php'
	endif

	let g:phpcd_root = '/'
	let g:phpcd_autoload_path = 'vendor/autoload.php'
	let g:phpcd_need_update = 0
	let g:phpcd_auto_restart = 0
	let g:phpcd_project_config_file_name = '.phpcd.vim'

	if ! exists('g:phpcd_insert_class_shortname')
		let g:phpcd_insert_class_shortname = 0
	endif

	autocmd BufLeave,VimLeave *.php if g:phpcd_need_update > 0 | call phpcd#UpdateIndex() | endif
	autocmd BufWritePost *.php let g:phpcd_need_update = 1
	autocmd FileType php setlocal omnifunc=phpcd#CompletePHP
	autocmd CompleteDone *.php call phpcd#completeDone()

finally
	let &cpo = s:save_cpo
	unlet s:save_cpo
endtry

" vim: foldmethod=marker:noexpandtab:ts=2:sts=2:sw=2
