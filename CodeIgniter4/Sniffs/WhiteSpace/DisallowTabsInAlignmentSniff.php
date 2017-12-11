<?php
/**
 * Disallow Tabs In Alignment
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
 * Disallow Tabs In Alignment Sniff
 *
 * Checks for use of tabs after indendation.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class DisallowTabsInAlignmentSniff implements Sniff
{

    /**
     * The --tab-width CLI value that is being used.
     *
     * @var integer
     */
    private $tabWidth = null;


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
     * @param \PHP_CodeSniffer\Files\File $phpcsFile All the tokens found in the document.
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
                $this->tabWidth = $phpcsFile->config->tabWidth;
            }
        }

        $checkTokens = array(
                        T_WHITESPACE             => true,
                        T_INLINE_HTML            => true,
                        T_DOC_COMMENT_WHITESPACE => true,
                       );

        $tokens = $phpcsFile->getTokens();

        for ($i = ($stackPtr); $i < $phpcsFile->numTokens; $i++) {
            // Skip whitespace at the start of a new line and tokens not consdered white space.
            if ($tokens[$i]['column'] === 1 || isset($checkTokens[$tokens[$i]['code']]) === false) {
                continue;
            }

            // If tabs are being converted to spaces by the tokeniser, the
            // original content should be checked instead of the converted content.
            if (isset($tokens[$i]['orig_content']) === true) {
                $content = $tokens[$i]['orig_content'];
            } else {
                $content = $tokens[$i]['content'];
            }

            if (strpos($content, "\t") !== false) {
                // Try to maintain intended alignment by counting tabs and spaces.
                $countTabs   = substr_count($content, "\t");
                $countSpaces = substr_count($content, " ");

                if ($countTabs === 1) {
                    $tabsPlural = '';
                } else {
                    $tabsPlural = 's';
                }

                if ($countSpaces === 1) {
                    $spacesPlural = '';
                } else {
                    $spacesPlural = 's';
                }

                $data  = array(
                          $countTabs,
                          $tabsPlural,
                          $countSpaces,
                          $spacesPlural,
                         );
                $error = 'Spaces must be used for alignment; %s tab%s and %s space%s found';

                // The fix might make some lines misaligned if the tab didn't fill the number
                // of 'tabWidth' spaces, other alignment and spacing sniffs should fix that.
                $fix = $phpcsFile->addFixableError($error, $i, 'TabsUsedInAlignment', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    $spaces = str_repeat(' ', (($this->tabWidth * $countTabs) + $countSpaces));
                    $phpcsFile->fixer->replaceToken($i, $spaces);
                    $phpcsFile->fixer->endChangeset();
                }//end if
            }//end if
        }//end for

        // Ignore the rest of the file.
        return ($phpcsFile->numTokens + 1);

    }//end process()


}//end class
