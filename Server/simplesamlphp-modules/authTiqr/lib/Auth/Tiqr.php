<?php
/**
 * This file is part of simpleSAMLphp.
 * 
 * The authTiqr module is a module adding authentication via the tiqr 
 * project to simpleSAMLphp. It was initiated by SURFnet and 
 * developed by Egeniq.
 *
 * See the README file for instructions and requirements.
 *
 * @author Ivo Jansch <ivo@egeniq.com>
 * 
 * @package simpleSAMLphp
 * @subpackage authTiqr
 *
 * @license New BSD License - See LICENSE file in the tiqr library for details
 * @copyright (C) 2010-2011 SURFnet BV
 *
 */

/**
 * This class implements basic Tiqr functionality that is shared betweeh
 * the authsource and the processing filter.
 * @author ivo
 *
 */
class sspmod_authTiqr_Auth_Tiqr
{
    /**
     * The string used to identify our states.
     */
    const STAGEID = sspmod_core_Auth_UserPassBase::STAGEID; // share stageid with userpass module so the modules can be combined
    const CONFIGID = 'sspmod_authTiqr_Auth_Tiqr.config';
    const USERPASSSOURCEID = 'sspmod_authTiqr_Auth_Tiqr.userPassSource';
    const SESSIONKEYID = 'sspmod_authTiqr_Auth_Tiqr.sessionkey';
    
    /**
     * User storage instance.
     */
    private static $_userStorage = null;
    
    /**
     * Adjust include path.
     */
    public static function adjustIncludePath() 
    {
        $config = SimpleSAML_Configuration::getConfig('module_tiqr.php')->toArray();
        
        $tiqrPath = dirname(__FILE__)."/../../../../library/tiqr";
        if (isset($config["tiqr.path"])) {
             $tiqrPath = $config["tiqr.path"];
        }
        
        ini_set('include_path', ini_get('include_path').':'.$tiqrPath);
    }
    
    /**
     * Returns the user storage.
     */ 
    public static function getUserStorage() 
    {
        require_once 'Tiqr/UserStorage.php';

        if (self::$_userStorage == null) {
            $config = SimpleSAML_Configuration::getConfig('module_tiqr.php')->toArray();
            self::$_userStorage = Tiqr_UserStorage::getStorage($config["userstorage"]["type"], $config["userstorage"]);
        }
        
        return self::$_userStorage;
    }
    
    /**
     * Handle login request.
     *
     * This function is used by the login form (core/www/loginuserpass.php) when the user
     * enters a username and password. On success, it will not return. On wrong
     * username/password failure, it will return the error code. Other failures will throw an
     * exception.
     *
     * @param string $authStateId  The identifier of the authentication state.
     * @param string $otp  The one time password entered-
     * @return string  Error code in the case of an error.
     */
    public static function verifyLogin($authStateId) 
    {
        self::_validateAuthState($authStateId);

        $server = self::getServer();

        $session = SimpleSAML_Session::getInstance();
        $sessionId = $session->getSessionId();
        
        $user = $server->getAuthenticatedUser($sessionId);
        if (empty($user)) {
            echo "NO";
            // Not logged in yet, ajax call can silently stop.
        
        } else {
            
            $url = SimpleSAML_Module::getModuleURL('authTiqr/complete.php');
            echo 'URL:'.$url.'?'.http_build_query(array('AuthState' => $authStateId));
        }
        
    }

    public static function verifyEnrollment($authStateId=NULL)
    {
        if ($authStateId!=NULL) {
            self::_validateAuthState($authStateId);
        }
        $server = self::getServer(); 

        $session = SimpleSAML_Session::getInstance();
        $sessionId = $session->getSessionId(); 

        $status = $server->getEnrollmentStatus($sessionId);
        if ($status==Tiqr_Service::ENROLLMENT_STATUS_FINALIZED) {
            $url = SimpleSAML_Module::getModuleURL('authTiqr/complete_enrollment.php');
            echo 'URL:'.$url.'?'.http_build_query(array('AuthState' => $authStateId));
        } else {
            echo "NO";
        }
    }
    
