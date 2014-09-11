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
 * Class implementing a factory to retrieve the ocra service to use
 *
 * @author lineke
 */
class Tiqr_OcraService
{
    /**
     * Get a ocra service of a certain type (default: 'tiqr')
     *
     * @param String $type The type of ocra service to create. Supported
     *                     types are 'tiqr' or 'oathservice'.
     * @param array $options The options to pass to the ocra service
     *                       instance.
     *
     * @return Tiqr_OcraService_Interface
     */
    public static function getOcraService($type="tiqr", $options=array())
    {
        switch ($type) {
            case "tiqr":
                require_once("Tiqr/OcraService/Tiqr.php");
                $instance = new Tiqr_OcraService_Tiqr($options);
                break;
            case "oathservice":
                require_once("Tiqr/OcraService/Oathservice.php");
                $instance = new Tiqr_OcraService_Oathservice($options);
                break;
            default:
                if (!isset($type)) {
                    throw new Exception('Class name not set');
                } elseif (!class_exists($type)) {
                    throw new Exception('Class not found: ' . var_export($type, TRUE));
                }
                $instance = new $type($options);
        }

        return $instance;
    }
}
