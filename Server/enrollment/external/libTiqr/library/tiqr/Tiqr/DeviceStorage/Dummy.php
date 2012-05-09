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
 * A simple implementation of a DeviceStorage. This treats every
 * devicetoken as if it were a notificationtoken. Not recommended
 * for production setups.
 * @author ivo
 *
 */
class Tiqr_DeviceStorage_Dummy extends Tiqr_DeviceStorage_Abstract
{
    /**
     *Get a deviceToken for a given notificationToken.
     */   
    public function getDeviceToken($notificationToken)
    {
        return $notificationToken;
    }
}