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
 * @copyright (C) 2010-2011 SURFnet BV
 */


/**
 * File based implementation to store session state data. 
 * Note that it is more secure to use a memory based storage such as memcached.
 * This implementation is mostly for demo, test or experimental setups that 
 * do not have access to a memcache instance.
 * 
 * This StateStorage implementation has no options, files are always stored
 * in /tmp and prefixed with tiqr_state_*
 * 
 * @author ivo
 *
 */
class Tiqr_StateStorage_File extends Tiqr_StateStorage_Abstract
{
    /**
     * (non-PHPdoc)
     * @see library/tiqr/Tiqr/StateStorage/Tiqr_StateStorage_Abstract::setValue()
     */
    public function setValue($key, $value, $expire=0)
    {   
        $envelope = array("expire"=>$expire,
                          "createdAt"=>time(),
                          "value"=>$value);
        $filename = $this->_stateFilename($key);
        
        file_put_contents($filename, serialize($envelope));
        
        return $key;
    }

    /**
     * (non-PHPdoc)
     * @see library/tiqr/Tiqr/StateStorage/Tiqr_StateStorage_Abstract::unsetValue()
     */
    public function unsetValue($key)
    {
        $filename = $this->_stateFilename($key);
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see library/tiqr/Tiqr/StateStorage/Tiqr_StateStorage_Abstract::getValue()
     */
    public function getValue($key)
    {
        $filename = $this->_stateFilename($key);
        if (file_exists($filename)) {
            $envelope = unserialize(file_get_contents($filename));
            if ($envelope["expire"]!=0) {
                 // This data is time-limited. If it's too old we discard it.
                 if (time()-$envelope["createdAt"] > $envelope["expire"]) {
                     $this->unsetValue($key); 
                     return NULL;
                 }
            }
            return $envelope["value"];
        }
        return NULL;
    }
    
    /**
     * Determine the name of a temporary file to hold the contents of $key
     * @param String $key The key for which to store data.
     */
    protected function _stateFilename($key)
    {
        return "/tmp/tiqr_state_".strtr(base64_encode($key), '+/', '-_');
    }
    
}
