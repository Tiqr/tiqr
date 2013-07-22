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
 * The version 1 implementation of the response object.
 *
 * @author Lineke Kerckhoffs-Willems <lineke@egeniq.com>
 *
 */
class Tiqr_Response_V1 extends Tiqr_Response_Abstract
{
    const RESPONSE_LOGIN_OK = 1;
    const RESPONSE_LOGIN_ERROR = 200;
    const RESPONSE_INVALID = 201;
    const RESPONSE_INVALID_USER = 205;
    const RESPONSE_INVALID_REQUEST = 202;
    const RESPONSE_INVALID_CHALLENGE = 203;
    const RESPONSE_ACCOUNT_BLOCKED = 204;
    const RESPONSE_ENROLLMENT_OK = 1;
    const RESPONSE_ENROLLMENT_ERROR = 101;
    
    /**
     * Get the Tiqr error response in the correct format
     * 
     * @return string The response
     */
    public function getErrorResponse()
    {
        $result['responseCode'] = self::RESPONSE_LOGIN_ERROR;
        return $result;
    }
    
    /**
     * Get the Tiqr invalid response
     * 
     * @param int $attemptsLeft Number of login attempts left before a block
     * 
     * @return array The response object
     */
    public function getInvalidResponse($attemptsLeft = null)
    {
        $result['responseCode'] = self::RESPONSE_INVALID;
        if (!is_null($attemptsLeft)) {
            $result['attemptsLeft'] = $attemptsLeft;
        }
        return $result;
    }
    
    /**
     * Get the Tiqr invalid userid response
     * 
     * @return array The response object
     */
    public function getInvalidUserResponse()
    {
        $result['responseCode'] = self::RESPONSE_INVALID_USER;
        return $result;
    }
    
    /**
     * Get the Tiqr invalid request response
     * 
     * @return array The response object
     */
    public function getInvalidRequestResponse()
    {
        $result['responseCode'] = self::RESPONSE_INVALID_REQUEST;
        return $result;
    }
    
    /**
     * Get the Tiqr invalid challenge response
     * 
     * @return array The response object
     */
    public function getInvalidChallengeResponse()
    {
        $result['responseCode'] = self::RESPONSE_INVALID_CHALLENGE;
        return $result;
    }
    
    /**
     * Get the Tiqr invalid userid response
     * 
     * @param int $duration The duration of the block (only for temporary blocks)
     * 
     * @return array The response object
     */
    public function getAccountBlockedResponse($duration = null)
    {
        $result['responseCode'] = self::RESPONSE_ACCOUNT_BLOCKED;
        if (!is_null($duration)) {
            $result['duration'] = $duration;
        }
        return $result;
    }
    
    /**
     * Get the Tiqr logged in response
     * 
     * @return array The response object
     */
    public function getLoginResponse()
    {
        $result['responseCode'] = self::RESPONSE_LOGIN_OK;
        return $result;
    }
    
    /**
     * Get the response when enrollment was succesful
     * 
     * @return array The response object 
     */
    public function getEnrollmentOkResponse() 
    {
        $result['responseCode'] = self::RESPONSE_ENROLLMENT_OK;
        return $result;
    }
    
    /**
     * Get the response when enrollment had an error 
     * 
     * @return array The response object
     */
    public function getEnrollmentErrorResponse()
    {
        $result['responseCode'] = self::RESPONSE_ENROLLMENT_ERROR;
        return $result;
    }
}