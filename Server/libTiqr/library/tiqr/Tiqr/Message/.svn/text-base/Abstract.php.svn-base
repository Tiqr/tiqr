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


/**
 * Abstract base class for notification messages.
 */
abstract class Tiqr_Message_Abstract
{
    private $_options;
    private $_id;
    private $_address;
    private $_text;
    private $_properties = array();
    
    /**
     * Construct a new message.
     *
     * @param array $options configuration options
     */
    public function __construct($options)
    {
        $this->_options = $options;
    }
    
    /**
     * Returns the configuration options.
     *
     * @return array configuration options
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * Returns the message id.
     *
     * @return string message id
     */
    public function getId() 
    {
        return $this->_id;
    }
    
    /**
     * Sets the message id.
     *
     * @param string $id message id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }
    
    /**
     * Returns the device address.
     *
     * @return string device address
     */
    public function getAddress()
    {
        return $this->_address;
    }
    
    /**
     * Sets the device address.
     *
     * @param string $address device address
     */
    public function setAddress($address)
    {
        $this->_address = $address;
    }

    /**
     * Returns the message text.
     *
     * @return string message text
     */
    public function getText()
    {
        return $this->_text;
    }
    
    /**
     * Sets the message text.
     *
     * @param string message text
     */
    public function setText($text)
    {
        $this->_text = $text;
    }
    
    /**
     * Returns the value for a custom property.
     *
     * @param string $name property name
     *
     * @return mixed property value
     */
    public function getCustomProperty($name)
    {
        return $this->_properties[$name];
    }
    
    /**
     * Returns all custom properties.
     *
     * @return array custom properties
     */
    public function getCustomProperties()
    {
        return $this->_properties;
    }
    
    /**
     * Sets the value for a custom property.
     *
     * @param string $name property name
     * @param mixed $value property value
     */
    public function setCustomProperty($name, $value)
    {
        $this->_properties[$name] = $value;
    }
    
    /**
     * Send message.
     *
     * @throws Tiqr_Message_Exception_AuthError
     * @throws Tiqr_Message_Exception_SendError
     * @throws Tiqr_Message_Exception_InvalidDevice     
     */    
    public abstract function send();
}