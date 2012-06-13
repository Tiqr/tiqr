<?php

require_once 'Tiqr/Response/Interface.php';

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
     * @return object Tiqr response class
     */
    public function createResponse() 
    {
        require_once("Tiqr/Response/V1.php");
        $instance = new Tiqr_Response_V1();
        return $instance;
    }
}