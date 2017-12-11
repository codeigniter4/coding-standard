<?php
/**
 * File Comment
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * File Comment Sniff
 *
 * Check a doc comment exists.
 * Check the order of the tags.
 * Check the indentation of each tag.
 * Check required and optional tags and the format of their content.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class FileCommentSniff implements Sniff
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
                                         'required'       => true,
                                         'allow_multiple' => true,
                                        ),
                       '@copyright'  => array(
                                         'required'       => false,
                                         'allow_multiple' => true,
                                        ),
                       '@license'    => array(
                                         'required'       => true,
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
        return array(T_OPEN_TAG);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token
     *                        in the stack passed in $tokens.
     *
     * @return int
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Find the next non whitespace token.
        $commentStart = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);

        // Allow namespace at top of file.
        if ($tokens[$commentStart]['code'] === T_NAMESPACE) {
            $semicolon    = $phpcsFile->findNext(T_SEMICOLON, ($commentStart + 1));
            $commentStart = $phpcsFile->findNext(T_WHITESPACE, ($semicolon + 1), null, true);
        }

        // Ignore vim header.
        if ($tokens[$commentStart]['code'] === T_COMMENT) {
            if (strstr($tokens[$commentStart]['content'], 'vim:') !== false) {
                $commentStart = $phpcsFile->findNext(
                    T_WHITESPACE,
                    ($commentStart + 1),
                    null,
                    true
                );
            }
        }

        $errorToken = ($stackPtr + 1);
        if (isset($tokens[$errorToken]) === false) {
            $errorToken--;
        }

        if ($tokens[$commentStart]['code'] === T_CLOSE_TAG) {
            // We are only interested if this is the first open tag.
            return ($phpcsFile->numTokens + 1);
        } else if ($tokens[$commentStart]['code'] === T_COMMENT) {
            $error = 'You must use "/**" style comments for a file comment';
            $phpcsFile->addError($error, $errorToken, 'WrongStyle');
            $phpcsFile->recordMetric($stackPtr, 'File has doc comment', 'yes');
            return ($phpcsFile->numTokens + 1);
        } else if ($commentStart === false
            || $tokens[$commentStart]['code'] !== T_DOC_COMMENT_OPEN_TAG
        ) {
            $phpcsFile->addError('Missing file doc comment', $errorToken, 'Missing');
            $phpcsFile->recordMetric($stackPtr, 'File has doc comment', 'no');
            return ($phpcsFile->numTokens + 1);
        } else {
            $phpcsFile->recordMetric($stackPtr, 'File has doc comment', 'yes');
        }

        // Check each tag.
        $this->processTags($phpcsFile, $stackPtr, $commentStart);

        // Check there is 1 empty line after.
        $commentCloser  = $tokens[$commentStart]['comment_closer'];
        $nextContentPtr = $phpcsFile->findNext(T_WHITESPACE, ($commentCloser + 1), null, true);
        $lineDiff       = ($tokens[$nextContentPtr]['line'] - $tokens[$commentCloser]['line']);
        if ($lineDiff === 1) {
            $data  = array(1);
            $error = 'Expected %s empty line after file doc comment';
            $fix   = $phpcsFile->addFixableError($error, ($commentCloser + 1), 'NoEmptyLineAfterFileDocComment', $data);
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->addNewlineBefore($nextContentPtr);
                $phpcsFile->fixer->endChangeset();
            }
        }

        // Ignore the rest of the file.
        return ($phpcsFile->numTokens + 1);

    }//end process()


    /**
     * Processes each required or optional tag.
     *
     * @param File $phpcsFile    The file being scanned.
     * @param int  $stackPtr     The position of the current token
     *                           in the stack passed in $tokens.
     * @param int  $commentStart Position in the stack where the comment started.
     *
     * @return void
     */
    protected function processTags(File $phpcsFile, $stackPtr, $commentStart)
    {
        $tokens = $phpcsFile->getTokens();

        if (get_class($this) === 'FileCommentSniff') {
            $docBlock = 'file';
        } else {
            $docBlock = 'class';
        }

        $commentEnd = $tokens[$commentStart]['comment_closer'];

        $foundTags = array();
        $tagTokens = array();
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            $name = $tokens[$tag]['content'];
            if (isset($this->tags[$name]) === false) {
                continue;
            }

            if ($this->tags[$name]['allow_multiple'] === false && isset($tagTokens[$name]) === true) {
                $error = 'Only one %s tag is allowed in a %s comment';
                $data  = array(
                          $name,
                          $docBlock,
                         );
                $phpcsFile->addError($error, $tag, 'Duplicate'.ucfirst(substr($name, 1)).'Tag', $data);
            }

            $foundTags[]        = $name;
            $tagTokens[$name][] = $tag;

            $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
            if ($string === false || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                $error = 'Content missing for %s tag in %s comment';
                $data  = array(
                          $name,
                          $docBlock,
                         );
                $phpcsFile->addError($error, $tag, 'Empty'.ucfirst(substr($name, 1)).'Tag', $data);
                continue;
            }
        }//end foreach

        // Check if the tags are in the correct position.
        $pos = 0;
        foreach ($this->tags as $tag => $tagData) {
            if (isset($tagTokens[$tag]) === false) {
                if ($tagData['required'] === true) {
                    $error = 'Missing %s tag in %s comment';
                    $data  = array(
                              $tag,
                              $docBlock,
                             );
                    $phpcsFile->addError($error, $commentEnd, 'Missing'.ucfirst(substr($tag, 1)).'Tag', $data);
                }

                continue;
            } else {
                $method = 'process'.substr($tag, 1);
                if (method_exists($this, $method) === true) {
                    // Process each tag if a method is defined.
                    call_user_func(array($this, $method), $phpcsFile, $tagTokens[$tag]);
                }
            }

            if (isset($foundTags[$pos]) === false) {
                break;
            }

            if ($foundTags[$pos] !== $tag) {
                $error = 'The tag in position %s should be the %s tag';
                $data  = array(
                          ($pos + 1),
                          $tag,
                         );
                $phpcsFile->addError($error, $tokens[$commentStart]['comment_tags'][$pos], ucfirst(substr($tag, 1)).'TagOrder', $data);
            }

            // Account for multiple tags.
            $pos++;
            while (isset($foundTags[$pos]) === true && $foundTags[$pos] === $tag) {
                $pos++;
            }
        }//end foreach

    }//end processTags()


    /**
     * Process the package tag.
     *
     * @param File  $phpcsFile The file being scanned.
     * @param array $tags      The tokens for these tags.
     *
     * @return void
     */
    protected function processPackage(File $phpcsFile, array $tags)
    {
        $tokens = $phpcsFile->getTokens();
        foreach ($tags as $tag) {
            if ($tokens[($tag + 2)]['code'] !== T_DOC_COMMENT_STRING) {
                // No content.
                continue;
            }

            $content = $tokens[($tag + 2)]['content'];

            $isInvalidPackage = false;

            if (strpos($content, '/') !== false) {
                $parts   = explode('/', $content);
                $newName = join('\\', $parts);

                $isInvalidPackage = true;
            }

            if ($isInvalidPackage === true) {
                $error     = 'Package name "%s" is not valid. Use "%s" instead';
                $validName = trim($newName, '_');
                $data      = array(
                              $content,
                              $validName,
                             );
                $phpcsFile->addWarning($error, $tag, 'InvalidPackage', $data);
            }
        }//end foreach

    }//end processPackage()


    /**
     * Process the subpackage tag.
     *
     * @param File  $phpcsFile The file being scanned.
     * @param array $tags      The tokens for these tags.
     *
     * @return void
     */
    protected function processSubpackage(File $phpcsFile, array $tags)
    {
        $tokens = $phpcsFile->getTokens();
        foreach ($tags as $tag) {
            if ($tokens[($tag + 2)]['code'] !== T_DOC_COMMENT_STRING) {
                // No content.
                continue;
            }

            $warning = 'The subpackage tag is considered deprecated. It is recommended to use the @package tag instead.';

            $phpcsFile->addWarning($warning, $tag, 'SubpackageDepreciated');
        }//end foreach

    }//end processSubpackage()


    /**
     * Process the category tag.
     *
     * @param File  $phpcsFile The file being scanned.
     * @param array $tags      The tokens for these tags.
     *
     * @return void
     */
    protected function processCategory(File $phpcsFile, array $tags)
    {
        $tokens = $phpcsFile->getTokens();
        foreach ($tags as $tag) {
            if ($tokens[($tag + 2)]['code'] !== T_DOC_COMMENT_STRING) {
                // No content.
                continue;
            }

            $warning = 'The category tag is considered deprecated. It is recommended to use the @package tag instead.';

            $phpcsFile->addWarning($warning, $tag, 'CategoryDepreciated');
        }//end foreach

    }//end processCategory()


    /**
     * Process the author tag(s) that this header comment has.
     *
     * @param File  $phpcsFile The file being scanned.
     * @param array $tags      The tokens for these tags.
     *
     * @return void
     */
    protected function processAuthor(File $phpcsFile, array $tags)
    {
        $tokens = $phpcsFile->getTokens();
        foreach ($tags as $tag) {
            if ($tokens[($tag + 2)]['code'] !== T_DOC_COMMENT_STRING) {
                // No content.
                continue;
            }

            $content = $tokens[($tag + 2)]['content'];

            // If it has an @ it's probably contains email address.
            if (strrpos($content, '@') !== false) {
                $local = '\da-zA-Z-_+';
                // Dot character cannot be the first or last character in the local-part.
                $localMiddle = $local.'.\w';
                if (preg_match('/^([^<]*)\s+<(['.$local.'](['.$localMiddle.']*['.$local.'])*@[\da-zA-Z][-.\w]*[\da-zA-Z]\.[a-zA-Z]{2,7})>$/', $content) === 0) {
                    $error = '"@author" with email must be "Display Name <username@example.com>"';
                    $phpcsFile->addError($error, $tag, 'InvalidAuthors');
                }
            }
        }

    }//end processAuthor()


    /**
     * Process the copyright tags.
     *
     * @param File  $phpcsFile The file being scanned.
     * @param array $tags      The tokens for these tags.
     *
     * @return void
     */
    protected function processCopyright(File $phpcsFile, array $tags)
    {
        $tokens = $phpcsFile->getTokens();
        foreach ($tags as $tag) {
            if ($tokens[($tag + 2)]['code'] !== T_DOC_COMMENT_STRING) {
                // No content.
                continue;
            }

            $content = $tokens[($tag + 2)]['content'];
            $matches = array();
            if (preg_match('/^([0-9]{4})((.{1})([0-9]{4}))? (.+)$/', $content, $matches) !== 0) {
                // Check earliest-latest year order.
                if ($matches[3] !== '') {
                    if ($matches[3] !== '-') {
                        $error = 'A hyphen must be used between the earliest and latest year';
                        $phpcsFile->addError($error, $tag, 'CopyrightHyphen');
                    }

                    if ($matches[4] !== '' && $matches[4] < $matches[1]) {
                        $error = "Invalid year span \"$matches[1]$matches[3]$matches[4]\" found; consider \"$matches[4]-$matches[1]\" instead";
                        $phpcsFile->addWarning($error, $tag, 'InvalidCopyright');
                    }
                }
            } else {
                $error = '"@copyright" must be "YYYY [- YYYY] Name of the copyright holder"';
                $phpcsFile->addError($error, $tag, 'IncompleteCopyright');
            }
        }//end foreach

    }//end processCopyright()


    /**
     * Process the license tag.
     *
     * @param File  $phpcsFile The file being scanned.
     * @param array $tags      The tokens for these tags.
     *
     * @return void
     */
    protected function processLicense(File $phpcsFile, array $tags)
    {
        $tokens = $phpcsFile->getTokens();
        foreach ($tags as $tag) {
            if ($tokens[($tag + 2)]['code'] !== T_DOC_COMMENT_STRING) {
                // No content.
                continue;
            }

            $content = $tokens[($tag + 2)]['content'];
            $matches = array();
            preg_match('/^([^\s]+)\s+(.*)/', $content, $matches);

            if (count($matches) !== 3) {
                $error = '@license tag must contain a URL and a license name';
                $phpcsFile->addError($error, $tag, 'IncompleteLicense');
            }

            // Check the url is before the text part if it's included.
            $parts = explode(' ', $content);
            if ((count($parts) > 1)) {
                $matches = array();
                preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $parts[0], $matches);
                if (count($matches) !== 1) {
                    $error = 'The URL must come before the license name';
                    $phpcsFile->addError($error, $tag, 'LicenseURLNotFirst');
                }
            }
        }//end foreach

    }//end processLicense()


    /**
     * Process the link tag.
     *
     * @param File  $phpcsFile The file being scanned.
     * @param array $tags      The tokens for these tags.
     *
     * @return void
     */
    protected function processLink(File $phpcsFile, array $tags)
    {
        $tokens = $phpcsFile->getTokens();
        foreach ($tags as $tag) {
            if ($tokens[($tag + 2)]['code'] !== T_DOC_COMMENT_STRING) {
                // No content.
                continue;
            }

            $content = $tokens[($tag + 2)]['content'];
            $matches = array();

            // Check the url is before the text part if it's included.
            $parts = explode(' ', $content);
            if ((count($parts) > 1)) {
                $matches = array();
                preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $parts[0], $matches);
                if (count($matches) !== 1) {
                    $error = 'The URL must come before the description';
                    $phpcsFile->addError($error, $tag, 'LinkURLNotFirst');
                }
            }
        }//end foreach

    }//end processLink()


    /**
     * Process the version tag.
     *
     * @param File  $phpcsFile The file being scanned.
     * @param array $tags      The tokens for these tags.
     *
     * @return void
     */
    protected function processVersion(File $phpcsFile, array $tags)
    {
        $tokens = $phpcsFile->getTokens();
        foreach ($tags as $tag) {
            if ($tokens[($tag + 2)]['code'] !== T_DOC_COMMENT_STRING) {
                // No content.
                continue;
            }

            $content = $tokens[($tag + 2)]['content'];
            // Split into parts if content has a space.
            $parts = explode(' ', $content);
            // Check if the first part contains a semantic version number.
            $matches = array();
            preg_match('/^(?:(\d+)\.)?(?:(\d+)\.)?(\*|\d+)$/', $parts[0], $matches);

            if (strstr($content, 'CVS:') === false
                && strstr($content, 'SVN:') === false
                && strstr($content, 'GIT:') === false
                && strstr($content, 'HG:') === false
                && count($matches) === 0
            ) {
                $error = 'It is recommended that @version is a semantic version number or is a VCS version vector "GIT: <git_id>" or "SVN: <svn_id>" or "HG: <hg_id>" or "CVS: <cvs_id>".';
                $phpcsFile->addWarning($error, $tag, 'InvalidVersion');
            }
        }//end foreach

    }//end processVersion()


}//end class
