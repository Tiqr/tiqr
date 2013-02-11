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
 * @author Peter Verhage <peter@egeniq.com>
 * 
 * @package simpleSAMLphp
 * @subpackage authTiqr
 *
 * @license New BSD License - See LICENSE file in the tiqr library for details
 * @copyright (C) 2010-2011 SURFnet BV
 *
 */

/**
 * Tiqr authentication module in combination with a UserPass based auth module.
 * 
 * Configure it by adding an entry to config/authsources.php such as this:
 *
 *    'tiqr' => array(
 *            'authTiqr:TiqrUserPass',
 *            'userPassSource' => 'ldap'
 *    ),
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class sspmod_authTiqr_Auth_Source_TiqrUserPass extends SimpleSAML_Auth_Source {
    /**
     * The key of the AuthId field in the state.
     */
    const AUTHID = 'sspmod_authTiqr_Auth_Source_TiqrUserPass.AuthId';

    /**
     * Username/password authentication source.
     * 
     * @var string
     */
    private $userPassSource;

    /**
     * Constructor for this authentication source.
     *
     * @param array $info  Information about this authentication source.
     * @param array $config  Configuration.
     */
    public function __construct($info, $config) {
        assert('is_array($info)');
        assert('is_array($config)');
        
        /* Call the parent constructor first, as required by the interface. */
        parent::__construct($info, $config);
        
        $this->userPassSource = $config['userPassSource'];
    }

    /**
     * Initialize login.
     *
     * This function saves the information about the login, and redirects to a
     * login page.
     *
     * @param array &$state  Information about the current authentication.
     */
    public function authenticate(&$state) {
        assert('is_array($state)');
        
        $config = SimpleSAML_Configuration::getConfig('authsources.php');
        $state[sspmod_authTiqr_Auth_Tiqr::CONFIGID] = $config->getArray(self::getAuthId(), array());

        /* We are going to need the authId in order to retrieve this authentication source later. */
        $state[self::AUTHID] = $this->authId;
        $state[sspmod_authTiqr_Auth_Tiqr::USERPASSSOURCEID] = $this->userPassSource;

        $id = SimpleSAML_Auth_State::saveState($state, sspmod_authTiqr_Auth_Tiqr::STAGEID);

        $server =  sspmod_authTiqr_Auth_Tiqr::getServer(false);
        
        $session = SimpleSAML_Session::getInstance();
        $sessionId = $session->getSessionId();
        
        $user = $server->getAuthenticatedUser($sessionId);
        
        if (empty($user)) {
            $url = SimpleSAML_Module::getModuleURL('authTiqr/loginuserpass.php');
            SimpleSAML_Utilities::redirect($url, array('AuthState' => $id));
        } else {
            $attributes = array(
                'uid' => array($user),
                'displayName' => array(sspmod_authTiqr_User::getStorage()->getDisplayName($user)),
            );
			
            $attributes = array_merge($attributes, sspmod_authTiqr_User::getStorage()->getAdditionalAttributes($user));			
            
            $state['Attributes'] = $attributes;
        }
    } 

    /**
     * Handle username-password login request.
     *
     * This function is used by the login form (www/loginuserpass.php) when the user
     * enters a username and password. On success, it will not return. If an error occurs,
     * it will return the error code.
     *
     * @param string $authStateId  The identifier of the authentication state.
     * @param string $username  The username the user wrote.
     * @param string $password  The password the user wrote.
     * @return string Error code in the case of an error.
     */
    public static function handleUserPassLogin($authStateId, $username, $password) {
        assert('is_string($authStateId)');
        assert('is_string($username)');
        assert('is_string($password)');

        /* Here we retrieve the state array we saved in the authenticate-function. */
        $state = SimpleSAML_Auth_State::loadState($authStateId, sspmod_authTiqr_Auth_Tiqr::STAGEID);

        /* Retrieve the authentication source we are executing. */
        assert('array_key_exists(self::AUTHID, $state)');
        $source = SimpleSAML_Auth_Source::getById($state[sspmod_authTiqr_Auth_Tiqr::USERPASSSOURCEID]);
        if ($source === NULL) {
            throw new Exception('Could not find authentication source with id ' . $state[sspmod_authTiqr_Auth_Tiqr::USERPASSSOURCEID]);
        }

        /*
         * $source now contains the authentication source on which authenticate()
         * was called. We should call login() on the same authentication source.
         */

        try {
            /* Attempt to log in. */
            $attributes = $source->login($username, $password);
        } catch (SimpleSAML_Error_Error $e) {
            /*
             * Login failed. Return the error code to the login form, so that it
             * can display an error message to the user.
             */
            return $e->getErrorCode();
        }

        /* Save the attributes we received from the login-function in the $state-array. */
        assert('is_array($attributes)');
        $state['Attributes'] = $attributes;

        /* Return control to simpleSAMLphp after successful authentication. */
        SimpleSAML_Auth_Source::completeAuth($state);
    }    
    
    /**
     * Logout. 
     * 
     * @see SimpleSAML_Auth_Source::logout()
     */
    public function logout(&$state)
    {
        parent::logout($state);
        $server =  sspmod_authTiqr_Auth_Tiqr::getServer(false);
        $session = SimpleSAML_Session::getInstance();
        $sessionId = $session->getSessionId();
        $server->logout($sessionId);
    }
}