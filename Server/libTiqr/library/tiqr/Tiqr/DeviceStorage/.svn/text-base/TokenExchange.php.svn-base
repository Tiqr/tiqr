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
 * @author Ivo Jansch <ivo@egeniq.com>
 * 
 * @package tiqr
 *
 * @license New BSD License - See LICENSE file for details.
 *
 * @copyright (C) 2010-2011 SURFnet BV
 */


/**
 * A DeviceStorage implementation that uses a tokenexhange server to swap
 * notificationTokens for deviceTokens.
 * 
 * The following options can be passed when creating a tokenexchange instance
 * (which should be done through the Tiqr_DeviceStorage factory):
 * - 'appid' the app identifier of the client app used to exchange tokens, 
 *           must match the appid used in the mobile client apps.
 * - 'url' the url of the tokenexchange service
 * 
 * @author ivo
 *
 */
class Tiqr_DeviceStorage_TokenExchange extends Tiqr_DeviceStorage_Abstract
{  
    /**
     * (non-PHPdoc)
     * @see library/tiqr/Tiqr/DeviceStorage/Tiqr_DeviceStorage_Abstract::getDeviceToken()
     */ 
    public function getDeviceToken($notificationToken)
    {
        $url = $this->_options["url"]."?appId=".$this->_options["appid"];
        
        $url.= "&notificationToken=".$notificationToken;
        
        $output = file_get_contents($url);
        if (stripos($output, "not found")!==false) return false;
        if (stripos($output, "error")!==false) return false;
        return trim($output);
    }
}