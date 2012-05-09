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
 * Base exception for exception handling.
 */
class Tiqr_Message_Exception extends Exception
{
    /**
     * Constructor.
     *
     * @param string    $message    exception message
     * @param Exception $parent     parent exception
     */
     public function __construct($message, $parent=null)
     {
         parent::__construct($message, 0, $parent);
     }
}