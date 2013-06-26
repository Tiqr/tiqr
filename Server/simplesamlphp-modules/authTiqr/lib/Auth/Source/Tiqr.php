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
 * Tiqr authentication module
 * 
 * Configure it by adding an entry to config/authsources.php such as this:
 *
 *    'tiqr' => array(
 *          'authTiqr:Tiqr',
 *          ),
 *
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class sspmod_authTiqr_Auth_Source_Tiqr extends SimpleSAML_Auth_Source {


    /**
     * The key of the AuthId field in the state.
     */
    const AUTHID = 'sspmod_authTiqr_Auth_Source_Tiqr.AuthId';


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

        $id = SimpleSAML_Auth_State::saveState($state, sspmod_authTiqr_Auth_Tiqr::STAGEID);

        $server =  sspmod_authTiqr_Auth_Tiqr::getServer(false);
        
        $session = SimpleSAML_Session::getInstance();
        $sessionId = $session->getSessionId();
        
        $user = $server->getAuthenticatedUser($sessionId);
        
        if (empty($user)) {
    
            $url = SimpleSAML_Module::getModuleURL('authTiqr/login.php');
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
     * Logout.
     * 
     * @see SimpleSAML_Auth_Source::logout()
     */   
    public function logout(&$state) 
    {
        $server =  sspmod_authTiqr_Auth_Tiqr::getServer(false);
        $session = SimpleSAML_Session::getInstance();
        $sessionId = $session->getSessionId();
        $server->logout($sessionId);
    }
}