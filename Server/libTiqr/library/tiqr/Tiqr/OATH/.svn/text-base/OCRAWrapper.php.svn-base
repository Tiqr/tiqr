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
 * @internal includes
 */
require_once("OCRA.php");
require_once("Tiqr/Random.php");

/**
 * A wrapper for the OCRA algorithm implementing just the features we support.
 * @author ivo
 */
class Tiqr_OCRAWrapper
{
    /**
     * derive the challenge configuration from an ocraSuite String
     * @param String $ocraSuite
     * @return Array an array with a format and length key. 
     *               - format determines what challenges should look like 
     *                 (H for Hex, A for alphanumeric, N for numeric)
     *               - length is the length of challenges
     */
    protected function _challengeConfig($ocraSuite)
    {
        // find the :QN10, -QN10, QH10 etc. bit
        $pos = stripos($ocraSuite, ":q");
        if ($pos===false) $pos = stripos($ocraSuite, "-q");
        if ($pos===false) {
            // No challenge config specified. Since we only support challenge based OCRA, we fallback to default 10 digit hexadecimal.
            return array("format"=>"H", "length"=>10);
        }
        $format = substr($ocraSuite, $pos+2, 1);
        if (!in_array($format, array("N", "A", "H"))) {
            $format = "H";
        }
        
        $length = (int)substr($ocraSuite, $pos+3, 2);
                
        if ($length<=0) {
            $length = 10;
        }
        
        return array("format"=>$format, "length"=>$length);
    }
    
    /**
     * Format a random set of bytes according to the ocrasuite's 
     * challenge configuration
     * @param String $challenge bytes containing a random challenge
     * @param String $format the format to return (H, A or N)
     * @param int $length The length of the desired challenge
     */
    protected function _formatChallenge($challenge, $format, $length)
    {
        // Convert random bytes to correct format.
        switch ($format) {
            case "H": 
                $result = bin2hex($challenge);
                break;
            case "A": 
                $result = bin2hex($challenge); // hex is alfanumeric, too
                break;
            case "N": 
                $result = '0';
                while (strlen($challenge)) {
                    $ord = ord(substr($challenge, 0, 1));
                    $result = bcadd(bcmul($result, 256), $ord);
                    $challenge = substr($challenge, 1);
                }
                break;
            default:
                $result = bin2hex($challenge);
        }        
        
        return substr($result, 0, $length); // simple truncate
    }
    
    /**
     * Generate a challenge string based on an ocraSuite
     * @param String $ocraSuite The ocrasuite that determines what the 
     *                          challenge will look like.
     * @return String An OCRA challenge that matches the specification of 
     *         the ocraSuite.
     */
    public function getChallenge($ocraSuite) 
    {
        $strong = false;
        
        $conf = $this->_challengeConfig($ocraSuite);
        
        $length = $conf["length"];   
        
        $rnd = Tiqr_Random::randomBytes($length);
     
        return $this->_formatChallenge($rnd, $conf["format"], $length);
    }
    
    /**
     * Calculate an OCRA repsonse to a given OCRA challenge, according to
     * the algorithm specified by an OCRA Suite.
     * @param String $ocraSuite
     * @param String $secret a hex representation of the user's secret
     * @param String $challenge a hex or (alfa)numeric challenge question
     * @param String $sessionKey the sessionKey identifying the current session
     * @return int An OCRA response, the length of which is determined by the 
     *             OCRA suite.
     */
    public function calculateResponse($ocraSuite, $secret, $challenge, $sessionKey)
    {       
        if (strpos(strtolower($ocraSuite), "qn")!==false) {
            
            // challenge is decimal, but generateOcra always wants it in hex.
            $challenge = dechex($challenge);
            
        }
        // for some reason we're seeing the secret in lowercase.
        return OCRA::generateOCRA($ocraSuite, strtoupper($secret), "", $challenge, "", $sessionKey, "");

    }
}