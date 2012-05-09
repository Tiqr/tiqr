<?php
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Session.php';

abstract class Tiqr_Controller_Abstract extends Zend_Controller_Action
{
    private $_tiqr;
    
    /**
     * Returns the Tiqr instance.
     *
     * @return Tiqr_Service Tiqr instance
     */
    protected function _getTiqr()
    {
        if ($this->_tiqr == null) {
            $this->_tiqr = $this->getFrontController()
                                    ->getParam('bootstrap')
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
}
