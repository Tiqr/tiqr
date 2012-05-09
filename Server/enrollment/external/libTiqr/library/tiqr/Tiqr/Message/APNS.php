<?php
/**
 * This file is part of the tiqr project.
 * 
 * The tiqr project aims to provide an open implementation for 
 * authentication using mobile devices. It was initiated by 
 * SURFnet and developed by Egeniq.
 *
 * More information: http://www.tiqr.org
 *
 * @author Peter Verhage <peter@egeniq.com>
 * 
 * @package tiqr
 *
 * @license New BSD License - See LICENSE file for details.
 *
 * @copyright (C) 2010-2011 SURFnet BV
 */


/** @internal includes */
require_once("Tiqr/Message/Abstract.php");

/**
 * Apple Push Notification Service message class.
 * @author peter
 */
class Tiqr_Message_APNS extends Tiqr_Message_Abstract
{
    private static $_services = array();
    private static $_libraryImported = false;
    
    /**
     * Import library classes.
     *
     * @param array $options configuration options     
     */
    private static function _importLibrary($options)
    {
        require_once $options['apns.path'].'/ApnsPHP/Autoload.php';        
    }
    
    /**
     * Factory method for returning a C2DM service instance for the given 
     * configuration options.
     *
     * @param array $options configuration options
     *
     * @return Zend_Service_Google_C2dm service instance
     *
     * @throws Tiqr_Message_Exception_AuthError
     */
    private static function _getService($options)
    {
        $certificate = $options['apns.certificate'];
        $env = $options['apns.environment'] == 'production' ? ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION : ApnsPHP_Abstract::ENVIRONMENT_SANDBOX;
        
        $key = "{$certificate}@{$env}";
        
        if (!isset(self::$_services[$key])) {
            $service = new ApnsPHP_Push($env, $certificate);            
            self::$_services[$key] = $service;
        }
        
        return self::$_services[$key];
    }    

    /**
     * Send message.
     *
     * @throws Tiqr_Message_Exception_AuthFailure
     * @throws Tiqr_Message_Exception_SendFailure
     * @throws Tiqr_Message_Exception_InvalidDevice    
     *
     * @todo Improve error handling. 
     */
    public function send()
    {
        self::_importLibrary($this->getOptions());
        
        ob_start(); // todo: we don't have an apns logger yet, so it dumps to stdout. 

        $result = false;
        
        try {
            $service = self::_getService($this->getOptions());
            
            // @todo: use root certification
            //$service->setRootCertificationAuthority('entrust_root_certification_authority.pem');
           
            $service->connect();
    
            $message = new ApnsPHP_Message($this->getAddress());
            $message->setCustomIdentifier($this->getId());
            $message->setText($this->getText());
            $message->setSound();
            $message->setExpiry(30);
            foreach ($this->getCustomProperties() as $name => $value) {
                $message->setCustomProperty($name, $value);
            }
            
            $service->add($message);
            $service->send();
            $service->disconnect();
    
            $errorQueue = $service->getErrors();
            if (!empty($errorQueue)) {
                throw new Tiqr_Message_Exception_SendFailure("General send error", false);
            }
        } catch (Tiqr_Message_Exception_SendFailure $e) {
            throw $e;
        } catch (Exception $e) {
            ob_end_clean();               
            throw new Tiqr_Message_Exception_SendFailure("General send error", false, $e->getMessage());
        }
        
        ob_end_clean();        
    }
}