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

/**
 * The abstract class that defines what a ocra service class should implement.
 *
 * @author lineke
 *
 */
abstract class Tiqr_OcraService_Abstract implements Tiqr_OcraService_Interface
{
    /**
     * Verify the response
     * Override in child class to implement this method if this is the verification method to use
     *
     * @param string $response
     * @param string $userSecret
     * @param string $challenge
     * @param string $sessionKey
     *
     * @return boolean True if response matches, false otherwise
     */
    public function verifyResponseWithSecret($response, $userSecret, $challenge, $sessionKey)
    {

    }

    /**
     * Verify the response
     * Override in child class to implement this method if this is the verification method to use
     *
     * @param string $response
     * @param string $userId
     * @param string $challenge
     * @param string $sessionKey
     *
     * @return boolean True if response matches, false otherwise
     */
    public function verifyResponseWithUserId($response, $userId, $challenge, $sessionKey)
    {

    }

    /**
     * Returns which method name to use to verify the response (verifyResponseWithSecret or verifyResponseWithUserId)
     *
     * @return string
     */
    public function getVerificationMethodName()
    {
        return 'verifyResponseWithSecret';
    }
}