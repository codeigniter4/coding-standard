<?php
/**
 * One Class Per File
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Sniffs\Files;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * One Class Per File Sniff
 *
 * Checks that only one class is declared per file. Unless the file
 * is allowed multiple classes.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class OneClassPerFileSniff implements Sniff
{

    /**
     * Files that are allowed multiple classes
     *
     * @var array
     */
    public $filesAllowedMultiClass = array(
                                      'Exception.php',
                                      'Exceptions.php',
                                      'CustomExceptions.php',
                                      'Response.php',
                                     );


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_CLASS);

    }//end register()


    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in
     *                                               the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $nextClass = $phpcsFile->findNext($this->register(), ($stackPtr + 1));

        $fileName = basename($phpcsFile->getFilename());

        if ($nextClass !== false) {
            if (in_array($fileName, $this->filesAllowedMultiClass) === false) {
                $error = 'Only one class is allowed in a file';
                $phpcsFile->addError($error, $nextClass, 'MultipleFound');
            }
        }

    }//end process()


}//end class
