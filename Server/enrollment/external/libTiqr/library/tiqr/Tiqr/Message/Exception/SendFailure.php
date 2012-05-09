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
require_once 'Message/Exception.php';

/**
 * Exception in case of a message that cannot be send.
 */
class Tiqr_Message_Exception_SendFailure extends Tiqr_Message_Exception
{
    private $_temporary;
    
    /**
     * Constructor
     *
     * @param string    $message    exception message
     * @param boolean   $temporary  temporary failure?
     * @param Exception $parent     parent exception
     */
    public function __construct($message, $temporary=false, Exception $parent=null)
    {
        parent::__construct($message, $parent);
        $this->_temporary = $temporary;
    }
    
    /**
     * Is temporary failure? E.g. it's possible to try again later on?
     *
     * @return boolean is temporary failure?
     */
    public function isTemporary()
    {
        return $this->_temporary;
    }
}