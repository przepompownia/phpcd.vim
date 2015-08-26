" Vim completion script
" Language:	PHP
" Maintainer:	Mikolaj Machowski ( mikmach AT wp DOT pl )
" Maintainer:	Shawn Biddle ( shawn AT shawnbiddle DOT com )
" Maintainer:	Szabó Dávid ( complex857 AT gmail DOT com )

let s:script_path = fnamemodify(resolve(expand('<sfile>:p')), ':h')
let s:cache_classstructures = {}

function! phpcd#CompletePHP(findstart, base) " {{{
	" we need to wait phpcd {{{
	if g:phpcd_channel_id < 0
		return
	endif " }}}

	if a:findstart " {{{
		unlet! b:php_menu
		" Check if we are inside of PHP markup
		let pos = getpos('.')
		let phpbegin = searchpairpos('<?', '', '?>', 'bWn',
				\ 'synIDattr(synID(line("."), col("."), 0), "name") =~? "string\\|comment"')
		let phpend = searchpairpos('<?', '', '?>', 'Wn',
				\ 'synIDattr(synID(line("."), col("."), 0), "name") =~? "string\\|comment"')

		if phpbegin == [0,0] && phpend == [0,0]
			" We are outside of any PHP markup. Complete HTML
			let htmlbegin = htmlcomplete#CompleteTags(1, '')
			let cursor_col = pos[2]
			let base = getline('.')[htmlbegin : cursor_col]
			let b:php_menu = htmlcomplete#CompleteTags(0, base)
			return htmlbegin
		else
			" locate the start of the word
			let line = getline('.')
			let start = col('.') - 1
			let compl_begin = col('.') - 2
			while start >= 0 && line[start - 1] =~ '[\\a-zA-Z_0-9\x7f-\xff$]'
				let start -= 1
			endwhile
			let b:phpbegin = phpbegin
			let b:compl_context = phpcd#GetCurrentInstruction(line('.'), max([0, col('.') - 2]), phpbegin)

			return start
			" We can be also inside of phpString with HTML tags. Deal with
			" it later (time, not lines).
		endif
	endif " }}}

	" If exists b:php_menu it means completion was already constructed {{{
	" we don't need to do anything more
	if exists("b:php_menu")
		return b:php_menu
	endif " }}}

	" a:base is very short - we need context {{{
	if exists("b:compl_context")
		let context = b:compl_context
		unlet! b:compl_context
		" chop of the "base" from the end of the current instruction
		if a:base != ""
			let context = substitute(context, '\s*[$a-zA-Z_0-9\x7f-\xff]*$', '', '')
		end
	else
		let context = ''
	end " }}}

	try
		let winheight = winheight(0)
		let winnr = winnr()

		let [current_namespace, imports] = phpcd#GetCurrentNameSpace(getline(0, line('.')))

		if context =~? '^use\s' || context ==? 'use' " {{{
			" TODO complete use
		endif " }}}

		if context =~ '\(->\|::\)$' " {{{
			let classname = phpcd#GetClassName(line('.'), context, current_namespace, imports)
			return rpcrequest(g:phpcd_channel_id, 'info', classname, a:base)
		elseif context =~? 'implements'
			" TODO complete class Foo implements
		elseif context =~? 'extends\s\+.\+$' && a:base == ''
			" TODO complete class Foo extends
		elseif context =~? 'extends'
			" TODO complete class Foo extends Prefix..
		elseif context =~? 'class [a-zA-Z_\x7f-\xff\\][a-zA-Z_0-9\x7f-\xff\\]*'
			" special case when you've typed the class keyword and the name too,
			" only extends and implements allowed there
			return filter(['extends', 'implements'], 'stridx(v:val, a:base) == 0')
		elseif context =~? 'new'
			" TODO complete $foo = new
		endif " }}}

		if a:base =~ '^\$' " {{{
			return phpcd#CompleteVariable(a:base)
		else
			return phpcd#CompleteGeneral(a:base, current_namespace, imports)
		endif " }}}
	finally " {{{
		silent! exec winnr.'resize '.winheight
	endtry " }}}
endfunction
" }}}

function! phpcd#CompleteGeneral(base, current_namespace, imports) " {{{
	" Complete everything
	"  + functions,  DONE
	"  + keywords of language DONE
	"  + defines (constant definitions), DONE
	"  + extend keywords for predefined constants, DONE
	"  + classes (after new), DONE
	"  + limit choice after -> and :: to funcs and vars DONE

	" Internal solution for finding functions in current file.

	if a:base =~? '^\'
		let leading_slash = '\'
	else
		let leading_slash = ''
	endif

	let base = substitute(a:base, '^\\', '', '')
	let [pattern, namespace] = phpcd#ExpandClassName(a:base, a:current_namespace, a:imports)
	return rpcrequest(g:phpcd_channel_id, 'info', '', pattern)
endfunction
" }}}

function! phpcd#CompleteUnknownClass(base, context) " {{{
	let res = []

	if g:phpcd_complete_for_unknown_classes != 1
		return []
	endif

	if a:base =~ '^\$'
		let adddollar = '$'
	else
		let adddollar = ''
	endif

	let file = getline(1, '$')

	" Internal solution for finding object properties in current file.
	if a:context =~ '::'
		let variables = filter(deepcopy(file),
					\ 'v:val =~ "^\\s*\\(static\\|static\\s\\+\\(public\\|var\\)\\|\\(public\\|var\\)\\s\\+static\\)\\s\\+\\$"')
	elseif a:context =~ '->'
		let variables = filter(deepcopy(file),
					\ 'v:val =~ "^\\s*\\(public\\|var\\)\\s\\+\\$"')
	endif
	let jvars = join(variables, ' ')
	let svars = split(jvars, '\$')
	let int_vars = {}
	for i in svars
		let c_var = matchstr(i,
					\ '^\zs[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*\ze')
		if c_var != ''
			let int_vars[adddollar.c_var] = ''
		endif
	endfor

	" Internal solution for finding functions in current file.
	call filter(deepcopy(file),
			\ 'v:val =~ "function\\s\\+&\\?[a-zA-Z_\\x7f-\\xff][a-zA-Z_0-9\\x7f-\\xff]*\\s*("')
	let jfile = join(file, ' ')
	let int_values = split(jfile, 'function\s\+')
	let int_functions = {}
	for i in int_values
		let f_name = matchstr(i,
				\ '^&\?\zs[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*\ze')
		let f_args = matchstr(i,
				\ '^&\?[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*\s*(\zs.\{-}\ze)\_s*\(;\|{\|$\)')

		let int_functions[f_name.'('] = f_args.')'
	endfor

	" collect external functions from tags
	let ext_functions = {}
	let tags = phpcd#GetTaglist('^'.substitute(a:base, '^\$', '', ''))
	for tag in tags
		if tag.kind ==? 'f'
			let item = tag.name
			if has_key(tag, 'signature')
				let prototype = tag.signature[1:-2]
			else
				let prototype = matchstr(tag.cmd,
						\ 'function\s\+&\?[^[:space:]]\+\s*(\s*\zs.\{-}\ze\s*)\s*{\?')
			endif
			let ext_functions[item.'('] = prototype.') - '.tag['filename']
		endif
	endfor

	" All functions to one hash for later reference when deciding kind
	call extend(int_functions, ext_functions)

	let all_values = {}
	call extend(all_values, int_functions)
	call extend(all_values, int_vars) " external variables are already in
	call extend(all_values, g:php_builtin_object_functions)

	for m in sort(keys(all_values))
		if m =~ '\(^\|::\)'.a:base
			call add(res, m)
		endif
	endfor

	let start_list = res

	let final_list = []
	for i in start_list
		if has_key(int_vars, i)
			let class = ' '
			if all_values[i] != ''
				let class = i.' class '
			endif
			let final_list += [{'word':i, 'info':class.all_values[i], 'kind':'v'}]
		else
			let final_list +=
					\ [{'word':substitute(i, '.*::', '', ''),
					\	'info':i.all_values[i],
					\	'menu':all_values[i],
					\	'kind':'f'}]
		endif
	endfor
	return final_list
endfunction
" }}}

function! phpcd#CompleteVariable(base) " {{{
	let res = []

	" Internal solution for current file.
	let file = getline(1, '$')
	let jfile = join(file, ' ')
	let int_vals = split(jfile, '\ze\$')
	let int_vars = {}
	for i in int_vals
		if i =~? '^\$[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*\s*=\s*new'
			let val = matchstr(i,
						\ '^\$[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*')
		else
			let val = matchstr(i,
						\ '^\$[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*')
		endif
		if val != ''
			let int_vars[val] = ''
		endif
	endfor

	for m in sort(keys(int_vars))
		if m =~# '^\'.a:base
			call add(res, m)
		endif
	endfor

	let int_list = res

	let int_dict = []
	for i in int_list
		if int_vars[i] != ''
			let class = ' '
			if int_vars[i] != ''
				let class = i.' class '
			endif
			let int_dict += [{'word':i, 'info':class.int_vars[i], 'menu':int_vars[i], 'kind':'v'}]
		else
			let int_dict += [{'word':i, 'kind':'v'}]
		endif
	endfor

	return int_dict
endfunction
" }}}

