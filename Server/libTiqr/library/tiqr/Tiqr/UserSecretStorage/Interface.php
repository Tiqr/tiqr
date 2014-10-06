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
 * The interface that defines what a user secret class should implement.
 * This interface can be used to adapt the module to a custom user backend. 
 * 
 * The interface defines the storage of a user's secret.
 * 
 * The implementing classes are only required to implement the necessary
 * getters and setters and should not worry about the actual meaning of this 
 * data. Tiqr supplies the data for storage in your backend and retrieves
 * it when necessary.
 * 
 * @author ivo
 *
 */
interface Tiqr_UserSecretStorage_Interface
{
    /**
     * Construct a user class
     *
     * @param array $config The configuration that a specific user class may use.
     */
    public function __construct($config);

    /**
     * Get the user's secret
     *
     * @param String $userId
     *
     * @return String The user's secret
     */
    public function getUserSecret($userId);
    
    /**
     * Store a secret for a user
     *
     * @param String $userId
     * @param String $secret
     */
    public function setUserSecret($userId, $secret);
}
