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
 * @author Patrick Honing <Patrick.Honing@han.nl>
 * 
 * @package tiqr
 *
 * @license New BSD License - See LICENSE file for details.
 *
 * @copyright (C) 2010-2012 SURFnet BV
 * 
 * Create SQL table (MySQL):
 * CREATE TABLE `tiqruser` (`userid` varchar(10) PRIMARY KEY, `displayname` varchar(45),`secret` varchar(100),`blocked` int,`loginattempts` int,
 * `tmpblockattempts` int,`tmpblocktimestamp` varchar(45) default NULL,`notificationtype` varchar(10),`notificationaddress` varchar(45))
 * 
 */


/**
 * This user storage implementation implements a simple user storage using json files.
 * This is mostly for demonstration and development purposes. In a production environment
 * please supply your own implementation that hosts the data in your user database OR
 * in a secure (e.g. hardware encrypted) storage.
 * @author ivo
 */
class Tiqr_UserStorage_Pdo extends Tiqr_UserStorage_Abstract
{
//    protected $_path;
    private $handle = null;
    private $errormsg = null;
    private $tablename;
    
    /**
     * Create an instance
     * @param $config
     */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->tablename = $config['table'];
        try {
            $this->handle = new PDO($config['dsn'],$config['username'],$config['password']);
        } catch (PDOException $e) {
            $this->errormsg = $e->getMessage();
            return false;
        }
    }

    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::createUser()
     */
    public function createUser($userId, $displayName)
    {
        if ($this->userExists($userId)) {
            $query = "UPDATE ".$this->tablename." SET displayname='$displayName' WHERE userid='$userId'";
        } else {
            $query = "INSERT INTO ".$this->tablename." (userid,displayname) VALUES ('$userId','$displayName')";
        }
        
        return $this->handle->query($query);
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::userExists()
     */
    public function userExists($userId)
    {
        $query = "SELECT userid FROM ".$this->tablename." WHERE userid='$userId'";
        return $this->handle->query($query)->fetchColumn();
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getDisplayName()
     */
    public function getDisplayName($userId)
    {
        $query = "SELECT displayname FROM ".$this->tablename." WHERE userid='$userId'";
        return $this->handle->query($query)->fetchColumn();
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getSecret()
     */
    protected function _getEncryptedSecret($userId)
    {
        $query = "SELECT secret FROM ".$this->tablename." WHERE userid='$userId'";
        return $this->handle->query($query)->fetchColumn();
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::setSecret()
     */
    protected function _setEncryptedSecret($userId, $secret)
    {
        $query = "UPDATE ".$this->tablename." SET secret='$secret' WHERE userid='$userId'";
        return $this->handle->query($query);
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getNotificationType()
     */
    public function getNotificationType($userId)
    {
        $query = "SELECT notificationtype FROM ".$this->tablename." WHERE userid='$userId'";
        return $this->handle->query($query)->fetchColumn();
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::setNotificationType()
     */
    public function setNotificationType($userId, $type)
    {
        $query = "UPDATE ".$this->tablename." SET notificationtype='$type' WHERE userid='$userId'";
        return $this->handle->query($query);
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getNotificationAddress()
     */
    public function getNotificationAddress($userId)
    {
        $query = "SELECT notificationaddress FROM ".$this->tablename." WHERE userid='$userId'";
        return $this->handle->query($query)->fetchColumn();
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::setNotificationAddress()
     */
    public function setNotificationAddress($userId, $address)
    {
        $query = "UPDATE ".$this->tablename." SET notificationaddress='$address' WHERE userid='$userId'";
        return $this->handle->query($query);
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp-module/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getFailedLoginAttempts()
     */
    public function getLoginAttempts($userId)
    {
        $query = "SELECT loginattempts FROM ".$this->tablename." WHERE userid='$userId'";
        return $this->handle->query($query)->fetchColumn();
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp-module/authTiqr/lib/User/sspmod_authTiqr_User_Interface::setFailedLoginAttempts()
     */
    public function setLoginAttempts($userId, $amount)
    {
        $query = "UPDATE ".$this->tablename." SET loginattempts='$amount' WHERE userid='$userId'";
        return $this->handle->query($query);
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp-module/authTiqr/lib/User/sspmod_authTiqr_User_Interface::isBlocked()
     */
    public function isBlocked($userId, $duration)
    {
        $query = "SELECT blocked FROM ".$this->tablename." WHERE userid='$userId'";
        return ($this->handle->query($query)->fetchColumn() == 1);
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp-module/authTiqr/lib/User/sspmod_authTiqr_User_Interface::block()
     */
    public function setBlocked($userId, $blocked)
    {
        $query = "UPDATE ".$this->tablename." SET blocked='".($blocked) ? "1" : "0"."' WHERE userid='$userId'";
        return $this->handle->query($query);
    }
    
    /**
     * (non-PHPdoc)
     * @see libTiqr/library/tiqr/Tiqr/UserStorage/Tiqr_UserStorage_Interface::setTemporaryBlockAttempts()
     */
    public function setTemporaryBlockAttempts($userId, $amount) {
        $query = "UPDATE ".$this->tablename." SET tmpblockattempts='$amount' WHERE userid='$userId'";
        return $this->handle->query($query);
    }
    
    /**
     * (non-PHPdoc)
     * @see libTiqr/library/tiqr/Tiqr/UserStorage/Tiqr_UserStorage_Interface::getTemporaryBlockAttempts()
     */
    public function getTemporaryBlockAttempts($userId) {
        if ($this->userExists($userId)) {
            $query = "SELECT tmpblockattempts FROM ".$this->tablename." WHERE userid='$userId'";
            return $this->handle->query($query)->fetchColumn();
        }
        return 0;
    }
    
    /**
     * (non-PHPdoc)
     * @see libTiqr/library/tiqr/Tiqr/UserStorage/Tiqr_UserStorage_Interface::setTemporaryBlockTimestamp()
     */
    public function setTemporaryBlockTimestamp($userId, $timestamp)
    {
        $query = "UPDATE ".$this->tablename." SET tmpblocktimestamp='$timestamp' WHERE userid='$userId'";
        return $this->handle->query($query);
    }
            
    /**
     * (non-PHPdoc)
     * @see libTiqr/library/tiqr/Tiqr/UserStorage/Tiqr_UserStorage_Interface::getTemporaryBlockTimestamp()
     */
    public function getTemporaryBlockTimestamp($userId)
    {
        if ($this->userExists($userId)) {
            $query = "SELECT tmpblocktimestamp FROM ".$this->tablename." WHERE userid='$userId'";
            $timestamp = $this->handle->query($query)->fetchColumn(); 
            if (null !== $timestamp) {
                return $timestamp;
            }
        }
        return false;
    }
    
}
