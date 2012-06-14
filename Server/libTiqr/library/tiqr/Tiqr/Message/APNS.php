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
require_once('Tiqr/Message/Abstract.php');

require_once('Zend/Mobile/Push/Apns.php');
require_once('Zend/Mobile/Push/Message/Apns.php');

/**
 * Apple Push Notification Service message class.
 * @author peter
 */
class Tiqr_Message_APNS extends Tiqr_Message_Abstract
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
     * @throws Tiqr_Message_Exception_AuthError
     */
    private static function _getService($options)
    {
        $certificate = $options['apns.certificate'];
        $uri = $options['apns.environment'] == 'production' ? Zend_Mobile_Push_Apns::SERVER_PRODUCTION_URI : Zend_Mobile_Push_Apns::SERVER_SANDBOX_URI;
        
        $key = "{$certificate}@{$uri}";
        
        if (!isset(self::$_services[$key])) {
            $service = new Zend_Mobile_Push_Apns();
            $service->setCertificate($certificate);
            $service->connect($uri);
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
     */
    public function send()
    {
        $service = self::_getService($this->getOptions());

        $message = new Zend_Mobile_Push_Message_Apns();
        $message->setToken($this->getAddress());
        $message->setId($this->getId());
        $message->setAlert($this->getText());
        $message->setSound('default');
        $message->setExpire(30);
        foreach ($this->getCustomProperties() as $name => $value) {
            $message->addCustomProperty($name, $value);
        }

        try {
            $service->send($message);
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

        $service->close();
    }
}
