<?php
/**
 * Vertical Empty Lines
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use CodeIgniter4\Util\Common;

/**
 * Vertical Empty Lines Sniff
 *
 * Checks for consecutive empty vertical lines.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class VerticalEmptyLinesSniff implements Sniff
{

    /**
     * Consecutive empty vertical lines allowed.
     *
     * @var integer
     */
    public $allowed = 1;


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_OPEN_TAG);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in
     *                                               the stack passed in $tokens.
     *
     * @return int
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $errors = array();
        $tokens = $phpcsFile->getTokens();
        for ($i = 1; $i < $phpcsFile->numTokens; $i++) {
            $nextContentPtr = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), null, true);

            $lines     = ($tokens[$nextContentPtr]['line'] - $tokens[$i]['line'] - 1);
            $errorLine = (($nextContentPtr - $lines) + $this->allowed - 1);

            if ($lines > ($this->allowed) && in_array($errorLine, $errors) === false) {
                $errors[] = $errorLine;

                $data  = array(
                          $this->allowed,
                          Common::pluralize('line', $this->allowed),
                         );
                $error = 'Expected only %s empty %s';
                $fix   = $phpcsFile->addFixableError($error, $errorLine, 'VerticalEmptyLines', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->replaceToken($errorLine, '');
                }
            }
        }

        // Ignore the rest of the file.
        return ($phpcsFile->numTokens + 1);

    }//end process()


}//end class
