# CodeIgniter4-Standard

[CodeIgniter](https://codeigniter.com) 4 Standard for [PHP_CodeSniffer 3](https://github.com/squizlabs/PHP_CodeSniffer).

Version 1.0.0-beta0007

***This is currently a work in progress.***

*Requested at: https://github.com/bcit-ci/CodeIgniter4/issues/182*
 
## Install

### Composer install.

As this is a beta version you will need to add `"minimum-stability": "dev"` to your 'composer.json' file.

`cd /Path/To/MyProject`  
`composer require louisl/codeigniter4-standard:1.* --dev`  

Set the `phpcs standard path` and `phpcbf standard path` in your editor/plugin config to:

`/Path/To/MyProject/vendor/louisl/codeigniter4-standard/CodeIgniter4/ruleset.xml`

### Download install

Download [CodeIgniter4-Standard](https://github.com/louisl/CodeIgniter4-Standard/archive/v1.0.0-beta0007.zip).

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
                // Optional don't show warnings
                // "-n": ""
            },
            "phpcbf_executable_path": "/Path/To/php_codesniffer/bin/phpcbf",
            // Or if installed globally. "phpcbf_executable_path": "phpcbf",
            "phpcbf_additional_args":
            {
                "--standard": "/Path/To/CodeIgniter4-Standard/CodeIgniter4/ruleset.xml",
                // Optional don't fix warnings (if they're fixable)
                // "-n": ""
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

Thanks to [EllisLab](https://ellislab.com) for originally creating codeigniter and the [British Columbia Institute of Technology](https://bcit.ca/) for continuing the project. Thanks to all the developers and contibutors working on [CodeIgniter 4](https://github.com/bcit-ci/CodeIgniter4).

