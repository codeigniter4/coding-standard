<?php
/**
 * Function Closing Brace Space
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
 * Function Closing Brace Space Sniff
 *
 * Checks that there is [allowedLines|allowedNestedLines] empty lines before the
 * closing brace of a function.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class FunctionClosingBraceSpaceSniff implements Sniff
{

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [
        'PHP',
        'JS',
    ];

    /**
     * Allowed lines before a closing function bracket.
     *
     * @var array
     */
    public $allowedLines = 0;

    /**
     * Allowed spaces before a closing function bracket.
     *
     * @var array
     */
    public $allowedNestedLines = 0;


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_FUNCTION,
            T_CLOSURE,
        ];

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token
     *                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_closer']) === false) {
            // Probably an interface method.
            return;
        }

        $closeBrace  = $tokens[$stackPtr]['scope_closer'];
        $prevContent = $phpcsFile->findPrevious(T_WHITESPACE, ($closeBrace - 1), null, true);

        // Special case for empty JS functions.
        if ($phpcsFile->tokenizerType === 'JS' && $prevContent === $tokens[$stackPtr]['scope_opener']) {
            // In this case, the opening and closing brace must be
            // right next to each other.
            if ($tokens[$stackPtr]['scope_closer'] !== ($tokens[$stackPtr]['scope_opener'] + 1)) {
                $error = 'The opening and closing braces of empty functions must be directly next to each other; e.g., function () {}';
                $fix   = $phpcsFile->addFixableError($error, $closeBrace, 'SpacingBetween');
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($tokens[$stackPtr]['scope_opener'] + 1); $i < $closeBrace; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                }
            }

            return;
        }

        $nestedFunction = false;
        if ($phpcsFile->hasCondition($stackPtr, T_FUNCTION) === true
            || $phpcsFile->hasCondition($stackPtr, T_CLOSURE) === true
            || isset($tokens[$stackPtr]['nested_parenthesis']) === true
        ) {
            $nestedFunction = true;
        }

        $braceLine = $tokens[$closeBrace]['line'];
        $prevLine  = $tokens[$prevContent]['line'];
        $found     = ($braceLine - $prevLine - 1);

        $afterKeyword  = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        $beforeKeyword = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        if ($nestedFunction === true) {
            if ($found < 0) {
                $error = 'Closing brace of nested function must be on a new line';
                $fix   = $phpcsFile->addFixableError($error, $closeBrace, 'ContentBeforeClose');
                if ($fix === true) {
                    $phpcsFile->fixer->addNewlineBefore($closeBrace);
                }
            } else if ($found > $this->allowedNestedLines) {
                $error = 'Expected %s blank lines before closing brace of nested function; %s found';
                $data  = [
                    $this->allowedNestedLines,
                    $found,
                ];
                $fix   = $phpcsFile->addFixableError($error, $closeBrace, 'SpacingBeforeNestedClose', $data);

                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    $changeMade = false;
                    for ($i = ($prevContent + 1); $i < $closeBrace; $i++) {
                        // Try to maintain indentation.
                        if ($tokens[$i]['line'] === ($braceLine - 1)) {
                            break;
                        }

                        $phpcsFile->fixer->replaceToken($i, '');
                        $changeMade = true;
                    }

                    // Special case for when the last content contains the newline
                    // token as well, like with a comment.
                    if ($changeMade === false) {
                        $phpcsFile->fixer->replaceToken(($prevContent + 1), '');
                    }

                    $phpcsFile->fixer->endChangeset();
                }//end if
            }//end if
        } else {
            if ($found !== (int) $this->allowedLines) {
                if ($this->allowedLines === 1) {
                    $plural = '';
                } else {
                    $plural = 's';
                }

                $error = 'Expected %s blank line%s before closing function brace; %s found';
                $data  = [
                    $this->allowedLines,
                    $plural,
                    $found,
                ];
                $fix   = $phpcsFile->addFixableError($error, $closeBrace, 'SpacingBeforeClose', $data);

                if ($fix === true) {
                    if ($found > $this->allowedLines) {
                        $phpcsFile->fixer->beginChangeset();
                        for ($i = ($prevContent + 1); $i < ($closeBrace); $i++) {
                            $phpcsFile->fixer->replaceToken($i, '');
                        }

                        $phpcsFile->fixer->replaceToken(($closeBrace - 1), $phpcsFile->eolChar);
                        $phpcsFile->fixer->endChangeset();
                    } else {
                        $phpcsFile->fixer->addNewlineBefore($closeBrace);
                    }
                }
            }//end if
        }//end if

    }//end process()


}//end class
