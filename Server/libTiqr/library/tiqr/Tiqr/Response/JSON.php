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
 * The JSON implementation of the response.
 *
 * @author Lineke Kerckhoffs-Willems <lineke@egeniq.com>
 *
 */
class Tiqr_Response_JSON extends Tiqr_Response_Abstract
{
    const RESPONSE_OK = 1;
    const RESPONSE_ERROR = 200;
    const RESPONSE_INVALID = 201;
    const RESPONSE_INVALID_USER = 201;
    const RESPONSE_INVALID_REQUEST = 202;
    const RESPONSE_INVALID_CHALLENGE = 203;
    const RESPONSE_ACCOUNT_BLOCKED = 204;
    
    /**
     * Get the Tiqr error response in the correct format
     * 
     * @return string The response
     */
    public function getErrorResponse()
    {
        $json['responseCode'] = self::RESPONSE_ERROR;
        return json_encode($json);
    }
    
    /**
     * Get the Tiqr invalid response in the correct format
     * 
     * @param int $attemptsLeft Number of login attempts left before a block
     * @return string The response
     */
    public function getInvalidResponse($attemptsLeft = null)
    {
        $json['responseCode'] = self::RESPONSE_INVALID;
        if (!is_null($attemptsLeft)) {
            $json['attemptsLeft'] = $attemptsLeft;
        }
        return json_encode($json);
    }
    
    /**
     * Get the Tiqr invalid userid response in the correct format
     * 
     * @return string The response
     */
    public function getInvalidUserResponse()
    {
        $json['responseCode'] = self::RESPONSE_INVALID_USER;
        return json_encode($json);
    }
    
    /**
     * Get the Tiqr invalid request response in the correct format
     * 
     * @return string The response
     */
    public function getInvalidRequestResponse()
    {
        $json['responseCode'] = self::RESPONSE_INVALID_REQUEST;
        return json_encode($json);
    }
    
    /**
     * Get the Tiqr invalid challenge response in the correct format
     * 
     * @return string The response
     */
    public function getInvalidChallengeResponse()
    {
        $json['responseCode'] = self::RESPONSE_INVALID_CHALLENGE;
        return json_encode($json);
    }
    
    /**
     * Get the Tiqr invalid userid response in the correct format
     * 
     * @param int $duration The duration of the block (only for temporary blocks)
     * @return string The response
     */
    public function getAccountBlockedResponse($duration = null)
    {
        $json['responseCode'] = self::RESPONSE_ACCOUNT_BLOCKED;
        if (!is_null($duration)) {
            $json['duration'] = $duration;
        }
        return json_encode($json);
    }
    
    /**
     * Get the Tiqr logged in response in the correct format
     * 
     * @return string The response
     */
    public function getLoginResponse()
    {
        $json['responseCode'] = self::RESPONSE_OK;
        return json_encode($json);
    }
}