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
 * Class implementing a factory to retrieve user data.
 *
 * @author ivo
 */
class Tiqr_UserStorage
{
    /**
     * Get a storage of a certain type (default: 'file')
     *
     * @param String $type The type of storage to create. Supported
     *                     types are 'file', 'ldap' or the full class name.
     * @param array $options The options to pass to the storage
     *                       instance. See the documentation
     *                       in the UserStorage/ subdirectory for
     *                       options per type.
     *
     * @return Tiqr_UserStorage_Interface
     */
    public static function getStorage($type="file", $options=array())
    {
        switch ($type) {
            case "file":
                require_once("Tiqr/UserStorage/File.php");
                $instance = new Tiqr_UserStorage_File($options);
                break;
            case "ldap":
                require_once("Tiqr/UserStorage/Ldap.php");
                $instance = new Tiqr_UserStorage_Ldap($options);
                break;
            case "pdo":
                require_once("Tiqr/UserStorage/Pdo.php");
                $instance = new Tiqr_UserStorage_Pdo($options);
                break;
            default: 
                if (!isset($type)) {
                    throw new Exception('Class name not set');
                } elseif (!class_exists($type)) {
                    throw new Exception('Class not found: ' . var_export($type, TRUE));
                } elseif (!is_subclass_of($type, 'Tiqr_UserStorage_Abstract')) {
                    throw new Exception('Class ' . $type . ' not subclass of Tiqr_UserStorage_Abstract');
                }
                $instance = new $type($options);
        }
        
        return $instance;
    }
}
