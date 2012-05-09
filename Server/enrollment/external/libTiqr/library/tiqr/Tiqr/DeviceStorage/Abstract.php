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
 * The abstract baseclass for DeviceStorage implementations
 * @author ivo
 *
 */
abstract class Tiqr_DeviceStorage_Abstract
{
    /**
     * The options available to the devicestorage implementation
     * @var array
     */
    protected $_options = array();
    
    /**
     * get a deviceToken for a certain notificationToken.
     * @param String $notificationToken
     * @return String deviceToken
     */
    public abstract function getDeviceToken($notificationToken);
           
    /**
     * Initialize the devicestorage instance right after creation. 
     * The derived classes may optionally use this to initialize the
     * storage.
     */
    public function init()
    {
        
    }

    /**
     * Create an instance of a device storage. Should not be used directly, as
     * the Tiqr_DeviceStorage factory will call this for you.
     * @param array $options The options for the s
     */
    public function __construct($options=array())
    {
        $this->_options = $options;        
    }
        
}