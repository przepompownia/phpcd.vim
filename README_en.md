- [中文](./README.md)
- English

[![asciicast](https://asciinema.org/a/4dzyyjymrguylqt21igxlhhqx.png)](https://asciinema.org/a/4dzyyjymrguylqt21igxlhhqx)

## Introduction

PHPCD (PHP Completion Daemon) is another vim omni complete engine for PHP.

PHPCD is based on [phpcomplete.vim](https://github.com/shawncplus/phpcomplete.vim) but is faster;

While phpcomplete.vim using the tag file to fetch the context info, PHPCD use the PHP's Reflection mechanism to fetch the context info, and this is why PHPCD is faster. All the phpcomplete VimL code related the tag file has been droped and reimplemented.

PHPCD consists of two parts. On part is written in VimL (mainly based on phpcomplete.vim), and the other in PHP. The communication between the VimL part and the PHP part is rely on the NeoVim's MsgPack-RPC mechanism. This is why the NeoVim is needed.

##  Feature
 * Fast, Lightweight, Strong
 * Correct restriction of static or standard methods based on context (show only static methods with `::` and only standard with `->`)
 * Real support for `self::`, `static::`, `parent::` and `$this->` with the aforementioned context restriction
 * Better class detection
     - Recognize `/* @var $yourvar YourClass */`、 `/* @var YourClass $yourvar */` type mark comments
     - Recognize `$instance = new Class;` class instantiations
     - Recognize `$instance = Class::foo()->bar();` method call chain return type use `bar`'s `@return` docblocks
     - Recognize `$date = DateTime::createFromFormat(...)` built-in class return types
     - Recognize type hinting in function prototypes
     - Recognize types in `@param` lines in function docblocks
     - Recognize array of objects via docblock like `$foo[42]->` or for variables created in `foreach`
 * Displays docblock info in the preview for methods and properties
 * Support built-in class support with constants, methods and properties
 * Enhanced jump-to-definition on <kbd>ctrl</kbd>+<kbd>]</kbd>

## Installation & Usage

### System requirement

 1. PHP 5.3+
 2. ~~[socket](http://php.net/manual/en/book.sockets.php) Extension~~
 3. [PCNTL](http://php.net/manual/en/book.pcntl.php) Extension
 4. [Msgpack 0.5.7+](https://github.com/msgpack/msgpack-php) Extension
 5. [NeoVim](http://neovim.io/)
 6. [Composer](https://getcomposer.org/) Project


### Install PHPCD

We recommend you use [Vim-Plug](https://github.com/junegunn/vim-plug/blob/master/README.md) to mange your vim plugins.

With Vim-Plug installed, put the following lines in your vimrc,

```
Plug 'phpvim/phpcd.vim', { 'for': 'php' , 'do': 'composer update' }
Plug 'vim-scripts/progressbar-widget' " used for showing the index progress
```

And then execute `:PlugInstall` in the command mode.

### Enable PHPCD

Before the first use PHPCD, in the phpcd.vim directory run `composer install`. This is needed to install dependences and generate the autoload file.

Let PHPCD complete php,

```
autocmd FileType php setlocal omnifunc=phpcd#CompletePHP
```

## Usage

First, in the project directory, run `composer install` to install all the dependent packages and generate the autoload file.

The default PHP command used to run PHP parts of daemon is simply `php`. You may override it by assigning `g:phpcd_php_cli_executable` another value in your `vimrc`, for example:
```
let g:phpcd_php_cli_executable = 'php7.0'
```

Then, use NeoVim to open a php file. You will see a progress bar several seconds later.
When the bar finish, you could enjoy you PHP coding.

Use <kbd>Ctrl</kbd>+<kbd>x</kbd><kbd>Ctrl</kbd>+<kbd>o</kbd> to complete and use <kbd>ctrl</kbd>+<kbd>]</kbd> to go to the defination.

Good luck :)
