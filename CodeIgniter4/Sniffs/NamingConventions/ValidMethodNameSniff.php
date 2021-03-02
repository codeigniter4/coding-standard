<?php
/**
 * Valid Method Name
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 British Columbia Institute of Technology
 * @license   https://github.com/bcit-ci/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Sniffs\NamingConventions;

use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use CodeIgniter4\Util\Common;
use PHP_CodeSniffer\Files\File;

/**
 * Valid Method Name Sniff
 *
 * Checks class methods are lowerCameCase.
 * Checks public methods are not prefixed with "_" except
 * methods defined in allowedPublicMethodNames.
 * Checks private and protected methods are prefixed with "_".
 * Checks functions are snake_case.
 * Warns if names are longer than 50 characters.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class ValidMethodNameSniff extends AbstractScopeSniff
{


    /**
     * Defines which token(s) in which scope(s) will be proceed.
     */
    public function __construct()
    {
        parent::__construct([T_CLASS, T_ANON_CLASS, T_INTERFACE, T_TRAIT], [T_FUNCTION], true);

    }//end __construct()


    /**
     * Processes a token within the scope that this test is listening to.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where the token was found.
     * @param int                         $stackPtr  The position in the stack where
     *                                               this token was found.
     *
     * @return void
     */
    protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
    {

    }//end processTokenOutsideScope()


    /**
     * Processes the tokens within the scope.
     *
     * @param File $phpcsFile The file being processed.
     * @param int  $stackPtr  The position where this token was
     *                        found.
     * @param int  $currScope The position of the current scope.
     *
     * @return void
     */
    protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope)
    {
        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        if ($methodName === null) {
            // Ignore closures.
            return;
        }

        $className = $phpcsFile->getDeclarationName($currScope);

        // Is this a magic method. i.e., is prefixed with "__"?
        if (preg_match('|^__[^_]|', $methodName) !== 0) {
            $magicPart = strtolower(substr($methodName, 2));
            if (isset(Common::$magicMethods[$magicPart]) === false) {
                $errorData = [$className.'::'.$methodName];
                $error     = 'Method name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
                $phpcsFile->addError($error, $stackPtr, 'MethodDoubleUnderscore', $errorData);
            }

            return;
        }

        // Get the method name without underscore prefix if it exists.
        if (strrpos($methodName, '_') === 0) {
            $namePart = substr($methodName, 1);
        } else {
            $namePart = $methodName;
        }

        // Naming check.
        if (Common::isCamelCaps($namePart, false, true, false) === false) {
            $errorData = [$methodName];
            $error     = 'Method "%s" must be lowerCamelCase';
            $phpcsFile->addError($error, $stackPtr, 'MethodNotLowerCamelCase', $errorData);
        }

        // Methods must not be prefixed with an underscore except those in publicMethodNames.
        if (strrpos($methodName, '_') === 0) {
            if (isset(Common::$publicMethodNames[$methodName]) === false) {
                $methodProps = $phpcsFile->getMethodProperties($stackPtr);
                $scope       = $methodProps['scope'];
                $errorData   = [$className.'::'.$methodName];
                $error       = ucfirst($scope).' method "%s" must not be prefixed with an underscore';
                $phpcsFile->addError($error, $stackPtr, 'MethodMustNotHaveUnderscore', $errorData);
            }
        }

        // Warn if method name is over 50 chars.
        $warningLimit = 50;
        if (strlen($methodName) > $warningLimit) {
            $errorData = [
                $methodName,
                $warningLimit,
            ];

            $warning = 'Method "%s" is over "%s" chars';
            $phpcsFile->addWarning($warning, $stackPtr, 'MethodNameIsLong', $errorData);
        }

    }//end processTokenWithinScope()


}//end class
