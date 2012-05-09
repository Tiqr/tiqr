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
 * Implementation of the OATH HOTP algorithm.
 * Currently superseded by the OCRA algorithm, but left in the codebase so future versions
 * could switch between OCRA and HOTP.
 * @author ivo
 *
 */
class Tiqr_HOTP
{
    /**
     * Calculate a HOTP response 
     * @param String $secret
     * @param String $counter
     * @return String The response
     */
    public function calculateResponse($secret, $counter)
    {
        $hash = $this->_getHash($secret, $counter);
        
        return $this->_truncate($hash, 6); 
    }   
    
    /**
     * Compute a hash
     * @param String $secret
     * @param String $counter
     * @return String hash
     */
    protected function _getHash ($secret, $counter)
    {
         // Counter
         //the counter value can be more than one byte long, so we need to go multiple times
         $cur_counter = array(0,0,0,0,0,0,0,0);
         for($i=7;$i>=0;$i--)
         {
             $cur_counter[$i] = pack ('C*', $counter);
             $counter = $counter >> 8;
         }
         $bin_counter = implode($cur_counter);
         // Pad to 8 chars
         if (strlen ($bin_counter) < 8)
         {
             $bin_counter = str_repeat (chr(0), 8 - strlen ($bin_counter)) . $bin_counter;
         }
     
         // HMAC
         $hash = hash_hmac ('sha1', $bin_counter, $secret);
         return $hash;
    }
 
    /**
     * Truncate a response to a certain length.
     * @param String $hash
     * @param int $length
     * @return String a truncated response
     */
    protected function _truncate($hash, $length = 6)
    {
         // Convert to dec
         foreach(str_split($hash,2) as $hex)
         {
             $hmac_result[]=hexdec($hex);
         }
     
         // Find offset
         $offset = $hmac_result[19] & 0xf;  
     
         // Algorithm from RFC
         return
         (
             (($hmac_result[$offset+0] & 0x7f) << 24 ) |
             (($hmac_result[$offset+1] & 0xff) << 16 ) |
             (($hmac_result[$offset+2] & 0xff) << 8 ) |
             ($hmac_result[$offset+3] & 0xff)
         ) % pow(10,$length);
    }
}
  