    public static function completeLogin($authStateId)
    {
        $state = self::_validateAuthState($authStateId);
        
        $server = self::getServer();

        $session = SimpleSAML_Session::getInstance();
        $sessionId = $session->getSessionId();
        
        $user = $server->getAuthenticatedUser($sessionId);
        if (empty($user)) {
        
            $url = SimpleSAML_Module::getModuleURL('authTiqr/login.php');
            SimpleSAML_Utilities::redirect($url, array('AuthState' => $authStateId));
        } else {
            
            if (!isset($state["tiqrUser"])) {
                // Single factor. We can now continue to login.
            
                $attributes = array(
                    'uid' => array($user),
                    'displayName' => array(self::getUserStorage()->getDisplayName($user)),
                );

                $attributes = array_merge($attributes, self::getUserStorage()->getAdditionalAttributes($user));

                $state['Attributes'] = $attributes;
                SimpleSAML_Auth_Source::completeAuth($state);
            } else {
                // Two factor, we can now complete the processing filter process.
                SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
            }
        }
        
        
    }
    
    public static function getAuthenticateUrl($sessionKey)
    {
        $server = self::getServer();
       
        return $server->generateAuthURL($sessionKey);
    }
    
    public static function sendAuthNotification($authStateId)
    {
        $server = self::getServer();
                
        $state = self::_validateAuthState($authStateId);
                
        $userId = NULL;
        if (isset($state["tiqrUser"])) {
            $userId = $state["tiqrUser"]["userId"];
        } else {
            return false; // Can't notify a nonexistent user.
        }

        $store = self::getUserStorage();
        if (!$store->userExists($userId)) {
            return false;
        }
        
        $notificationType = $store->getNotificationType($userId);
        $notificationAddress = $store->getNotificationAddress($userId);
        $translatedAddress = $server->translateNotificationAddress($notificationType, $notificationAddress);
                        
        if ($translatedAddress) {
            return $server->sendAuthNotification($state[self::SESSIONKEYID], $notificationType, $translatedAddress);
        } else {
            return false;
        }
    }
    
