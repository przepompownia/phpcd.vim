let s:current_dir = expand('%:p:h')

function SetUp()
	below 1new
endfunction

function TearDown()
	silent! bw! %
endfunction

function s:startEditFixture(fixtureRelativePath)
	let path =  s:current_dir.'/../fixtures/'.a:fixtureRelativePath
	call assert_true(filereadable(path))
	exe ":edit ".path
	" exe ":silent! edit ".path
endfunction

function s:runCompletion()
	let start = phpcd#CompletePHP(1, '')
	let base = strpart(getline("."), start)
	return phpcd#CompletePHP(0, base)
endfunction

function s:appendIncompleteLineText(lineNumber, lineText)
	call cursor(a:lineNumber, 0)
	execute "normal! o".a:lineText."\<esc>l"
endfunction

function! Test_expect_plugin_was_loaded()
	call <SID>startEditFixture('PHPCD/B/C/ExpectPublicVariable.php')
	call assert_equal('phpcd#CompletePHP', &omnifunc)
endfunction

function! Test_expect_public_property()
	call <SID>startEditFixture('PHPCD/B/C/ExpectPublicVariable.php')

	call <SID>appendIncompleteLineText(9, '$alpha->pb')
	let res = <SID>runCompletion()
	" res == [{'word': 'pubvar', 'info': '', 'kind': 'p', 'abbr': '  - pubvar', 'icase': 1}]

	call assert_equal(
		\'pubvar',
		\res[0].word)
endf

function Test_expect_constant()
	call <SID>startEditFixture('PHPCD/B/C/ExpectClassConstantOnly.php')

	call <SID>appendIncompleteLineText(9, '\PHPCD\A\Alpha::iv')
	let res = <SID>runCompletion()

	" this test with fixtures
	" intentionally exposes completion of static variables
	" even without leading dollar sign
	call assert_equal('d', res[0].kind)
	call assert_equal('p', res[1].kind)
endf
