<?php
/**
 * Unit test class for the ArrayDeclarationTest sniff.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2015 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

namespace CodeIgniter4\Tests\Arrays;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;
use \CodeIgniter4\Util\Common;

class ArrayDeclarationUnitTest extends AbstractSniffUnitTest
{


    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * Note 1.
     * Cheating a little here, because PHPCS counts tabs as 1 column.
     * Assuming tabs are 4 spaces. The indented token will start
     * column 3 for 2 tabs, but if it were indented with spaces it would
     * start at column 9.
     * Elsewhere in the standard we fix spaced indentation with tabs and
     * everything works but I haven't been able to fix it in this unit test.
     *
     * @return array<int, int>
     */
    public function getErrorList()
    {
        return array(
                10 => 1,
                11 => 1,
                12 => 1,
                13 => 1,
                14 => 1,
                15 => 1,
                16 => 1,
                17 => 1,
                18 => 2,
                19 => 1,
                20 => 1,
                // See Note 1.
                25 => 1,
                // See Note 1.
                28 => 1,
               );

    }//end getErrorList()


    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of warnings that should occur on that line.
     *
     * @return array<int, int>
     */
    public function getWarningList()
    {
        return array();

    }//end getWarningList()


}//end class
