# CodeIgniter4-Standard

[CodeIgniter](https://codeigniter.com) 4 coding standard for use with [PHP_CodeSniffer 3](https://github.com/squizlabs/PHP_CodeSniffer).

Version 1

| Master | Develop |
| :---: | :---: |
| [![Build Status](https://travis-ci.org/bcit-ci/CodeIgniter4-Standard.svg?branch=master)](https://travis-ci.org/bcit-ci/CodeIgniter4-Standard) | [![Build Status](https://travis-ci.org/bcit-ci/CodeIgniter4-Standard.svg?branch=develop)](https://travis-ci.org/bcit-ci/CodeIgniter4-Standard) |
| [![Coverage Status](https://coveralls.io/repos/github/bcit-ci/CodeIgniter4-Standard/badge.svg?branch=master)](https://coveralls.io/github/bcit-ci/CodeIgniter4-Standard?branch=master) | [![Coverage Status](https://coveralls.io/repos/github/bcit-ci/CodeIgniter4-Standard/badge.svg?branch=develop)](https://coveralls.io/github/bcit-ci/CodeIgniter4-Standard?branch=develop) |

***This is currently a work in progress.***

*Requested at: https://github.com/bcit-ci/CodeIgniter4/issues/182*

## Requirements

[PHP_CodeSniffer 3](https://github.com/squizlabs/PHP_CodeSniffer). (3.1.1 or greater).

PHP (7.1 or greater) with mbstring extension.

## Install

### Composer install

`cd /Path/To/MyProject`
`composer require codeigniter4/codeigniter4-standard --dev`

Set the `phpcs standard path` and `phpcbf standard path` in your editor/plugin config to:

`/Path/To/MyProject/vendor/codeigniter4/codeigniter4-standard/CodeIgniter4/ruleset.xml`

### Download install

Download [CodeIgniter4-Standard](https://github.com/bcit-ci/CodeIgniter4-Standard/archive/v1.0.1.zip).

Set `standard ` paths to your local filesystem:

`'/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml'`

### Global install

Globally [install PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/README.md) with one of the various methods.

Once complete you should be able to execute `phpcs -i` on the command line.

You should see something like:-

`The installed coding standards are MySource, PEAR, PSR1, PSR2, Squiz and Zend.`

Either clone this repository...

`git clone -b master --depth 1 https://github.com/bcit-ci/CodeIgniter4-Standard.git`.

or use composer...

`composer global require codeigniter4/codeigniter4-standard`

or download.

Take note of the paths where they were installed.

Create a symbolic link to the `CodeIgniter4-Standard/CodeIgniter4` directory in `php_codesniffer/src/Standards/` eg.

`ln -s ~/Documents/Projects/CodeIgniter4-Standard/CodeIgniter4 ~/.composer/vendor/squizlabs/php_codesniffer/src/Standards/CodeIgniter4`

or copy the `CodeIgniter4-Standard/CodeIgniter4` directory to `php_codesniffer/src/Standards/`

Executing `phpcs -i` should now show CodeIgniter4 installed eg.

`The installed coding standards are CodeIgniter4, MySource, PEAR, PSR1, PSR2, Squiz and Zend.`

You should now be able to set 'CodeIgniter4' as the phpcs standard in the plugin/editor/IDE of your choice.

### Command line use

#### Sniffing errors & warnings (reporting).

Single file...

`phpcs /Path/To/MyFile.php --standard='/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml'`

or if globally installed.

`phpcs /Path/To/MyFile.php --standard=CodeIgniter4`

Directory (recursive).

`phpcs /Path/To/MyProject --standard='/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml'`

or if globally installed.

`phpcs /Path/To/MyProject --standard=CodeIgniter4`

#### Fixing fixable errors.

Single file.

`phpcbf /Path/To/MyFile.php --standard='/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml'`

or if globally installed.

`phpcbf /Path/To/MyFile.php --standard=CodeIgniter4`

Directory (recursive).

`phpcbf /Path/To/MyProject --standard='/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml'`

or if globally installed.

`phpcbf /Path/To/MyProject --standard=CodeIgniter4`

## Credits

Thanks to Greg Sherwood, Marc McIntyre, Andy Grunwald, Thomas Ernest and Erik Torsner, for providing open source code which helped me build this standard and a big thanks to [Squiz Labs](http://www.squizlabs.com) for creating [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).

Thanks to [EllisLab](https://ellislab.com) for originally creating CodeIgniter and the [British Columbia Institute of Technology](https://bcit.ca/) for continuing the project. Thanks to all the developers and contibutors working on [CodeIgniter 4](https://github.com/bcit-ci/CodeIgniter4).
