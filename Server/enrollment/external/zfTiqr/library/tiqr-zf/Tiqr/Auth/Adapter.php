<?php
require_once 'Zend/Auth/Adapter/Interface.php';
require_once 'Zend/Session.php';

/**
 * Tiqr authentication adapter for Zend Framework.
 *
 * Zend_Auth session store bekijken.
 */
class Tiqr_Auth_Adapter implements Zend_Auth_Adapter_Interface 
{
    private $_tiqr;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
    }
    
    /**
     * Returns the Tiqr instance.
     *
     * @return Tiqr_Service Tiqr instance
     */    
    protected function _getTiqr()
    {
        if ($this->_tiqr == null) {
            $frontController = Zend_Controller_Front::getInstance();
            $this->_tiqr = $frontController->getParam('bootstrap')
                                               ->getResource('tiqr');        
        }
        
        return $this->_tiqr;        
    }
    
    /**
     * Returns the session identifier for this enrollment.
     *
     * Defaults to the PHP session identifier.
     *
     * @return string session identifier
     */
    protected function _getSessionId()
    {
        Zend_Session::start();
        return Zend_Session::getId();
    }    

    /**
     * Try to authenticate.
     */
    public function authenticate() 
    {
        $user = $this->_getTiqr()->getAuthenticatedUser($this->_getSessionId());     
                
        if ($user !== null && $user !== false) {
            return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $user);
        } else {
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, null);            
        }
    }
}
