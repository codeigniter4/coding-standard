<?php
/**
 * Filename Matches Class
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 Louis Linehan
 * @license   https://github.com/louisl/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter\Sniffs\Files;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Filename Matches Class Sniff
 *
 * Checks that the filename matches the class name.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class FilenameMatchesClassSniff implements Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_CLASS,
                T_INTERFACE,
               );

    }//end register()


    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in
     *                        the stack passed in $tokens.
     *
     * @return int
     */
    public function process(File $phpcsFile, $stackPtr)
    {

        $fileName = basename($phpcsFile->getFilename());

        $className = trim($phpcsFile->getDeclarationName($stackPtr));

        if ($fileName !== $className.'.php') {
            $data  = array(
                      $fileName,
                      $className.'.php',
                     );
            $error = 'Filename "%s" doesn\'t match the expected filename "%s"';
            $phpcsFile->addError($error, 1, 'NotFound', $data);
            $phpcsFile->recordMetric(1, 'Filename matches class', 'no');
        } else {
            $phpcsFile->recordMetric(1, 'Filename matches class', 'yes');
        }

        // Ignore the rest of the file.
        return ($phpcsFile->numTokens + 1);

    }//end process()


}//end class
