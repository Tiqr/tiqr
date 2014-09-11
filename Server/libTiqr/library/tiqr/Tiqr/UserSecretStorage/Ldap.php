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

require_once 'Zend/Ldap.php';
require_once 'Zend/Ldap/Attribute.php';
require_once 'Zend/Ldap/Dn.php';

require_once 'Tiqr/UserStorage/Ldap.php';

/**
 * LDAP Tiqr storage driver.
 */
class Tiqr_UserSecretStorage_Ldap extends Tiqr_UserStorage_Ldap implements Tiqr_UserSecretStorage_Interface
{
    /**
     * Construct a user class
     *
     * @param array $config The configuration that a specific user class may use.
     */
    public function __construct($config)
    {
        $this->_userClass = isset($config['userClass']) ? $config['userClass'] : "tiqrPerson";
        $this->_dnPattern = isset($config['dnPattern']) ? $config['dnPattern'] : "%s";
        $this->_idAttr = isset($config['idAttr']) ? $config['idAttr'] : 'dn';
        $this->_secretAttr = isset($config['secretAttr']) ? $config['secretAttr'] : 'tiqrSecret';

        $ldapOptions = array(
            "host" => $config['host'],
            "username" => $config['username'],
            "password" => $config['password'],
            "bindRequiresDn" => $config['bindRequiresDn'],
            "accountDomainName" => $config['accountDomainName'],
            "baseDn" => $config['baseDn'],
        );

        $this->_ldap = new Zend_Ldap($ldapOptions);
    }

    /**
     * Get the user's secret
     *
     * @param String $userId
     *
     * @return String The user's secret
     */
    public function getUserSecret($userId)
    {
        if ($user = $this->_loadUser($userId)) {
            return $this->_getLDAPAttribute($user, $this->_secretAttr);
        }
        return NULL;
    }

    /**
     * Store a secret for a user
     *
     * @param String $userId
     * @param String $secret
     */
    public function setUserSecret($userId, $secret)
    {
        $user = $this->_loadUser($userId);
        $this->_setLDAPAttribute($user, $this->_secretAttr, $secret);
        $this->_saveUser($userId, $user);
    }
}
