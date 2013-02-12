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
require_once("OATH/OCRAParser.php");
require_once("Tiqr/Random.php");
require_once("Tiqr/OATH/OCRA.php");

/**
 * A wrapper for the OCRA algorithm implementing just the features we support.
 * @author ivo
 */
class Tiqr_OCRAWrapper
{
    private $_ocraParser;
    private $_ocraSuite;

    public function __construct($ocraSuite) {
        $this->_ocraSuite = $ocraSuite;
        $this->_ocraParser = new OATH_OCRAParser($ocraSuite);
    }


    /**
     * Generate a challenge string based on an ocraSuite
     * @return String An OCRA challenge that matches the specification of
     *         the ocraSuite.
     */
    public function generateChallenge()
    {
        return $this->_ocraParser->generateChallenge();
    }
    
    /**
     * Generate a session key based on an ocraSuite
     * @return String Hexadecimal session key
     */
    public function generateSessionKey()
    {
        return $this->_ocraParser->generateSessionInformation();
    }

    /**
     * Calculate an OCRA repsonse to a given OCRA challenge, according to
     * the algorithm specified by an OCRA Suite.
     * @param String $secret a hex representation of the user's secret
     * @param String $challenge a hex or (alfa)numeric challenge question
     * @param String $sessionKey a hex sessionKey identifying the current session
     * @return String An OCRA response, the length of which is determined by the
     *             OCRA suite.
     */
    public function calculateResponse($secret, $challenge, $sessionKey)
    {
        return OCRA::generateOCRA($this->_ocraSuite, $secret, "", $challenge, "", $sessionKey, "");
    }

    /**
     * Calculate and verify an OCRA response.
     * @param String $response Expected OCRA response
     * @param String $secret a hex representation of the user's secret
     * @param String $challenge a hex or (alfa)numeric challenge question
     * @param String $sessionKey the sessionKey identifying the current session
     * @return Boolean True if response matches, false otherwise
     */
    public function verifyResponse($response, $secret, $challenge, $sessionKey)
    {
        $expected = OCRA::generateOCRA($this->_ocraSuite, $secret, "", $challenge, "", $sessionKey, "");
        return $this->_ocraParser->constEqual($expected, $response);
        
    }
}
