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
 * 
 * Create SQL table (MySQL):
 * CREATE TABLE `tiqrstate` (`key` varchar(255) PRIMARY KEY,`expire` int,`value` text);
 * 
 */


class Tiqr_StateStorage_Pdo extends Tiqr_StateStorage_Abstract
{    
    private $handle = null;
    private $tablename;
        
    private function keyExists($key)
    {
        $sth = $this->handle->prepare("SELECT `key` FROM ".$this->tablename." WHERE `key` = ?");
        $sth->execute(array($key));
        return $sth->fetchColumn();
    }
    
    private function cleanExpired() {
        $sth = $this->handle->prepare("DELETE FROM ? WHERE `expire` < ?");
        $sth->execute(array(time()));
    }
    
    /**
     * (non-PHPdoc)
     * @see library/tiqr/Tiqr/StateStorage/Tiqr_StateStorage_Abstract::setValue()
     */
    public function setValue($key, $value, $expire=0)
    {
        if ($this->keyExists($key)) {
            $sth = $this->handle->prepare("UPDATE ".$this->tablename." SET `value` = ?, `expire` = ? WHERE `key` = ?");
        } else {
            $sth = $this->handle->prepare("INSERT INTO ".$this->tablename." (`value`,`expire`,`key`) VALUES (?,?,?)");
        }
        $sth->execute(array(serialize($value),time()+$expire,$key));
    }
        
    /**
     * (non-PHPdoc)
     * @see library/tiqr/Tiqr/StateStorage/Tiqr_StateStorage_Abstract::unsetValue()
     */
    public function unsetValue($key)
    {
        $sth = $this->handle->prepare("DELETE FROM ".$this->tablename." WHERE `key` = ?");
        $sth->execute(array($key));
    }
    
    /**
     * (non-PHPdoc)
     * @see library/tiqr/Tiqr/StateStorage/Tiqr_StateStorage_Abstract::getValue()
     */
    public function getValue($key)
    {
        if (rand(0, 1000) < 10) {
            $this->cleanExpired();
        }
        if ($this->keyExists($key)) {
            $sth = $this->handle->prepare("SELECT `value` FROM ".$this->tablename." WHERE `key` = ?");
            $sth->execute(array($key));
            $result = unserialize($sth->fetchColumn());
            return  $result;
        }
        return NULL;
    }
    
    public function __construct($config=array())
    {
        $this->tablename = $config['table'];
        $this->handle = new PDO($config['dsn'],$config['username'],$config['password']);
    }
    
}
