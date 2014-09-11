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
     * @param array $secretoptions  The options to pass to the secret storage
     *                              instance. See the documentation
     *                              in the UserSecretStorage/ subdirectory for
     *                              options per type.
     *
     * @return Tiqr_UserStorage_Interface
     *
     * @throws Exception
     */
    public static function getStorage($type="file", $options=array(), $secretoptions=array())
    {
        switch ($type) {
            case "file":
                require_once("Tiqr/UserStorage/File.php");
                $instance = new Tiqr_UserStorage_File($options, $secretoptions);
                break;
            case "ldap":
                require_once("Tiqr/UserStorage/Ldap.php");
                $instance = new Tiqr_UserStorage_Ldap($options, $secretoptions);
                break;
            case "pdo":
                require_once("Tiqr/UserStorage/Pdo.php");
                $instance = new Tiqr_UserStorage_Pdo($options, $secretoptions);
                break;
            default: 
                if (!isset($type)) {
                    throw new Exception('Class name not set');
                } elseif (!class_exists($type)) {
                    throw new Exception('Class not found: ' . var_export($type, TRUE));
                }
                $instance = new $type($options, $secretoptions);
        }

        return $instance;
    }
}
