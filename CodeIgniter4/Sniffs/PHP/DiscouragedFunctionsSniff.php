<?php
/**
 * Discouraged Functions
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Sniffs\PHP;

use CodeIgniter4\Sniffs\PHP\ForbiddenFunctionsSniff;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Discouraged Functions Sniff
 *
 * Discourages the use of debug functions.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class DiscouragedFunctionsSniff extends ForbiddenFunctionsSniff
{

    /**
     * A list of discouraged functions with their alternatives.
     *
     * The value is NULL if no alternative exists. IE, the
     * function should just not be used.
     *
     * @var array(string => string|null)
     */
    public $forbiddenFunctions = array(
                                  'error_log' => null,
                                  'print_r'   => null,
                                  'var_dump'  => null,
                                 );

    /**
     * Set error to false to show warnings.
     *
     * @var boolean
     */
    public $error = false;

}//end class
