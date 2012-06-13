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
 * The interface that defines what a response class should implement for each version.
 * This interface can be used for the different versions of Tiqr.
 *
 * The interface defines the functionality that a response should support
 * to be able to house the object response required for each version of tiqr.
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
     * @return object Tiqr response class
     */
    public function createResponse();
    
    /**
     * Get the Tiqr error response
     * 
     * @return array The response object
     */
    public function getErrorResponse();
    
    /**
     * Get the Tiqr invalid response
     * 
     * @param int $attemptsLeft Number of login attempts left before a block
     * 
     * @return array The response object
     */
    public function getInvalidResponse($attemptsLeft = null);
    
    /**
     * Get the Tiqr invalid userid response
     * 
     * @return array The response object
     */
    public function getInvalidUserResponse();
    
    /**
     * Get the Tiqr invalid request response
     * 
     * @return array The response object
     */
    public function getInvalidRequestResponse();
    
    /**
     * Get the Tiqr invalid challenge response
     * 
     * @return array The response object
     */
    public function getInvalidChallengeResponse();
    
    /**
     * Get the Tiqr invalid userid response
     * 
     * @param int $duration The duration of the block (only for temproary blocks)
     * @return array The response object
     */
    public function getAccountBlockedResponse($duration = null);
    
    /**
     * Get the Tiqr logged in response
     * 
     * @return array The response object
     */
    public function getLoginResponse();
    
    /**
     * Get the response when enrollment was succesful
     * 
     * @return array The response object 
     */
    public function getEnrollmentOkResponse();
    
    /**
     * Get the response when enrollment had an error 
     * 
     * @return array The response object
     */
    public function getEnrollmentErrorResponse();
}