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

require_once('Tiqr/API/Client.php');

/**
 * The implementation for the oathservice ocra service class.
 *
 * @author lineke
 *
 */
class Tiqr_OcraService_OathServiceClient extends Tiqr_OcraService_Abstract
{
    protected $_apiClient;

    /**
     * Construct a ocra service class
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
     * Get the ocra challenge
     *
     * @return String The challenge
     */
    public function generateChallenge()
    {
        $result = $this->_apiClient->call('/oath/challenge/ocra');
        if ($result->code == '200') {
            return $result->body;
        }
        return null;
    }

    /**
     * Verify the response
     *
     * @param string $response
     * @param string $userId
     * @param string $challenge
     * @param string $sessionKey
     *
     * @return boolean True if response matches, false otherwise
     */
    public function verifyResponseWithUserId($response, $userId, $challenge, $sessionKey)
    {
        try {
            $result = $this->_apiClient->call('/oath/validate/ocra?response='.urlencode($response).'&challenge='.urlencode($challenge).'&userId='.urlencode($userId).'&sessionKey='.urlencode($sessionKey));
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns which method name to use to verify the response (verifyResponseWithSecret or verifyResponseWithUserId)
     *
     * @return string
     */
    public function getVerificationMethodName()
    {
        return 'verifyResponseWithUserId';
    }
}
