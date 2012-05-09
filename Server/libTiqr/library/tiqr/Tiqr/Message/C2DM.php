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


/** @internal base includes */
require_once("Tiqr/Message/Abstract.php");

/**
 * Android Cloud To Device Messaging message.
 * @author peter
 */
class Tiqr_Message_C2DM extends Tiqr_Message_Abstract
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
        if (self::$_libraryImported) {
            return;
        }
        
        ini_set('include_path', ini_get('include_path').':'.dirname(__FILE__).'/../../../zend/'.':'.dirname(__FILE__).'/../../../c2dm/');
        require_once 'Zend/GData/ClientLogin.php';
        require_once 'Zend/Service/Google/C2dm.php';            
        self::$_libraryImported = true;
    }
    
    /**
     * Factory method for returning a C2DM service instance for the given 
     * configuration options.
     *
     * @param array $options configuration options
     *
     * @return Zend_Service_Google_C2dm service instance
     *
     * @throws Tiqr_Message_Exception_AuthFailure
     */
    private static function _getService($options)
    {
        $username = $options['c2dm.username'];
        $password = $options['c2dm.password'];        
        $application = $options['c2dm.application'];
        
        $key = "{$username}:{$password}@{$application}";
        
        if (!isset(self::$_services[$key])) {
            try {
                $client = Zend_GData_ClientLogin::getHttpClient($username, $password, Zend_Service_Google_C2dm::AUTH_SERVICE_NAME, null, $application);
            } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
                throw new Tiqr_Message_Exception_AuthFailure("Manual login required", $e);
            } catch (Zend_Gdata_App_AuthException $e) {
                throw new Tiqr_Message_Exception_AuthFailure("Problem authenticating", $e);                
            }            
            
            $service = new Zend_Service_Google_C2dm();
            $service->setLoginToken($client->getClientLoginToken());        
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
     */
    public function send()
    {
        self::_importLibrary($this->getOptions());
        
        $service = self::_getService($this->getOptions());
        
        $data = $this->getCustomProperties();
        $data['text'] = $this->getText();
        $message = new Zend_Service_Google_C2dm_Message($this->getAddress(), $this->getId(), $data);

        try {
            $service->sendMessage($message);
        } catch (Zend_Service_Google_C2dm_Exception_QuotaExceeded $e) {
            throw new Tiqr_Message_Exception_SendFailure("Quota exceeded", true, $e);
        } catch (Zend_Service_Google_C2dm_Exception_ServerUnavailable $e) {
            throw new Tiqr_Message_Exception_SendFailure("Server unavailable", true, $e);
        } catch (Zend_Service_Google_C2dm_Exception_InvalidRegistration $e) {
            throw new Tiqr_Message_Exception_InvalidDevice("Invalid registration", $e);
        } catch (Zend_Service_Google_C2dm_Exception_NotRegistered $e) {
            throw new Tiqr_Message_Exception_InvalidDevice("Not registered", $e);
        } catch (Zend_Service_Google_C2dm_Exception $e) {
            throw new Tiqr_Message_Exception_SendFailure("General send error", false, $e);
        }
    }
}