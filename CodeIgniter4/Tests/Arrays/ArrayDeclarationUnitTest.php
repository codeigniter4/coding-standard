<?php
/**
 * Array Declaration Unit Test
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Tests\Arrays;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class ArrayDeclarationUnitTest extends AbstractSniffUnitTest
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
                6   => 1,
                7   => 1,
                8   => 1,
                9   => 1,
                10  => 1,
                11  => 1,
                12  => 1,
                13  => 1,
                14  => 2,
                15  => 1,
                16  => 1,
                22  => 1,
                29  => 1,
                30  => 1,
                36  => 1,
                37  => 2,
                42  => 2,
                43  => 2,
                44  => 2,
                45  => 2,
                47  => 2,
                48  => 2,
                49  => 2,
                52  => 2,
                56  => 2,
                60  => 1,
                61  => 1,
                62  => 1,
                64  => 1,
                70  => 1,
                71  => 1,
                77  => 1,
                80  => 1,
                81  => 1,
                82  => 1,
                85  => 2,
                87  => 1,
                89  => 1,
                92  => 1,
                93  => 1,
                98  => 1,
                99  => 1,
                100 => 1,
                101 => 1,
                104 => 1,
                105 => 1,
                106 => 1,
                107 => 1,
                112 => 1,
                113 => 1,
                116 => 1,
                117 => 1,
                118 => 1,
                119 => 1,
                120 => 1,
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
