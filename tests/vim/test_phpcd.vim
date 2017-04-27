let s:current_dir = expand('%:p:h')

function SetUp()
	below 1new
endfunction

function TearDown()
	silent! bw! %
endfunction

function s:startEditFixture(fixtureRelativePath)
	let path =  s:current_dir.'/fixtures/'.a:fixtureRelativePath
	call assert_true(filereadable(path))
	exe ":edit ".path
	" exe ":silent! edit ".path
endfunction

function s:runCompletion()
	let start = phpcd#CompletePHP(1, '')
	let base = strpart(getline("."), start)
	return phpcd#CompletePHP(0, base)
endfunction

function! Test_expect_public_property()
	call <SID>startEditFixture('PHPCD/B/C/ExpectPublicVariable.php')

	call cursor(11, 18)
	let res = <SID>runCompletion()
	" res == [{'word': 'pubvar', 'info': '', 'kind': 'p', 'abbr': '  - pubvar', 'icase': 1}]

	call assert_equal(
		\'pubvar',
		\res[0].word)
endf

function Test_expect_constant()
	call <SID>startEditFixture('PHPCD/B/C/ExpectClassConstantOnly.php')

	call cursor(9, 27)
	let res = <SID>runCompletion()

	" this test with fixtures
	" intentionally exposes completion of static variables
	" even without leading dollar sign
	call assert_equal('d', res[0].kind)
	call assert_equal('p', res[1].kind)
endf