    public static function isEnrolled($userId)
    {
        $store = self::getUserStorage();
        if ($store->userExists($userId)) {
            $userSecret = $store->getSecret($userId);
            if ($userSecret!=false) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function generateAuthQR($authStateId)
    {
        $server = self::getServer();
                
        $state = self::_validateAuthState($authStateId);
                         
        return $server->generateAuthQR($state[self::SESSIONKEYID]);
    }

    public static function resetEnrollmentSession()
    {
        $server = self::getServer();
        $session = SimpleSAML_Session::getInstance();
        $sessionId = $session->getSessionId();
      
        $server->resetEnrollmentSession($sessionId);
        
    }
    
    protected static function _getSpIdentifier($state)
    {
        if (isset($state["saml:RelayState"])) {
            // We're running in IDP mode. RelayState is the page we are actually logging into.
            $url = $state["saml:RelayState"];
        } else if (isset($state['SimpleSAML_Auth_Default.ReturnURL'])) {
            // We're probably running in local mode.
            $url = $state['SimpleSAML_Auth_Default.ReturnURL'];
        } else {
            // Nothing to go by. Fall back to our own hostname.
            $url =  "http".(isset($_SERVER['HTTPS'])?'s':'')."://".$_SERVER["HTTP_HOST"];
        }
        
        $host = parse_url($url, PHP_URL_HOST);
        
        return $host;
        
    }
    
    public static function startAuthenticationSession($userId="", $state)
    {
        $server = self::getServer();
        $session = SimpleSAML_Session::getInstance();
        $sessionId = $session->getSessionId();
        $spIdentifier = self::_getSpIdentifier($state);
        
        return $server->startAuthenticationSession($userId, $sessionId, $spIdentifier);
    }
    
    public static function generateEnrollmentQR()
    {
        $server = self::getServer();
        
        $session = SimpleSAML_Session::getInstance();
        
        $userid = $session->getData("String", "enroll_userid");
        $fullname = $session->getData("String", "enroll_fullname");

        $sessionId = $session->getSessionId();
        $enrollmentKey = $server->startEnrollmentSession($userid, $fullname, $sessionId);
        
        $metadataUrl = SimpleSAML_Module::getModuleURL('authTiqr/metadata.php')."?key=".$enrollmentKey;
        
        $server->generateEnrollmentQR($metadataUrl);
    }
    
    public static function processManualLogin($userId, $otp, $sessionKey)
    {        
        return self::_processLogin($userId, $otp, $sessionKey);
    }
    
    public static function processMobileLogin($request)
    {
        $responseObj = self::getResponse();
        if (!isset($request["sessionKey"]) || !isset($request["userId"]) || !isset($request["response"])) {
            return $responseObj->getInvalidRequestResponse();
        } 
            
        $key = $request["sessionKey"];
        $userId = $request["userId"];
        $response = $request["response"];
        
        return self::_processLogin($userId, $response, $key);
    }
    
    /**
     * 
     * Enter description here ...
     * @param string $userId
     * @param string $response
     * @param string $sessionKey
     * @return string|array an all-caps string indicating the authentication result or an array.
     */
    protected static function _processLogin($userId, $response, $sessionKey)
    {
        $responseObj = self::getResponse();
        
        try {
            $server = self::getServer();
                
            $store  = self::getUserStorage();
            $config = SimpleSAML_Configuration::getConfig('module_tiqr.php')->toArray();
            
            $tempBlockDuration = array_key_exists('temporaryBlockDuration', $config) ? $config['temporaryBlockDuration'] : 0;
            $maxTempBlocks = array_key_exists('maxTemporaryBlocks', $config) ? $config['maxTemporaryBlocks'] : 0;
            $maxAttempts = array_key_exists('maxAttempts', $config) ? $config['maxAttempts'] : 3;
            
            if ($store->isBlocked($userId, $tempBlockDuration)) {
                return $responseObj->getAccountBlockedResponse($tempBlockDuration);
            } else if ($store->userExists($userId)) {
                $secret = $store->getSecret($userId);
                $result = $server->authenticate($userId, $secret, $sessionKey, $response); 
                switch ($result) {
                    case Tiqr_Service::AUTH_RESULT_AUTHENTICATED:
                        // Reset the login attempts counter
                        $store->setLoginAttempts($userId, 0);
                        
                        // update notification information if given, on successful login
                        if (isset($request["notificationType"])) {
                            $store->setNotificationType($userId, $request["notificationType"]);
                            if (isset($request['notificationAddress'])) {
                                $store->setNotificationAddress($userId, $request["notificationAddress"]);                    
                            }
                        }
                        return $responseObj->getLoginResponse();
                    case Tiqr_Service::AUTH_RESULT_INVALID_CHALLENGE:
                        return $responseObj->getInvalidChallengeResponse();
                    case Tiqr_Service::AUTH_RESULT_INVALID_REQUEST:
                        return $responseObj->getInvalidRequestResponse();
                    case Tiqr_Service::AUTH_RESULT_INVALID_RESPONSE:
                        $attempts = $store->getLoginAttempts($userId);
                        if (0 == $maxAttempts) {
                            return $responseObj->getInvalidResponse();
                        }
                        else if ($attempts < ($maxAttempts-1)) {
                            $store->setLoginAttempts($userId, $attempts+1);
                        } else {
                            // Block user and destroy secret
                            $store->setBlocked($userId, true);
                            $store->setSecret($userId, NULL);
                            $store->setLoginAttempts($userId, 0);
                            
                            if ($tempBlockDuration > 0) {
                                $tempAttempts = $store->getTemporaryBlockAttempts($userId);
                                if (0 == $maxTempBlocks) {
                                    // always a temporary block
                                    $store->setTemporaryBlockTimestamp($userId, date("Y-m-d H:i:s"));
                                }
                                else if ($tempAttempts < ($maxTempBlocks - 1)) {
                                    // temporary block which could turn into a permanent block
                                    $store->setTemporaryBlockAttempts($userId, $tempAttempts+1);
                                    $store->setTemporaryBlockTimestamp($userId, date("Y-m-d H:i:s"));
                                } 
                                else {
                                    // remove timestamp to make this a permanent block
                                    $store->setTemporaryBlockTimestamp($userId, false);
                                }
                            }
                        }
                        return $responseObj->getInvalidResponse(($maxAttempts-1)-$attempts);
                    case Tiqr_Service::AUTH_RESULT_INVALID_USERID:
                        return $responseObj->getInvalidUserResponse();
                    default:
                        return $responseObj->getErrorResponse(); // Shouldn't happen
                }
            }
            return $responseObj->getInvalidResponse(); 
        }
        catch (Exception $error) {
            // If anything goes wrong, we should return a generic error.
            return $responseObj->getErrorResponse();
        }
    
    }
    
    public static function getEnrollmentMetadata($request)
    {
        if (!isset($request["key"])) {
            return false;
        }

        $authenticationUrl = SimpleSAML_Module::getModuleURL('authTiqr/post.php');

        $server = self::getServer();

        $enrollmentSecret = $server->getEnrollmentSecret($request["key"]);
        
        $enrollmentUrl = SimpleSAML_Module::getModuleURL('authTiqr/enroll.php').'?key='.$enrollmentSecret;
        
        $metadata = $server->getEnrollmentMetadata($request["key"], $authenticationUrl, $enrollmentUrl);
        
        if (!is_array($metadata)) {
            return false;
        }
        
        return $metadata;
    }

    public static function processMobileEnrollment($request)
    {
        if (!isset($request["key"])||!isset($request["secret"])) {
            return false;
        }
        $server = self::getServer(); 
        $responseObj = self::getResponse();
        
        $userId = $server->validateEnrollmentSecret($request["key"]);
        if ($userId !== false) {
            $store = self::getUserStorage();
            if ($store->userExists($userId)) {
                $store->setSecret($userId, $request["secret"]);
                $store->setBlocked($userId, false); // remove any pending blocks upon re-enrollment.
                $store->setLoginAttempts($userId, 0);
                
                if (method_exists($store, 'setTemporaryBlockAttempt')) {
                    $store->setTemporaryBlockAttempt($userId, 0);
                }
                
                if (method_exists($store, 'setTemporaryBlockTimestamp')) {
                    $store->setTemporaryBlockTimestamp($userId, false);
                }
                
                if (isset($request["notificationType"])) {
                    $store->setNotificationType($userId, $request["notificationType"]);
                    if (isset($request['notificationAddress'])) {
                        $store->setNotificationAddress($userId, $request["notificationAddress"]);                    
                    }
                }
                $server->finalizeEnrollment($request["key"]);
                return $responseObj->getEnrollmentOkResponse();
            }
        }
        return $responseObj->getEnrollmentErrorResponse();
    } 
    
    /**
     * @return Tiqr_Service
     */
    public static function getServer()
    {
        $config = SimpleSAML_Configuration::getConfig('module_tiqr.php')->toArray();
        
        require_once "Tiqr/Service.php";
        $server = new Tiqr_Service($config);
        return $server;
    }
    
    public static function getAuthSourceConfig($authStateId)
    {
        $state = SimpleSAML_Auth_State::loadState($authStateId, self::STAGEID);
        if (isset($state[self::CONFIGID])) {
            return $state[self::CONFIGID];
        }
        return array();
    }
    
    /**
     * Get the response object
     * 
     * @return object 
     */
    public static function getResponse()
    {
        // check if the client supports json, if not fallback to the plain text
        if (stristr('json', $_SERVER['HTTP_ACCEPT'])) {
            require_once "Tiqr/Response/Abstract.php";
            return Tiqr_Response_Abstract::createResponse();
        }
        else {
            require_once SimpleSAML_Module::getModuleDir('authTiqr')."/lib/Response/Plain.php";
            return new Response_Plain();
        }
    }
    
    protected static function _validateAuthState($authStateId)
    {       
        assert('is_string($authStateId)');
        /* Retrieve the authentication state. */
        $state = SimpleSAML_Auth_State::loadState($authStateId, self::STAGEID);

        return $state;   
    }
}

sspmod_authTiqr_Auth_Tiqr::adjustIncludePath();
