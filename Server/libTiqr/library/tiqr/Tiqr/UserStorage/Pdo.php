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
 * This user storage implementation implements a user storage using PDO.
 * It is usable for any database with a PDO driver
 * 
 * @author Patrick Honing <Patrick.Honing@han.nl>
 */
class Tiqr_UserStorage_Pdo extends Tiqr_UserStorage_Abstract
{
    private $handle = null;
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
            return false;
        }
    }

    public function createUser($userId, $displayName)
    {
        if ($this->userExists($userId)) {
            $sth = $this->handle->prepare("UPDATE ".$this->tablename." SET displayname = ? WHERE userid = ?");
        } else {
            $sth = $this->handle->prepare("INSERT INTO ".$this->tablename." (displayname,userid) VALUES (?,?)");
        }
        $sth->execute(array($displayName,$userId));
    }
    
    public function userExists($userId)
    {
        $sth = $this->handle->prepare("SELECT userid FROM ".$this->tablename." WHERE userid = ?");
        $sth->execute(array($userId));
        return $sth->fetchColumn();
    }
    
    public function getDisplayName($userId)
    {
        $sth = $this->handle->prepare("SELECT displayname FROM ".$this->tablename." WHERE userid = ?");
        $sth->execute(array($userId));
        return $sth->fetchColumn();
    }
    
    protected function _getEncryptedSecret($userId)
    {
        $sth = $this->handle->prepare("SELECT secret FROM ".$this->tablename." WHERE userid = ?");
        $sth->execute(array($userId));
        return $sth->fetchColumn();
    }
    
    protected function _setEncryptedSecret($userId, $secret)
    {
        $sth = $this->handle->prepare("UPDATE ".$this->tablename." SET secret = ? WHERE userid = ?");
        $sth->execute(array($secret,$userId));
    }
    
    public function getNotificationType($userId)
    {
        $sth = $this->handle->prepare("SELECT notificationtype FROM ".$this->tablename." WHERE userid = ?");
        $sth->execute(array($userId));
        return $sth->fetchColumn();
    }
    
    public function setNotificationType($userId, $type)
    {
        $sth = $this->handle->prepare("UPDATE ".$this->tablename." SET notificationtype = ? WHERE userid = ?");
        $sth->execute(array($type,$userId));
    }
    
    public function getNotificationAddress($userId)
    {
        $sth = $this->handle->prepare("SELECT notificationaddress FROM ".$this->tablename." WHERE userid = ?");
        $sth->execute(array($userId));
        return $sth->fetchColumn();
    }
    
    public function setNotificationAddress($userId, $address)
    {
        $sth = $this->handle->prepare("UPDATE ".$this->tablename." SET notificationaddress = ?  WHERE userid = ?");
        $sth->execute(array($address,$userId));
    }
    
    public function getLoginAttempts($userId)
    {
        $sth = $this->handle->prepare("SELECT loginattempts FROM ".$this->tablename." WHERE userid = ?");
        $sth->execute(array($userId));
        return $sth->fetchColumn();
    }
    
    public function setLoginAttempts($userId, $amount)
    {
        $sth = $this->handle->prepare("UPDATE ".$this->tablename." SET loginattempts = ? WHERE userid = ?");
        $sth->execute(array($amount,$userId));
    }
    
    public function isBlocked($userId, $duration)
    {
        if ($this->userExists($userId)) {
            $sth = $this->handle->prepare("SELECT blocked FROM ".$this->tablename." WHERE userid = ?");
            $sth->execute(array($userId));
            $blocked = ($sth->fetchColumn() == 1);
            $timestamp = $this->getTemporaryBlockTimestamp($userId);
            // if not blocked or block is expired, return false
            if (!$blocked || (false !== $timestamp && false != $duration && (strtotime($timestamp) + duration * 60) < time())) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function setBlocked($userId, $blocked)
    {
        $sth = $this->handle->prepare("UPDATE ".$this->tablename." SET blocked = ? WHERE userid = ?");
        $sth->execute(array(
                ($blocked) ? "1" : "0",
                $userId
        ));
    }
    
    public function setTemporaryBlockAttempts($userId, $amount) {
        $sth = $this->handle->prepare("UPDATE ".$this->tablename." SET tmpblockattempts = ? WHERE userid = ?");
        $sth->execute(array($amount,$userId));
    }
    
    public function getTemporaryBlockAttempts($userId) {
        if ($this->userExists($userId)) {
            $sth = $this->handle->prepare("SELECT tmpblockattempts FROM ".$this->tablename." WHERE userid = ?");
            $sth->execute(array($userId));
            return $sth->fetchColumn();
        }
        return 0;
    }
    
    public function setTemporaryBlockTimestamp($userId, $timestamp)
    {
        $sth = $this->handle->prepare("UPDATE ".$this->tablename." SET tmpblocktimestamp = ? WHERE userid = ?");
        $sth->execute(array($timestamp,$userId));
    }
            
    public function getTemporaryBlockTimestamp($userId)
    {
        if ($this->userExists($userId)) {
            $sth = $this->handle->prepare("SELECT tmpblocktimestamp FROM ".$this->tablename." WHERE userid = ?");
            $sth->execute(array($userId));
            $timestamp = $sth->fetchColumn(); 
            if (null !== $timestamp) {
                return $timestamp;
            }
        }
        return false;
    }
    
}
