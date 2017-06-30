# CodeIgniter4-Standard

CodeIgniter 4 Standard for [PHP_CodeSniffer 3](https://github.com/squizlabs/PHP_CodeSniffer).

Version 1.0.0-beta0003

This is currently a work in progress.
 
## Install

### Composer install (coming soon).

`cd /Path/To/MyProject`  

`composer require louisl/codeigniter4-standard --dev`  

Set the `phpcs standard path` and `phpcbf standard path` in your editor/plugin config to:

`/Path/To/MyProject/vendor/louisl/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml`

### Download install

Download [CodeIgniter4-Standard](https://github.com/louisl/CodeIgniter4-Standard/archive/master.zip).

Set `standard ` paths to your local filesystem:

`'/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml'`

### Command line use

#### Sniffing errors & warnings (reporting).

Single file.

`phpcs /Path/To/MyFile.php --standard='/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml'`

Directory (recursive).

`phpcs /Path/To/MyProject --standard='/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml'`

#### Fixing fixable errors.

Single file.

`phpcbf /Path/To/MyFile.php --standard='/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml'`

Directory (recursive).

`phpcbf /Path/To/MyProject --standard='/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml'`

## Example editor configs

### SublimeText project config.

Project > Edit Project

Set it to your preference.

```
{
    "SublimeLinter":
    {
        "linters":
        {
            "phpcs":
            {
                "@disable": false,
                "cmd": "/Path/To/php_codesniffer/bin/phpcs",
                // Or if installed globally. "cmd": "phpcs",
                "standard": "/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml"
            }
        }
    },
    "folders":
    [
        {
            "path": "/Path/To/MyProject"
        }
    ],
    "settings":
    {
        "phpcs":
        {
            "extensions_to_execute":
            [
                "php"
            ],
            "phpcs_executable_path": "/Path/To/php_codesniffer/bin/phpcs",
            // Or if installed globally. "phpcbf_executable_path": "phpcs",
            "phpcs_additional_args":
            {
                "--standard": "/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml",
                "-n": ""
            },
            "phpcbf_executable_path": "/Path/To/php_codesniffer/bin/phpcbf",
            // Or if installed globally. "phpcbf_executable_path": "phpcbf",
            "phpcbf_additional_args":
            {
                "--standard": "/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml",
                "-n": ""
            },
            // Execute the sniffer on file save. (Using contextual menu instead)
            "phpcs_execute_on_save": false,
            // Show the error list after save. (Using sublime linter instead)
            "phpcs_show_errors_on_save": false,
            // Show the errors in the quick panel so you can then goto line. (Gets annoying)
            "phpcs_show_quick_panel": false,
            // Turn the debug output on/off.
            "show_debug": false
        }
    }
}
```

## Credits

Thanks to Greg Sherwood, Marc McIntyre, Andy Grunwald, Thomas Ernest and Erik Torsner, for providing open source code which helped me build this standard and a big thanks to [Squiz Labs](http://www.squizlabs.com) for creating [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).
