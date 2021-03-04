<?php
/**
 * Boolean Not Space After
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Boolean Not Space After Sniff
 *
 * Checks there is a space after '!'.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class BooleanNotSpaceAfterSniff implements Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_BOOLEAN_NOT];

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The current file being scanned.
     * @param int  $stackPtr  The position of the current token
     *                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $nextToken = $tokens[($stackPtr + 1)];
        if (T_WHITESPACE !== $nextToken['code']) {
            $error = 'There must be a space after !';
            $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'BooleanNotNoWhiteSpaceAfter');

            if ($fix === true) {
                $nextContentPtr = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
                $phpcsFile->fixer->beginChangeset();
                for ($i = ($nextContentPtr + 1); $i < $stackPtr; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }

                $phpcsFile->fixer->addContent(($stackPtr), ' ');
                $phpcsFile->fixer->endChangeset();
                $phpcsFile->recordMetric($stackPtr, 'Boolean not space after', 'yes');
            }//end if
        }//end if

    }//end process()


}//end class
