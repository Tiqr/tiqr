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
 * The interface that defines what a user class should implement. 
 * This interface can be used to adapt the module to a custom user backend. 
 * 
 * The interface defines the type of data that a user storage should support
 * to be able to house the data required for the tiqr authentication.
 * 
 * The implementing classes are only required to implement the necessary
 * getters and setters and should not worry about the actual meaning of this 
 * data. Tiqr supplies the data for storage in your backend and retrieves
 * it when necessary.
 * 
 * @author ivo
 *
 */
interface Tiqr_UserStorage_Interface
{
    /**
     * Construct a user class
     * @param array $config         The configuration that a specific user class may use.
     * @param array $secretconfig   The configuration for storing the user's secret, fallback to $config if not available
     */
    public function __construct($config, $secretconfig = array());
    
    /**
     * Store a new user with a certain displayName.
     * @param String $userId
     * @param String $displayName
     */
    public function createUser($userId, $displayName);
    
    /**
     * Check if a user exists
     * @param String $userId
     * @return boolean true if the user exists
     */
    public function userExists($userId);
    
    /**
     * Get the display name of a user.
     * @param String $userId
     * @return String the display name of this user
     */
    public function getDisplayName($userId);
    
    /**
     * Get the user's secret
     * @param String $userId
     * @return String The user's secret
     */
    public function getSecret($userId);

    /**
     * Store a secret for a user.
     * @param String $userId
     * @param String $secret
     */
    public function setSecret($userId, $secret);

    /**
     * Get the type of device notifications a user supports 
     * @param String $userId
     * @return String The notification type
     */
    public function getNotificationType($userId);
    
    /**
     * Set the notification type of a user.
     * @param String $userId
     * @param String $type
     */
    public function setNotificationType($userId, $type);
    
    /**
     * get the notification address of a user's device. 
     * @param String $userId
     * @return String The notification address
     */
    public function getNotificationAddress($userId);
    
    /**
     * Set the notification address of a user's device
     * @param String $userId
     * @param String $address
     */
    public function setNotificationAddress($userId, $address);
    
    /**
     * Get the amount of unsuccesful login attempts.
     */
    public function getLoginAttempts($userId);
    
    /**
     * Set the amount of unsuccessful login attempts.
     * @param String $userId
     * @param int $amount
     */
    public function setLoginAttempts($userId, $amount);
    
    /**
     * Check if the user is allowed to login.
     * @param string $userId
     * @param int $duration Duration of the block in minutes (for temporary blocks)
     */
    public function isBlocked($userId, $duration);
    
    /**
     * Block the user account.
     * @param $userId
     * @param $blocked true to block, false to unblock
     */
    public function setBlocked($userId, $blocked);
    
    /**
     * Set the number of times a temporary block was set during this session
     * @param string $userId
     * @param int $amount
     */
    public function setTemporaryBlockAttempts($userId, $amount);
    
    /**
     * Get the number of times a temporary block was set during this session
     * @param string $userId
     */
    public function getTemporaryBlockAttempts($userId);
    
    /**
     * Set the timestamp for the temporary block
     * @param string $userId
     * @param string $timestamp
     */
    public function setTemporaryBlockTimestamp($userId, $timestamp);
    
    /**
     * Get the temporary block timestamp
     * @param string $userId
     */
    public function getTemporaryBlockTimestamp($userId);

    /**
     * Returns additional attributes for the given user.
     *
     * @param string $userId User identifier.
     * 
     * @return array additional user attributes
     */
    public function getAdditionalAttributes($userId);
}
