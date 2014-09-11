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
 * Class implementing a factory to retrieve user secrets.
 *
 * @author lineke
 */
class Tiqr_UserSecretStorage
{
    /**
     * Get a secret storage of a certain type (default: 'file')
     *
     * @param String $type The type of storage to create. Supported
     *                     types are 'file', 'ldap', 'pdo' or 'oathservice'.
     * @param array $options The options to pass to the storage
     *                       instance. See the documentation
     *                       in the UserSecretStorage/ subdirectory for
     *                       options per type.
     *
     * @return Tiqr_UserSecretStorage_Interface
     */
    public static function getSecretStorage($type="file", $options=array())
    {
        switch ($type) {
            case "file":
                require_once("Tiqr/UserSecretStorage/File.php");
                $instance = new Tiqr_UserSecretStorage_File($options);
                break;
            case "ldap":
                require_once("Tiqr/UserSecretStorage/Ldap.php");
                $instance = new Tiqr_UserSecretStorage_Ldap($options);
                break;
            case "pdo":
                require_once("Tiqr/UserSecretStorage/Pdo.php");
                $instance = new Tiqr_UserSecretStorage_Pdo($options);
                break;
            case "oathservice":
                require_once("Tiqr/UserSecretStorage/Oathservice.php");
                $instance = new Tiqr_UserSecretStorage_Oathservice($options);
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
