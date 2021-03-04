<?php
/**
 * Common
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Util;

use PHP_CodeSniffer\Util\Common as BaseCommon;

/**
 * Common
 *
 * Extends common functions.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class Common extends BaseCommon
{

    /**
     * A list of all PHP magic methods.
     *
     * @var array
     */
    public static $magicMethods = [
        'construct'  => true,
        'destruct'   => true,
        'call'       => true,
        'callstatic' => true,
        'get'        => true,
        'set'        => true,
        'isset'      => true,
        'unset'      => true,
        'sleep'      => true,
        'wakeup'     => true,
        'tostring'   => true,
        'set_state'  => true,
        'clone'      => true,
        'invoke'     => true,
        'debuginfo'  => true,
    ];

    /**
     * Allowed public methodNames
     *
     * @var array
     */
    public static $publicMethodNames = ['_remap' => true];


    /**
     * Is lower snake case
     *
     * @param string $string The string to verify.
     *
     * @return boolean
     */
    public static function isLowerSnakeCase($string)
    {
        if (strcmp($string, strtolower($string)) !== 0) {
            return false;
        }

        if (strpos($string, ' ') !== false) {
            return false;
        }

        return true;

    }//end isLowerSnakeCase()


    /**
     * Has an underscore prefix
     *
     * @param string $string The string to verify.
     *
     * @return boolean
     */
    public static function hasUnderscorePrefix($string)
    {
        if (strpos($string, '_') !== 0) {
            return false;
        }

        return true;

    }//end hasUnderscorePrefix()


    /**
     * Pluralize
     *
     * Basic pluralize intended for use in error messages
     * tab/s, space/s, error/s etc.
     *
     * @param string $string String.
     * @param float  $num    Number.
     *
     * @return string
     */
    public static function pluralize($string, $num)
    {
        if ($num > 1) {
            return $string.'s';
        } else {
            return $string;
        }

    }//end pluralize()


}//end class
