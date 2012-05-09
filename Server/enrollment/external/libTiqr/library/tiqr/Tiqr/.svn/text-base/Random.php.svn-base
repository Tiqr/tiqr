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
 * A class implementing secure random number generation.
 * If openssl functionality is available, openssl is used to generate 
 * secure random data, if not, a twisted sha1 hash of a random number is used.
 * 
 * @author ivo
 *
 */
class Tiqr_Random
{
    /**
     * Generate $length random bytes.
     * 
     * Code courtesy of http://www.zimuel.it/blog/2011/01/strong-cryptography-in-php/
     * 
     * @param int $length the number of bytes to generate.
     */
    public static function randomBytes($length)
    {
       if(function_exists('openssl_random_pseudo_bytes')) {
            $rnd = openssl_random_pseudo_bytes($length, $strong);
            if($strong === TRUE) {
                return $rnd;
            }
        }
        
        $rnd='';
        
        for ($i=0;$i<$length;$i++) {
            $sha= sha1(mt_rand());
            $char= mt_rand(0,30);
            $rnd.= chr(hexdec($sha[$char].$sha[$char+1]));
        }
        
        return $rnd;
     
    }
    
    /**
     * Generate a random hex string of a certain length.
     * @param int $length the desired length of the string
     */
    public static function randomHexString($length)
    {
         $result = bin2hex(self::randomBytes($length));
         return $result;
    }
}