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
	let base = strpart(getline('.'), start, col('.') - start - 1)
	return phpcd#CompletePHP(0, base)
endfunction

function s:appendIncompleteLineText(lineNumber, lineText)
	call cursor(a:lineNumber, 0)
	execute "normal! o".a:lineText."\<esc>l"
endfunction

function s:insertInlineIncompleteLineText(lineNumber, columnNumber, inlineText)
	call cursor(a:lineNumber, a:columnNumber)
	execute "normal! a".a:inlineText."\<esc>l"
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
endfunction

function! Test_expect_constant()
	call <SID>startEditFixture('PHPCD/B/C/ExpectClassConstantOnly.php')

	call <SID>appendIncompleteLineText(9, '\PHPCD\A\Alpha::iv')
	let res = <SID>runCompletion()

	" this test with fixtures
	" intentionally exposes completion of static variables
	" even without leading dollar sign
	call assert_equal('d', res[0].kind)
	call assert_equal('p', res[1].kind)
endfunction

function! Test_expect_class_path_completion_at_import_declaration()
	call <SID>startEditFixture('PHPCD/A/Alpha.php')

	call <SID>appendIncompleteLineText(3, 'use Expect')
	let res = <SID>runCompletion()

	call assert_equal(2, len(res))
	call assert_equal('PHPCD\B\C\ExpectClassConstantOnly', res[0].word)
	call assert_equal('PHPCD\B\C\ExpectPublicVariable', res[1].word)
endfunction

function! Test_expect_class_path_completion_at_function_declaration()
	call <SID>startEditFixture('PHPCD/A/Alpha.php')

	call <SID>insertInlineIncompleteLineText(22, 34, 'Expect')

	let g:phpcd_insert_class_shortname = 1
	let res = <SID>runCompletion()

	call assert_equal(2, len(res))
	call assert_equal('ExpectClassConstantOnly', res[0].word)
	call assert_equal('ExpectPublicVariable', res[1].word)

	let g:phpcd_insert_class_shortname = 0
	let res = <SID>runCompletion()

	call assert_equal(2, len(res))
	call assert_equal('\PHPCD\B\C\ExpectClassConstantOnly', res[0].word)
	call assert_equal('\PHPCD\B\C\ExpectPublicVariable', res[1].word)
endfunction

function! Test_complete_throw_new()
	call <SID>startEditFixture('PHPCD/A/Alpha.php')

	call <SID>appendIncompleteLineText(23, 'throw new Custom')
	let res = <SID>runCompletion()

	call assert_equal(2, len(res))
	call assert_equal('\PHPCD\Throwable\CustomError', res[0].word)
	call assert_equal('\PHPCD\Throwable\CustomException', res[1].word)
endfunction

function! Test_complete_catch_exception()
	call <SID>startEditFixture('PHPCD/A/Alpha.php')

	call <SID>appendIncompleteLineText(23, 'try {} catch(Custom')
	let res = <SID>runCompletion()

	call assert_equal(3, len(res))
	call assert_equal('\PHPCD\Throwable\AbstractCustomError', res[0].word)
	call assert_equal('\PHPCD\Throwable\CustomError', res[1].word)
	call assert_equal('\PHPCD\Throwable\CustomException', res[2].word)
endfunction

function! Test_complete_based_on_returned_value()
	call <SID>startEditFixture('PHPCD/Docblock/Foo.php')

	call <SID>appendIncompleteLineText(11, '$rv->get')
	let res = <SID>runCompletion()

	call assert_equal(1, len(res))
	call assert_equal('getReflectionClass', res[0].word)
endfunction

function! Test_complete_public_methods()
	call <SID>startEditFixture('PHPCD/Docblock/Foo.php')

	call <SID>appendIncompleteLineText(11, '$rc = $rv->getReflectionClass();')
	call <SID>appendIncompleteLineText(12, '$rc->isAbs')
	let res = <SID>runCompletion()

	call assert_equal(1, len(res))
	call assert_equal('isAbstract', res[0].word)
endfunction
