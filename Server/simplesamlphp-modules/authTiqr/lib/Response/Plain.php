<?php

require_once 'Tiqr/Response/Abstract.php';

/**
 * The plain implementation of the response.
 *
 * @author Lineke Kerckhoffs-Willems <lineke@egeniq.com>
 *
 */
class sspmod_authTiqr_Response_Plain extends Tiqr_Response_Abstract
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
    
    /**
     * Get the response when enrollment was succesful
     * 
     * @return array The response object 
     */
    public function getEnrollmentOkResponse() 
    {
        return 'OK';
    }
    
    /**
     * Get the response when enrollment had an error 
     * 
     * @return array The response object
     */
    public function getEnrollmentErrorResponse()
    {
        return 'There was an error';
    }
}
