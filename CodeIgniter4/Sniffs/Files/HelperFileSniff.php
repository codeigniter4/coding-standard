<?php
/**
 * Helper File
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
 * Helper File Sniff
 *
 * Checks *_helper.php files only contain functions
 * and that the filename is lower snake_case.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class HelperFileSniff implements Sniff
{

    /**
     * Files that are allowed multiple classes
     *
     * @var array
     */
    public $unwantedTokens = array(
                              T_CLASS,
                              T_ANON_CLASS,
                              T_INTERFACE,
                              T_TRAIT,
                             );

    /**
     * If the file has a bad filename.
     *
     * Change to true and check it later to avoid displaying multiple errors.
     *
     * @var boolean
     */
    protected $badFilename = false;


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_FUNCTION,
                T_CLASS,
                T_ANON_CLASS,
                T_INTERFACE,
                T_TRAIT,
               );

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
        $fileName = basename($phpcsFile->getFilename());
        if (strpos($fileName, '_helper.php') === false) {
            return;
        } else {
            // Check the filename.
            $expectedFilename = preg_replace('/_{2,}/', '_', strtolower($fileName));
            if ($fileName !== $expectedFilename && $this->badFilename === false) {
                $data  = array(
                          $fileName,
                          $expectedFilename,
                         );
                $error = 'Helper filename "%s" doesn\'t match the expected filename "%s"';
                $phpcsFile->addError($error, 1, 'HelperBadFilename', $data);
                $this->badFilename = true;
            }

            // Check for class, interface, trait etc.
            $tokens = $phpcsFile->getTokens();
            if (in_array($tokens[$stackPtr]['code'], $this->unwantedTokens) === true) {
                $error = 'Helper files must only contain functions';
                $phpcsFile->addError($error, $stackPtr, 'HelperOnlyFunctions');
            }
        }//end if

    }//end process()


}//end class
