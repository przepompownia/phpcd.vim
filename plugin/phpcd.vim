let s:save_cpo = &cpo
set cpo&vim

if (!exists('g:phpcd_server_options'))
	let g:phpcd_server_options = {}
endif

if (!exists('g:phpcd_server_options.completion_match_type'))
	let g:phpcd_server_options.completion_match_type = 'head'
endif

let g:phpcd_root = '/'

function! GetRoot(globalRoot) " {{{
	let root = expand("%:p:h")

	if a:globalRoot != '/' && stridx(root, a:globalRoot) == 0
		return a:globalRoot
	endif

	let g:isFoundPHPCD = 0
	while root != "/"
		if (filereadable(root.'/.phpcd.vim'))
			let g:isFoundPHPCD = 1
			break
		endif
		let root = fnamemodify(root, ":h")
	endwhile

	if g:isFoundPHPCD != 1
		let root = expand("%:p:h")
		while root != "/"
			if (filereadable(root . "/vendor/autoload.php"))
				break
			endif
			let root = fnamemodify(root, ":h")
		endwhile
	endif
	return root
endfunction " }}}

let g:phpcd_root = GetRoot(g:phpcd_root)
if filereadable(g:phpcd_root.'/.phpcd.vim')
	exec 'source 'g:phpcd_root.'/.phpcd.vim'
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
