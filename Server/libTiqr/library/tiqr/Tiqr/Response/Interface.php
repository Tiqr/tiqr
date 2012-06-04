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
 * The interface that defines what a response class should implement.
 * This interface can be used to adapt the module to a custom response backend.
 *
 * The interface defines the functionality that a response should support
 * to be able to house the response required for tiqr.
 *
 * The implementing classes are only required to implement the necessary
 * methods. 
 *
 * @author Lineke Kerckhoffs-Willems <lineke@egeniq.com>
 *
 */
interface Tiqr_Response_Interface
{
    /**
     * Create the response class
     * 
     * @param string $type The type of response (json or plain supported)
     * 
     * @return object Tiqr response class
     */
    public function createResponse($type = 'plain');
    
    /**
     * Get the Tiqr error response in the correct format
     * 
     * @return string The response
     */
    public function getErrorResponse();
    
    /**
     * Get the Tiqr invalid response in the correct format
     * 
     * @param int $attemptsLeft Number of login attempts left before a block
     * @return string The response
     */
    public function getInvalidResponse($attemptsLeft = null);
    
    /**
     * Get the Tiqr invalid userid response in the correct format
     * 
     * @return string The response
     */
    public function getInvalidUserResponse();
    
    /**
     * Get the Tiqr invalid request response in the correct format
     * 
     * @return string The response
     */
    public function getInvalidRequestResponse();
    
    /**
     * Get the Tiqr invalid challenge response in the correct format
     * 
     * @return string The response
     */
    public function getInvalidChallengeResponse();
    
    /**
     * Get the Tiqr invalid userid response in the correct format
     * 
     * @param int $duration The duration of the block (only for temproary blocks)
     * @return string The response
     */
    public function getAccountBlockedResponse($duration = null);
    
    /**
     * Get the Tiqr logged in response in the correct format
     * 
     * @return string The response
     */
    public function getLoginResponse();
}