function! phpcd#CompleteClassName(base, kinds, current_namespace, imports) " {{{
	let kinds = sort(a:kinds)
	" Complete class name
	let res = []
	if a:base =~? '^\'
		let leading_slash = '\'
		let base = substitute(a:base, '^\', '', '')
	else
		let leading_slash = ''
		let base = a:base
	endif

	" Internal solution for finding classes in current file.
	let file = getline(1, '$')
	let filterstr = ''

	if kinds == ['c', 'i']
		let filterstr = 'v:val =~? "\\(class\\|interface\\)\\s\\+[a-zA-Z_\\x7f-\\xff][a-zA-Z_0-9\\x7f-\\xff]*\\s*"'
	elseif kinds == ['c']
		let filterstr = 'v:val =~? "class\\s\\+[a-zA-Z_\\x7f-\\xff][a-zA-Z_0-9\\x7f-\\xff]*\\s*"'
	elseif kinds == ['i']
		let filterstr = 'v:val =~? "interface\\s\\+[a-zA-Z_\\x7f-\\xff][a-zA-Z_0-9\\x7f-\\xff]*\\s*"'
	endif

	call filter(file, filterstr)

	for line in file
		let c_name = matchstr(line, '\c\(class\|interface\)\s*\zs[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*')
		let kind = (line =~? '^\s*class' ? 'c' : 'i')
		if c_name != '' && c_name =~? '^'.base
			call add(res, {'word': c_name, 'kind': kind})
		endif
	endfor

	" resolve the typed in part with namespaces (if theres a \ in it)
	let [tag_match_pattern, namespace_for_class] = phpcd#ExpandClassName(a:base, a:current_namespace, a:imports)

	let tags = []
	if len(tag_match_pattern) >= g:phpcd_min_num_of_chars_for_namespace_completion
		let tags = phpcd#GetTaglist('^\c'.tag_match_pattern)
	endif

	if len(tags)
		let base_parts = split(a:base, '\')
		if len(base_parts) > 1
			let namespace_part = join(base_parts[0:-2], '\').'\'
		else
			let namespace_part = ''
		endif
		let no_namespace_matches = []
		let namespaced_matches = []
		let seen_namespaced_tag = 0
		for tag in tags
			if has_key(tag, 'namespace')
				let seen_namespaced_tag = 1
			endif
			let relative_name = namespace_part.tag.name
			" match base without the namespace part for namespaced base but not namespaced tags, for tagfiles with old ctags
			if !has_key(tag, 'namespace') && index(kinds, tag.kind) != -1 && stridx(tolower(tag.name), tolower(base[len(namespace_part):])) == 0
				call add(no_namespace_matches, {'word': leading_slash.relative_name, 'kind': tag.kind, 'menu': tag.filename, 'info': tag.filename })
			endif
			if has_key(tag, 'namespace') && index(kinds, tag.kind) != -1 && tag.namespace ==? namespace_for_class
				let full_name = tag.namespace.'\'.tag.name " absolute namespaced name (without leading '\')
				call add(namespaced_matches, {'word': leading_slash == '\' ? leading_slash.full_name : relative_name, 'kind': tag.kind, 'menu': tag.filename, 'info': tag.filename })
			endif
		endfor
		" if there was a tag with namespace field, assume tag files with namespace support, so the matches
		" without a namespace field are in the global namespace so if there were namespace in the base
		" we should not add them to the matches
		if seen_namespaced_tag && namespace_part != ''
			let no_namespace_matches = []
		endif
		let res += no_namespace_matches + namespaced_matches
	endif

	" look for built in classnames and interfaces
	let base_parts = split(base, '\')
	if a:current_namespace == '\' || (leading_slash == '\' && len(base_parts) < 2)
		if index(kinds, 'c') != -1
			let builtin_classnames = filter(keys(copy(g:php_builtin_classnames)), 'v:val =~? "^'.substitute(a:base, '\\', '', 'g').'"')
			for classname in builtin_classnames
				let menu = ''
				" if we have a constructor for this class, add parameters as to the info
				if has_key(g:php_builtin_classes[tolower(classname)].methods, '__construct')
					let menu = g:php_builtin_classes[tolower(classname)]['methods']['__construct']['signature']
				endif
				call add(res, {'word': leading_slash.g:php_builtin_classes[tolower(classname)].name, 'kind': 'c', 'menu': menu})
			endfor
		endif

		if index(kinds, 'i') != -1
			let builtin_interfaces = filter(keys(copy(g:php_builtin_interfaces)), 'v:val =~? "^'.substitute(a:base, '\\', '', 'g').'"')
			for interfacename in builtin_interfaces
				call add(res, {'word': leading_slash.g:php_builtin_interfaces[interfacename]['name'], 'kind': 'i', 'menu': ''})
			endfor
		endif
	endif

	" add matching imported things
	for [imported_name, import] in items(a:imports)
		if imported_name =~? '^'.base && index(kinds, import.kind) != -1
			let menu = import.name.(import.builtin ? ' - builtin' : '')
			call add(res, {'word': imported_name, 'kind': import.kind, 'menu': menu})
		endif
	endfor

	let res = sort(res, 'phpcd#CompareCompletionRow')
	return res
endfunction
" }}}

function! phpcd#CompareCompletionRow(i1, i2) " {{{
	return a:i1.word == a:i2.word ? 0 : a:i1.word > a:i2.word ? 1 : -1
endfunction
" }}}

function! phpcd#JumpToDefinition(mode) " {{{
	if g:phpcd_channel_id < 0
		return
	endif

	let [symbol, symbol_context, symbol_namespace, current_imports] = phpcd#GetCurrentSymbolWithContext()
	if symbol == ''
		return
	endif

	let [symbol_file, symbol_line, symbol_col] = phpcd#LocateSymbol(symbol, symbol_context, symbol_namespace, current_imports)
	if symbol_file == ''
		return
	endif

    silent! execute "e +". symbol_line . ' ' . symbol_file
    silent! execute "normal! zt"
    normal! zv
    normal! zz
endfunction " }}}

function! phpcd#GetCurrentSymbolWithContext() " {{{
	" Check if we are inside of PHP markup
	let pos = getpos('.')
	let phpbegin = searchpairpos('<?', '', '?>', 'bWn',
			\ 'synIDattr(synID(line("."), col("."), 0), "name") =~? "string\\|comment"')
	let phpend = searchpairpos('<?', '', '?>', 'Wn',
			\ 'synIDattr(synID(line("."), col("."), 0), "name") =~? "string\\|comment"')

	if (phpbegin == [0, 0] && phpend == [0, 0])
		return ['', '', '', '']
	endif

	" locate the start of the word
	let b:phpbegin = phpbegin

	let line = getline('.')
	let start = col('.') - 1
	let end = start
	if start < 0
		let start = 0;
	endif
	if end < 0
		let end = 0;
	endif

	while start >= 0 && line[start - 1] =~ '[\\a-zA-Z_0-9\x7f-\xff$]'
		let start -= 1
	endwhile
	while end + 1 <= len(line) && line[end + 1] =~ '[\\a-zA-Z_0-9\x7f-\xff$]'
		let end += 1
	endwhile
	let word = line[start : end]
	" trim extra non-word chars from the end line "(" that can come from a
	" function call
	let word = substitute(word, '\v\c[^\\a-zA-Z_0-9$]*$', '', '')

	let current_instruction = phpcd#GetCurrentInstruction(line('.'), max([0, col('.') - 2]), phpbegin)
	let context = substitute(current_instruction, '\s*[$a-zA-Z_0-9\x7f-\xff]*$', '', '')

	let [current_namespace, current_imports] = phpcd#GetCurrentNameSpace(getline(0, line('.')))
	let [symbol, symbol_namespace] = phpcd#ExpandClassName(word, current_namespace, current_imports)

	return [symbol, context, symbol_namespace, current_imports]
endfunction " }}}

function! phpcd#LocateSymbol(symbol, symbol_context, symbol_namespace, current_imports) " {{{
	let unknow_location = ['', '', '']

	if a:symbol =~ '\\'
		let symbol_parts = split(a:symbol, '\')
		let search_symbol = symbol_parts[-1]
	else
		let search_symbol = a:symbol
	endif

	" are we looking for a method?
	if a:symbol_context =~ '\(->\|::\)$' " {{{
		" Get name of the class
		let classname = phpcd#GetClassName(line('.'), a:symbol_context, a:symbol_namespace, a:current_imports)

		" Get location of class definition, we have to iterate through all
		if classname != ''
			let [path, line] = rpcrequest(g:phpcd_channel_id, 'location', classname, a:symbol)
			return [path, line, 0]
		endif " }}}
	elseif a:symbol_context == 'new' || a:symbol_context =~ '^class ' " {{{
		if (has_key(a:current_imports, a:symbol))
			let full_classname = a:current_imports[a:symbol]['name']
		else
			let full_classname = a:symbol_namespace . '\' . a:symbol
		endif

		let [path, line] = rpcrequest(g:phpcd_channel_id, 'location', full_classname, '')
		return [path, line, 0] " }}}
	elseif a:symbol_context != '' " {{{
		" try to find interface method's implementation
		let interface = phpcd#GetClassName(line('.'), a:symbol_context, a:symbol_namespace, a:current_imports)
		if interface != '' && g:phpid_channel_id >= 0
			let impls = rpcrequest(g:phpid_channel_id, 'ls', interface, 1)
			let impl = ''
			if len(impls) == 1
				let impl = impls[0]
			elseif len(impls) > 1
				let impls_list = []
				let impls_len = len(impls)
				for i in range(1, impls_len)
					call add(impls_list, printf("%2d %s", i, impls[i - 1]))
				endfor
				let index = inputlist(impls_list)
				if index >= 1 && index <= impls_len
					let impl = impls[index - 1]
				endif
			endif

			if impl != ''
				let [path, line] = rpcrequest(g:phpcd_channel_id, 'location', impl, a:symbol)
				return [path, line, 0]
			endif
		endif " }}}
	else " {{{
		if a:symbol[0] >= 'A' && a:symbol[0] <= 'Z'
			let [classname, namespace] = phpcd#ExpandClassName(a:symbol, a:symbol_namespace, a:current_imports)
			let full_classname = namespace . '\' . classname
			let [path, line] = rpcrequest(g:phpcd_channel_id, 'location', full_classname)
		else
			let [path, line] = rpcrequest(g:phpcd_channel_id, 'location', '', a:symbol)
		end

		return [path, line, 0]
	endif " }}}

	return unknow_location
endfunction " }}}

function! s:readfileToTmpbuffer(file) " {{{
	let cfile = join(readfile(a:file), "\n")
	silent! below 1new
	silent! 0put =cfile
	silent! exec "set ft=phpcompletetempbuffer"
	return [bufnr('$'), bufname('%')]
endfunction " }}}

function! s:getNextCharWithPos(filelines, current_pos) " {{{
	let line_no   = a:current_pos[0]
	let col_no    = a:current_pos[1]
	let last_line = a:filelines[len(a:filelines) - 1]
	let end_pos   = [len(a:filelines) - 1, strlen(last_line) - 1]
	if line_no > end_pos[0] || line_no == end_pos[0] && col_no > end_pos[1]
		return ['EOF', 'EOF']
	endif

	" we've not reached the end of the current line break
	if col_no + 1 < strlen(a:filelines[line_no])
		let col_no += 1
	else
		" we've reached the end of the current line, jump to the next
		" non-blank line (blank lines have no position where we can read from,
		" not even a whitespace. The newline char does not positionable by vim
		let line_no += 1
		while strlen(a:filelines[line_no]) == 0
			let line_no += 1
		endwhile

		let col_no = 0
	endif

	" return 'EOF' string to signal end of file, normal results only one char
	" in length
	if line_no == end_pos[0] && col_no > end_pos[1]
		return ['EOF', 'EOF']
	endif

	return [[line_no, col_no], a:filelines[line_no][col_no]]
endfunction " }}}

function! phpcd#EvaluateModifiers(modifiers, required_modifiers, prohibited_modifiers) " {{{
	" if theres no modifier, and no modifier is allowed and no modifier is required
	if len(a:modifiers) == 0 && len(a:required_modifiers) == 0
		return 1
	else
		" check if every requred modifier is present
		for required_modifier in a:required_modifiers
			if index(a:modifiers, required_modifier) == -1
				return 0
			endif
		endfor

		for modifier in a:modifiers
			" if the modifier is prohibited its a no match
			if index(a:prohibited_modifiers, modifier) != -1
				return 0
			endif
		endfor

		" anything that is not explicitly required or prohibited is allowed
		return 1
	endif
endfunction
" }}}

function! phpcd#CompleteUserClass(context, base, sccontent, visibility) " {{{
	let final_list = []
	let res  = []

	let required_modifiers = []
	let prohibited_modifiers = []

	if a:visibility == 'public'
		let prohibited_modifiers += ['private', 'protected']
	endif

	" limit based on context to static or normal methods
	let static_con = ''
	if a:context =~ '::$' && a:context !~? 'parent::$'
		if g:phpcd_relax_static_constraint != 1
			let required_modifiers += ['static']
		endif
	elseif a:context =~ '->$'
		let prohibited_modifiers += ['static']
	endif

	let all_function = filter(deepcopy(a:sccontent),
				\ 'v:val =~ "^\\s*\\(public\\s\\+\\|protected\\s\\+\\|private\\s\\+\\|final\\s\\+\\|abstract\\s\\+\\|static\\s\\+\\)*function"')

	let functions = []
	for i in all_function
		let modifiers = split(matchstr(tolower(i), '\zs.\+\zefunction'), '\s\+')
		if phpcd#EvaluateModifiers(modifiers, required_modifiers, prohibited_modifiers) == 1
			call add(functions, i)
		endif
	endfor

	let c_functions = {}
	let c_doc = {}
	for i in functions
		let f_name = matchstr(i,
					\ 'function\s*&\?\zs[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*\ze')
		let f_args = matchstr(i,
					\ 'function\s*&\?[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*\s*(\zs.\{-}\ze)\_s*\(;\|{\|\_$\)')
		if f_name != '' && stridx(f_name, '__') != 0
			let c_functions[f_name.'('] = f_args
			if g:phpcd_parse_docblock_comments
				let c_doc[f_name.'('] = phpcd#GetDocBlock(a:sccontent, 'function\s*&\?\<'.f_name.'\>')
			endif
		endif
	endfor

	" limit based on context to static or normal attributes
	if a:context =~ '::$' && a:context !~? 'parent::$'
		" variables must have static to be accessed as static unlike functions
		let required_modifiers += ['static']
	endif
	let all_variable = filter(deepcopy(a:sccontent),
					\ 'v:val =~ "^\\s*\\(var\\s\\+\\|public\\s\\+\\|protected\\s\\+\\|private\\s\\+\\|final\\s\\+\\|abstract\\s\\+\\|static\\s\\+\\)\\+\\$"')

	let variables = []
	for i in all_variable
		let modifiers = split(matchstr(tolower(i), '\zs.\+\ze\$'), '\s\+')
		if phpcd#EvaluateModifiers(modifiers, required_modifiers, prohibited_modifiers) == 1
			call add(variables, i)
		endif
	endfor

	let static_vars = split(join(variables, ' '), '\$')
	let c_variables = {}

	let var_index = 0
	for i in static_vars
		let c_var = matchstr(i,
					\ '^\zs[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*\ze')
		if c_var != ''
			if a:context =~ '::$'
				let c_var = '$'.c_var
			endif
			let c_variables[c_var] = ''
			if g:phpcd_parse_docblock_comments && len(get(variables, var_index)) > 0
				let c_doc[c_var] = phpcd#GetDocBlock(a:sccontent, variables[var_index])
			endif
			let var_index += 1
		endif
	endfor

	let constants = filter(deepcopy(a:sccontent),
				\ 'v:val =~ "^\\s*const\\s\\+"')

	let jcons = join(constants, ' ')
	let scons = split(jcons, 'const')

	let c_constants = {}
	let const_index = 0
	for i in scons
		let c_con = matchstr(i,
					\ '^\s*\zs[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*\ze')
		if c_con != ''
			let c_constants[c_con] = ''
			if g:phpcd_parse_docblock_comments && len(get(constants, const_index)) > 0
				let c_doc[c_con] = phpcd#GetDocBlock(a:sccontent, constants[const_index])
			endif
			let const_index += 1
		endif
	endfor

	let all_values = {}
	call extend(all_values, c_functions)
	call extend(all_values, c_variables)
	call extend(all_values, c_constants)

	for m in sort(keys(all_values))
		if stridx(m, a:base) == 0
			call add(res, m)
		endif
	endfor

	let start_list = res

	let final_list = []
	for i in start_list
		let docblock = phpcd#ParseDocBlock(get(c_doc, i, ''))
		if has_key(c_variables, i)
			let final_list +=
						\ [{'word': i,
						\	'info':phpcd#FormatDocBlock(docblock),
						\	'menu':get(docblock.var, 'type', ''),
						\	'kind':'v'}]
		elseif has_key(c_constants, i)
			let info = phpcd#FormatDocBlock(docblock)
			if info != ''
				let info = "\n".info
			endif
			let final_list +=
						\ [{'word':i,
						\	'info':i.info,
						\	'menu':all_values[i],
						\	'kind':'d'}]
		else
			let return_type = get(docblock.return, 'type', '')
			if return_type != ''
				let return_type = ' | '.return_type
			endif
			let info = phpcd#FormatDocBlock(docblock)
			if info != ''
				let info = "\n".info
			endif
			let final_list +=
						\ [{'word':substitute(i, '.*::', '', ''),
						\	'info':i.all_values[i].')'.info,
						\	'menu':all_values[i].')'.return_type,
						\	'kind':'f'}]
		endif
	endfor

	return final_list
endfunction
" }}}

function! phpcd#CompleteBuiltInClass(context, classname, base) " {{{
	let class_info = g:php_builtin_classes[tolower(a:classname)]
	let res = []
	if a:context =~ '->$' " complete for everything instance related
		" methods
		for [method_name, method_info] in items(class_info.methods)
			if stridx(method_name, '__') != 0 && (a:base == '' || method_name =~? '^'.a:base)
				call add(res, {'word':method_name.'(', 'kind': 'f', 'menu': method_info.signature, 'info': method_info.signature })
			endif
		endfor
		" properties
		for [property_name, property_info] in items(class_info.properties)
			if a:base == '' || property_name =~? '^'.a:base
				call add(res, {'word':property_name, 'kind': 'v', 'menu': property_info.type, 'info': property_info.type })
			endif
		endfor
	elseif a:context =~ '::$' " complete for everything static
		" methods
		for [method_name, method_info] in items(class_info.static_methods)
			if a:base == '' || method_name =~? '^'.a:base
				call add(res, {'word':method_name.'(', 'kind': 'f', 'menu': method_info.signature, 'info': method_info.signature })
			endif
		endfor
		" properties
		for [property_name, property_info] in items(class_info.static_properties)
			if a:base == '' || property_name =~? '^'.a:base
				call add(res, {'word':property_name, 'kind': 'v', 'menu': property_info.type, 'info': property_info.type })
			endif
		endfor
		" constants
		for [constant_name, constant_info] in items(class_info.constants)
			if a:base == '' || constant_name =~? '^'.a:base
				call add(res, {'word':constant_name, 'kind': 'd', 'menu': constant_info, 'info': constant_info})
			endif
		endfor
	endif
	return res
endfunction
" }}}

function! phpcd#GetTaglist(pattern) " {{{
	let cache_checksum = ''
	if g:phpcd_cache_taglists == 1
		" build a string with  format of "<tagfile>:<mtime>$<tagfile2>:<mtime2>..."
		" to validate that the tags are not changed since the time we saved the results in cache
		for tagfile in sort(tagfiles())
			let cache_checksum .= fnamemodify(tagfile, ':p').':'.getftime(tagfile).'$'
		endfor

		if s:cache_tags_checksum != cache_checksum
			" tag file(s) changed
			" since we don't know where individual tags coming from when calling taglist() we zap the whole cache
			" no way to clear only the entries originating from the changed tag file
			let s:cache_tags = {}
		endif

		if has_key(s:cache_tags, a:pattern)
			return s:cache_tags[a:pattern]
		endif
	endif

	let tags = taglist(a:pattern)
	let s:cache_tags[a:pattern] = tags
	let has_key = has_key(s:cache_tags, a:pattern)
	let s:cache_tags_checksum = cache_checksum
	return tags
endfunction
" }}}

function! phpcd#GetCurrentInstruction(line_number, col_number, phpbegin) " {{{
	" locate the current instruction (up until the previous non comment or string ";" or php region start (<?php or <?) without newlines
	let col_number = a:col_number
	let line_number = a:line_number
	let line = getline(a:line_number)
	let current_char = -1
	let instruction = ''
	let parent_depth = 0
	let bracket_depth = 0
	let stop_chars = [
				\ '!', '@', '%', '^', '&',
				\ '*', '/', '-', '+', '=',
				\ ':', '>', '<', '.', '?',
				\ ';', '(', '|', '['
				\ ]

	let phpbegin_length = len(matchstr(getline(a:phpbegin[0]), '\zs<?\(php\)\?\ze'))
	let phpbegin_end = [a:phpbegin[0], a:phpbegin[1] - 1 + phpbegin_length]

	" will hold the first place where a coma could have ended the match
	let first_coma_break_pos = -1
	let next_char = len(line) < col_number ? line[col_number + 1] : ''

	while !(line_number == 1 && col_number == 1)
		if current_char != -1
			let next_char = current_char
		endif

		let current_char = line[col_number]
		let synIDName = synIDattr(synID(line_number, col_number + 1, 0), 'name')

		if col_number - 1 == -1
			let prev_line_number = line_number - 1
			let prev_line = getline(line_number - 1)
			let prev_col_number = strlen(prev_line)
		else
			let prev_line_number = line_number
			let prev_col_number = col_number - 1
			let prev_line = line
		endif
		let prev_char = prev_line[prev_col_number]

		" skip comments
		if synIDName =~? 'comment\|phpDocTags'
			let current_char = ''
		endif

		" break on the last char of the "and" and "or" operators
		if synIDName == 'phpOperator' && (current_char == 'r' || current_char == 'd')
			break
		endif

		" break on statements as "return" or "throws"
		if synIDName == 'phpStatement' || synIDName == 'phpException'
			break
		endif

		" if the current char should be considered
		if current_char != '' && parent_depth >= 0 && bracket_depth >= 0 && synIDName !~? 'comment\|string'
			" break if we are on a "naked" stop_char (operators, colon, openparent...)
			if index(stop_chars, current_char) != -1
				let do_break = 1
				" dont break if it does look like a "->"
				if (prev_char == '-' && current_char == '>') || (current_char == '-' && next_char == '>')
					let do_break = 0
				endif
				" dont break if it does look like a "::"
				if (prev_char == ':' && current_char == ':') || (current_char == ':' && next_char == ':')
					let do_break = 0
				endif

				if do_break
					break
				endif
			endif

			" save the coma position for later use if theres a "naked" , possibly separating a parameter and it is not in a parented part
			if first_coma_break_pos == -1 && current_char == ','
				let first_coma_break_pos = len(instruction)
			endif
		endif

		" count nested darenthesis and brackets so we can tell if we need to break on a ';' or not (think of for (;;) loops)
		if synIDName =~? 'phpBraceFunc\|phpParent\|Delimiter'
			if current_char == '('
				let parent_depth += 1
			elseif current_char == ')'
				let parent_depth -= 1

			elseif current_char == '['
				let bracket_depth += 1
			elseif current_char == ']'
				let bracket_depth -= 1
			endif
		endif

		" stop collecting chars if we see a function start { (think of first line in a function)
		if (current_char == '{' || current_char == '}') && synIDName =~? 'phpBraceFunc\|phpParent\|Delimiter'
			break
		endif

		" break if we are reached the php block start (<?php or <?)
		if [line_number, col_number] == phpbegin_end
			break
		endif

		let instruction = current_char.instruction

		" step a char or a line back if we are on the first column of the line already
		let col_number -= 1
		if col_number == -1
			let line_number -= 1
			let line = getline(line_number)
			let col_number = strlen(line)
		endif
	endwhile

	" strip leading whitespace
	let instruction = substitute(instruction, '^\s\+', '', '')

	" there were a "naked" coma in the instruction
	if first_coma_break_pos != -1
		if instruction !~? '^use' && instruction !~? '^class' " use ... statements and class delcarations should not be broken up by comas
			let pos = (-1 * first_coma_break_pos) + 1
			let instruction = instruction[pos :]
		endif
	endif

	" HACK to remove one line conditionals from code like "if ($foo) echo 'bar'"
	" what the plugin really need is a proper php tokenizer
	if instruction =~? '\c^\(if\|while\|foreach\|for\)\s*('
		" clear everything up until the first (
		let instruction = substitute(instruction, '^\(if\|while\|foreach\|for\)\s*(\s*', '', '')

		" lets iterate trough the instruction until we can find the pair for the opening (
		let i = 0
		let depth = 1
		while i < len(instruction)
			if instruction[i] == '('
				let depth += 1
			endif
			if instruction[i] == ')'
				let depth -= 1
			endif
			if depth == 0
				break
			end
			let i += 1
		endwhile
		let instruction = instruction[i + 1 : len(instruction)]
	endif

	" trim whitespace from the ends
	let instruction = substitute(instruction, '\v^(^\s+)|(\s+)$', '', 'g')

	return instruction
endfunction " }}}

function! phpcd#GetCallChainReturnType(classname_candidate, class_candidate_namespace, imports, methodstack) " {{{
	" Tries to get the classname and namespace for a chained method call like:
	"	$this->foo()->bar()->baz()->

	let classname_candidate = a:classname_candidate " {{{
	let class_candidate_namespace = a:class_candidate_namespace
	let methodstack = a:methodstack
	let unknown_result = ['', '']
	let prev_method_is_array = (methodstack[0] =~ '\v^[^([]+\[' ? 1 : 0)
	let classname_candidate_is_array = (classname_candidate =~ '\[\]$' ? 1 : 0) " }}}

	if prev_method_is_array " {{{
		if classname_candidate_is_array
			let classname_candidate = substitute(classname_candidate, '\[\]$', '', '')
		else
			return unknown_result
		endif
	endif " }}}

	if (len(methodstack) == 1) " {{{
		let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, class_candidate_namespace, a:imports)
		return [classname_candidate, class_candidate_namespace] " }}}
	else " {{{
		call remove(methodstack, 0)
		let method_is_array = (methodstack[0] =~ '\v^[^[]+\[' ? 1 : 0)
		let method = matchstr(methodstack[0], '\v^\$*\zs[^[(]+\ze')

		let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, class_candidate_namespace, a:imports)

		let classlocation = phpcd#GetClassLocation(classname_candidate, class_candidate_namespace)

		if classlocation != '' && filereadable(classlocation) " {{{
			" Read the next method from the stack and extract only the name

			let classcontents = phpcd#GetCachedClassContents(classlocation, classname_candidate)

			" Get Structured information of all classes and subclasses including namespace and includes
			" try to find the method's return type in docblock comment
			for classstructure in classcontents
				let docblock_target_pattern = 'function\s\+&\?'.method.'\|\(public\|private\|protected\|var\).\+\$'.method
				let doc_str = phpcd#GetDocBlock(split(classstructure.content, '\n'), docblock_target_pattern)
				if doc_str != ''
					break
				endif
			endfor
			if doc_str != ''
				let docblock = phpcd#ParseDocBlock(doc_str)
				if has_key(docblock.return, 'type') || has_key(docblock.var, 'type')
					let type = has_key(docblock.return, 'type') ? docblock.return.type : docblock.var.type

					" there's a namespace in the type, threat the type as FQCN
					if type =~ '\\'
						let parts = split(substitute(type, '^\\', '', ''), '\')
						let class_candidate_namespace = join(parts[0:-2], '\')
						let classname_candidate = parts[-1]
						" check for renamed namepsace in imports
						if has_key(classstructure.imports, class_candidate_namespace)
							let class_candidate_namespace = classstructure.imports[class_candidate_namespace].name
						endif
					else
						" no namespace in the type, threat it as a relative classname
						let returnclass = type
						if has_key(classstructure.imports, returnclass)
							if has_key(classstructure.imports[returnclass], 'namespace')
								let fullnamespace = classstructure.imports[returnclass].namespace
							else
								let fullnamespace = class_candidate_namespace
							endif
						else
							let fullnamespace = class_candidate_namespace
						endif
						" make @return self, static, $this the same way
						" (not exactly what php means by these)
						if returnclass == 'self' || returnclass == 'static' || returnclass == '$this' || returnclass == 'self[]' || returnclass == 'static[]' || returnclass == '$this[]'
							if returnclass =~ '\[\]$'
								let classname_candidate = a:classname_candidate.'[]'
							else
								let classname_candidate = a:classname_candidate
							endif
							let class_candidate_namespace = a:class_candidate_namespace
						else
							let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(returnclass, fullnamespace, a:imports)
						endif
					endif

					return phpcd#GetCallChainReturnType(classname_candidate, class_candidate_namespace, a:imports, methodstack)
				endif
			endif

			return unknown_result " }}}
		else " {{{
			return unknown_result
		endif " }}}
	endif " }}}
endfunction " }}}

function! phpcd#GetMethodStack(line) " {{{
	let methodstack = []
	let i = 0
	let end = len(a:line)

	let current_part = ''

	let parent_depth = 0
	let in_string = 0
	let string_start = ''

	let next_char = ''

	while i < end
		let current_char = a:line[i]
		let next_char = i + 1 < end ? a:line[i + 1] : ''
		let prev_char = i >= 1 ? a:line[i - 1] : ''
		let prev_prev_char = i >= 2 ? a:line[i - 2] : ''

		if in_string == 0 && parent_depth == 0 && ((current_char == '-' && next_char == '>') || (current_char == ':' && next_char == ':'))
			call add(methodstack, current_part)
			let current_part = ''
			let i += 2
			continue
		endif

		" if it's looks like a string
		if current_char == "'" || current_char == '"'
			" and it is not escaped
			if prev_char != '\' || (prev_char == '\' && prev_prev_char == '\')
				" and we are in a string already
				if in_string
					" and that string started with this char too
					if current_char == string_start
						" clear the string mark
						let in_string = 0
					endif
				else " ... and we are not in a string
					" set the string mark
					let in_string = 1
					let string_start = current_char
				endif
			endif
		endif

		if !in_string && a:line[i] == '('
			let parent_depth += 1
		endif
		if !in_string && a:line[i] == ')'
			let parent_depth -= 1
		endif

		let current_part .= current_char
		let i += 1
	endwhile

	" add the last remaining part, this can be an empty string and this is expected
	" the empty string represents the completion base (which happen to be an empty string)
	if current_part != ''
		call add(methodstack, current_part)
	endif

	return methodstack
endfunction
" }}}

function! phpcd#GetClassName(start_line, context, current_namespace, imports) " {{{
	" Get class name
	" Class name can be detected in few ways:
	" - @var $myVar class
	" - @var class $myVar
	" - in the same line (php 5.4 (new Class)-> syntax)
	" - line above

	let class_name_pattern = '[a-zA-Z_\x7f-\xff\\][a-zA-Z_0-9\x7f-\xff\\]*' " {{{
	let function_name_pattern = '[a-zA-Z_\x7f-\xff][a-zA-Z_0-9\x7f-\xff]*'
	let function_invocation_pattern = '[a-zA-Z_\x7f-\xff\\][a-zA-Z_0-9\x7f-\xff\\]*('
	let variable_name_pattern = '\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*'

	let classname_candidate = ''
	let class_candidate_namespace = a:current_namespace
	let class_candidate_imports = a:imports
	let methodstack = phpcd#GetMethodStack(a:context) " }}}

	if a:context =~? '\$this->' || a:context =~? '\(self\|static\)::' || a:context =~? 'parent::' " {{{
		let i = 1
		while i < a:start_line
			let line = getline(a:start_line - i)

			" Don't complete self:: or $this if outside of a class
			" (assumes correct indenting)
			if line =~ '^}'
				return ''
			endif

			if line =~? '\v^\s*(abstract\s+|final\s+)*\s*class\s'
				let class_name = matchstr(line, '\cclass\s\+\zs'.class_name_pattern.'\ze')
				let extended_class = matchstr(line, '\cclass\s\+'.class_name_pattern.'\s\+extends\s\+\zs'.class_name_pattern.'\ze')

				let classname_candidate = a:context =~? 'parent::' ? extended_class : class_name
				if classname_candidate != ''
					let [classname_candidate, class_candidate_namespace] = phpcd#GetCallChainReturnType(classname_candidate, class_candidate_namespace, class_candidate_imports, methodstack)
					" return absolute classname, without leading \
					return (class_candidate_namespace == '\' || class_candidate_namespace == '') ? classname_candidate : class_candidate_namespace.'\'.classname_candidate
				endif
			endif

			let i += 1
		endwhile " }}}
	elseif a:context =~ '\vpublic|protected|private|final|static' " {{{
		let i = 1
		while i < a:start_line
			let line = getline(a:start_line - i)

			" Don't complete self:: or $this if outside of a class
			" (assumes correct indenting)
			if line =~ '^}'
				return ''
			endif

			if line =~? '\v^\s*interface\s'
				let class_name = matchstr(line, '\cinterface\s\+\zs'.class_name_pattern.'\ze')

				if class_name != ''
					return a:current_namespace . '\' . class_name
				endif
			endif

			let i += 1
		endwhile " }}}
	elseif a:context =~? '(\s*new\s\+'.class_name_pattern.'\s*)->' " {{{
		let classname_candidate = matchstr(a:context, '\cnew\s\+\zs'.class_name_pattern.'\ze')
		let [classname_candidate, class_candidate_namespace] = phpcd#GetCallChainReturnType(classname_candidate, class_candidate_namespace, class_candidate_imports, methodstack)
		" return absolute classname, without leading \
		return (class_candidate_namespace == '\' || class_candidate_namespace == '') ? classname_candidate : class_candidate_namespace.'\'.classname_candidate " }}}
	elseif get(methodstack, 0) =~# function_invocation_pattern " {{{
		let function_name = matchstr(methodstack[0], '^\s*\zs'.function_name_pattern)
		let function_file = phpcd#GetFunctionLocation(function_name, a:current_namespace)

		if function_file != '' && filereadable(function_file)
			let file_lines = readfile(function_file)
			let docblock_str = phpcd#GetDocBlock(file_lines, 'function\s*&\?\<'.function_name.'\>')
			let docblock = phpcd#ParseDocBlock(docblock_str)

			if has_key(docblock.return, 'type')
				let classname_candidate = docblock.return.type
				let [class_candidate_namespace, function_imports] = phpcd#GetCurrentNameSpace(file_lines)
				" try to expand the classname of the returned type with the context got from the function's source file
				let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, class_candidate_namespace, function_imports)
			endif
		endif

		if classname_candidate != ''
			let [classname_candidate, class_candidate_namespace] = phpcd#GetCallChainReturnType(classname_candidate, class_candidate_namespace, class_candidate_imports, methodstack)
			" return absolute classname, without leading \
			return (class_candidate_namespace == '\' || class_candidate_namespace == '') ? classname_candidate : class_candidate_namespace.'\'.classname_candidate
		endif " }}}
	else " {{{
		" extract the variable name from the context
		let object = methodstack[0]
		let object_is_array = (object =~ '\v^[^[]+\[' ? 1 : 0)
		let object = matchstr(object, variable_name_pattern)

		let function_boundary = phpcd#GetCurrentFunctionBoundaries()
		let search_end_line = max([1, function_boundary[0][0]])
		" -1 makes us ignore the current line (where the completion was invoked
		let lines = reverse(getline(search_end_line, a:start_line - 1))

		" check Constant lookup
		let constant_object = matchstr(a:context, '\zs'.class_name_pattern.'\ze::')
		if constant_object != ''
			let classname_candidate = constant_object
		endif

		if classname_candidate == '' " {{{
			" scan the file backwards from current line for explicit type declaration (@var $variable Classname)
			for line in lines
				" in file lookup for /* @var $foo Class */
				if line =~# '@var\s\+'.object.'\s\+'.class_name_pattern
					let classname_candidate = matchstr(line, '@var\s\+'.object.'\s\+\zs'.class_name_pattern.'\(\[\]\)\?')
					let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, a:current_namespace, a:imports)
					break
				endif
				" in file lookup for /* @var Class $foo */
				if line =~# '@var\s\+'.class_name_pattern.'\s\+'.object
					let classname_candidate = matchstr(line, '@var\s\+\zs'.class_name_pattern.'\(\[\]\)\?\ze'.'\s\+'.object)
					let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, a:current_namespace, a:imports)
					break
				endif
			endfor
		endif " }}}

		if classname_candidate != '' " {{{
			let [classname_candidate, class_candidate_namespace] = phpcd#GetCallChainReturnType(classname_candidate, class_candidate_namespace, class_candidate_imports, methodstack)
			" return absolute classname, without leading \
			return (class_candidate_namespace == '\' || class_candidate_namespace == '') ? classname_candidate : class_candidate_namespace.'\'.classname_candidate
		endif " }}}

		" scan the file backwards from the current line
		let i = 1
		for line in lines " {{{
			" do in-file lookup of $var = new Class
			if line =~# '^\s*'.object.'\s*=\s*new\s\+'.class_name_pattern && !object_is_array
				let classname_candidate = matchstr(line, object.'\c\s*=\s*new\s*\zs'.class_name_pattern.'\ze')
				let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, a:current_namespace, a:imports)
				break
			endif

			" in-file lookup for Class::getInstance()
			if line =~# '^\s*'.object.'\s*=&\?\s*'.class_name_pattern.'\s*::\s*getInstance' && !object_is_array
				let classname_candidate = matchstr(line, object.'\s*=&\?\s*\zs'.class_name_pattern.'\ze\s*::\s*getInstance')
				let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, a:current_namespace, a:imports)
				break
			endif

			" do in-file lookup for static method invocation of a built-in class, like: $d = DateTime::createFromFormat()
			if line =~# '^\s*'.object.'\s*=&\?\s*'.class_name_pattern.'\s*::\s*$\?[a-zA-Z_0-9\x7f-\xff]\+'
				let classname  = matchstr(line, '^\s*'.object.'\s*=&\?\s*\zs'.class_name_pattern.'\ze\s*::')
				if has_key(a:imports, classname) && a:imports[classname].kind == 'c'
					let classname = a:imports[classname].name
				endif
				if has_key(g:php_builtin_classes, tolower(classname))
					let sub_methodstack = phpcd#GetMethodStack(matchstr(line, '^\s*'.object.'\s*=&\?\s*\s\+\zs.*'))
					let [classname_candidate, class_candidate_namespace] = phpcd#GetCallChainReturnType(classname, '\', {}, sub_methodstack)
					return classname_candidate
				else
					" try to get the class name from the static method's docblock
					let [classname, namespace_for_class] = phpcd#ExpandClassName(classname, a:current_namespace, a:imports)
					let sub_methodstack = phpcd#GetMethodStack(matchstr(line, '^\s*'.object.'\s*=&\?\s*\s\+\zs.*'))
					let [classname_candidate, class_candidate_namespace] = phpcd#GetCallChainReturnType(
						\ classname,
						\ a:current_namespace,
						\ a:imports,
						\ sub_methodstack)
					return (class_candidate_namespace == '\' || class_candidate_namespace == '') ? classname_candidate : class_candidate_namespace.'\'.classname_candidate
				endif
			endif

			" function declaration line
			if line =~? 'function\(\s\+'.function_name_pattern.'\)\?\s*('
				let function_lines = join(reverse(copy(lines)), " ")
				" search for type hinted arguments
				if function_lines =~? 'function\(\s\+'.function_name_pattern.'\)\?\s*(.\{-}'.class_name_pattern.'\s\+'.object && !object_is_array
					let f_args = matchstr(function_lines, '\cfunction\(\s\+'.function_name_pattern.'\)\?\s*(\zs.\{-}\ze)')
					let args = split(f_args, '\s*\zs,\ze\s*')
					for arg in args
						if arg =~# object.'\(,\|$\)'
							let classname_candidate = matchstr(arg, '\s*\zs'.class_name_pattern.'\ze\s\+'.object)
							let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, a:current_namespace, a:imports)
							break
						endif
					endfor
					if classname_candidate != ''
						break
					endif
				endif

				" search for docblock for the function
				let match_line = substitute(line, '\\', '\\\\', 'g')
				let sccontent = getline(0, a:start_line - i)
				let doc_str = phpcd#GetDocBlock(sccontent, match_line)
				if doc_str != ''
					let docblock = phpcd#ParseDocBlock(doc_str)
					for param in docblock.params
						if param.name =~? object
							let classname_candidate = matchstr(param.type, class_name_pattern.'\(\[\]\)\?')
							let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, a:current_namespace, a:imports)
							break
						endif
					endfor
					if classname_candidate != ''
						break
					endif
				endif
			endif

			" assignment for the variable in question with a variable on the right hand side
			if line =~# '^\s*'.object.'\s*=&\?\s\+\(clone\)\?\s*'.variable_name_pattern

				" try to find the next non-comment or string ";" char
				let start_col = match(line, '^\s*'.object.'\C\s*=\zs&\?\s\+\(clone\)\?\s*'.variable_name_pattern)
				let filelines = reverse(copy(lines))
				let [pos, char] = s:getNextCharWithPos(filelines, [len(filelines) - i, start_col])
				let chars_read = 1
				let last_pos = pos
				" function_boundary == 0 if we are not in a function
				let real_lines_offset = len(function_boundary) == 1 ? 1 : function_boundary[0][0]
				" read while end of the file
				while char != 'EOF' && chars_read < 1000
					let last_pos = pos
					let [pos, char] = s:getNextCharWithPos(filelines, pos)
					let chars_read += 1
					" we got a candidate
					if char == ';'
						" pos values is relative to the function's lines,
						" line 0 need to be offsetted with the line number
						" where te function was started to get the line number
						" in real buffer terms
						let synIDName = synIDattr(synID(real_lines_offset + pos[0], pos[1] + 1, 0), 'name')
						" it's not a comment or string, end search
						if synIDName !~? 'comment\|string'
							break
						endif
					endif
				endwhile

				let prev_context = phpcd#GetCurrentInstruction(real_lines_offset + last_pos[0], last_pos[1], b:phpbegin)
				if prev_context == ''
					" cannot get previous context give up
					return
				endif
				let prev_class = phpcd#GetClassName(a:start_line - i, prev_context, a:current_namespace, a:imports)

				if stridx(prev_class, '\') != -1
					let classname_parts = split(prev_class, '\\\+')
					let classname_candidate = classname_parts[-1]
					let class_candidate_namespace = join(classname_parts[0:-2], '\')
				else
					let classname_candidate = prev_class
					let class_candidate_namespace = '\'
				endif
				break
			endif

			" assignment for the variable in question with a function on the right hand side
			if line =~# '^\s*'.object.'\s*=&\?\s*'.function_invocation_pattern
				" try to find the next non-comment or string ";" char
				let start_col = match(line, '\C^\s*'.object.'\s*=\zs&\?\s*'.function_invocation_pattern)
				let filelines = reverse(copy(lines))
				let [pos, char] = s:getNextCharWithPos(filelines, [len(filelines) - i, start_col])
				let chars_read = 1
				let last_pos = pos
				" function_boundary == 0 if we are not in a function
				let real_lines_offset = len(function_boundary) == 1 ? 1 : function_boundary[0][0]
				" read while end of the file
				while char != 'EOF' && chars_read < 1000
					let last_pos = pos
					let [pos, char] = s:getNextCharWithPos(filelines, pos)
					let chars_read += 1
					" we got a candidate
					if char == ';'
						" pos values is relative to the function's lines,
						" line 0 need to be offsetted with the line number
						" where te function was started to get the line number
						" in real buffer terms
						let synIDName = synIDattr(synID(real_lines_offset + pos[0], pos[1] + 1, 0), 'name')
						" it's not a comment or string, end search
						if synIDName !~? 'comment\|string'
							break
						endif
					endif
				endwhile

				let prev_context = phpcd#GetCurrentInstruction(real_lines_offset + last_pos[0], last_pos[1], b:phpbegin)
				if prev_context == ''
					" cannot get previous context give up
					return
				endif

				let function_name = matchstr(prev_context, '^'.function_invocation_pattern.'\ze')
				let function_name = matchstr(function_name, '^\zs.\+\ze\s*($') " strip the trailing (
				let [function_name, function_namespace] = phpcd#ExpandClassName(function_name, a:current_namespace, a:imports)

				let function_file = phpcd#GetFunctionLocation(function_name, function_namespace)

				if function_file == 'VIMPHP_BUILTINFUNCTION'
					" built in function, grab the return type from the info string
					let return_type = matchstr(g:php_builtin_functions[function_name.'('], '\v\|\s+\zs.+$')
					let classname_candidate = return_type
					let class_candidate_namespace = '\'
					break
				elseif function_file != '' && filereadable(function_file)
					let file_lines = readfile(function_file)
					let docblock_str = phpcd#GetDocBlock(file_lines, 'function\s*&\?\<'.function_name.'\>')
					let docblock = phpcd#ParseDocBlock(docblock_str)
					if has_key(docblock.return, 'type')
						let classname_candidate = docblock.return.type
						let [class_candidate_namespace, function_imports] = phpcd#GetCurrentNameSpace(file_lines)
						" try to expand the classname of the returned type with the context got from the function's source file
						let [classname_candidate, unused] = phpcd#ExpandClassName(classname_candidate, class_candidate_namespace, function_imports)
						break
					endif
				endif
			endif

			" foreach with the variable in question
			if line =~? 'foreach\s*(.\{-}\s\+'.object.'\s*)'
				let sub_context = matchstr(line, 'foreach\s*(\s*\zs.\{-}\ze\s\+as')
				let prev_class = phpcd#GetClassName(a:start_line - i, sub_context, a:current_namespace, a:imports)

				" the iterated expression should return an array type
				if prev_class =~ '\[\]$'
					let prev_class = matchstr(prev_class, '\v^[^[]+')
				else
					return
				endif

				if stridx(prev_class, '\') != -1
					let classname_parts = split(prev_class, '\\\+')
					let classname_candidate = classname_parts[-1]
					let class_candidate_namespace = join(classname_parts[0:-2], '\')
				else
					let classname_candidate = prev_class
					let class_candidate_namespace = '\'
				endif
				break
			endif

			" catch clause with the variable in question
			if line =~? 'catch\s*(\zs'.class_name_pattern.'\ze\s\+'.object
				let classname = matchstr(line, 'catch\s*(\zs'.class_name_pattern.'\ze\s\+'.object)
				if stridx(classname, '\') != -1
					let classname_parts = split(classname, '\\\+')
					let classname_candidate = classname_parts[-1]
					let class_candidate_namespace = join(classname_parts[0:-2], '\')
				else
					let classname_candidate = classname
					let class_candidate_namespace = '\'
				endif
				break
			endif

			let i += 1
		endfor " }}}

		if classname_candidate != '' " {{{
			let [classname_candidate, class_candidate_namespace] = phpcd#GetCallChainReturnType(classname_candidate, class_candidate_namespace, class_candidate_imports, methodstack)
			" return absolute classname, without leading \
			return (class_candidate_namespace == '\' || class_candidate_namespace == '') ? classname_candidate : class_candidate_namespace.'\'.classname_candidate
		endif " }}}

		" OK, first way failed, now check tags file(s)
		" This method is useless when local variables are not indexed by ctags and
		" pretty inaccurate even if it is
	endif " }}}
endfunction
" }}}

function! phpcd#GetClassLocation(classname, namespace) " {{{
	let full_classname = a:namespace . '\' . a:classname
	let [path, line] = rpcrequest(g:phpcd_channel_id, 'location', full_classname, '')
	return path

	if a:namespace == '' || a:namespace == '\'
		let search_namespace = '\'
	else
		let search_namespace = tolower(a:namespace)
	endif

	" do in-file lookup for class definition
	let i = 1
	while i < line('.')
		let line = getline(line('.')-i)
		if line =~? '^\s*\(abstract\s\+\|final\s\+\)*\s*\(class\|interface\|trait\)\s*'.a:classname.'\(\s\+\|$\|{\)' && tolower(current_namespace) == search_namespace
			return expand('%:p')
		else
			let i += 1
			continue
		endif
	endwhile

	" Get class location from tags
	let no_namespace_candidate = ''
	return ''
endfunction
" }}}

function! phpcd#GetFunctionLocation(function_name, namespace) " {{{
	" builtin functions doesn't need explicit \ in front of them even in namespaces,
	" aliased built-in function names are not handled
	" do in-file lookup for function definition
	let [path, line] = rpcrequest(g:phpcd_channel_id, 'location', '', a:function_name)
	return path
endfunction
" }}}

function! phpcd#GetCachedClassContents(classlocation, class_name) " {{{
	let full_file_path = fnamemodify(a:classlocation, ':p')
	let cache_key = full_file_path.'#'.a:class_name.'#'.getftime(full_file_path)

	" try to read from the cache first
	if has_key(s:cache_classstructures, cache_key)
		let classcontents = s:cache_classstructures[cache_key]
		" cached class contents can contain content from multiple files (superclasses) so we have to
		" validate cached result's validness by the filemtimes used to create the cached value
		let valid = 1
		for classstructure in classcontents
			if getftime(classstructure.file) != classstructure.mtime
				let valid = 0
				" we could break here, but the time required for checking probably worth
				" the the memory we can free by checking every file in the cached hirearchy
				call phpcd#ClearCachedClassContents(classstructure.file)
			endif
		endfor

		if valid
			" cache hit, we found an entry for this file + class pair and every
			" file in the response is also valid
			return classcontents
		else
			" clear the outdated cached value from the cache store
			call remove(s:cache_classstructures, cache_key)
			call phpcd#ClearCachedClassContents(full_file_path)

			" fall trough for the read from files path
		endif
	else
		call phpcd#ClearCachedClassContents(full_file_path)
	endif

	" cache miss, fetch the content from the files itself
	let classfile = readfile(a:classlocation)
	let classcontents = phpcd#GetClassContentsStructure(full_file_path, classfile, a:class_name)
	let s:cache_classstructures[cache_key] = classcontents

	return classcontents
endfunction " }}}

function! phpcd#ClearCachedClassContents(full_file_path) " {{{
	for [cache_key, cached_value] in items(s:cache_classstructures)
		if stridx(cache_key, a:full_file_path.'#') == 0
			call remove(s:cache_classstructures, cache_key)
		endif
	endfor
endfunction " }}}

function! phpcd#GetClassContentsStructure(file_path, file_lines, class_name) " {{{
	" returns dictionary containing content, namespace and imports for the class and all parent classes.
	" Example:
	" [
	"	{
	"		class: 'foo',
	"		content: '... class foo extends bar ... ',
	"		namespace: 'NS\Foo',
	"		imports : { ... },
	"		file: '/foo.php',
	"		mtime: 42,
	"	},
	"	{
	"		class: 'bar',
	"		content: '... class bar extends baz ... ',
	"		namespace: 'NS\Bar',
	"		imports : { ... }
	"		file: '/bar.php',
	"		mtime: 42,
	"	},
	"	...
	" ]
	"
	let full_file_path = fnamemodify(a:file_path, ':p')
	let class_name_pattern = '[a-zA-Z_\x7f-\xff\\][a-zA-Z_0-9\x7f-\xff\\]*'
	let cfile = join(a:file_lines, "\n")
	let result = []
	" We use new buffer and (later) normal! because
	" this is the most efficient way. The other way
	" is to go through the looong string looking for
	" matching {}

	" remember the window we started at
	let phpcomplete_original_window = winnr()

	silent! below 1new
	silent! 0put =cfile
	silent! exec "setlocal ft=phpcompletetempbuffer"

	call search('\c\(class\|interface\|trait\)\_s\+'.a:class_name.'\(\>\|$\)')
	let cfline = line('.')
	call search('{')
	let endline = line('.')

	let content = join(getline(cfline, endline), "\n")
	" Catch extends
	if content =~? 'extends'
		let extends_string = matchstr(content, '\(class\|interface\)\_s\+'.a:class_name.'\_.\+extends\_s\+\zs\('.class_name_pattern.'\(,\|\_s\)*\)\+\ze\(extends\|{\)')
		let extended_classes = map(split(extends_string, '\(,\|\_s\)\+'), 'substitute(v:val, "\\_s\\+", "", "g")')
	else
		let extended_classes = ''
	endif

	" Catch implements
	if content =~? 'implements'
		let implements_string = matchstr(content, 'class\_s\+'.a:class_name.'\_.\+implements\_s\+\zs\('.class_name_pattern.'\(,\|\_s\)*\)\+\ze')
		let implemented_interfaces = map(split(implements_string, '\(,\|\_s\)\+'), 'substitute(v:val, "\\_s\\+", "", "g")')
	else
		let implemented_interfaces = []
	endif

	call searchpair('{', '', '}', 'W', 'synIDattr(synID(line("."), col("."), 0), "name") =~? "string\\|comment"')
	let class_closing_bracket_line = line('.')
	let classcontent = join(getline(cfline, class_closing_bracket_line), "\n")

	let used_traits = []
	" move back to the line next to the class's definition
	call cursor(endline + 1, 1)
	let keep_searching = 1
	while keep_searching != 0
		" try to grab "use..." keywords
		let [lnum, col] = searchpos('\c^\s\+use\s\+'.class_name_pattern, 'cW', class_closing_bracket_line)
		let syn_name = synIDattr(synID(lnum, col, 0), "name")
		if syn_name =~? 'string\|comment'
			call cursor(lnum + 1, 1)
			continue
		endif

		let trait_line = getline(lnum)
		if trait_line !~? ';'
			" try to find the next line containing ';'
			let l = lnum
			let search_line = trait_line

			" add lines from the file until theres no ';' in them
			while search_line !~? ';' && l > 0
				" file lines are reversed so we need to go backwards
				let l += 1
				let search_line = getline(l)
				let trait_line .= ' '.substitute(search_line, '\(^\s\+\|\s\+$\)', '', 'g')
			endwhile
		endif
		let use_expression = matchstr(trait_line, '^\s*use\s\+\zs.\{-}\ze;')
		let use_parts = map(split(use_expression, '\s*,\s*'), 'substitute(v:val, "\\s+", " ", "g")')
		let used_traits += map(use_parts, 'substitute(v:val, "\\s", "", "g")')
		call cursor(lnum + 1, 1)

		if [lnum, col] == [0, 0]
			let keep_searching = 0
		endif
	endwhile

	silent! bw! %

	let [current_namespace, imports] = phpcd#GetCurrentNameSpace(a:file_lines[0:cfline])
	" go back to original window
	exe phpcomplete_original_window.'wincmd w'
	call add(result, {
				\ 'class': a:class_name,
				\ 'content': classcontent,
				\ 'namespace': current_namespace,
				\ 'imports': imports,
				\ 'file': full_file_path,
				\ 'mtime': getftime(full_file_path),
				\ })

	let all_extends = used_traits
	if len(extended_classes) > 0
		call extend(all_extends, extended_classes)
	endif
	if len(implemented_interfaces) > 0
		call extend(all_extends, implemented_interfaces)
	endif
	if len(all_extends) > 0
		for class in all_extends
			let [class, namespace] = phpcd#ExpandClassName(class, current_namespace, imports)
			if namespace == ''
				let namespace = '\'
			endif
			let classlocation = phpcd#GetClassLocation(class, namespace)
			if classlocation == "VIMPHP_BUILTINOBJECT"
				if has_key(g:php_builtin_classes, tolower(class))
					let result += [phpcd#GenerateBuiltinClassStub('class', g:php_builtin_classes[tolower(class)])]
				endif
				if has_key(g:php_builtin_interfaces, tolower(class))
					let result += [phpcd#GenerateBuiltinClassStub('interface', g:php_builtin_interfaces[tolower(class)])]
				endif
			elseif classlocation != '' && filereadable(classlocation)
				let full_file_path = fnamemodify(classlocation, ':p')
				let result += phpcd#GetClassContentsStructure(full_file_path, readfile(full_file_path), class)
			elseif tolower(current_namespace) == tolower(namespace) && match(join(a:file_lines, "\n"), '\c\(class\|interface\|trait\)\_s\+'.class.'\(\>\|$\)') != -1
				" try to find the declaration in the same file.
				let result += phpcd#GetClassContentsStructure(full_file_path, a:file_lines, class)
			endif
		endfor
	endif

	return result
endfunction
" }}}

function! phpcd#GetClassContents(classlocation, class_name) " {{{
	let classcontents = phpcd#GetCachedClassContents(a:classlocation, a:class_name)
	let result = []
	for classstructure in classcontents
		call add(result, classstructure.content)
	endfor
	return join(result, "\n")
endfunction
" }}}

function! phpcd#GenerateBuiltinClassStub(type, class_info) " {{{
	let re = a:type.' '.a:class_info['name']." {"
	if has_key(a:class_info, 'constants')
		for [name, initializer] in items(a:class_info.constants)
			let re .= "\n\tconst ".name." = ".initializer.";"
		endfor
	endif
	if has_key(a:class_info, 'properties')
		for [name, info] in items(a:class_info.properties)
			let re .= "\n\t// @var $".name." ".info.type
			let re .= "\n\tpublic $".name.";"
		endfor
	endif
	if has_key(a:class_info, 'static_properties')
		for [name, info] in items(a:class_info.static_properties)
			let re .= "\n\t// @var ".name." ".info.type
			let re .= "\n\tpublic static ".name." = ".info.initializer.";"
		endfor
	endif
	if has_key(a:class_info, 'methods')
		for [name, info] in items(a:class_info.methods)
			if name =~ '^__'
				continue
			endif
			let re .= "\n\t/**"
			let re .= "\n\t * ".name
			let re .= "\n\t *"
			let re .= "\n\t * @return ".info.return_type
			let re .= "\n\t */"
			let re .= "\n\tpublic function ".name."(".info.signature."){"
			let re .= "\n\t}"
		endfor
	endif
	if has_key(a:class_info, 'static_methods')
		for [name, info] in items(a:class_info.static_methods)
			let re .= "\n\t/**"
			let re .= "\n\t * ".name
			let re .= "\n\t *"
			let re .= "\n\t * @return ".info.return_type
			let re .= "\n\t */"
			let re .= "\n\tpublic static function ".name."(".info.signature."){"
			let re .= "\n\t}"
		endfor
	endif
	let re .= "\n}"

	return { a:type : a:class_info['name'],
				\ 'content': re,
				\ 'namespace': '',
				\ 'imports': {},
				\ 'file': 'VIMPHP_BUILTINOBJECT',
				\ 'mtime': 0,
				\ }
endfunction " }}}

function! phpcd#GetDocBlock(sccontent, search) " {{{
	let i = 0
	let l = 0
	let comment_start = -1
	let comment_end = -1
	let sccontent_len = len(a:sccontent)

	while (i < sccontent_len)
		let line = a:sccontent[i]
		" search for a function declaration
		if line =~? a:search
			let l = i - 1
			" start backward serch for the comment block
			while l != 0
				let line = a:sccontent[l]
				" if it's a one line docblock like comment and we can just return it right away
				if line =~? '^\s*\/\*\*.\+\*\/\s*$'
					return substitute(line, '\v^\s*(\/\*\*\s*)|(\s*\*\/)\s*$', '', 'g')
				"... or if comment end found save line position and end search
				elseif line =~? '^\s*\*/'
					let comment_end = l
					break
				" ... or the line doesn't blank (only whitespace or nothing) end search
				elseif line !~? '^\s*$'
					break
				endif
				let l -= 1
			endwhile
			" no comment found
			if comment_end == -1
				return ''
			end

			while l != 0
				let line = a:sccontent[l]
				if line =~? '^\s*/\*\*'
					let comment_start = l
					break
				endif
				let l -= 1
			endwhile

			" no docblock comment start found
			if comment_start == -1
				return ''
			end

			let comment_start += 1 " we dont need the /**
			let comment_end   -= 1 " we dont need the */

			" remove leading whitespace and '*'s
			let docblock = join(map(copy(a:sccontent[comment_start :comment_end]), 'substitute(v:val, "^\\s*\\*\\s*", "", "")'), "\n")
			return docblock
		endif
		let i += 1
	endwhile
	return ''
endfunction
" }}}

function! phpcd#ParseDocBlock(docblock) " {{{
	let res = {
		\ 'description': '',
		\ 'params': [],
		\ 'return': {},
		\ 'throws': [],
		\ 'var': {},
		\ }

	let res.description = substitute(matchstr(a:docblock, '\zs\_.\{-}\ze\(@var\|@param\|@return\|$\)'), '\(^\_s*\|\_s*$\)', '', 'g')
	let docblock_lines = split(a:docblock, "\n")

	let param_lines = filter(copy(docblock_lines), 'v:val =~? "^@param"')
	for param_line in param_lines
		let parts = matchlist(param_line, '@param\s\+\(\S\+\)\s\+\(\S\+\)\s*\(.*\)')
		if len(parts) > 0
			call add(res.params, {
						\ 'line': parts[0],
						\ 'type': phpcd#GetTypeFromDocBlockParam(get(parts, 1, '')),
						\ 'name': get(parts, 2, ''),
						\ 'description': get(parts, 3, '')})
		endif
	endfor

	let return_line = filter(copy(docblock_lines), 'v:val =~? "^@return"')
	if len(return_line) > 0
		let return_parts = matchlist(return_line[0], '@return\s\+\(\S\+\)\s*\(.*\)')
		let res['return'] = {
					\ 'line': return_parts[0],
					\ 'type': phpcd#GetTypeFromDocBlockParam(get(return_parts, 1, '')),
					\ 'description': get(return_parts, 2, '')}
	endif

	let exception_lines = filter(copy(docblock_lines), 'v:val =~? "^\\(@throws\\|@exception\\)"')
	for exception_line in exception_lines
		let parts = matchlist(exception_line, '^\(@throws\|@exception\)\s\+\(\S\+\)\s*\(.*\)')
		if len(parts) > 0
			call add(res.throws, {
						\ 'line': parts[0],
						\ 'type': phpcd#GetTypeFromDocBlockParam(get(parts, 2, '')),
						\ 'description': get(parts, 3, '')})
		endif
	endfor

	let var_line = filter(copy(docblock_lines), 'v:val =~? "^@var"')
	if len(var_line) > 0
		let var_parts = matchlist(var_line[0], '@var\s\+\(\S\+\)\s*\(.*\)')
		let res['var'] = {
					\ 'line': var_parts[0],
					\ 'type': phpcd#GetTypeFromDocBlockParam(get(var_parts, 1, '')),
					\ 'description': get(var_parts, 2, '')}
	endif

	return res
endfunction
" }}}

function! phpcd#GetTypeFromDocBlockParam(docblock_type) " {{{
	if a:docblock_type !~ '|'
		return a:docblock_type
	endif

	let primitive_types = [
				\ 'string', 'float', 'double', 'int',
				\ 'scalar', 'array', 'bool', 'void', 'mixed',
				\ 'null', 'callable', 'resource', 'object']

	" add array of primitives to the list too, like string[]
	let primitive_types += map(copy(primitive_types), 'v:val."[]"')
	let types = split(a:docblock_type, '|')
	for type in types
		if index(primitive_types, type) == -1
			return type
		endif
	endfor

	" only primitive types found, return the first one
	return types[0]

endfunction
" }}}

function! phpcd#FormatDocBlock(info) " {{{
	let res = ''
	if len(a:info.description)
		let res .= "Description:\n".join(map(split(a:info['description'], "\n"), '"\t".v:val'), "\n")."\n"
	endif

	if len(a:info.params)
		let res .= "\nArguments:\n"
		for arginfo in a:info.params
			let res .= "\t".arginfo['name'].' '.arginfo['type']
			if len(arginfo.description) > 0
				let res .= ': '.arginfo['description']
			endif
			let res .= "\n"
		endfor
	endif

	if has_key(a:info.return, 'type')
		let res .= "\nReturn:\n\t".a:info['return']['type']
		if len(a:info.return.description) > 0
			let res .= ": ".a:info['return']['description']
		endif
		let res .= "\n"
	endif

	if len(a:info.throws)
		let res .= "\nThrows:\n"
		for excinfo in a:info.throws
			let res .= "\t".excinfo['type']
			if len(excinfo['description']) > 0
				let res .= ": ".excinfo['description']
			endif
			let res .= "\n"
		endfor
	endif

	if has_key(a:info.var, 'type')
		let res .= "Type:\n\t".a:info['var']['type']."\n"
		if len(a:info['var']['description']) > 0
			let res .= ': '.a:info['var']['description']
		endif
	endif

	return res
endfunction!
" }}}

function! phpcd#GetCurrentNameSpace(file_lines) " {{{
	let original_window = winnr()

	silent! below 1new
	silent! 0put =a:file_lines
	silent! exec "setlocal ft=phpcompletetempbuffer"
	normal! G

	" clear out classes, functions and other blocks
	while 1
		let block_start_pos = searchpos('\c\(class\|trait\|function\|interface\)\s\+\_.\{-}\zs{', 'Web')
		if block_start_pos == [0, 0]
			break
		endif
		let block_end_pos = searchpairpos('{', '', '}\|\%$', 'W', 'synIDattr(synID(line("."), col("."), 0), "name") =~? "string\\|comment"')

		if block_end_pos != [0, 0]
			" end of the block found, just delete it
			silent! exec block_start_pos[0].','.block_end_pos[0].'d _'
		else
			" block pair not found, use block start as beginning and the end
			" of the buffer instead
			silent! exec block_start_pos[0].',$d _'
		endif
	endwhile
	normal! G

	" grab the remains
	let file_lines = reverse(getline(1, line('.') - 1))

	silent! bw! %
	exe original_window.'wincmd w'

	let namespace_name_pattern = '[a-zA-Z_\x7f-\xff\\][a-zA-Z_0-9\x7f-\xff\\]*'
	let i = 0
	let file_length = len(file_lines)
	let imports = {}
	let current_namespace = '\'

	while i < file_length
		let line = file_lines[i]

		if line =~? '^\(<?php\)\?\s*namespace\s*'.namespace_name_pattern
			let current_namespace = matchstr(line, '\c^\(<?php\)\?\s*namespace\s*\zs'.namespace_name_pattern.'\ze')
			break
		endif

		if line =~? '^\s*use\>'
			if line =~? ';' " {{{
				let use_line = line
			else
				" try to find the next line containing ';'
				let l = i
				let search_line = line
				let use_line = line

				" add lines from the file until theres no ';' in them
				while search_line !~? ';' && l > 0
					" file lines are reversed so we need to go backwards
					let l -= 1
					let search_line = file_lines[l]
					let use_line .= ' '.substitute(search_line, '\(^\s\+\|\s\+$\)', '', 'g')
				endwhile
			endif " }}}
			let use_expression = matchstr(use_line, '^\c\s*use\s\+\zs.\{-}\ze;')
			let use_parts = map(split(use_expression, '\s*,\s*'), 'substitute(v:val, "\\s+", " ", "g")')
			for part in use_parts " {{{
				if part =~? '\s\+as\s\+'
					let [object, name] = split(part, '\s\+as\s\+\c')
					let object = substitute(object, '^\\', '', '')
					let name   = substitute(name, '^\\', '', '')
				else
					let object = part
					let name = part
					let object = substitute(object, '^\\', '', '')
					let name   = substitute(name, '^\\', '', '')
					if name =~? '\\'
						let	name = matchstr(name, '\\\zs[^\\]\+\ze$')
					endif
				endif
				" leading slash is not required use imports are always absolute
				let imports[name] = {'name': object, 'kind': ''}
			endfor " }}}
		endif
		let i += 1
	endwhile
	let sorted_imports = {}
	for name in sort(keys(imports))
		let sorted_imports[name] = imports[name]
	endfor
	return [current_namespace, sorted_imports]
endfunction
" }}}

function! phpcd#GetCurrentFunctionBoundaries() " {{{
	let old_cursor_pos = [line('.'), col('.')]
	let current_line_no = old_cursor_pos[0]
	let function_pattern = '\c\(.*\%#\)\@!\_^\s*\zs\(abstract\s\+\|final\s\+\|private\s\+\|protected\s\+\|public\s\+\|static\s\+\)*function\_.\{-}(\_.\{-})\_.\{-}{'

	let func_start_pos = searchpos(function_pattern, 'Wbc')
	if func_start_pos == [0, 0]
		call cursor(old_cursor_pos[0], old_cursor_pos[1])
		return 0
	endif

	" get the line where the function declaration actually started
	call search('\cfunction\_.\{-}(\_.\{-})\_.\{-}{', 'Wce')

	" get the position of the function block's closing "}"
	let func_end_pos = searchpairpos('{', '', '}', 'W', 'synIDattr(synID(line("."), col("."), 0), "name") =~? "string\\|comment"')
	if func_end_pos == [0, 0]
		" there is a function start but no end found, assume that we are in a
		" function but the user did not typed the closing "}" yet and the
		" function runs to the end of the file
		let func_end_pos = [line('$'), len(getline(line('$')))]
	endif

	" Decho func_start_pos[0].' <= '.current_line_no.' && '.current_line_no.' <= '.func_end_pos[0]
	if func_start_pos[0] <= current_line_no && current_line_no <= func_end_pos[0]
		call cursor(old_cursor_pos[0], old_cursor_pos[1])
		return [func_start_pos, func_end_pos]
	endif

	call cursor(old_cursor_pos[0], old_cursor_pos[1])
	return 0
endfunction
" }}}

function! phpcd#ExpandClassName(classname, current_namespace, imports) " {{{
	" if there's an imported class, just use that class's information
	if has_key(a:imports, a:classname)
		let full_classname = a:imports[a:classname].name
		let classname_parts = split(full_classname, '\\\+')
		let namespace = join(classname_parts[0:-2], '\')
		let classname = classname_parts[-1]
		return [classname, namespace]
	endif

	" try to find relative namespace in imports, imported names takes precedence over
	" current namespace when resolving relative namespaced class names
	if a:classname !~ '^\' && a:classname =~ '\\'
		let classname_parts = split(a:classname, '\\\+')
		if has_key(a:imports, classname_parts[0]) && a:imports[classname_parts[0]].kind == 'n'
			let classname_parts[0] = a:imports[classname_parts[0]].name
			let namespace = join(classname_parts[0:-2], '\')
			let classname = classname_parts[-1]
			return [classname, namespace]
		endif
	endif

	" no imported class or namespace matched, expand with the current namespace
	let namespace = ''
	let classname = a:classname
	" if the classname have namespaces in in or we are in a namespace
	if a:classname =~ '\\' || (a:current_namespace != '\' && a:current_namespace != '')
		" add current namespace to the a:classname
		if a:classname !~ '^\'
			let classname = a:current_namespace.'\'.substitute(a:classname, '^\\', '', '')
		else
			" remove leading \, tag files doesn't have those
			let classname = substitute(a:classname, '^\\', '', '')
		endif
		" split classname to classname and namespace
		let classname_parts = split(classname, '\\\+')
		if len(classname_parts) > 1
			let namespace = join(classname_parts[0:-2], '\')
			let classname = classname_parts[-1]
		endif
	endif
	return [classname, namespace]
endfunction
" }}}

function! phpcd#getComposerRoot() " {{{
	let root = getcwd()
	while root != "/"
		if (filereadable(root . "/vendor/autoload.php"))
			break
		endif
		let root = fnamemodify(root, ":h")
	endwhile
	return root
endfunction " }}}

" vim: foldmethod=marker:noexpandtab:ts=4:sts=4:sw=4
