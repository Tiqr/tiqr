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

require_once 'Tiqr/UserStorage/Abstract.php';

/**
 * This user storage implementation implements a simple user storage using json files.
 * This is mostly for demonstration and development purposes. In a production environment
 * please supply your own implementation that hosts the data in your user database OR
 * in a secure (e.g. hardware encrypted) storage.
 * @author ivo
 */
class Tiqr_UserStorage_File extends Tiqr_UserStorage_Abstract
{
    protected $_path;

    /**
     * Create an instance
     * @param $config
     */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->_path = $config["path"];
    }

    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::createUser()
     */
    public function createUser($userId, $displayName) 
    {
        $user = array("userId"=>$userId,
                      "displayName"=>$displayName);
        return $this->_saveUser($userId, $user);
    }

    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::userExists()
     */
    public function userExists($userId)
    {
        $user = $this->_loadUser($userId);
        return (is_array($user)||is_object($user)); 
    }

    /**
     * This function takes care of actually saving the user data to a JSON file.
     * @param String $userId
     * @param array $data
     */
    protected function _saveUser($userId, $data)
    {
        file_put_contents($this->getPath().$userId.".json", json_encode($data));
        return true;
    }
  
    /**
     * Retrieve the path where the json files are stored.
     * @return String
     */
    public function getPath()
    {
         if (substr($this->_path, -1)!="/") return $this->_path."/";
         return $this->_path;
    }

    /**
     * This function takes care of loading the user data from a JSON file.
     * @param String $userId
     * @return false if the data is not present, or an array containing the data.
     */
    protected function _loadUser($userId)
    {
        $fileName = $this->getPath().$userId.".json";
        if (file_exists($fileName)) { 
            return json_decode(file_get_contents($this->getPath().$userId.".json"), true);
        }
        return false;
    }

    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getDisplayName()
     */
    public function getDisplayName($userId) 
    {
        if ($data = $this->_loadUser($userId)) {
            return $data["displayName"];
        }
        return NULL;
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getSecret()
     */
    protected function _getEncryptedSecret($userId)
    {
        if ($data = $this->_loadUser($userId)) {
            if (isset($data["secret"])) {
                return $data["secret"];
            }
        }
        return NULL;
    }

    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::setSecret()
     */
    protected function _setEncryptedSecret($userId, $secret)
    {
        $data = $this->_loadUser($userId);
        $data["secret"] = $secret;
        $this->_saveUser($userId, $data);
    } 
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getNotificationType()
     */
    public function getNotificationType($userId)
    {
        if ($data = $this->_loadUser($userId)) {
            if (isset($data["notificationType"])) {
               return $data["notificationType"];
            }
        }
        return NULL;
    }

    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::setNotificationType()
     */
    public function setNotificationType($userId, $type)
    {
        $data = $this->_loadUser($userId);
        $data["notificationType"] = $type;
        $this->_saveUser($userId, $data);
    }    
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getNotificationAddress()
     */
    public function getNotificationAddress($userId)
    {
        if ($data = $this->_loadUser($userId)) {
            if (isset($data["notificationAddress"])) {
               return $data["notificationAddress"];
            }
        }
        return NULL;
    }

    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::setNotificationAddress()
     */
    public function setNotificationAddress($userId, $address)
    {
        $data = $this->_loadUser($userId);
        $data["notificationAddress"] = $address;
        $this->_saveUser($userId, $data);
    } 

    /**
     * (non-PHPdoc)
     * @see simplesamlphp-module/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getFailedLoginAttempts()
     */
    public function getLoginAttempts($userId)
    {
        if ($data = $this->_loadUser($userId)) {
            if (isset($data["loginattempts"])) {
                return $data["loginattempts"];
            }
        }
        return 0;
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp-module/authTiqr/lib/User/sspmod_authTiqr_User_Interface::setFailedLoginAttempts()
     */
    public function setLoginAttempts($userId, $amount)
    {
        $data = $this->_loadUser($userId);
        $data["loginattempts"] = $amount;
        $this->_saveUser($userId, $data);
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp-module/authTiqr/lib/User/sspmod_authTiqr_User_Interface::isBlocked()
     */
    public function isBlocked($userId)
    {
        if ($data = $this->_loadUser($userId)) {
            if (!isset($data["blocked"]) || $data["blocked"]==false) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp-module/authTiqr/lib/User/sspmod_authTiqr_User_Interface::block()
     */
    public function setBlocked($userId, $blocked) 
    {
        $data = $this->_loadUser($userId);
        $data["blocked"] = $blocked;
        $this->_saveUser($userId, $data);
  
    }
    
}
