let s:save_cpo = &cpo
set cpo&vim

if !exists('g:phpcd_php_cli_executable')
	let g:phpcd_php_cli_executable = 'php'
endif

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

if has('nvim')
	let messenger = 'msgpack'
else
	let messenger = 'json'
end

let g:phpcd_server_options['messenger'] = messenger
let g:php_autoload_path = g:phpcd_root.'/'.g:phpcd_autoload_path
let g:phpcd_server_options['autoload_path'] = g:php_autoload_path

if ! exists('*json_encode')
	echoerr 'Function json_encode is not defined.'
endif

let s:encoded_options = json_encode(g:phpcd_server_options)
command! -nargs=0 PHPID call phpcd#Index()

let s:phpcd_path = expand('<sfile>:p:h:h') . '/php/main.php'
if !exists('g:phpcd_channel_id')
	let g:phpcd_channel_id = rpc#start(g:phpcd_php_cli_executable,
				\ s:phpcd_path, g:phpcd_root, 'PHPCD', s:encoded_options)
endif

if g:phpcd_root == '/'
	let &cpo = s:save_cpo
	unlet s:save_cpo
	finish
endif

if !exists('g:phpid_channel_id')
	let g:phpid_channel_id = rpc#start(g:phpcd_php_cli_executable,
				\ s:phpcd_path, g:phpcd_root, 'PHPID', s:encoded_options)
endif

let &cpo = s:save_cpo
unlet s:save_cpo

" vim: foldmethod=marker:noexpandtab:ts=2:sts=2:sw=2
