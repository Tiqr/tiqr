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

require_once 'Tiqr/UserStorage/File.php';

/**
 * This user storage implementation implements a simple user's secret storage using json files.
 * This is mostly for demonstration and development purposes. In a production environment
 * please supply your own implementation that hosts the data in your user database OR
 * in a secure (e.g. hardware encrypted) storage.
 * @author ivo
 */
class Tiqr_UserSecretStorage_File extends Tiqr_UserStorage_File implements Tiqr_UserSecretStorage_Interface
{
    /**
     * Create an instance
     *
     * @param array $config
     */
    public function __construct($config, $secretconfig = array())
    {
        $this->_path = $config["path"];
    }

    /**
     * Get the user's secret
     *
     * @param String $userId
     *
     * @return String The user's secret
     */
    public function getUserSecret($userId)
    {
        if ($data = $this->_loadUser($userId)) {
            if (isset($data["secret"])) {
                return $data["secret"];
            }
        }
        return NULL;
    }

    /**
     * Store a secret for a user
     *
     * @param String $userId
     * @param String $secret
     */
    public function setUserSecret($userId, $secret)
    {
        $data = $this->_loadUser($userId, false);
        $data["secret"] = $secret;
        $this->_saveUser($userId, $data);
    }
}
