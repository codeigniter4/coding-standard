<?php
/**
 * Function Declaration
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Sniffs\Functions;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Functions\OpeningFunctionBraceKernighanRitchieSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Functions\OpeningFunctionBraceBsdAllmanSniff;

/**
 * Function Declaration Sniff
 *
 * Ensure single and multi-line function declarations are defined correctly.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class FunctionDeclarationSniff implements Sniff
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
     * The number of spaces code should be indented.
     *
     * @var integer
     */
    public $indent = 4;


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
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['parenthesis_opener']) === false
            || isset($tokens[$stackPtr]['parenthesis_closer']) === false
            || $tokens[$stackPtr]['parenthesis_opener'] === null
            || $tokens[$stackPtr]['parenthesis_closer'] === null
        ) {
            return;
        }

        $openBracket  = $tokens[$stackPtr]['parenthesis_opener'];
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];

        if (strtolower($tokens[$stackPtr]['content']) === 'function') {
            // Must be one space after the FUNCTION keyword.
            if ($tokens[($stackPtr + 1)]['content'] === $phpcsFile->eolChar) {
                $spaces = 'newline';
            } else if ($tokens[($stackPtr + 1)]['code'] === T_WHITESPACE) {
                $spaces = strlen($tokens[($stackPtr + 1)]['content']);
            } else {
                $spaces = 0;
            }

            if ($spaces !== 1) {
                $error = 'Expected 1 space after FUNCTION keyword; %s found';
                $data  = [$spaces];
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterFunction', $data);
                if ($fix === true) {
                    if ($spaces === 0) {
                        $phpcsFile->fixer->addContent($stackPtr, ' ');
                    } else {
                        $phpcsFile->fixer->replaceToken(($stackPtr + 1), ' ');
                    }
                }
            }
        }//end if

        // Must be one space before the opening parenthesis. For closures, this is
        // enforced by the first check because there is no content between the keywords
        // and the opening parenthesis.
        if ($tokens[$stackPtr]['code'] === T_FUNCTION) {
            if ($tokens[($openBracket - 1)]['content'] === $phpcsFile->eolChar) {
                $spaces = 'newline';
            } else if ($tokens[($openBracket - 1)]['code'] === T_WHITESPACE) {
                $spaces = strlen($tokens[($openBracket - 1)]['content']);
            } else {
                $spaces = 0;
            }

            if ($spaces !== 0) {
                $error = 'Expected 0 spaces before opening parenthesis; %s found';
                $data  = [$spaces];
                $fix   = $phpcsFile->addFixableError($error, $openBracket, 'SpaceBeforeOpenParenthesis', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->replaceToken(($openBracket - 1), '');
                }
            }
        }//end if

        // Must be one space before and after USE keyword for closures.
        if ($tokens[$stackPtr]['code'] === T_CLOSURE) {
            $use = $phpcsFile->findNext(T_USE, ($closeBracket + 1), $tokens[$stackPtr]['scope_opener']);
            if ($use !== false) {
                if ($tokens[($use + 1)]['code'] !== T_WHITESPACE) {
                    $length = 0;
                } else if ($tokens[($use + 1)]['content'] === "\t") {
                    $length = '\t';
                } else {
                    $length = strlen($tokens[($use + 1)]['content']);
                }

                if ($length !== 1) {
                    $error = 'Expected 1 space after USE keyword; found %s';
                    $data  = [$length];
                    $fix   = $phpcsFile->addFixableError($error, $use, 'SpaceAfterUse', $data);
                    if ($fix === true) {
                        if ($length === 0) {
                            $phpcsFile->fixer->addContent($use, ' ');
                        } else {
                            $phpcsFile->fixer->replaceToken(($use + 1), ' ');
                        }
                    }
                }

                if ($tokens[($use - 1)]['code'] !== T_WHITESPACE) {
                    $length = 0;
                } else if ($tokens[($use - 1)]['content'] === "\t") {
                    $length = '\t';
                } else {
                    $length = strlen($tokens[($use - 1)]['content']);
                }

                if ($length !== 1) {
                    $error = 'Expected 1 space before USE keyword; found %s';
                    $data  = [$length];
                    $fix   = $phpcsFile->addFixableError($error, $use, 'SpaceBeforeUse', $data);
                    if ($fix === true) {
                        if ($length === 0) {
                            $phpcsFile->fixer->addContentBefore($use, ' ');
                        } else {
                            $phpcsFile->fixer->replaceToken(($use - 1), ' ');
                        }
                    }
                }
            }//end if
        }//end if

        if ($this->isMultiLineDeclaration($phpcsFile, $stackPtr, $openBracket, $tokens) === true) {
            $this->processMultiLineDeclaration($phpcsFile, $stackPtr, $tokens);
        } else {
            $this->processSingleLineDeclaration($phpcsFile, $stackPtr, $tokens);
        }

    }//end process()


    /**
     * Determine if this is a multi-line function declaration.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile   The file being scanned.
     * @param int                         $stackPtr    The position of the current token
     *                                                 in the stack passed in $tokens.
     * @param int                         $openBracket The position of the opening bracket
     *                                                 in the stack passed in $tokens.
     * @param array                       $tokens      The stack of tokens that make up
     *                                                 the file.
     *
     * @return void
     */
    public function isMultiLineDeclaration($phpcsFile, $stackPtr, $openBracket, $tokens)
    {
        $bracketsToCheck = [$stackPtr => $openBracket];

        // Closures may use the USE keyword and so be multi-line in this way.
        if ($tokens[$stackPtr]['code'] === T_CLOSURE) {
            $use = $phpcsFile->findNext(T_USE, ($tokens[$openBracket]['parenthesis_closer'] + 1), $tokens[$stackPtr]['scope_opener']);
            if ($use !== false) {
                $open = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1));
                if ($open !== false) {
                    $bracketsToCheck[$use] = $open;
                }
            }
        }

        foreach ($bracketsToCheck as $stackPtr => $openBracket) {
            // If the first argument is on a new line, this is a multi-line
            // function declaration, even if there is only one argument.
            $next = $phpcsFile->findNext(Tokens::$emptyTokens, ($openBracket + 1), null, true);
            if ($tokens[$next]['line'] !== $tokens[$stackPtr]['line']) {
                return true;
            }

            $closeBracket = $tokens[$openBracket]['parenthesis_closer'];

            $end = $phpcsFile->findEndOfStatement($openBracket + 1);
            while ($tokens[$end]['code'] === T_COMMA) {
                // If the next bit of code is not on the same line, this is a
                // multi-line function declaration.
                $next = $phpcsFile->findNext(Tokens::$emptyTokens, ($end + 1), $closeBracket, true);
                if ($next === false) {
                    continue(2);
                }

                if ($tokens[$next]['line'] !== $tokens[$end]['line']) {
                    return true;
                }

                $end = $phpcsFile->findEndOfStatement($next);
            }

            // We've reached the last argument, so see if the next content
            // (should be the close bracket) is also on the same line.
            $next = $phpcsFile->findNext(Tokens::$emptyTokens, ($end + 1), $closeBracket, true);
            if ($next !== false && $tokens[$next]['line'] !== $tokens[$end]['line']) {
                return true;
            }
        }//end foreach

        return false;

    }//end isMultiLineDeclaration()


    /**
     * Processes single-line declarations.
     *
     * Just uses the Generic BSD-Allman brace sniff.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     * @param array                       $tokens    The stack of tokens that make up
     *                                               the file.
     *
     * @return void
     */
    public function processSingleLineDeclaration($phpcsFile, $stackPtr, $tokens)
    {
        if ($tokens[$stackPtr]['code'] === T_CLOSURE) {
            $sniff = new OpeningFunctionBraceKernighanRitchieSniff();
        } else {
            $sniff = new OpeningFunctionBraceBsdAllmanSniff();
        }

        $sniff->checkClosures = true;
        $sniff->process($phpcsFile, $stackPtr);

    }//end processSingleLineDeclaration()


    /**
     * Processes multi-line declarations.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     * @param array                       $tokens    The stack of tokens that make up
     *                                               the file.
     *
     * @return void
     */
    public function processMultiLineDeclaration($phpcsFile, $stackPtr, $tokens)
    {
        // We need to work out how far indented the function
        // declaration itself is, so we can work out how far to
        // indent parameters.
        $functionIndent = 0;
        for ($i = ($stackPtr - 1); $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $tokens[$stackPtr]['line']) {
                $i++;
                break;
            }
        }

        if ($tokens[$i]['code'] === T_WHITESPACE) {
            $functionIndent = strlen($tokens[$i]['content']);
        }

        // The closing parenthesis must be on a new line, even
        // when checking abstract function definitions.
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];
        $prev         = $phpcsFile->findPrevious(
            T_WHITESPACE,
            ($closeBracket - 1),
            null,
            true
        );

        if ($tokens[$closeBracket]['line'] !== $tokens[$tokens[$closeBracket]['parenthesis_opener']]['line']) {
            if ($tokens[$prev]['line'] === $tokens[$closeBracket]['line']) {
                $error = 'The closing parenthesis of a multi-line function declaration must be on a new line';
                $fix   = $phpcsFile->addFixableError($error, $closeBracket, 'CloseBracketLine');
                if ($fix === true) {
                    $phpcsFile->fixer->addNewlineBefore($closeBracket);
                }
            }
        }

        // If this is a closure and is using a USE statement, the closing
        // parenthesis we need to look at from now on is the closing parenthesis
        // of the USE statement.
        if ($tokens[$stackPtr]['code'] === T_CLOSURE) {
            $use = $phpcsFile->findNext(T_USE, ($closeBracket + 1), $tokens[$stackPtr]['scope_opener']);
            if ($use !== false) {
                $open         = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1));
                $closeBracket = $tokens[$open]['parenthesis_closer'];

                $prev = $phpcsFile->findPrevious(
                    T_WHITESPACE,
                    ($closeBracket - 1),
                    null,
                    true
                );

                if ($tokens[$closeBracket]['line'] !== $tokens[$tokens[$closeBracket]['parenthesis_opener']]['line']) {
                    if ($tokens[$prev]['line'] === $tokens[$closeBracket]['line']) {
                        $error = 'The closing parenthesis of a multi-line use declaration must be on a new line';
                        $fix   = $phpcsFile->addFixableError($error, $closeBracket, 'UseCloseBracketLine');
                        if ($fix === true) {
                            $phpcsFile->fixer->addNewlineBefore($closeBracket);
                        }
                    }
                }
            }//end if
        }//end if

        // Each line between the parenthesis should be indented 4 spaces.
        $openBracket = $tokens[$stackPtr]['parenthesis_opener'];
        $lastLine    = $tokens[$openBracket]['line'];
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            if ($tokens[$i]['line'] !== $lastLine) {
                if ($i === $tokens[$stackPtr]['parenthesis_closer']
                    || ($tokens[$i]['code'] === T_WHITESPACE
                    && (($i + 1) === $closeBracket
                    || ($i + 1) === $tokens[$stackPtr]['parenthesis_closer']))
                ) {
                    // Closing braces need to be indented to the same level
                    // as the function.
                    $expectedIndent = $functionIndent;
                } else {
                    $expectedIndent = ($functionIndent + $this->indent);
                }

                // We changed lines, so this should be a whitespace indent token.
                if ($tokens[$i]['code'] !== T_WHITESPACE) {
                    $foundIndent = 0;
                } else if ($tokens[$i]['line'] !== $tokens[($i + 1)]['line']) {
                    // This is an empty line, so don't check the indent.
                    $foundIndent = $expectedIndent;

                    $error = 'Blank lines are not allowed in a multi-line function declaration';
                    $fix   = $phpcsFile->addFixableError($error, $i, 'EmptyLine');
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }
                } else {
                    $foundIndent = strlen($tokens[$i]['content']);
                }

                if ($expectedIndent !== $foundIndent) {
                    $error = 'Multi-line function declaration not indented correctly; expected %s spaces but found %s';
                    $data  = [
                        $expectedIndent,
                        $foundIndent,
                    ];

                    $fix = $phpcsFile->addFixableError($error, $i, 'Indent', $data);
                    if ($fix === true) {
                        $spaces = str_repeat(' ', $expectedIndent);
                        if ($foundIndent === 0) {
                            $phpcsFile->fixer->addContentBefore($i, $spaces);
                        } else {
                            $phpcsFile->fixer->replaceToken($i, $spaces);
                        }
                    }
                }

                $lastLine = $tokens[$i]['line'];
            }//end if

            if ($tokens[$i]['code'] === T_ARRAY || $tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                // Skip arrays as they have their own indentation rules.
                if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                    $i = $tokens[$i]['bracket_closer'];
                } else {
                    $i = $tokens[$i]['parenthesis_closer'];
                }

                $lastLine = $tokens[$i]['line'];
                continue;
            }
        }//end for

        if (isset($tokens[$stackPtr]['scope_opener']) === false) {
            return;
        }

        $openBracket = $tokens[$stackPtr]['parenthesis_opener'];
        $this->processBracket($phpcsFile, $openBracket, $tokens, 'function');

        if ($tokens[$stackPtr]['code'] !== T_CLOSURE) {
            return;
        }

        $use = $phpcsFile->findNext(T_USE, ($tokens[$stackPtr]['parenthesis_closer'] + 1), $tokens[$stackPtr]['scope_opener']);
        if ($use === false) {
            return;
        }

        $openBracket = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1), null);
        $this->processBracket($phpcsFile, $openBracket, $tokens, 'use');

        // Also check spacing.
        if ($tokens[($use - 1)]['code'] === T_WHITESPACE) {
            $gap = strlen($tokens[($use - 1)]['content']);
        } else {
            $gap = 0;
        }

    }//end processMultiLineDeclaration()


    /**
     * Processes the contents of a single set of brackets.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile   The file being scanned.
     * @param int                         $openBracket The position of the open bracket
     *                                                 in the stack passed in $tokens.
     * @param array                       $tokens      The stack of tokens that make up
     *                                                 the file.
     * @param string                      $type        The type of the token the brackets
     *                                                 belong to (function or use).
     *
     * @return void
     */
    public function processBracket($phpcsFile, $openBracket, $tokens, $type='function')
    {
        $errorPrefix = '';
        if ($type === 'use') {
            $errorPrefix = 'Use';
        }

        $closeBracket = $tokens[$openBracket]['parenthesis_closer'];

        // The open bracket should be the last thing on the line.
        if ($tokens[$openBracket]['line'] !== $tokens[$closeBracket]['line']) {
            $next = $phpcsFile->findNext(Tokens::$emptyTokens, ($openBracket + 1), null, true);
            if ($tokens[$next]['line'] !== ($tokens[$openBracket]['line'] + 1)) {
                $error = 'The first parameter of a multi-line '.$type.' declaration must be on the line after the opening bracket';
                $fix   = $phpcsFile->addFixableError($error, $next, $errorPrefix.'FirstParamSpacing');
                if ($fix === true) {
                    $phpcsFile->fixer->addNewline($openBracket);
                }
            }
        }

        // Each line between the brackets should contain a single parameter.
        $lastComma = null;
        for ($i = ($openBracket + 1); $i < $closeBracket; $i++) {
            // Skip brackets, like arrays, as they can contain commas.
            if (isset($tokens[$i]['bracket_opener']) === true) {
                $i = $tokens[$i]['bracket_closer'];
                continue;
            }

            if (isset($tokens[$i]['parenthesis_opener']) === true) {
                $i = $tokens[$i]['parenthesis_closer'];
                continue;
            }

            if ($tokens[$i]['code'] !== T_COMMA) {
                continue;
            }

            $next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), null, true);
            if ($tokens[$next]['line'] === $tokens[$i]['line']) {
                $error = 'Multi-line '.$type.' declarations must define one parameter per line';
                $fix   = $phpcsFile->addFixableError($error, $next, $errorPrefix.'OneParamPerLine');
                if ($fix === true) {
                    $phpcsFile->fixer->addNewline($i);
                }
            }
        }//end for

    }//end processBracket()


}//end class
