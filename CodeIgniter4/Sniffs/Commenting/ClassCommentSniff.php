<?php
/**
 * Class Comment
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Sniffs\Commenting;

use CodeIgniter4\Sniffs\Commenting\FileCommentSniff;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Class Comment Sniff
 *
 * Parses and verifies the doc comments for classes.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class ClassCommentSniff extends FileCommentSniff
{

    /**
     * Tags in correct order and related info.
     *
     * @var array
     */
    protected $tags = array(
                       '@package'    => array(
                                         'required'       => true,
                                         'allow_multiple' => false,
                                        ),
                       '@subpackage' => array(
                                         'required'       => false,
                                         'allow_multiple' => false,
                                        ),
                       '@category'   => array(
                                         'required'       => false,
                                         'allow_multiple' => false,
                                        ),
                       '@author'     => array(
                                         'required'       => false,
                                         'allow_multiple' => true,
                                        ),
                       '@copyright'  => array(
                                         'required'       => false,
                                         'allow_multiple' => true,
                                        ),
                       '@license'    => array(
                                         'required'       => false,
                                         'allow_multiple' => false,
                                        ),
                       '@link'       => array(
                                         'required'       => false,
                                         'allow_multiple' => true,
                                        ),
                       '@since'      => array(
                                         'required'       => false,
                                         'allow_multiple' => false,
                                        ),
                       '@version'    => array(
                                         'required'       => false,
                                         'allow_multiple' => false,
                                        ),
                       '@see'        => array(
                                         'required'       => false,
                                         'allow_multiple' => true,
                                        ),
                       '@deprecated' => array(
                                         'required'       => false,
                                         'allow_multiple' => false,
                                        ),
                      );


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
        $this->currentFile = $phpcsFile;

        $tokens    = $phpcsFile->getTokens();
        $type      = strtolower($tokens[$stackPtr]['content']);
        $errorData = array($type);

        $find   = Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;

        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT
        ) {
            $phpcsFile->addError('Missing class doc comment', $stackPtr, 'Missing');
            $phpcsFile->recordMetric($stackPtr, 'Class has doc comment', 'no');
            return;
        } else {
            $phpcsFile->recordMetric($stackPtr, 'Class has doc comment', 'yes');
        }

        // Try and determine if this is a file comment instead of a class comment.
        // We assume that if this is the first comment after the open PHP tag, then
        // it is most likely a file comment instead of a class comment.
        if ($tokens[$commentEnd]['code'] === T_DOC_COMMENT_CLOSE_TAG) {
            $start = ($tokens[$commentEnd]['comment_opener'] - 1);
        } else {
            $start = $phpcsFile->findPrevious(T_COMMENT, ($commentEnd - 1), null, true);
        }

        $prev = $phpcsFile->findPrevious(T_WHITESPACE, $start, null, true);
        if ($tokens[$prev]['code'] === T_OPEN_TAG) {
            $prevOpen = $phpcsFile->findPrevious(T_OPEN_TAG, ($prev - 1));
            if ($prevOpen === false) {
                // This is a comment directly after the first open tag,
                // so probably a file comment.
                $phpcsFile->addError('Missing class doc comment', $stackPtr, 'Missing');
                return;
            }
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError('You must use "/**" style comments for a class comment', $stackPtr, 'WrongStyle');
            return;
        }

        // Check each tag.
        $this->processTags($phpcsFile, $stackPtr, $tokens[$commentEnd]['comment_opener']);

    }//end process()


}//end class
