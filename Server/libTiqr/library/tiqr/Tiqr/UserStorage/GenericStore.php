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
abstract class Tiqr_UserStorage_GenericStore extends Tiqr_UserStorage_Abstract
{

    abstract protected function _loadUser($userId, $failOnNotFound = TRUE);
  
    abstract protected function _saveUser($userId, $data);

    abstract protected function _deleteUser($userId);


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
        $user = $this->_loadUser($userId, FALSE);
        return (is_array($user)||is_object($user)); 
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
    public function isBlocked($userId, $duration)
    {
        if ($data = $this->_loadUser($userId)) {
            $timestamp = $this->getTemporaryBlockTimestamp($userId);
            // if not blocked or block is expired, return false
            if (!isset($data["blocked"]) || $data["blocked"]==false || (false !== $timestamp && false != $duration && (strtotime($timestamp) + duration * 60) < time())) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * (non-PHPdoc)
     * @see libTiqr/library/tiqr/Tiqr/UserStorage/Tiqr_UserStorage_Interface::setTemporaryBlockAttempts()
     */
    public function setTemporaryBlockAttempts($userId, $amount) {
        $data = $this->_loadUser($userId);
        $data["temporaryBlockAttempts"] = $amount;
        $this->_saveUser($userId, $data);
    }
    
    /**
     * (non-PHPdoc)
     * @see libTiqr/library/tiqr/Tiqr/UserStorage/Tiqr_UserStorage_Interface::getTemporaryBlockAttempts()
     */
    public function getTemporaryBlockAttempts($userId) {
        if ($data = $this->_loadUser($userId)) {
            if (isset($data["temporaryBlockAttempts"])) {
                return $data["temporaryBlockAttempts"];
            }
        }
        return 0;
    }
    
    /**
     * (non-PHPdoc)
     * @see libTiqr/library/tiqr/Tiqr/UserStorage/Tiqr_UserStorage_Interface::setTemporaryBlockTimestamp()
     */
    public function setTemporaryBlockTimestamp($userId, $timestamp) {
        $data = $this->_loadUser($userId);
        $data["temporaryBlockTimestamp"] = $timestamp;
        $this->_saveUser($userId, $data);
    }
    
    /**
     * (non-PHPdoc)
     * @see libTiqr/library/tiqr/Tiqr/UserStorage/Tiqr_UserStorage_Interface::getTemporaryBlockTimestamp()
     */
    public function getTemporaryBlockTimestamp($userId) {
        if ($data = $this->_loadUser($userId)) {
            if (isset($data["temporaryBlockTimestamp"])) {
                return $data["temporaryBlockTimestamp"];
            }
        }
        return false;
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
