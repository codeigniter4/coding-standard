<?php
/**
 * Allman Control Signature Unit Test
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Tests\ControlStructures;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class AllmanControlSignatureUnitTest extends AbstractSniffUnitTest
{


    /**
     * Get a list of CLI values to set before the file is tested.
     *
     * @param string                  $testFile The name of the file being tested.
     * @param \PHP_CodeSniffer\Config $config   The config data for the test run.
     *
     * @return void
     */
    public function setCliValues($testFile, $config)
    {
        $config->tabWidth = 4;

    }//end setCliValues()


    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @return array<int, int>
     */
    public function getErrorList()
    {
        return array(
                3   => 1,
                5   => 1,
                10  => 1,
                18  => 4,
                20  => 1,
                22  => 1,
                24  => 1,
                28  => 2,
                32  => 3,
                34  => 1,
                38  => 2,
                42  => 3,
                44  => 1,
                48  => 2,
                52  => 3,
                54  => 1,
                56  => 2,
                60  => 1,
                62  => 2,
                66  => 7,
                68  => 1,
                70  => 2,
                74  => 1,
                76  => 3,
                80  => 7,
                82  => 2,
                86  => 2,
                90  => 2,
                95  => 1,
                99  => 1,
                102 => 1,
                104 => 2,
                108 => 5,
                112 => 2,
                113 => 1,
                115 => 2,
                120 => 2,
                122 => 1,
                123 => 1,
                126 => 1,
                130 => 2,
                148 => 1,
                151 => 1,
                154 => 1,
                175 => 1,
                185 => 2,
                206 => 1,
                208 => 2,
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
