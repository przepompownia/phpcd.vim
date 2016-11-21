let s:save_cpo = &cpo
set cpo&vim

if (!exists('g:phpcd_server_options'))
	let g:phpcd_server_options = {}
endif

if (!exists('g:phpcd_server_options.completion_match_type'))
	let g:phpcd_server_options.completion_match_type = 'head'
endif

let g:phpcd_root = '/'

function! GetRoot() " {{{
	let root = expand("%:p:h")

	if g:phpcd_root != '/' && stridx(root, g:phpcd_root) == 0
		return g:phpcd_root
	endif

	while root != "/"
		if (filereadable(root . "/vendor/autoload.php") || filereadable(root.'/.phpcd.vim'))
			break
		endif
		let root = fnamemodify(root, ":h")
	endwhile
	let g:phpcd_root = root
	return root
endfunction " }}}

let s:root = GetRoot()
if filereadable(s:root.'/.phpcd.vim')
	exec 'source '.s:root.'/.phpcd.vim'
endif

let g:phpcd_need_update = 0
let g:phpcd_jump_stack = []

if !exists('g:phpcd_autoload_path')
	let g:phpcd_autoload_path = 'vendor/autoload.php'
endif

if (!exists('g:phpcd_insert_class_shortname'))
	let g:phpcd_insert_class_shortname = 0
endif

autocmd BufLeave,VimLeave *.php if g:phpcd_need_update > 0 | call phpcd#UpdateIndex() | endif
autocmd BufWritePost *.php let g:phpcd_need_update = 1
autocmd FileType php setlocal omnifunc=phpcd#CompletePHP

let &cpo = s:save_cpo
unlet s:save_cpo

autocmd CompleteDone *.php call phpcd#completeDone()

" vim: foldmethod=marker:noexpandtab:ts=2:sts=2:sw=2
