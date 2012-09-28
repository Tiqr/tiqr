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
        $query = "SELECT `key` FROM ".$this->tablename." WHERE `key`='$key'";
        return $this->handle->query($query)->fetchColumn();
    }
    
    private function cleanExpired() {
        
        $query = "DELETE FROM ".$this->tablename." WHERE `expire`<".time();
        $this->handle->query($query);
    }
            
    /**
     * (non-PHPdoc)
     * @see library/tiqr/Tiqr/StateStorage/Tiqr_StateStorage_Abstract::setValue()
     */
    public function setValue($key, $value, $expire=0)
    {
        if ($this->keyExists($key)) {
            $query = "UPDATE ".$this->tablename." SET `value`='$value' WHERE `key`='$key";
        } else {
            $query = "INSERT INTO ".$this->tablename." (`key`,`value`,`expire`) VALUES ('$key','".serialize($value)."','".(time()+$expire)."')";
        }
        return $this->handle->query($query);
    }
        
    /**
     * (non-PHPdoc)
     * @see library/tiqr/Tiqr/StateStorage/Tiqr_StateStorage_Abstract::unsetValue()
     */
    public function unsetValue($key)
    {
        $query = "DELETE FROM ".$this->tablename." WHERE `key`='$key'";
        return $this->handle->query($query);
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
            $query = "SELECT `value` FROM ".$this->tablename." WHERE `key`='$key'";
            $result = unserialize($this->handle->query($query)->fetchColumn());
            return  $result;
        }
        return NULL;
    }
    
    public function __construct($config=array())
    {
        parent::__construct($config);
        $this->tablename = $config['table'];
        $this->handle = new PDO($config['dsn'],$config['username'],$config['password']);
    }
    
}
