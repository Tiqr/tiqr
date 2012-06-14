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

require_once 'Tiqr/UserStorage/Abstract.php';

/**
 * LDAP Tiqr storage driver.
 */
class Tiqr_UserStorage_Ldap extends Tiqr_UserStorage_Abstract
{    
    /**
     * LDAP object.
     * 
     * @var Zend_Ldap
     */
    protected $_ldap;
    
    /**
     * User class.
     * 
     * @var string
     */
    protected $_userClass;
    
    /**
     * DN pattern. Can contain a single %s variable.
     * 
     * @var string
     */
    protected $_dnPattern;    
    
    /**
     * User identifier LDAP attribute.
     * 
     * @var string
     */
    protected $_idAttr;

    /**
     * User display name LDAP attribute.
     * 
     * @var string
     */
    protected $_displayNameAttr;

    /**
     * Tiqr user secret LDAP attribute.
     * 
     * @var string
     */
    protected $_secretAttr;

    /**
     * Device notification type LDAP attribute.
     * 
     * @var string
     */
    protected $_notificationTypeAttr;

    /**
     * Device notification address LDAP attribute.
     * 
     * @var string
     */
    protected $_notificationAddressAttr;
    
    /**
     * Is account blocked LDAP attribute.
     * 
     * @var string
     */
    protected $_isBlockedAttr;

    /**
     * Login attempts LDAP attribute.
     * 
     * @var string
     */
    protected $_loginAttemptsAttr;
    
    /**
     * Temporary block attempts LDAP attribute
     * 
     * @var string
     */
    protected $_temporaryBlockAttemptsAttr;
    
    /**
     * Temporary block timestamp LDAP attribute
     * 
     * @var string
     */
    protected $_temporaryBlockTimestampAttr;

