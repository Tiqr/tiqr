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
 * Filter for requiring the user to login using Tiqr before his authentication
 * is approved.
 *
 * This adds step-up authentication to an existing simpleSAMLphp authsource, 
 * for example logging in with a username/password first, then confirming the
 * login using a Tiqr app on a mobile device.
 *
 */
class sspmod_authTiqr_Auth_Process_Tiqr extends SimpleSAML_Auth_ProcessingFilter {

    protected $_uidAttribute = "uid";
    protected $_cnAttribute = "cn";
    
    /**
     * Initialize consent filter.
     *
     * This is the constructor for the consent filter. It validates and parses the configuration.
     *
     * @param array $config  Configuration information about this filter.
     * @param mixed $reserved  For future use.
     */
    public function __construct($config, $reserved) {
        parent::__construct($config, $reserved);
        
        if (isset($config["uidAttribute"])) {
            $this->_uidAttribute = $config["uidAttribute"];
        }
        
        if (isset($config["cnAttribute"])) { 
            $this->_cnAttribute = $config["cnAttribute"];
        }
        
        assert('is_array($config)');

    }


    /**
     * Process a authentication response.
     *
     * This function saves the state, and redirects the user to the page where the user
     * can authorize the release of the attributes.
     *
     * @param array $state  The state of the response.
     */
    public function process(&$state) {
        assert('is_array($state)');

        $session = SimpleSAML_Session::getInstance(); 
        
        // Register a logout handler so we can later log ourselves out when needed.
        // @todo, this doesn't work; simplesamlphp mailinglist has been notified
        $session->registerLogoutHandler('sspmod_authTiqr_Auth_Process_Tiqr', 'logout');
        
        $metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
        
        $sessionId = $session->getSessionId();
              
        $server = sspmod_authTiqr_Auth_Tiqr::getServer();
        $user = $server->getAuthenticatedUser($sessionId);
                                
        if (!empty($user)) {
            // User is already authenticated        
            return;
        }

        /* User interaction nessesary. Throw exception on isPassive request */  
        if (isset($state['isPassive']) && $state['isPassive'] == TRUE) {
            throw new SimpleSAML_Error_NoPassive('Unable to perform mobile authentication on passive request.');
        }
        if (!isset($state["Attributes"][$this->_uidAttribute])) {
            throw new SimpleSAML_Error_Exception('No user id present, is first factor authentication properly set up?');
        }
        $userId = $state["Attributes"][$this->_uidAttribute][0];
        $displayName = $state["Attributes"][$this->_cnAttribute][0];
                
        $state["tiqrUser"] = array("userId"=>$userId, "displayName"=>$displayName);        

        /* Save state and redirect. */
        $id = SimpleSAML_Auth_State::saveState($state, sspmod_authTiqr_Auth_Tiqr::STAGEID);
        $url = SimpleSAML_Module::getModuleURL('authTiqr/login.php');
        SimpleSAML_Utilities::redirect($url, array('AuthState' => $id));
    }
    
    public static function logout()
    {
        $server =  sspmod_authTiqr_Auth_Tiqr::getServer();
        $session = SimpleSAML_Session::getInstance();
        $sessionId = $session->getSessionId();
        $server->logout($sessionId);
    }
    
}