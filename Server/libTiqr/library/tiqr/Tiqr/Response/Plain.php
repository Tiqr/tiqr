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
 * The plain implementation of the response.
 *
 * @author Lineke Kerckhoffs-Willems <lineke@egeniq.com>
 *
 */
class Tiqr_Response_Plain extends Tiqr_Response_Abstract
{
    /**
     * Get the Tiqr error response in the correct format
     * 
     * @return string The response
     */
    public function getErrorResponse()
    {
        return 'ERROR';
    }
    
    /**
     * Get the Tiqr invalid response in the correct format
     * 
     * @param int $attemptsLeft Number of login attempts left before a block
     * @return string The response
     */
    public function getInvalidResponse($attemptsLeft = null)
    {
        $response = 'INVALID_RESPONSE';
        if (!is_null($attemptsLeft)) {
            $response .= ':'.$attemptsLeft;
        }
        return $response;
    }
    
    /**
     * Get the Tiqr invalid userid response in the correct format
     * 
     * @return string The response
     */
    public function getInvalidUserResponse()
    {
        return 'INVALID_USER';
    }
    
    /**
     * Get the Tiqr invalid request response in the correct format
     * 
     * @return string The response
     */
    public function getInvalidRequestResponse()
    {
        return 'INVALID_REQUEST';
    }
    
    /**
     * Get the Tiqr invalid challenge response in the correct format
     * 
     * @return string The response
     */
    public function getInvalidChallengeResponse()
    {
        return 'INVALID_CHALLENGE';
    }
    
    /**
     * Get the Tiqr invalid userid response in the correct format
     * 
     * @param int $duration The duration of the block (only for temproary blocks)
     * @return string The response
     */
    public function getAccountBlockedResponse($duration = null)
    {
        $response = 'ACCOUNT_BLOCKED';
        if (!is_null($duration)) {
            $response .= ':'.$duration;
        }
        return $response;
    }
    
    /**
     * Get the Tiqr logged in response in the correct format
     * 
     * @return string The response
     */
    public function getLoginResponse()
    {
        return 'OK';
    }
}