    /**
     * LDAP attributes that need to be retrieved.
     *
     * Use null for everything.
     *
     * @param array
     */
    protected $_attributes;

    
    /**
     * Create an instance
     * @param $config
     */
    public function __construct($config)
    {
	parent::__construct($config);

        $this->_userClass = isset($config['userClass']) ? $config['userClass'] : "tiqrPerson";
        $this->_dnPattern = isset($config['dnPattern']) ? $config['dnPattern'] : "%s";        
        $this->_idAttr = isset($config['idAttr']) ? $config['idAttr'] : 'dn';
        $this->_displayNameAttr = isset($config['displayNameAttr']) ? $config['displayNameAttr'] : 'sn';
        $this->_secretAttr = isset($config['secretAttr']) ? $config['secretAttr'] : 'tiqrSecret';
        $this->_notificationTypeAttr = isset($config['notificationTypeAttr']) ? $config['notificationTypeAttr'] : 'tiqrNotificationType';        
        $this->_notificationAddressAttr = isset($config['notificationAddressAttr']) ? $config['notificationAddressAttr'] : 'tiqrNotificationAddress';        
        $this->_isBlockedAttr = isset($config['isBlockedAttr']) ? $config['isBlockedAttr'] : 'tiqrIsBlocked';                
        $this->_loginAttemptsAttr = isset($config['loginAttemptsAttr']) ? $config['loginAttemptsAttr'] : 'tiqrLoginAttempts';  
        $this->_temporaryBlockAttemptAttr = isset($config['temporaryBlockAttemptsAttr']) ? $config['temporaryBlockAttemptsAttr'] : 'tiqrTemporaryBlockAttempts';
        $this->_temporaryBlockTimestampAttr = isset($config['temporaryBlockTimestampAttr']) ? $config['temporaryBlockTimestampAttr'] : 'tiqrTemporaryBlockTimestamp';
        $this->_attributes = isset($config['attributes']) ? $config['attributes'] : null;
        
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
     * Returns the value for the given LDAP attribute.
     * 
     * @param array  $entry      LDAP entry
     * @param string $attribName Attribute name.
     * 
     * @return mixed LDAP attribute value
     */
    private function _getLDAPAttribute($entry, $attribName, $index=0)
    {
        $attribName = strtolower($attribName);
        
        if (!isset($entry[$attribName])) {
            return null;
        } else if (is_array($entry[$attribName])) {
            return Zend_Ldap_Attribute::getAttribute($entry, $attribName, $index);
        } else {
            return $entry[$attribName];
        }
    }
    
    /**
     * Sets the value for the given LDAP attribute.
     * 
     * @param array  $entry      LDAP entry
     * @param string $attribName Attribute name.
     * @param mixed  $value      Value.
     */
    private function _setLDAPAttribute(&$entry, $attribName, $value)
    {
        $attribName = strtolower($attribName);
        Zend_Ldap_Attribute::setAttribute($entry, $attribName, $value);        
    }    
    
    /**
     * Returns the dn for the given user identifier.
     * 
     * @param string $userId user identifier
     * 
     * @return string LDAP dn
     */
    protected function _getDNForUserId($userId)
    {
        return sprintf($this->_dnPattern, $userId);
    }
    
    /**
     * This function takes care of actually saving the user data into LDAP
     * @param String $userId
     * @param array $data
     */
    protected function _saveUser($userId, $data)
    {
        if ($this->userExists($userId)) { 
            $this->_ldap->update($this->_getDNForUserId($userId), $data);
        } else { 
            $this->_ldap->add($this->_getDNForUserId($userId), $data);
        }
        return true;
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::createUser()
     */
    public function createUser($userId, $displayName) 
    {
        $user = array();
        $this->_setLDAPAttribute($user, "objectClass", $this->_userClass);
        $this->_setLDAPAttribute($user, $this->_idAttr, $userId);        
        $this->_setLDAPAttribute($user, $this->_displayNameAttr, $displayName);

        return $this->_saveUser($this->_getDNForUserId($userId), $user);
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::userExists()
     */
    public function userExists($userId)
    {
        $user = $this->_loadUser($userId);
        return (is_array($user) || is_object($user)); 
    }

    /**
     * This function takes care of loading the user from LDAP
     * @param String $userId
     * @return false if the data is not present, or an array containing the data.
     */
    protected function _loadUser($userId)
    {
        $user = $this->_ldap->getEntry($this->_getDNForUserId($userId));
        return $user;
    }

    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getDisplayName()
     */
    public function getDisplayName($userId) 
    {
        if ($user = $this->_loadUser($userId)) {
            return $this->_getLDAPAttribute($user, $this->_displayNameAttr);
        }
        return NULL;
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getSecret()
     */
    protected function _getEncryptedSecret($userId)
    {
        if ($user = $this->_loadUser($userId)) {
            return $this->_getLDAPAttribute($user, $this->_secretAttr);
        }
        return NULL;
    }

    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::setSecret()
     */
    protected function _setEncryptedSecret($userId, $secret)
    {
        $user = $this->_loadUser($userId);
        $this->_setLDAPAttribute($user, $this->_secretAttr, $secret);
        $this->_saveUser($userId, $user);
    } 
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getNotificationType()
     */
    public function getNotificationType($userId)
    {
        if ($user = $this->_loadUser($userId)) {
            return $this->_getLDAPAttribute($user, $this->_notificationTypeAttr);
        }
        return NULL;
    }

    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::setNotificationType()
     */
    public function setNotificationType($userId, $type)
    {
        $user = $this->_loadUser($userId);
        $this->_setLDAPAttribute($user, $this->_notificationTypeAttr, $type);
        $this->_saveUser($userId, $user);
    }    
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getNotificationAddress()
     */
    public function getNotificationAddress($userId)
    {
        if ($user = $this->_loadUser($userId)) {
            return $this->_getLDAPAttribute($user, $this->_notificationAddressAttr);
        }
        return NULL;
    }

    /**
     * (non-PHPdoc)
     * @see simplesamlphp/modules/authTiqr/lib/User/sspmod_authTiqr_User_Interface::setNotificationAddress()
     */
    public function setNotificationAddress($userId, $address)
    {
        $user = $this->_loadUser($userId);
        $this->_setLDAPAttribute($user, $this->_notificationAddressAttr, $address);
        $this->_saveUser($userId, $user);
    } 

    /**
     * (non-PHPdoc)
     * @see simplesamlphp-module/authTiqr/lib/User/sspmod_authTiqr_User_Interface::getFailedLoginAttempts()
     */
    public function getLoginAttempts($userId)
    {
        if ($user = $this->_loadUser($userId)) {
            return $this->_getLDAPAttribute($user, $this->_loginAttemptsAttr);
        }
        return 0;
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp-module/authTiqr/lib/User/sspmod_authTiqr_User_Interface::setFailedLoginAttempts()
     */
    public function setLoginAttempts($userId, $amount)
    {
        $user = $this->_loadUser($userId);
        $this->_setLDAPAttribute($user, $this->_loginAttemptsAttr, $amount);
        $this->_saveUser($userId, $user);
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp-module/authTiqr/lib/User/sspmod_authTiqr_User_Interface::isBlocked()
     */
    public function isBlocked($userId, $duration)
    {
        if ($user = $this->_loadUser($userId)) {
            $isBlocked = $this->_getLDAPAttribute($user, $this->_isBlockedAttr);
            $timestamp = $this->getTemporaryBlockTimestamp($userId);
            if (false === (bool) $isBlocked || (false !== $timestamp && false != $duration && (strtotime($timestamp) + duration * 60) < time())) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * (non-PHPdoc)
     * @see simplesamlphp-module/authTiqr/lib/User/sspmod_authTiqr_User_Interface::block()
     */
    public function setBlocked($userId, $blocked) 
    {
        $user = $this->_loadUser($userId);
        $this->_setLDAPAttribute($user, $this->_isBlockedAttr, $blocked);
        $this->_saveUser($userId, $user);
    }
    
    /**
     * (non-PHPdoc)
     * @see libTiqr/library/tiqr/Tiqr/UserStorage/Tiqr_UserStorage_Interface::setTemporaryBlockAttempts()
     */
    public function setTemporaryBlockAttempts($userId, $amount) {
        $data = $this->_loadUser($userId);
        $this->_setLDAPAttribute($user, $this->_temporaryBlockAttemptsAttr, $amount);
        $this->_saveUser($userId, $data);
    }
    
    /**
     * (non-PHPdoc)
     * @see libTiqr/library/tiqr/Tiqr/UserStorage/Tiqr_UserStorage_Interface::getTemporaryBlockAttempts()
     */
    public function getTemporaryBlockAttempts($userId) {
        if ($user = $this->_loadUser($userId)) {
            return $this->_getLDAPAttribute($user, $this->_temporaryBlockAttemptsAttr);
        }
        return 0;
    }
    
    /**
     * (non-PHPdoc)
     * @see libTiqr/library/tiqr/Tiqr/UserStorage/Tiqr_UserStorage_Interface::setTemporaryBlockTimestamp()
     */
    public function setTemporaryBlockTimestamp($userId, $timestamp) {
        $data = $this->_loadUser($userId);
        $this->_setLDAPAttribute($user, $this->_temporaryBlockTimestampAttr, $timestamp);
        $this->_saveUser($userId, $data);
    }
    
    /**
     * (non-PHPdoc)
     * @see libTiqr/library/tiqr/Tiqr/UserStorage/Tiqr_UserStorage_Interface::getTemporaryBlockTimestamp()
     */
    public function getTemporaryBlockTimestamp($userId) {
        if ($data = $this->_loadUser($userId)) {
            $timestamp = $this->_getLDAPAttribute($user, $this->_temporaryBlockTimestampAttr);
            if (null !== $timestamp) {
                return $timestamp;
            } 
        }
        return false;
    }

    /**
     * Returns additional attributes for the given user.
     *
     * @param string $userId User identifier.
     * 
     * @return array additional user attributes
     */
    public function getAdditionalAttributes($userId) 
    {
        $user = $this->_loadUser($userId);
        if ($user == null) {
            return array();
        } else if (!is_array($this->_attributes)) {
            return $user;
        } else {
            $result = array();
            foreach ($this->_attributes as $name) {
                $name = strtolower($name);
                if (isset($user[$name])) {
                    $result[$name] = $user[$name];
                }
            }

            return $result;
        } 
    }
}
