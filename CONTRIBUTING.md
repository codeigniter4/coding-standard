# CodeIgniter4-Standard

## Contributing

### PHPUnit Testing

***This is currently a work in progress. Not all unit tests are complete or working as intended.***

`cd /Path/To/CodeIgniter4-Standard`

 Install dependencies (PHP_Codesniffer).

`composer install`

Verify PHP_Codesniffer works.

`./vendor/squizlabs/php_codesniffer/bin/phpcs -i`

Set installed standard to CodeIgniter4.

`./vendor/squizlabs/php_codesniffer/bin/phpcs --config-set installed_paths /Path/To/CodeIgniter4-Standard/CodeIgniter4`

Verify the CodeIgniter4 standard is installed.

`./vendor/squizlabs/php_codesniffer/bin/phpcs -i`

Change directory to 'php_codesniffer'.

`cd ./vendor/squizlabs/php_codesniffer/`

Install PHP_Codesniffer dependencies (PHPUnit).

`composer install`

Change directory back to 'CodeIgniter4-Standard'.

`cd ../../../`

Run unit tests.

`./vendor/squizlabs/php_codesniffer/vendor/bin/phpunit --debug --filter CodeIgniter4 ./vendor/squizlabs/php_codesniffer/tests/AllTests.php`