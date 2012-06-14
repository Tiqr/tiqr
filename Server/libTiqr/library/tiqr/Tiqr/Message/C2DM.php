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
require_once('Tiqr/Message/Abstract.php');

require_once('Zend/Gdata/ClientLogin.php');
require_once 'Zend/Mobile/Push/C2dm.php';
require_once 'Zend/Mobile/Push/Message/C2dm.php';

/**
 * Android Cloud To Device Messaging message.
 * @author peter
 */
class Tiqr_Message_C2DM extends Tiqr_Message_Abstract
{
    private static $_services = array();
    
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
                $client = Zend_GData_ClientLogin::getHttpClient($username, $password, Zend_Mobile_Push_C2dm::AUTH_SERVICE_NAME, null, $application);
            } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
                throw new Tiqr_Message_Exception_AuthFailure("Manual login required", $e);
            } catch (Zend_Gdata_App_AuthException $e) {
                throw new Tiqr_Message_Exception_AuthFailure("Problem authenticating", $e);                
            }            
            
            $service = new Zend_Mobile_Push_C2dm();
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
        $service = self::_getService($this->getOptions());
        
        $data = $this->getCustomProperties();
        $data['text'] = $this->getText();

        $message = new Zend_Mobile_Push_Message_C2dm();
        $message->setToken($this->getAddress());
        $message->setId($this->getId());
        $message->setData($data);

        try {
            $service->send($message);
        } catch (Zend_Mobile_Push_Exception_QuotaExceeded $e) {
            throw new Tiqr_Message_Exception_SendFailure("Device quota exceeded", true, $e);
        } catch (Zend_Mobile_Push_Exception_DeviceQuotaExceeded $e) {
            throw new Tiqr_Message_Exception_SendFailure("Quota exceeded", true, $e);
        } catch (Zend_Mobile_Push_Exception_ServerUnavailable $e) {
            throw new Tiqr_Message_Exception_SendFailure("Server unavailable", true, $e);
        } catch (Zend_Mobile_Push_Exception_InvalidToken $e) {
            throw new Tiqr_Message_Exception_InvalidDevice("Invalid token", $e);
        } catch (Zend_Mobile_Push_Exception_InvalidPayload $e) {
            throw new Tiqr_Message_Exception_SendFailure("Invalid payload", false, $e);
        } catch (Zend_Mobile_Push_Exception_InvalidTopic $e) {
            throw new Tiqr_Message_Exception_SendFailure("Invalid topic", false, $e);
        } catch (Zend_Mobile_Push_Exception $e) {
            throw new Tiqr_Message_Exception_SendFailure("General send error", false, $e);
        }
    }
}
