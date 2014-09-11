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
 * @author Lineke Kerckhoffs-Willems <lineke@egeniq.com>
 *
 * @package tiqr
 *
 * @license New BSD License - See LICENSE file for details.
 *
 * @copyright (C) 2014 SURFnet BV
 */

require_once('Tiqr/API/Client.php');

/**
 * OATHService storage for user's secret
 */
class Tiqr_UserSecretStorage_Oathservice implements Tiqr_UserSecretStorage_Interface
{
    protected $_apiClient;

    /**
     * Construct a user class
     *
     * @param array $config The configuration that a specific user class may use.
     */
    public function __construct($config)
    {
        $this->_apiClient = new Tiqr_API_Client();
        $this->_apiClient->setBaseURL($config['apiURL']);
        $this->_apiClient->setConsumerKey($config['consumerKey']);
    }

    /**
     * Get the user's secret
     * Not implemented because the idea of the oathservice is that secrets cannot be retrieved
     *
     * @param String $userId
     *
     * @return String The user's secret
     */
    public function getUserSecret($userId)
    {
        return null;
    }

    /**
     * Store a secret for a user
     *
     * @param String $userId
     * @param String $secret
     */
    public function setUserSecret($userId, $secret)
    {
        $this->_apiClient->call('/secrets/'.$userId, 'POST', array('secret' => $secret));
    }
}
