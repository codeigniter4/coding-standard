# CodeIgniter4-Standard

## Contributing

### PHPUnit Testing

***This is currently a work in progress.***

`cd /Path/To/CodeIgniter4-Standard`

 Install dependencies (PHP_Codesniffer).

`composer install`

Verify PHP_Codesniffer works.

`./vendor/bin/phpcs -i`

Set installed standard to CodeIgniter4.

`./vendor/bin/phpcs --config-set installed_paths /Path/To/CodeIgniter4-Standard/CodeIgniter4`

Verify the CodeIgniter4 standard is installed.

`./vendor/bin/phpcs -i`

Run unit tests.

`./vendor/bin/phpunit --debug --filter CodeIgniter4`
