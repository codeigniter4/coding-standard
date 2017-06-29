<?php
/**
 * Valid Method Name
 *
 * @package   CodeIgniter4-Standard
 * @author    Louis Linehan <louis.linehan@gmail.com>
 * @copyright 2017 Louis Linehan
 * @license   https://github.com/louisl/CodeIgniter4-Standard/blob/master/LICENSE MIT License
 */

namespace CodeIgniter4\Sniffs\NamingConventions;

use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Files\File;

/**
 * Valid Method Name Sniff
 *
 * Checks class methods are lowerCameCase.
 * Checks public methods are not prefixed with "_" execept
 * methiods defined in allowedPublicMethodNames.
 * Checks private and protected methods are prefixed with "_".
 * Checks functions are snake_case.
 * Warns if names are longer than 50 characters.
 *
 * @author Louis Linehan <louis.linehan@gmail.com>
 */
class ValidMethodNameSniff extends AbstractScopeSniff
{
    /**
     * A list of all PHP magic methods.
     *
     * @var array
     */
    protected static $magicMethods = array(
                                      'construct',
                                      'destruct',
                                      'call',
                                      'callStatic',
                                      'get',
                                      'set',
                                      'isset',
                                      'unset',
                                      'sleep',
                                      'wakeup',
                                      'toString',
                                      'set_state',
                                      'clone',
                                     );
    /**
     * Allowed public methodNames
     *
     * @var array
     */
    protected $allowedPublicMethodNames = array(
                                           '_init',
                                           '_remap',
                                           '_like_statement',
                                           '_prepare',
                                           '_execute',
                                           '_getResult',
                                           '_backup',
                                           '_fieldData',
                                           '_indexData',
                                           '_flip',
                                           '_resize',
                                           '_crops',
                                           '_text',
                                          );


    /**
     * Defines which token(s) in which scope(s) will be proceed.
     */
    public function __construct()
    {
        parent::__construct(array(T_CLASS, T_INTERFACE), array(T_FUNCTION), true);

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

        // Is this a magic method i.e. is prefixed with "__".
        if (strrpos($methodName, '__') === 0) {
            $namePart = substr($methodName, 2);
            if (in_array($namePart, static::$magicMethods) === false) {
                $errorData = array(
                              $className,
                              $methodName,
                             );
                 $error    = 'Method "%s::%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
                 $phpcsFile->addError($error, $stackPtr, 'OnlyPHPMagicMethodsToBePrefixedDoubleUnderscore', $errorData);
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
        if (Common::isCamelCaps($namePart, false, true, false) === false
            && in_array($methodName, $this->allowedPublicMethodNames) === false
        ) {
            $errorData = array($methodName);
            $error     = 'Method "%s" is not lowerCamelCase';
            $phpcsFile->addError($error, $stackPtr, 'MethodNotCamelCase', $errorData);
        }

        $methodProps    = $phpcsFile->getMethodProperties($stackPtr);
        $scope          = $methodProps['scope'];
        $scopeSpecified = $methodProps['scope_specified'];
        $errorData      = array(
                           $className,
                           $methodName,
                          );

        // If it's a private or protected method, it must be prefixed with an underscore.
        if (($scope === 'private' || $scope === 'protected') && strrpos($methodName, '_') !== 0) {
            $error = ucfirst($scope).' method "%s::%s" must be prefixed with an underscore';
            $phpcsFile->addError($error, $stackPtr, 'PrivateAndProtectedMethodMustHaveUnderscore', $errorData);
            return;
        }

        // If it's a public method, it must not be prefixed with an underscore.
        if ($scope === 'public' && strrpos($methodName, '_') === 0) {
            if (in_array($methodName, $this->allowedPublicMethodNames) === false) {
                $error = ucfirst($scope).' method "%s::%s" must be not prefixed with an underscore';
                $phpcsFile->addError($error, $stackPtr, 'PublicMethodMustNotHaveUnderscore', $errorData);
                return;
            }
        }

        // Warn if method name is over 50 chars.
        $warningLimit = 50;
        if (strlen($methodName) > $warningLimit) {
            $errorData = array(
                          $methodName,
                          $warningLimit,
                         );

            $warning = 'Method "%s" is over "%s" chars';
            $phpcsFile->addWarning($warning, $stackPtr, 'MethodNameIsLong', $errorData);
        }

    }//end processTokenWithinScope()


}//end class
