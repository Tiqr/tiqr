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
 * The interface that defines what a ocra service class should implement.
 *
 * The interface defines the generation of the ocra challenge.
 *
 * @author lineke
 *
 */
interface Tiqr_OcraService_Interface
{
    /**
     * Construct a ocra service class
     *
     * @param array $config The configuration that a specific user class may use.
     */
    public function __construct($config);

    /**
     * Get the ocra challenge
     *
     * @return string The challenge
     */
    public function generateChallenge();

    /**
     * Verify the response
     *
     * @param string $response
     * @param string $user          This can be userId or user secret depending on which type ocra service is used
     * @param string $challenge
     * @param string $sessionKey
     *
     * @return boolean True if response matches, false otherwise
     */
    public function verifyResponse($response, $user, $challenge, $sessionKey);
}
