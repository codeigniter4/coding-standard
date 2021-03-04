<?php
/**
 * Array Declaration
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Sniffs\Arrays;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use CodeIgniter4\Util\Common;

/**
 * Array Declaration Sniff
 *
 * Check array declaration, indentation, alignment and that the last item
 * has a trailing comma.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class ArrayDeclarationSniff implements Sniff
{

    /**
     * The --tab-width CLI value that is being used.
     *
     * @var integer
     */
    private $tabWidth = null;

    /**
     * Indent unit.
     *
     * Either space or tab.
     *
     * @var string
     */
    private $indentUnit = 'space';


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_ARRAY,
            T_OPEN_SHORT_ARRAY,
        ];

    }//end register()


    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being checked.
     * @param int                         $stackPtr  The position of the current token in
     *                                               the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {

        if ($this->tabWidth === null) {
            if (isset($phpcsFile->config->tabWidth) === false || $phpcsFile->config->tabWidth === 0) {
                // We have no idea how wide tabs are, so assume 4 spaces for fixing.
                // It shouldn't really matter because alignment and spacing sniffs
                // elsewhere in the standard should fix things up.
                $this->tabWidth = 4;
            } else {
                $this->tabWidth   = $phpcsFile->config->tabWidth;
                $this->indentUnit = 'tab';
            }
        }

        $tokens = $phpcsFile->getTokens();

        // First make sure all arrays use short array syntax, this makes fixing much easier.
        if ($tokens[$stackPtr]['code'] === T_ARRAY) {
            $error = 'Short array syntax must be used to define arrays';
            $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'FoundLongArray');

            if ($fix === true) {
                $tokens = $phpcsFile->getTokens();
                $opener = $tokens[$stackPtr]['parenthesis_opener'];
                $closer = $tokens[$stackPtr]['parenthesis_closer'];

                $phpcsFile->fixer->beginChangeset();

                if ($opener === null) {
                    $phpcsFile->fixer->replaceToken($stackPtr, '[]');
                } else {
                    $phpcsFile->fixer->replaceToken($stackPtr, '');
                    $phpcsFile->fixer->replaceToken($opener, '[');
                    $phpcsFile->fixer->replaceToken($closer, ']');
                }

                $phpcsFile->fixer->endChangeset();
            }
        }//end if

        if ($tokens[$stackPtr]['code'] === T_ARRAY) {
            $arrayStart = $tokens[$stackPtr]['parenthesis_opener'];
            if (isset($tokens[$arrayStart]['parenthesis_closer']) === false) {
                return;
            }

            $arrayEnd = $tokens[$arrayStart]['parenthesis_closer'];
        } else {
            $phpcsFile->recordMetric($stackPtr, 'Short array syntax used', 'yes');
            $arrayStart = $stackPtr;
            $arrayEnd   = $tokens[$stackPtr]['bracket_closer'];
        }//end if

        // Check for empty arrays.
        $content = $phpcsFile->findNext(T_WHITESPACE, ($arrayStart + 1), ($arrayEnd + 1), true);
        if ($content === $arrayEnd) {
            // Empty array, but if the brackets aren't together, there's a problem.
            if (($arrayEnd - $arrayStart) !== 1) {
                $error = 'Empty array declaration must have no space between the parentheses';
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceInEmptyArray');

                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                }
            }

            // We can return here because there is nothing else to check. All code
            // below can assume that the array is not empty.
            return;
        }

        if ($tokens[$arrayStart]['line'] === $tokens[$arrayEnd]['line']) {
            $this->processSingleLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd);
        } else {
            $this->processMultiLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd);
        }

    }//end process()


    /**
     * Processes a single-line array definition.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile  The current file being checked.
     * @param int                         $stackPtr   The position of the current token
     *                                                in the stack passed in $tokens.
     * @param int                         $arrayStart The token that starts the array definition.
     * @param int                         $arrayEnd   The token that ends the array definition.
     *
     * @return void
     */
    public function processSingleLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd)
    {
        $tokens = $phpcsFile->getTokens();

        // Check if there are multiple values. If so, then it has to be multiple lines
        // unless it is contained inside a function call or condition.
        $valueCount = 0;
        $commas     = [];
        for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
            // Skip bracketed statements, like function calls.
            if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                $i = $tokens[$i]['parenthesis_closer'];
                continue;
            }

            if ($tokens[$i]['code'] === T_COMMA) {
                // Before counting this comma, make sure we are not
                // at the end of the array.
                $next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), $arrayEnd, true);
                if ($next !== false) {
                    $valueCount++;
                    $commas[] = $i;
                } else {
                    // There is a comma at the end of a single line array.
                    $error = 'Comma not allowed after last value in single-line array declaration';
                    $fix   = $phpcsFile->addFixableError($error, $i, 'CommaAfterLast');
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }
                }
            }
        }//end for

        // Now check each of the double arrows (if any).
        $nextArrow = $arrayStart;
        while (($nextArrow = $phpcsFile->findNext(T_DOUBLE_ARROW, ($nextArrow + 1), $arrayEnd)) !== false) {
            if ($tokens[($nextArrow - 1)]['code'] !== T_WHITESPACE) {
                $content = $tokens[($nextArrow - 1)]['content'];
                $error   = 'Expected 1 space between "%s" and double arrow; 0 found';
                $data    = [$content];
                $fix     = $phpcsFile->addFixableError($error, $nextArrow, 'NoSpaceBeforeDoubleArrow', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->addContentBefore($nextArrow, ' ');
                }
            } else {
                $spaceLength = $tokens[($nextArrow - 1)]['length'];
                if ($spaceLength !== 1) {
                    $content = $tokens[($nextArrow - 2)]['content'];
                    $error   = 'Expected 1 space between "%s" and double arrow; %s found';
                    $data    = [
                        $content,
                        $spaceLength,
                    ];

                    $fix = $phpcsFile->addFixableError($error, $nextArrow, 'SpaceBeforeDoubleArrow', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($nextArrow - 1), ' ');
                    }
                }
            }//end if

            if ($tokens[($nextArrow + 1)]['code'] !== T_WHITESPACE) {
                $content = $tokens[($nextArrow + 1)]['content'];
                $error   = 'Expected 1 space between double arrow and "%s"; 0 found';
                $data    = [$content];
                $fix     = $phpcsFile->addFixableError($error, $nextArrow, 'NoSpaceAfterDoubleArrow', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->addContent($nextArrow, ' ');
                }
            } else {
                $spaceLength = $tokens[($nextArrow + 1)]['length'];
                if ($spaceLength !== 1) {
                    $content = $tokens[($nextArrow + 2)]['content'];
                    $error   = 'Expected 1 space between double arrow and "%s"; %s found';
                    $data    = [
                        $content,
                        $spaceLength,
                    ];

                    $fix = $phpcsFile->addFixableError($error, $nextArrow, 'SpaceAfterDoubleArrow', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($nextArrow + 1), ' ');
                    }
                }
            }//end if
        }//end while

        if ($valueCount > 0) {
            $conditionCheck = $phpcsFile->findPrevious([T_OPEN_PARENTHESIS, T_SEMICOLON], ($stackPtr - 1), null, false);

            if ($conditionCheck === false
                || $tokens[$conditionCheck]['line'] !== $tokens[$stackPtr]['line']
            ) {
                $error = 'Array with multiple values cannot be declared on a single line';
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SingleLineNotAllowed');
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    $phpcsFile->fixer->addNewline($arrayStart);
                    $phpcsFile->fixer->addNewlineBefore($arrayEnd);
                    $phpcsFile->fixer->endChangeset();
                }

                return;
            }

            // We have a multiple value array that is inside a condition or
            // function. Check its spacing is correct.
            foreach ($commas as $comma) {
                if ($tokens[($comma + 1)]['code'] !== T_WHITESPACE) {
                    $content = $tokens[($comma + 1)]['content'];
                    $error   = 'Expected 1 space between comma and "%s"; 0 found';
                    $data    = [$content];
                    $fix     = $phpcsFile->addFixableError($error, $comma, 'NoSpaceAfterComma', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->addContent($comma, ' ');
                    }
                } else {
                    $spaceLength = $tokens[($comma + 1)]['length'];
                    if ($spaceLength !== 1) {
                        $content = $tokens[($comma + 2)]['content'];
                        $error   = 'Expected 1 space between comma and "%s"; %s found';
                        $data    = [
                            $content,
                            $spaceLength,
                        ];

                        $fix = $phpcsFile->addFixableError($error, $comma, 'SpaceAfterComma', $data);
                        if ($fix === true) {
                            $phpcsFile->fixer->replaceToken(($comma + 1), ' ');
                        }
                    }
                }//end if

                if ($tokens[($comma - 1)]['code'] === T_WHITESPACE) {
                    $content     = $tokens[($comma - 2)]['content'];
                    $spaceLength = $tokens[($comma - 1)]['length'];
                    $error       = 'Expected 0 spaces between "%s" and comma; %s found';
                    $data        = [
                        $content,
                        $spaceLength,
                    ];

                    $fix = $phpcsFile->addFixableError($error, $comma, 'SpaceBeforeComma', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($comma - 1), '');
                    }
                }
            }//end foreach
        }//end if

    }//end processSingleLineArray()


    /**
     * Processes a multi-line array definition.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile  The current file being checked.
     * @param int                         $stackPtr   The position of the current token
     *                                                in the stack passed in $tokens.
     * @param int                         $arrayStart The token that starts the array definition.
     * @param int                         $arrayEnd   The token that ends the array definition.
     *
     * @return void
     */
    public function processMultiLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd)
    {
        $tokens       = $phpcsFile->getTokens();
        $keywordStart = $tokens[$stackPtr]['column'];

        $prevNonWhitespaceToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);

        // Find where this array should be indented from.
        switch ($tokens[$prevNonWhitespaceToken]['code']) {
        case T_EQUAL:
        case T_OPEN_PARENTHESIS:
            // It's "=", "(" or "return".
            $starts = [
                T_VARIABLE,
                T_VAR,
                T_PUBLIC,
                T_PRIVATE,
                T_PROTECTED,
                T_ARRAY_CAST,
                T_UNSET_CAST,
                T_OBJECT_CAST,
                T_STATIC,
                T_CONST,
                T_RETURN,
                T_OBJECT_OPERATOR,
            ];

            $firstOnLine = $phpcsFile->findFirstOnLine($starts, $prevNonWhitespaceToken);
            $indentStart = $firstOnLine;
            break;
        case T_DOUBLE_ARROW:
            // It's an array in an array "=> []".
            $indentStart = $phpcsFile->findPrevious(T_WHITESPACE, ($prevNonWhitespaceToken - 1), null, true);
            break;
        case T_RETURN:
            $indentStart = $prevNonWhitespaceToken;
            break;
        case T_COMMENT:
        case T_OPEN_SHORT_ARRAY:
            // It's an array in an array "[[]]" or the end of an array line "[],".
            $indentStart = $stackPtr;
            break;
        case T_COMMA:
            // The end of an array line "[],".
            // Argument in a function "$item->save($data, [...], ...)".
            $starts = [
                T_VARIABLE,
                T_VAR,
                T_PUBLIC,
                T_PRIVATE,
                T_PROTECTED,
                T_ARRAY_CAST,
                T_UNSET_CAST,
                T_OBJECT_CAST,
                T_STATIC,
                T_CONST,
                T_RETURN,
                T_OBJECT_OPERATOR,
                T_CLOSE_SHORT_ARRAY,
                T_CONSTANT_ENCAPSED_STRING,
            ];

            $firstOnLine = $phpcsFile->findFirstOnLine($starts, $prevNonWhitespaceToken);
            $indentStart = $firstOnLine;
            break;
        default:
            // Nothing expected preceded this so leave ptr where it is and
            // it should get picked in a future pass.
            $indentStart = $stackPtr;
        }//end switch

        // If this is the first argument in a function ensure the bracket to be right after the parenthesis. eg "array_combine([".
        if ($tokens[$prevNonWhitespaceToken]['code'] === T_OPEN_PARENTHESIS && $tokens[$stackPtr]['code'] === T_OPEN_SHORT_ARRAY) {
            if ($tokens[$stackPtr]['line'] > $tokens[$prevNonWhitespaceToken]['line']) {
                $error = 'Array openening bracket should be after function open parenthesis "(["';
                $data  = [];
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'ShortArrayOpenWrongLine', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($prevNonWhitespaceToken + 1); $i < $stackPtr; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                }
            }
        }

        // Get content before closing array bracket/brace.
        $lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($arrayEnd - 1), $arrayStart, true);

        // Check for ) after last Array end.
        $afterCloser = $phpcsFile->findNext(T_WHITESPACE, ($arrayEnd + 1), null, true);
        if ($tokens[$afterCloser]['code'] === T_CLOSE_PARENTHESIS) {
            if ($tokens[$afterCloser]['column'] !== ($tokens[$arrayEnd]['column'] + 1)) {
                $error = 'Closing parenthesis should be after array closing bracket "])"';
                $data  = [];
                $fix   = $phpcsFile->addFixableError($error, $afterCloser, 'CloseBracketAfterArrayBracket');
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($arrayEnd + 1); $i < $afterCloser; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                }
            }
        }

        // Check the closing bracket is on a new line.
        if ($tokens[$lastContent]['line'] === $tokens[$arrayEnd]['line']) {
            $error = 'Closing parenthesis of array declaration must be on a new line';
            $fix   = $phpcsFile->addFixableError($error, $arrayEnd, 'CloseArrayBraceNewLine');
            if ($fix === true) {
                $phpcsFile->fixer->addNewlineBefore($arrayEnd);
            }
        } else if ($tokens[$arrayEnd]['column'] !== $tokens[$indentStart]['column']) {
            // Check the closing bracket with the array declarer or if an
            // array in an array the previous array key.
            if ($tokens[$indentStart]['column'] > 1) {
                $expected = ($tokens[$indentStart]['column'] - 1);
            } else {
                $expected = $this->tabWidth;
            }

            $found = ($tokens[$arrayEnd]['column'] - 1);
            $error = 'Closing parenthesis not aligned correctly; expected %s %s but found %s';
            $data  = [
                ($expected / $this->tabWidth),
                Common::pluralize($this->indentUnit, ($expected / $this->tabWidth)),
                ($found / $this->tabWidth),
            ];

            $fix = $phpcsFile->addFixableError($error, $arrayEnd, 'CloseArrayBraceNotAligned', $data);
            if ($fix === true) {
                if ($found === 0) {
                    $phpcsFile->fixer->addContent(($arrayEnd - 1), str_repeat(' ', $expected));
                } else {
                    $phpcsFile->fixer->replaceToken(($arrayEnd - 1), str_repeat(' ', $expected));
                }
            }
        }//end if

        $keyUsed    = false;
        $singleUsed = false;
        $indices    = [];
        $maxLength  = 0;

        if ($tokens[$stackPtr]['code'] === T_ARRAY) {
            $lastToken = $tokens[$stackPtr]['parenthesis_opener'];
        } else {
            $lastToken = $stackPtr;
        }

        // Find all the double arrows that reside in this scope.
        for ($nextToken = ($stackPtr + 1); $nextToken < $arrayEnd; $nextToken++) {
            // Skip bracketed statements, like function calls.
            if ($tokens[$nextToken]['code'] === T_OPEN_PARENTHESIS
                && (isset($tokens[$nextToken]['parenthesis_owner']) === false
                || $tokens[$nextToken]['parenthesis_owner'] !== $stackPtr)
            ) {
                $nextToken = $tokens[$nextToken]['parenthesis_closer'];
                continue;
            }

            if ($tokens[$nextToken]['code'] === T_ARRAY
                || $tokens[$nextToken]['code'] === T_OPEN_SHORT_ARRAY
                || $tokens[$nextToken]['code'] === T_CLOSURE
            ) {
                // Let subsequent calls of this test handle nested arrays.
                if ($tokens[$lastToken]['code'] !== T_DOUBLE_ARROW) {
                    $indices[] = ['value' => $nextToken];
                    $lastToken = $nextToken;
                }

                if ($tokens[$nextToken]['code'] === T_ARRAY) {
                    $nextToken = $tokens[$tokens[$nextToken]['parenthesis_opener']]['parenthesis_closer'];
                } else if ($tokens[$nextToken]['code'] === T_OPEN_SHORT_ARRAY) {
                    $nextToken = $tokens[$nextToken]['bracket_closer'];
                } else {
                    $nextToken = $tokens[$nextToken]['scope_closer'];
                }

                $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true);
                if ($tokens[$nextToken]['code'] !== T_COMMA) {
                    $nextToken--;
                } else {
                    $lastToken = $nextToken;
                }

                continue;
            }//end if

            if ($tokens[$nextToken]['code'] !== T_DOUBLE_ARROW
                && $tokens[$nextToken]['code'] !== T_COMMA
            ) {
                continue;
            }

            $currentEntry = [];

            if ($tokens[$nextToken]['code'] === T_COMMA) {
                $stackPtrCount = 0;
                if (isset($tokens[$stackPtr]['nested_parenthesis']) === true) {
                    $stackPtrCount = count($tokens[$stackPtr]['nested_parenthesis']);
                }

                $commaCount = 0;
                if (isset($tokens[$nextToken]['nested_parenthesis']) === true) {
                    $commaCount = count($tokens[$nextToken]['nested_parenthesis']);
                    if ($tokens[$stackPtr]['code'] === T_ARRAY) {
                        // Remove parenthesis that are used to define the array.
                        $commaCount--;
                    }
                }

                if ($commaCount > $stackPtrCount) {
                    // This comma is inside more parenthesis than the ARRAY keyword,
                    // then there it is actually a comma used to separate arguments
                    // in a function call.
                    continue;
                }

                if ($keyUsed === true && $tokens[$lastToken]['code'] === T_COMMA) {
                    $error = 'No key specified for array entry; first entry specifies key';
                    $phpcsFile->addError($error, $nextToken, 'NoKeySpecified');
                    return;
                }

                if ($keyUsed === false) {
                    if ($tokens[($nextToken - 1)]['code'] === T_WHITESPACE) {
                        $content = $tokens[($nextToken - 2)]['content'];
                        if ($tokens[($nextToken - 1)]['content'] === $phpcsFile->eolChar) {
                            $spaceLength = 'newline';
                        } else {
                            $spaceLength = $tokens[($nextToken - 1)]['length'];
                        }

                        $error = 'Expected 0 spaces between "%s" and comma; %s found';
                        $data  = [
                            $content,
                            $spaceLength,
                        ];

                        $fix = $phpcsFile->addFixableError($error, $nextToken, 'SpaceBeforeComma', $data);
                        if ($fix === true) {
                            $phpcsFile->fixer->replaceToken(($nextToken - 1), '');
                        }
                    }

                    $valueContent = $phpcsFile->findNext(
                        Tokens::$emptyTokens,
                        ($lastToken + 1),
                        $nextToken,
                        true
                    );

                    $indices[]  = ['value' => $valueContent];
                    $singleUsed = true;
                }//end if

                $lastToken = $nextToken;
                continue;
            }//end if

            if ($tokens[$nextToken]['code'] === T_DOUBLE_ARROW) {
                if ($singleUsed === true) {
                    $error = 'Key specified for array entry; first entry has no key';
                    $phpcsFile->addError($error, $nextToken, 'KeySpecified');
                    return;
                }

                $currentEntry['arrow'] = $nextToken;
                $keyUsed = true;

                // Find the start of index that uses this double arrow.
                $indexEnd   = $phpcsFile->findPrevious(T_WHITESPACE, ($nextToken - 1), $arrayStart, true);
                $indexStart = $phpcsFile->findStartOfStatement($indexEnd);

                if ($indexStart === $indexEnd) {
                    $currentEntry['index']         = $indexEnd;
                    $currentEntry['index_content'] = $tokens[$indexEnd]['content'];
                } else {
                    $currentEntry['index']         = $indexStart;
                    $currentEntry['index_content'] = $phpcsFile->getTokensAsString($indexStart, ($indexEnd - $indexStart + 1));
                }

                $indexLength = mb_strlen($currentEntry['index_content']);
                if ($maxLength < $indexLength) {
                    $maxLength = $indexLength;
                }

                // Find the value of this index.
                $nextContent = $phpcsFile->findNext(
                    Tokens::$emptyTokens,
                    ($nextToken + 1),
                    $arrayEnd,
                    true
                );

                if ($nextContent === false) {
                    break;
                }

                $currentEntry['value'] = $nextContent;
                $indices[] = $currentEntry;
                $lastToken = $nextToken;
            }//end if
        }//end for

        /*
            This section checks for arrays that don't specify keys.

            Arrays such as:
               array(
                'aaa',
                'bbb',
                'd',
               );
        */

        if ($keyUsed === false && empty($indices) === false) {
            $count     = count($indices);
            $lastIndex = $indices[($count - 1)]['value'];

            $trailingContent = $phpcsFile->findPrevious(
                Tokens::$emptyTokens,
                ($arrayEnd - 1),
                $lastIndex,
                true
            );

            if ($tokens[$trailingContent]['code'] !== T_COMMA) {
                $phpcsFile->recordMetric($stackPtr, 'Array end comma', 'no');
                $error = 'Comma required after last value in array declaration';
                $fix   = $phpcsFile->addFixableError($error, $trailingContent, 'NoCommaAfterLast');
                if ($fix === true) {
                    $phpcsFile->fixer->addContent($trailingContent, ',');
                }
            } else {
                $phpcsFile->recordMetric($stackPtr, 'Array end comma', 'yes');
            }

            $lastValueLine = false;
            foreach ($indices as $value) {
                if (empty($value['value']) === true) {
                    // Array was malformed and we couldn't figure out
                    // the array value correctly, so we have to ignore it.
                    // Other parts of this sniff will correct the error.
                    continue;
                }

                if ($lastValueLine !== false && $tokens[$value['value']]['line'] === $lastValueLine) {
                    $error = 'Each value in a multi-line array must be on a new line';
                    $fix   = $phpcsFile->addFixableError($error, $value['value'], 'ValueNoNewline');
                    if ($fix === true) {
                        if ($tokens[($value['value'] - 1)]['code'] === T_WHITESPACE) {
                            $phpcsFile->fixer->replaceToken(($value['value'] - 1), '');
                        }

                        $phpcsFile->fixer->addNewlineBefore($value['value']);
                    }
                } else if ($tokens[($value['value'] - 1)]['code'] === T_WHITESPACE) {
                    // Expected indent.
                    if ($tokens[$indentStart]['column'] > 1) {
                        $expected = ($tokens[$indentStart]['column'] + $this->tabWidth - 1);
                    } else {
                        $expected = $this->tabWidth;
                    }

                    $first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $value['value'], true);
                    $found = ($tokens[$first]['column'] - 1);

                    if ($found !== $expected) {
                        $error = 'Array value not aligned correctly; expected %s %s but found %s';
                        $data  = [
                            ($expected / $this->tabWidth),
                            Common::pluralize($this->indentUnit, ($expected / $this->tabWidth)),
                            ($found / $this->tabWidth),
                        ];

                        $fix = $phpcsFile->addFixableError($error, $value['value'], 'ValueNotAligned', $data);
                        if ($fix === true) {
                            if ($found === 0) {
                                $phpcsFile->fixer->addContent(($value['value'] - 1), str_repeat(' ', $expected));
                            } else {
                                $phpcsFile->fixer->replaceToken(($value['value'] - 1), str_repeat(' ', $expected));
                            }
                        }
                    }
                }//end if

                $lastValueLine = $tokens[$value['value']]['line'];
            }//end foreach
        }//end if

        /*
            Below the actual indentation of the array is checked.
            Errors will be thrown when a key is not aligned, when
            a double arrow is not aligned, and when a value is not
            aligned correctly.
            If an error is found in one of the above areas, then errors
            are not reported for the rest of the line to avoid reporting
            spaces and columns incorrectly. Often fixing the first
            problem will fix the other 2 anyway.

            For example:

            $a = array(
                'index'  => '2',
            );

            or

            $a = [
                'index'  => '2',
            ];

            In this array, the double arrow is indented too far, but this
            will also cause an error in the value's alignment. If the arrow were
            to be moved back one space however, then both errors would be fixed.
        */

        $numValues = count($indices);

        // Expected indent.
        if ($tokens[$indentStart]['column'] > 1) {
            $indicesStart = ($tokens[$indentStart]['column'] + $this->tabWidth);
        } else {
            $indicesStart = $this->tabWidth;
        }

        $arrowStart    = ($indicesStart + $maxLength + 1);
        $valueStart    = ($arrowStart + 3);
        $indexLine     = $tokens[$stackPtr]['line'];
        $lastIndexLine = null;
        foreach ($indices as $index) {
            if (isset($index['index']) === false) {
                // Array value only.
                if ($tokens[$index['value']]['line'] === $tokens[$stackPtr]['line'] && $numValues > 1) {
                    $error = 'The first value in a multi-value array must be on a new line';
                    $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'FirstValueNoNewline');
                    if ($fix === true) {
                        $phpcsFile->fixer->addNewlineBefore($index['value']);
                    }
                }

                continue;
            }

            $lastIndexLine = $indexLine;
            $indexLine     = $tokens[$index['index']]['line'];

            if ($indexLine === $tokens[$stackPtr]['line']) {
                $error = 'The first index in a multi-value array must be on a new line';
                $fix   = $phpcsFile->addFixableError($error, $index['index'], 'FirstIndexNoNewline');
                if ($fix === true) {
                    $phpcsFile->fixer->addNewlineBefore($index['index']);
                }

                continue;
            }

            if ($indexLine === $lastIndexLine) {
                $error = 'Each index in a multi-line array must be on a new line';
                $fix   = $phpcsFile->addFixableError($error, $index['index'], 'IndexNoNewline');
                if ($fix === true) {
                    if ($tokens[($index['index'] - 1)]['code'] === T_WHITESPACE) {
                        $phpcsFile->fixer->replaceToken(($index['index'] - 1), '');
                    }

                    $phpcsFile->fixer->addNewlineBefore($index['index']);
                }

                continue;
            }

            if ($tokens[$index['index']]['column'] !== $indicesStart) {
                $expected = ($indicesStart - 1);

                $found = ($tokens[$index['index']]['column'] - 1);
                $error = 'Array key not aligned correctly; expected %s %s but found %s';
                $data  = [
                    ($expected / $this->tabWidth),
                    Common::pluralize($this->indentUnit, ($expected / $this->tabWidth)),
                    ($found / $this->tabWidth),
                ];

                $fix = $phpcsFile->addFixableError($error, $index['index'], 'KeyNotAligned', $data);
                if ($fix === true) {
                    if ($found === 0) {
                        $phpcsFile->fixer->addContent(($index['index'] - 1), str_repeat(' ', $expected));
                    } else {
                        $phpcsFile->fixer->replaceToken(($index['index'] - 1), str_repeat(' ', $expected));
                    }
                }

                continue;
            }//end if

            if ($tokens[$index['arrow']]['column'] !== $arrowStart) {
                $expected = ($arrowStart - (mb_strlen($index['index_content']) + $tokens[$index['index']]['column']));

                $found = ($tokens[$index['arrow']]['column'] - (mb_strlen($index['index_content']) + $tokens[$index['index']]['column']));
                $error = 'Array double arrow not aligned correctly; expected %s %s but found %s';
                $data  = [
                    $expected,
                    Common::pluralize('space', $expected),
                    $found,
                ];

                $fix = $phpcsFile->addFixableError($error, $index['arrow'], 'DoubleArrowNotAligned', $data);
                if ($fix === true) {
                    if ($found === 0) {
                        $phpcsFile->fixer->addContent(($index['arrow'] - 1), str_repeat(' ', $expected));
                    } else {
                        $phpcsFile->fixer->replaceToken(($index['arrow'] - 1), str_repeat(' ', $expected));
                    }
                }

                continue;
            }//end if

            if ($tokens[$index['value']]['column'] !== $valueStart) {
                $expected = ($valueStart - ($tokens[$index['arrow']]['length'] + $tokens[$index['arrow']]['column']));
                $found    = ($tokens[$index['value']]['column'] - ($tokens[$index['arrow']]['length'] + $tokens[$index['arrow']]['column']));
                if ($found < 0) {
                    $found = 'newline';
                }

                $error = 'Array value not aligned correctly; expected %s %s but found %s';
                $data  = [
                    $expected,
                    Common::pluralize('space', $expected),
                    $found,
                ];

                $fix = $phpcsFile->addFixableError($error, $index['arrow'], 'ValueNotAligned', $data);
                if ($fix === true) {
                    if ($found === 'newline') {
                        $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($index['value'] - 1), null, true);
                        $phpcsFile->fixer->beginChangeset();
                        for ($i = ($prev + 1); $i < $index['value']; $i++) {
                            $phpcsFile->fixer->replaceToken($i, '');
                        }

                        $phpcsFile->fixer->replaceToken(($index['value'] - 1), str_repeat(' ', $expected));
                        $phpcsFile->fixer->endChangeset();
                    } else if ($found === 0) {
                        $phpcsFile->fixer->addContent(($index['value'] - 1), str_repeat(' ', $expected));
                    } else {
                        $phpcsFile->fixer->replaceToken(($index['value'] - 1), str_repeat(' ', $expected));
                    }
                }
            }//end if

            // Check each line ends in a comma.
            $valueLine = $tokens[$index['value']]['line'];
            $nextComma = false;
            for ($i = $index['value']; $i < $arrayEnd; $i++) {
                // Skip bracketed statements, like function calls.
                if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                    $i         = $tokens[$i]['parenthesis_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_ARRAY) {
                    $i         = $tokens[$tokens[$i]['parenthesis_opener']]['parenthesis_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                // Skip to the end of multi-line strings.
                if (isset(Tokens::$stringTokens[$tokens[$i]['code']]) === true) {
                    $i = $phpcsFile->findNext($tokens[$i]['code'], ($i + 1), null, true);
                    $i--;
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_OPEN_SHORT_ARRAY) {
                    $i         = $tokens[$i]['bracket_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_CLOSURE) {
                    $i         = $tokens[$i]['scope_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if ($tokens[$i]['code'] === T_COMMA) {
                    $nextComma = $i;
                    break;
                }
            }//end for

            if ($nextComma === false || ($tokens[$nextComma]['line'] !== $valueLine)) {
                $error = 'Each line in an array declaration must end in a comma';
                $fix   = $phpcsFile->addFixableError($error, $index['value'], 'NoComma');
                if ($fix === true) {
                    // Find the end of the line and put a comma there.
                    for ($i = ($index['value'] + 1); $i < $arrayEnd; $i++) {
                        if ($tokens[$i]['line'] > $valueLine) {
                            break;
                        }
                    }

                    $phpcsFile->fixer->addContentBefore(($i - 1), ',');
                }
            }//end if

            // Check that there is no space before the comma.
            if ($nextComma !== false && $tokens[($nextComma - 1)]['code'] === T_WHITESPACE) {
                $content     = $tokens[($nextComma - 2)]['content'];
                $spaceLength = $tokens[($nextComma - 1)]['length'];
                $error       = 'Expected 0 spaces between "%s" and comma; %s found';
                $data        = [
                    $content,
                    $spaceLength,
                ];

                $fix = $phpcsFile->addFixableError($error, $nextComma, 'SpaceBeforeComma', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->replaceToken(($nextComma - 1), '');
                }
            }
        }//end foreach

    }//end processMultiLineArray()


}//end class
