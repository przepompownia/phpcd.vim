try
	let s:save_cpo = &cpo
	set cpo&vim

	silent! nnoremap <silent> <unique> <buffer> <C-]>
				\ :<C-u>call phpcd#JumpToDefinition('normal')<CR>
	silent! nnoremap <silent> <unique> <buffer> <C-W><C-]>
				\ :<C-u>call phpcd#JumpToDefinition('split')<CR>
	silent! nnoremap <silent> <unique> <buffer> <C-W><C-\>
				\ :<C-u>call phpcd#JumpToDefinition('vsplit')<CR>
	silent! nnoremap <silent> <unique> <buffer> K
				\ :<C-u>call phpcd#JumpToDefinition('preview')<CR>
	silent! nnoremap <silent> <unique> <buffer> <C-t>
				\ :<C-u>call phpcd#JumpBack()<CR>

	command! -nargs=0 PHPID call phpcd#Index()

	if has('nvim')
		let messenger = 'msgpack'
	else
		let messenger = 'json'
	end

	let s:phpcd_path = expand('<sfile>:p:h:h') . '/php/main.php'

	let g:phpcd_server_options['messenger'] = messenger
	let g:php_autoload_path = g:phpcd_root.'/'.g:phpcd_autoload_path
	let g:phpcd_server_options['autoload_path'] = g:php_autoload_path

	if ! exists('*json_encode')
		echoerr 'Function json_encode is not defined.'
	endif
	let s:encoded_options = json_encode(g:phpcd_server_options)

	function! Init(new_phpcd_root, projectConfigFile) " {{{
		let phpcd_vim = a:new_phpcd_root.'/'.a:projectConfigFile
		if filereadable(phpcd_vim)
			exec 'source 'a:projectConfigFile
		endif

		" if exists('g:phpcd_channel_id')
		" 	call rpc#stop(g:phpcd_channel_id)

		" 	unlet g:phpcd_channel_id
		" 	if exists('g:phpid_channel_id')
		" 		unlet g:phpid_channel_id
		" 	endif
		" endif
	endfunction " }}}

	let directory = expand("%:p:h")
	if ! isdirectory(directory)
		echoerr(printf('The directory path %s of the current file does not exist. Trying to use the current directory as the project root instead.', directory))
		let directory = getcwd()
	endif

	let new_phpcd_root = phpcd#GetRoot(
		\ g:phpcd_root,
		\ directory,
		\ g:phpcd_project_config_file_name,
		\ g:phpcd_autoload_path
		\ )

	if (g:phpcd_auto_restart == 0 && g:phpcd_root == '/') || (g:phpcd_auto_restart == 1 && new_phpcd_root != g:phpcd_root)
		call Init(new_phpcd_root, g:phpcd_project_config_file_name)
		let g:phpcd_root = new_phpcd_root
	endif

	if !exists('g:phpcd_channel_id')
		let g:phpcd_channel_id = rpc#start(g:phpcd_php_cli_executable,
					\ s:phpcd_path, g:phpcd_root, s:encoded_options)

		if g:phpcd_root != '/'
			let g:phpid_channel_id = g:phpcd_channel_id
		endif
	endif

finally
	let &cpo = s:save_cpo
	unlet s:save_cpo
endtry

" vim: foldmethod=marker:noexpandtab:ts=2:sts=2:sw=2
