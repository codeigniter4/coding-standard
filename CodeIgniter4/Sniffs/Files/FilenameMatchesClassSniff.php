<?php
/**
 * Filename Matches Class
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
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
                T_CLASS,
                T_INTERFACE,
                T_TRAIT,
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

        $tokens = $phpcsFile->getTokens();

        $fileName = basename($phpcsFile->getFilename());

        if (strpos($fileName, '_helper.php') !== false) {
            return;
        }

        $className = trim($phpcsFile->getDeclarationName($stackPtr));

        if (strpos($className, 'Migration') === 0 && strpos($fileName, '_') !== false) {
            return;
        }

        $nextContentPtr = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        $type           = $tokens[$stackPtr]['content'];

        if ($fileName !== $className.'.php' && $this->badFilename === false) {
            $data  = array(
                      $fileName,
                      $className.'.php',
                     );
            $error = 'Filename "%s" doesn\'t match the expected filename "%s"';
            $phpcsFile->addError($error, $nextContentPtr, ucfirst($type).'BadFilename', $data);
            $phpcsFile->recordMetric($nextContentPtr, 'Filename matches '.$type, 'no');
            $this->badFilename = true;
        } else {
            $phpcsFile->recordMetric($nextContentPtr, 'Filename matches '.$type, 'yes');
        }

        // Ignore the rest of the file.
        return ($phpcsFile->numTokens + 1);

    }//end process()


}//end class
