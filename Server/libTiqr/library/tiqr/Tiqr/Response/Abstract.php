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
 * @copyright (C) 2010-2012 SURFnet BV
 */

/**
 * The abstract implementation of the interface that creates the response object.
 *
 * @author Lineke Kerckhoffs-Willems <lineke@egeniq.com>
 *
 */
abstract class Tiqr_Response_Abstract implements Tiqr_Response_Interface
{
    /**
     * Create the response class
     * 
     * @param string $type The type of response (json or plain supported)
     * 
     * @return object Tiqr response class
     */
    public function createResponse($type = 'plain') 
    {
        switch ($type) {
            case 'json':
                require_once("Tiqr/Response/JSON.php");
                $instance = new Tiqr_Response_JSON();
                break;
            case 'plain':
            default: 
                require_once("Tiqr/Response/Plain.php");
                $instance = new Tiqr_Response_Plain();
        }
        
        return $instance;
    }
}