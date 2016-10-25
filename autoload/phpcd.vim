function! phpcd#CompletePHP(findstart, base) " {{{
	" we need to wait phpcd {{{
	if !exists('g:phpcd_channel_id')
		return
	endif " }}}

	if a:findstart " {{{
		unlet! b:php_menu
		" locate the start of the word
		let line = getline('.')
		let start = col('.') - 1
		while start >= 0 && line[start - 1] =~ '[\\a-zA-Z_0-9\x7f-\xff$]'
			let start -= 1
		endwhile

		" TODO 清理 phpbegin
		let phpbegin = searchpairpos('<?', '', '?>', 'bWn',
				\ 'synIDattr(synID(line("."), col("."), 0), "name") =~? "string\\|comment"')
		let b:phpbegin = phpbegin
		let b:compl_context = phpcd#GetCurrentInstruction(line('.'), max([0, col('.') - 2]), phpbegin)

		return start
	endif " }}}

	" If exists b:php_menu it means completion was already constructed {{{
	" we don't need to do anything more
	if exists('b:php_menu')
		return b:php_menu
	endif " }}}

	" a:base is very short - we need context {{{
	if exists('b:compl_context')
		let context = b:compl_context
		unlet! b:compl_context
		" chop of the "base" from the end of the current instruction
		if a:base != ""
			let context = substitute(context, '\s*[$a-zA-Z_0-9\x7f-\xff]*$', '', '')
		end
	else
		let context = ''
	end " }}}

	try " {{{
		let winheight = winheight(0)
		let winnr = winnr()
		if context =~? '^namespace'
			return phpcd#GetPsrNamespace()
		endif

		if context =~? '\v^(final|abstract\s+)?(class|interface|trait)$'
			return [expand('%:t:r')]
		end

		let [current_namespace, imports] = phpcd#GetCurrentNameSpace()

		" TODO distinguish import of trait inside a class
		if context =~? '^use\s' || context ==? 'use' " {{{
			return phpcd#getAbsoluteClassesPaths(a:base)
		endif " }}}

		if context =~ '\(->\|::\)$' " {{{
			let classname = phpcd#GetClassName(line('.'), context, current_namespace, imports)

			" TODO Fix it for variables with reference to $this etc.
			let public_only = (context !~# '^\(\$this\|self\|static\|parent\)' )

			let is_static = v:false

			if strridx(context, '::') == strlen(context) - 2 " context =~ '::$'
				if stridx(context, 'parent') != 0
					let is_static = v:true
				else
					let is_static = v:null
				endif
			endif

			return rpc#request(g:phpcd_channel_id, 'getMatchingClassDetails', classname, a:base, is_static, public_only)
		elseif context =~? 'implements'
			return phpcd#getInterfaces(a:base)
		elseif context =~? 'extends\s\+.\+$' && a:base == ''
		elseif context =~? 'extends\s\+.\+$'
			return phpcd#getPotentialSuperclasses(a:base)
		elseif context =~? 'extends$'
			return phpcd#getPotentialSuperclasses(a:base)
		elseif context =~? 'class [a-zA-Z_\x7f-\xff\\][a-zA-Z_0-9\x7f-\xff\\]*'
			" special case when you've typed the class keyword and the name too,
			" only extends and implements allowed there
			return filter(['extends', 'implements'], 'stridx(v:val, a:base) == 0')
		elseif context =~? 'new$'
			return phpcd#getInstantiableClasses(a:base)
		endif " }}}

		if a:base =~ '^[^$]' " {{{
			return phpcd#CompleteDefault(a:base, current_namespace, imports)
		endif " }}}
	finally
		silent! exec winnr.'resize '.winheight
	endtry " }}}
endfunction " }}}

function! phpcd#GetPsrNamespace() " {{{
	return rpc#request(g:phpcd_channel_id, 'psr4ns', expand('%:p'))
endfunction " }}}

function! s:remapClassInfoItem(class_item, make_use_entry) " {{{
	let result = {}
	let result.menu = a:class_item.short_name

	if a:make_use_entry == 1
		let result.word = a:class_item.short_name
		let result.abbr = a:class_item.full_name
	else
		let result.word = a:class_item.full_name
	endif

	" It is a fix of buggy behaviour:
	" when the currently highlighted item has no info
	" then the preview window displayed info of the previously highlighted one.
	" This effect still remains after we hit back
	" to the item with no completion
	let result.info = empty(a:class_item.doc_comment) ? 'No doc comment' : a:class_item.doc_comment

	let result.kind = 'c'

	return result
endfunction " }}}

function! s:prepareClassInfoOutput(response, make_use_entry) " {{{
	let result = []

	for class_item in a:response
		call add(result, s:remapClassInfoItem(class_item, a:make_use_entry))
	endfor

	return result
endfunction  " }}}

function! phpcd#getPotentialSuperclasses(path) " {{{
	let class_info = rpc#request(g:phpid_channel_id, 'getPotentialSuperclasses', a:path)
	return <SID>prepareClassInfoOutput(class_info, g:phpcd_insert_class_shortname)
endfunction " }}}

function! phpcd#getInterfaces(path) " {{{
	let class_info = rpc#request(g:phpid_channel_id, 'getInterfaces', a:path)
	return <SID>prepareClassInfoOutput(class_info, g:phpcd_insert_class_shortname)
endfunction " }}}

function! phpcd#getInstantiableClasses(path) " {{{
	let class_info = rpc#request(g:phpid_channel_id, 'getInstantiableClasses', a:path)
	return <SID>prepareClassInfoOutput(class_info, g:phpcd_insert_class_shortname)
endfunction " }}}

function! phpcd#getAbsoluteClassesPaths(path) " {{{
	let class_info = rpc#request(g:phpid_channel_id, 'getAbsoluteClassesPaths', a:path)
	return <SID>prepareClassInfoOutput(class_info, 0)
endfunction " }}}

function! phpcd#CompleteDefault(base, current_namespace, imports) " {{{
	let base = substitute(a:base, '^\\', '', '')
	let [pattern, namespace] = phpcd#ExpandClassName(a:base, a:current_namespace, a:imports)
	return rpc#request(g:phpcd_channel_id, 'getFunctionsAndConstants', pattern)
endfunction " }}}

function! phpcd#completeDone() " {{{
	if empty(v:completed_item)
		return
	endif

	let current_line = getline('.')
	if v:completed_item.kind == 'c'
		" if inserted string is not an absolute path
		if stridx(v:completed_item.word, '\') != 0
			let new_alias = v:completed_item.word
			let new_full_path = v:completed_item.abbr

			" if not in line with "use" and not within class definition (case when use trait)
			" TODO check if the cursor is inside a class
			" inside_class || not "use"
			if current_line !~ '^\s*use\s\+'
				let fixes = phpcd#getFixForNewClassUsage(new_alias, new_full_path)

				if !empty(fixes)
					let fix = phpcd#getSelectedItem(fixes)
				endif

				if (fix.alias != v:null)
					if (fix.full_path != v:null)
						" v:completed_item.word is intentionally used there
						" as new_alias may be changed
						let prompt = 'Confirm the new alias lub enter any custom: '
						let fix.alias = phpcd#promptForChangeString(prompt, fix.alias)

						" todo fix.alias is not any from

						" TODO write loop 'phpcd#promptForChangeString; call phpcd#getFixForNewClassUsage'
						" to check if the new alias is valid and has no conflicts
					endif
					"
					" Assume we know that the changed alias is valid.
					" We may replace v:completed_item.word with it
					call phpcd#replaceCurrentWordAfterCompletion(v:completed_item.word, fix.alias)
				endif

				if (fix.full_path != v:null)
					call phpcd#putImport(fix.full_path, fix.alias, 0)
				endif
			endif
		endif
	endif
endfunction " }}}

function phpcd#getFixForNewClassUsage(new_alias, new_full_path) "{{{
	 return rpc#request(g:phpcd_channel_id, 'getFixForNewClassUsage', expand('%:p'), { 'alias': a:new_alias, 'full_path': a:new_full_path })
endfunction "}}}

" TODO make it private after test
function! phpcd#getSelectedItem(list) "{{{
	let list = a:list

	if empty(list)
		return {}
	endif

	if len(list) == 1
		return list[0]
	endif

	let prompt = printf("%s: ", 'Select path')
	return <SID>promptByInputList(list, prompt)
endfunction "}}}

" Select implementation of input list
function s:promptByInputList(list, msg) "{{{
	if exists('g:loaded_tlib')
		return <SID>promptByTlibInputList(a:list, a:msg)
	else
		return <SID>promptByBuiltinInputList(a:list, a:msg)
	endif
endfunction "}}}

function s:promptByTlibInputList(list, msg) "{{{
	let list = map(copy(a:list), '(empty(v:val["alias"]) ? "" : v:val["alias"]) . (empty(v:val["full_path"]) ? "" : " ".v:val["full_path"])')

	let item = tlib#input#List('si', a:msg, list)

	return a:list[item - 1]
endfunction "}}}

function s:promptByBuiltinInputList(list, msg) "{{{
	let list = map(copy(a:list), '(v:key + 1) . ": " . (empty(v:val["alias"]) ? "" : v:val["alias"]) . (empty(v:val["full_path"]) ? "" : " ".v:val["full_path"])')
	let list = insert(list, a:msg)
	let item = 0

	while (item <= 0 || item > len(a:list))
		let item = inputlist(list)
	endwhile

	return a:list[item - 1]
endfunction "}}}

function s:makeImportLineText(classpath, alias) "{{{
	if empty(a:alias)
		let line = printf('use %s;', a:classpath)
	else
		let line = printf('use %s as %s;', a:classpath, a:alias)
	endif

	return line
endfunction "}}}

" TODO make it private after test
function phpcd#putImport(classpath, alias, stay_here) "{{{

	if phpcd#goToLineForNewUseStatement(a:stay_here) == 0
		exec 'normal! cc'.<SID>makeImportLineText(a:classpath, a:alias)
	endif
endfunction "}}}

function! phpcd#goToLineForNewUseStatement(stay_here) "{{{
	if a:stay_here == 1
		return 0
	endif

	if searchdecl('use', 1) == 0
		" cursor is at an existing use statement
		exec 'normal! O'

		return 0
	else
		" cursor somewhere else, try to search namespace declaration
		if searchdecl('namespace', 1) == 0
			exec 'normal 2o'

			return 0
		endif
	endif

	return 1
endfunction "}}}

function phpcd#promptForChangeString(msg, string) "{{{
	let string = inputdialog(a:msg, a:string)
	return string
endfunction "}}}

" TODO make it private after test
" TODO better implementation are welcome - the current assumes that the cursor
" stays at the end of inserted word
function! phpcd#replaceCurrentWordAfterCompletion(current_word, new_word) "{{{
	exec 'normal! hv'. (len(a:current_word) - 1) .'hc'. a:new_word
endfunction "}}}
function! phpcd#JumpToDefinition(mode) " {{{
	if !exists('g:phpcd_channel_id')
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

	if a:mode == 'normal'
		let edit_cmd = "e +"
	else
		let edit_cmd = a:mode . " +"
	endif

	let cur_pos = getcurpos()
	let cur_pos[0] = bufnr('%')
	call add(g:phpcd_jump_stack, cur_pos)
	if str2nr(symbol_line) > 0
		silent! execute edit_cmd . symbol_line . ' ' . symbol_file
	else
		silent! execute edit_cmd . '1 ' . symbol_file
		silent! call search(symbol_line)
	endif

	silent! execute "normal! zt"
	normal! zv
	normal! zz
endfunction " }}}

function! phpcd#JumpBack() "{{{"
	if len(g:phpcd_jump_stack) == 0
		return
	endif

	let prev_pos = g:phpcd_jump_stack[-1]
	let prev_buf = prev_pos[0]
	let prev_pos[0] = 0
	unlet g:phpcd_jump_stack[-1]
	exec 'buffer '.prev_buf
	call setpos('.', prev_pos)
endfunction "}}}"

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
		let start = 0
	endif
	if end < 0
		let end = 0
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
	let context = substitute(current_instruction, 'clone ', '', '')
	let context = substitute(context, '\s*[$a-zA-Z_0-9\\\x7f-\xff]*$', '', '')
	let context = substitute(context, '\s\+\([\-:]\)', '\1', '')

	let [current_namespace, current_imports] = phpcd#GetCurrentNameSpace()
	if word != ''
		let [symbol, symbol_namespace] = phpcd#ExpandClassName(word, current_namespace, current_imports)
	else
		let [symbol, symbol_namespace] = [word, current_namespace]
	endif

	return [symbol, context, symbol_namespace, current_imports]
endfunction " }}}

function! phpcd#LocateSymbol(symbol, symbol_context, symbol_namespace, current_imports) " {{{
	let unknow_location = ['', '', '']

	" are we looking for a method?
	if a:symbol_context =~ '\(->\|::\)$' " {{{
		" Get name of the class
		let classname = phpcd#GetClassName(line('.'), a:symbol_context, a:symbol_namespace, a:current_imports)

		" Get location of class definition, we have to iterate through all
		if classname != ''
			let [path, line] = rpc#request(g:phpcd_channel_id, 'locateMethodOrConstantDeclaration', classname, a:symbol)
			return [path, line, 0]
		endif " }}}
	elseif a:symbol_context == 'new' || a:symbol_context =~ '\vimplements|extends'" {{{
		if (a:symbol_namespace == '\')
			let full_classname = a:symbol
		else
			let full_classname = a:symbol_namespace . '\' . a:symbol
		endif
		let [path, line] = rpc#request(g:phpid_channel_id, 'locateClassDeclaration', full_classname)
		return [path, line, 0] " }}}
	elseif a:symbol_context =~ 'function' " {{{
		" try to find interface method's implementation
		" or the subclass of abstract class
		" the var 'interface' is the interface name
		" or the abstract class name
		let interface = phpcd#GetClassName(line('.'), a:symbol_context, a:symbol_namespace, a:current_imports)
		let is_interface = 1
		if a:symbol_context =~ '^abstract'
			let is_interface = 0
		endif
		if interface != '' && exists('g:phpid_channel_id')
			let impls = rpc#request(g:phpid_channel_id, 'ls', interface, is_interface)
			let impl = phpcd#SelectOne(impls)

			if impl != ''
				let [path, line] = rpc#request(g:phpcd_channel_id, 'locateMethodOrConstantDeclaration', impl, a:symbol)
				return [path, line, 0]
			endif
		endif " }}}
	elseif a:symbol_context =~ '\/' " {{{
		let path = matchstr(getline('.'), '\(require\|include\)\(_once\)\?\s*__DIR__\s*\.\s*\zs.*\ze;')
		let cwd = expand('%:p:h')
		let path = cwd.substitute(path, "'", '', 'g')
		let path = fnamemodify(path, ':p:.')
		return [path, '$', 0] "}}}
	elseif a:symbol_context =~ '.\+@' " laravel route {{{
		let full_classname = strpart(a:symbol_context, 1, strlen(a:symbol_context)-2)
		let [path, line] = rpc#request(g:phpcd_channel_id, 'locateMethodOrConstantDeclaration', full_classname, a:symbol)
		return [path, line, 0] " }}}
	else " {{{
		if a:symbol =~ '\v\C^[A-Z]'
			let [classname, namespace] = phpcd#ExpandClassName(a:symbol, a:symbol_namespace, a:current_imports)
			if namespace == '\'
				let full_classname = classname
			else
				let full_classname = namespace . '\' . classname
			endif
			let [path, line] = rpc#request(g:phpid_channel_id, 'locateClassDeclaration', full_classname)
		else
			let [path, line] = rpc#request(g:phpcd_channel_id, 'locateFunctionDeclaration', a:symbol_namespace.'\'.a:symbol)
			if path == ''
				let [path, line] = rpc#request(g:phpcd_channel_id, 'locateFunctionDeclaration', a:symbol)
			endif
		end

		return [path, line, 0]
	endif " }}}

	return unknow_location
endfunction " }}}

function! phpcd#SelectOne(items) " {{{
	let items = a:items
	let len = len(items)
	if (len == 1)
		return items[0]
	elseif (len == 0)
		return
	endif

	let list = []
	for i in range(1, len)
		call add(list, printf("%2d %s", i, items[i - 1]))
	endfor
	let index = inputlist(list)
	if index >= 1 && index <= len
		return items[index - 1]
	endif
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

function! phpcd#GetCurrentInstruction(line_number, col_number, phpbegin) " {{{
	" locate the current instruction
	" up until the previous non comment or string ";"
	" or php region start (<?php or <?) without newlines
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

	if a:class_candidate_namespace[0] == '\'
		let imports = {}
	else
		let imports = a:imports
	endif

	let classname_candidate = a:classname_candidate " {{{
	let class_candidate_namespace = a:class_candidate_namespace
	let methodstack = a:methodstack
	let unknown_result = ''
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
		let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, class_candidate_namespace, imports)
		if (class_candidate_namespace == '\')
			let return_type = '\' . classname_candidate
		else
			let return_type = class_candidate_namespace . '\' . classname_candidate
		endif

		return return_type
	endif " }}}

	call remove(methodstack, 0)
	let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, class_candidate_namespace, imports)
	let full_classname = class_candidate_namespace . '\' . classname_candidate

	if methodstack[0] =~ '('
		let method = matchstr(methodstack[0], '\v^\$*\zs[^[(]+\ze')
		let return_types = rpc#request(g:phpcd_channel_id, 'functype', full_classname, method)
	else
		let prop = matchstr(methodstack[0], '\v^\$*\zs[^[(]+\ze')
		let return_types = rpc#request(g:phpcd_channel_id, 'proptype', full_classname, prop)
	endif

	if len(return_types) > 0
		let return_type = phpcd#SelectOne(return_types)
		return phpcd#GetCallChainReturnType(return_type, '', imports, methodstack)
	endif

	return unknown_result
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
endfunction " }}}

function! phpcd#GetClassName(start_line, context, current_namespace, imports) " {{{
	" Get class name
	" Class name can be detected in few ways:
	" - @var $myVar class
	" - @var class $myVar
	" - in the same line (php 5.4 (new Class)-> syntax)
	" - line above

	let class_name_pattern = '[a-zA-Z_\x7f-\xff\\][a-zA-Z_0-9\x7f-\xff\\]*' " {{{
	let function_name_pattern = '[a-zA-Z_\x7f-\xff\\][a-zA-Z_0-9\x7f-\xff\\]*'
	let function_invocation_pattern = '[a-zA-Z_\x7f-\xff\\][a-zA-Z_0-9\x7f-\xff\\]*('
	let variable_name_pattern = '\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*'

	let classname_candidate = ''
	let class_candidate_namespace = a:current_namespace
	let class_candidate_imports = a:imports
	let methodstack = phpcd#GetMethodStack(a:context) " }}}

	if a:context =~? '^\$this->' || a:context =~? '^\(self\|static\)::' || a:context =~? 'parent::' " {{{
		let i = 1
		while i < a:start_line
			let line = getline(a:start_line - i)

			" Don't complete self:: or $this if outside of a class
			" (assumes correct indenting)
			if line =~ '^}'
				return ''
			endif

			if line =~? '\v^\s*(abstract\s+|final\s+)*\s*(class|trait)\s'
				let class_name = matchstr(line, '\c\(class\|trait\)\s\+\zs'.class_name_pattern.'\ze')
				let extended_class = matchstr(line, '\cclass\s\+'.class_name_pattern.'\s\+extends\s\+\zs'.class_name_pattern.'\ze')

				let classname_candidate = a:context =~? 'parent::' ? extended_class : class_name
				if classname_candidate != ''
					return phpcd#GetCallChainReturnType(classname_candidate, class_candidate_namespace, class_candidate_imports, methodstack)
				endif
			endif

			let i += 1
		endwhile " }}}
	elseif a:context =~ 'function' " {{{
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

			if line =~? '\v^\s*abstract\s'
				let class_name = matchstr(line, '\cabstract\s\+class\s\+\zs'.class_name_pattern.'\ze')

				if class_name != ''
					return a:current_namespace . '\' . class_name
				endif
			endif

			let i += 1
		endwhile " }}}
	elseif a:context =~? '(\s*new\s\+'.class_name_pattern.'\s*\(([^)]*)\)\?)->' " {{{
		let classname_candidate = matchstr(a:context, '\cnew\s\+\zs'.class_name_pattern.'\ze')
		if classname_candidate == 'static' || classname_candidate == 'Static' " {{{
			let i = 1
			while i < a:start_line
				let line = getline(a:start_line - i)

				if line =~? '\v^\s*(abstract\s+|final\s+)*\s*class\s'
					let classname_candidate = matchstr(line, '\cclass\s\+\zs'.class_name_pattern.'\ze')
					break
				endif

				let i += 1
			endwhile
		end " }}}
		return phpcd#GetCallChainReturnType(classname_candidate, class_candidate_namespace, class_candidate_imports, methodstack) " }}}
	elseif get(methodstack, 0) =~# function_invocation_pattern " {{{
		" just for laravel {{{
		let laravel_interface = matchstr(methodstack[0], '^app(\zs.\+\ze::class)$')
		if laravel_interface != ''
			let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(laravel_interface, a:current_namespace, a:imports)
			return class_candidate_namespace . '\' . classname_candidate
		endif " }}}
		let function_name = matchstr(methodstack[0], '^\s*\zs'.function_name_pattern)
		let return_types = rpc#request(g:phpcd_channel_id, 'functype', '', function_name)
		if len(return_types) > 0
			let return_type = phpcd#SelectOne(return_types)
			return phpcd#GetCallChainReturnType(return_type, '', class_candidate_imports, methodstack)
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
				" in file lookup for function (Foo $foo)
				if line =~# 'function\s*('.class_name_pattern . '\s\+' . object
					let classname_candidate = matchstr(line, class_name_pattern . '\ze\s\+' . object)
					let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, a:current_namespace, a:imports)
					break
				endif
			endfor
		endif " }}}

		if classname_candidate != '' " {{{
			return phpcd#GetCallChainReturnType(classname_candidate, class_candidate_namespace, class_candidate_imports, methodstack)
		endif " }}}

		" scan the file backwards from the current line {{{
		let i = 1
		for line in lines
			" do in-file lookup of $var = new Class or $var = new [s|S]tatic
			if line =~# '^\s*'.object.'\s*=\s*new\s\+'.class_name_pattern && !object_is_array " {{{
				let classname_candidate = matchstr(line, object.'\c\s*=\s*new\s*\zs'.class_name_pattern.'\ze')
				if classname_candidate == 'static' || classname_candidate == 'Static' " {{{
					let i = 1
					while i < a:start_line
						let line = getline(a:start_line - i)

						if line =~? '\v^\s*(abstract\s+|final\s+)*\s*class\s'
							let classname_candidate = matchstr(line, '\cclass\s\+\zs'.class_name_pattern.'\ze')
							break
						endif

						let i += 1
					endwhile
				end " }}}
				if classname_candidate[0] == '\'
					return classname_candidate
				endif
				let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, a:current_namespace, a:imports)
				break
			endif " }}}

			" do in-file lookup of $var = (new static)
			if line =~# '^\s*'.object.'\s*=\s*(\s*new\s\+static\s*)' && !object_is_array " {{{
				let classname_candidate = '' " {{{
				let i = 1
				while i < a:start_line
					let line = getline(a:start_line - i)

					if line =~? '\v^\s*(abstract\s+|final\s+)*\s*class\s'
						let classname_candidate = matchstr(line, '\cclass\s\+\zs'.class_name_pattern.'\ze')
						break
					endif

					let i += 1
				endwhile " }}}
				let [classname_candidate, class_candidate_namespace] = phpcd#ExpandClassName(classname_candidate, a:current_namespace, a:imports)
				break
			end " }}}

			if line =~# '^\s*'.object.'\s*=\s*\(require\|include\).*' && !object_is_array " {{{
				let path = matchstr(line, '\(require\|include\)\(_once\)\?\s*__DIR__\s*\.\s*\zs.*\ze;')
				let cwd = expand('%:p:h')
				let path = cwd.substitute(path, "'", '', 'g')
				let path = fnamemodify(path, ':p:.')

				silent! below 1sp
				exec 'e +$ ' . path
				call search(';')
				let [symbol, context, namespace, imports] = phpcd#GetCurrentSymbolWithContext()
				let classname = phpcd#GetClassName(line('.'), symbol.'->', namespace, imports)
				q
				return classname
			endif " }}}

			" function declaration line
			if line =~? 'function\(\s\+'.function_name_pattern.'\)\?\s*(' " {{{
				let function_lines = join(reverse(copy(lines)), " ")
				" search for type hinted arguments {{{
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
				endif " }}}

				" search for docblock for the function {{{
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
				endif " }}}
			endif " }}}

			" assignment for the variable in question with a variable on the right hand side
			if line =~# '^\s*'.object.'\s*=&\?\s\+\(clone\)\?\s*'.variable_name_pattern " {{{

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
			endif " }}}

			" assignment for the variable in question with function chains on the right hand side
			if line =~? '^\s*' . object . '\s*=.*);\?$' " {{{
				let classname = phpcd#GetCallChainReturnTypeAt(a:start_line - i)
				let classname_parts = split(classname, '\\\+')
				let classname_candidate = classname_parts[-1]
				let class_candidate_namespace = join(classname_parts[0:-2], '\')
				break
			endif " }}}

			" foreach with the variable in question
			if line =~? 'foreach\s*(.\{-}\s\+'.object.'\s*)' " {{{
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
			endif " }}}

			" catch clause with the variable in question
			if line =~? 'catch\s*(\zs'.class_name_pattern.'\ze\s\+'.object " {{{
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
			endif " }}}

			let i += 1
		endfor " }}}

		if classname_candidate != '' " {{{
			return phpcd#GetCallChainReturnType(classname_candidate, class_candidate_namespace, class_candidate_imports, methodstack)
		endif " }}}
	endif " }}}
endfunction " }}}

function! phpcd#UpdateIndex() " {{{
	if !exists('g:phpid_channel_id')
		return
	endif

	let g:phpcd_need_update = 0
	let nsuse = rpc#request(g:phpcd_channel_id, 'nsuse', expand('%:p'))
	let classname = nsuse.namespace . '\' . nsuse.class
	return rpc#notify(g:phpid_channel_id, 'update', classname)
endfunction " }}}

function! phpcd#Index() "{{{
	if !exists('g:phpid_channel_id')
		return
	endif

	call rpc#notify(g:phpid_channel_id, 'index')
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
endfunction " }}}

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
endfunction " }}}

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
	let valid_types = []
	for type in types
		if index(primitive_types, type) == -1
			call add(valid_types, type)
		endif
	endfor

	return phpcd#SelectOne(valid_types)
endfunction " }}}

function! phpcd#GetCurrentNameSpace() " {{{
	let nsuse = rpc#request(g:phpcd_channel_id, 'nsuse', expand('%:p'))

	return [nsuse.namespace, nsuse.imports]
endfunction " }}}

function! phpcd#GetCurrentFunctionBoundaries() " {{{
	let old_cursor_pos = [line('.'), col('.')]
	let current_line_no = old_cursor_pos[0]
	let function_pattern = '\<function\s\+.*('

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
endfunction " }}}

function! phpcd#ExpandClassName(classname, current_namespace, imports) " {{{
	if a:classname[0] == '\'
		let last_slash_pos = strridx(a:classname, '\')
		if last_slash_pos <= 0
			return [a:classname[1:], '\']
		else
			return [a:classname[last_slash_pos+1:], a:classname[:last_slash_pos-1]]
		endif
	endif

	let parts = split(a:classname, '\\\+')
	if has_key(a:imports, parts[0])
		let parts[0] = a:imports[parts[0]]
	else
		call insert(parts, a:current_namespace, 0)
	endif

	let classname = parts[-1]
	let namespace = join(parts[0:-2], '\')
	return [classname, namespace]
endfunction " }}}

function! phpcd#GetCallChainReturnTypeAt(line) " {{{
	silent! below 1sp
	exec 'normal! ' . a:line . 'G'
	call search(';')
	let [_, context, namespace, imports] = phpcd#GetCurrentSymbolWithContext()
	let classname = phpcd#GetClassName(line('.'), context, namespace, imports)
	q
	return classname
endfunction " }}}

" vim: foldmethod=marker:noexpandtab:ts=2:sts=2:sw=2
