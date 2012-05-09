<?php
require_once 'Tiqr/Controller/Enroll/Abstract.php';

/**
 * Enrollment controller.
 */
class IndexController extends Tiqr_Controller_Enroll_Abstract
{
    /**
     * Config.
     * 
     * @var Zend_Config
     */
    private $_config;
    
    /**
     * LDAP Connection
     * @var Zend_Ldap $_ldap
     */
    private $_ldap;
    
    /**
     * Initialize.
     */
    public function init()
    {
        parent::init();
        $this->_config = Zend_Registry::get('config'); 
    }
    
    /**
     * Pre-dispatch, require authentication.
     */
    public function preDispatch()
    {
        parent::preDispatch();
        Zend_Session::start();

        $action = $this->getRequest()->getActionName();
        if ($action != 'metadata' && $action != 'enroll') {
            require_once($this->_config->simplesaml->path . '/lib/_autoload.php');
            $as = new SimpleSAML_Auth_Simple($this->_config->simplesaml->serviceProvider);
            $as->requireAuth();
        }        
    }
    
    /**
     * Returns the LDAP client.
     * 
     * @return Zend_Ldap
     */
    private function _getLDAPClient()
    {      
        if ($this->_ldap instanceof Zend_Ldap) {
            return $this->_ldap;
        }

        $ldap = new Zend_Ldap($this->_config->enrollment->ldap->client);
        $this->_ldap = $ldap;
        return $this->_ldap;
    }
    
    /**
     * Returns the LDAP user with the given id.
     * 
     * @param string  $userId         user filter string
     * @param array   $attribNames    list of attribute names (empty array for all)
     * @param boolean $validateResult throw an exception if the result is not a user?
     * 
     * @throws Exception
     */
    private function _getLDAPUser($userId, $attribNames=array(), $validateResult=true)
    {
        // Require "objectClass" to be part of attributes so we can check if are actually dealing with a user
        $addedObjectClass = false;        
        $attribNames = array_map('strtolower', $attribNames);
        if (count($attribNames) > 0 && !in_array('objectclass', $attribNames)) {
            $attribNames[] = 'objectclass';
            $addedObjectClass = true;
        }
        
        $dn = sprintf($this->_config->enrollment->ldap->dnPattern, $userId);
        $user = $this->_getLDAPClient()->getEntry($dn, $attribNames);

        if ($validateResult) {
            if (empty($user)) {
                throw new Exception("User doesn't exist");
            }
          
            if (array_search($this->_config->enrollment->ldap->userClass, $user['objectclass']) === false) {
                throw new Exception("User not a " . $this->_config->enrollment->ldap->userClass);
            }
        }
        
        if ($addedObjectClass) {
            unset($user['objectclass']);
        }
        
        return $user;
    }

    /**
     * Updates the LDAP user with the given id.
     * 
     * @param string $userId user filter string.
     * @param array  $data   user data
     */
    private function _updateLDAPUser($userId, $data)
    {
        $dn = sprintf($this->_config->enrollment->ldap->dnPattern, $userId);        
        $this->_getLDAPClient()->update($dn, $data);        
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
     * Returns the authentication verification URL, for future logins.
     *
     * Should return a complete server URL including hostname etc.
     *
     * @return string authentication URL
     */
    protected function _getAuthenticationURL()
    {
        return $this->_config->enrollment->authenticationURL;
    }
    
    /**
     * Returns the enrollment user metadata.
     *
     * Object with the following properties:
     * - userId
     * - displayName
     *
     * @return stdClass user metadata
     */
    protected function _getUserData()
    {
        $session = new Zend_Session_Namespace(__CLASS__);    
        return $session->user;
    }
    
    /**
     * Encrypt secret.
     * 
     * @param string $secret
     */
    protected function _encryptSecret($secret)
    {
        require_once 'Tiqr/UserStorage/Encryption.php';
        $encryption = Tiqr_UserStorage_Encryption::getEncryption($this->_config->enrollment->encryption->type, $this->_config->enrollment->encryption->toArray());
        return $encryption->encrypt($secret);
    }
    
    /**
     * Stores the enrollment data for the given user.
     *
     * Enrollment data object contains the following properties:
     * - secret
     * - notificationType
     * - notificationAddress
     *
     * @param string   $userId         user identifier
     * @param stdClass $enrollmentData enrollment data
     *
     * @throws Exception throws an exception when the user doesn't exist or the data cannot be stored
     */
    protected function _storeEnrollmentData($userId, $enrollmentData)
    {
        $user = $this->_getLDAPUser($userId, array("objectClass", $this->_config->enrollment->ldap->secretAttr));
        $secret = $this->_getLDAPAttribute($user, $this->_config->enrollment->ldap->secretAttr);
        if (!empty($secret)) {
            throw new Exception("User already enrolled");
        }      

        Zend_Ldap_Attribute::setAttribute($user, "objectClass", "tiqrPerson", true);

        $this->_setLDAPAttribute($user, $this->_config->enrollment->ldap->secretAttr, $this->_encryptSecret($enrollmentData->secret));
        $this->_setLDAPAttribute($user, $this->_config->enrollment->ldap->notificationTypeAttr, $enrollmentData->notificationType);
        $this->_setLDAPAttribute($user, $this->_config->enrollment->ldap->notificationAddressAttr, $enrollmentData->notificationAddress);
        $this->_setLDAPAttribute($user, $this->_config->enrollment->ldap->isBlockedAttr, 'FALSE');
        $this->_setLDAPAttribute($user, $this->_config->enrollment->ldap->loginAttemptsAttr, 0);

        $this->_updateLDAPUser($userId, $user);        
    }
    
    /**
     * Destroys the enrollment data for the given user.
     *
     * @param string $userId user identifier
     * 
     * @throws Exception
     */
    protected function _destroyEnrollmentData($userId)
    {
        $user = $this->_getLDAPUser($userId, array("objectClass", $this->_config->enrollment->ldap->secretAttr));
        $secret = $this->_getLDAPAttribute($user, $this->_config->enrollment->ldap->secretAttr);
        if (empty($secret)) {
            throw new Exception("User is not enrolled");
        }
        
        $this->_setLDAPAttribute($user, $this->_config->enrollment->ldap->secretAttr, null);
        $this->_setLDAPAttribute($user, $this->_config->enrollment->ldap->notificationTypeAttr, null);
        $this->_setLDAPAttribute($user, $this->_config->enrollment->ldap->notificationAddressAttr, null);
        $this->_setLDAPAttribute($user, $this->_config->enrollment->ldap->isBlockedAttr, null);
        $this->_setLDAPAttribute($user, $this->_config->enrollment->ldap->loginAttemptsAttr, null);
        
        Zend_Ldap_Attribute::removeFromAttribute($user, "objectClass", "tiqrPerson");

        $this->_updateLDAPUser($userId, $user); 
    }
    
    /**
     * Display the enrollment form.
     */
    public function indexAction()
    {
        $result = $this->_getLDAPClient()->search('(objectClass=' . $this->_config->enrollment->ldap->userClass . ')', $this->_config->enrollment->ldap->userBaseDn, Zend_Ldap::SEARCH_SCOPE_ONE, array($this->_config->enrollment->ldap->idAttr, $this->_config->enrollment->ldap->displayNameAttr, $this->_config->enrollment->ldap->sortAttr, $this->_config->enrollment->ldap->secretAttr), $this->_config->enrollment->ldap->sortAttr);

        $users = array();
        $enrolledUsers = array();
        foreach ($result as $entry) {
            $displayName = $this->_getLDAPAttribute($entry, $this->_config->enrollment->ldap->displayNameAttr);
            $chunks = explode(" ", $displayName);
            $lastName = array_pop($chunks);
            $firstName = implode(" ", $chunks);
            $displayName = $lastName . ', ' . $firstName;
            if ($this->_getLDAPAttribute($entry, $this->_config->enrollment->ldap->secretAttr) === NULL) {
                $users[$this->_getLDAPAttribute($entry, $this->_config->enrollment->ldap->idAttr)] = $displayName;
            } else {
                $enrolledUsers[$this->_getLDAPAttribute($entry, $this->_config->enrollment->ldap->idAttr)] = $displayName;
            }
            unset($entry[$this->_config->enrollment->ldap->secretAttr]);
        }
        asort($users);
        asort($enrolledUsers);
        
        $this->view->users = $users;
        $this->view->enrolledUsers = $enrolledUsers;
        $this->view->messages = $this->_helper->FlashMessenger->getMessages();
        $this->view->processURL = $this->_helper->url('process');
    }
    
    /**
     * Process user creation form.
     */
    public function processAction()
    {
        $userId = $this->_request->userId;
        $user = $this->_getLDAPUser($userId, array($this->_config->enrollment->ldap->idAttr, $this->_config->enrollment->ldap->displayNameAttr, $this->_config->enrollment->ldap->secretAttr));

        if (empty($user)) {
            throw new Exception("Invalid user");
        }

        $secret = Zend_Ldap_Attribute::getAttribute($user, $this->_config->enrollment->ldap->secretAttr);
        /**
         * TODO this action should only be permitted by certain administrator users
         */
        if (!empty($secret)) {
            // User previously enrolled. Destroy data
            $this->_destroyEnrollmentData($userId);
        }
        
        $session = new Zend_Session_Namespace(__CLASS__);           
        $session->user = new stdClass();
        $session->user->userId = $this->_getLDAPAttribute($user, $this->_config->enrollment->ldap->idAttr);
        $session->user->displayName =  $this->_getLDAPAttribute($user, $this->_config->enrollment->ldap->displayNameAttr);
        
        $this->_helper->redirector->gotoSimple('scan');        
    }
    
    /**
     * Display the scan page.
     */
    public function scanAction()
    {
        parent::scanAction();
        $this->view->verifyURL = $this->_helper->url('verify');
        $this->view->returnURL = $this->_helper->url('index');
    }
    
    /**
     * Check if the user is rolled in yet.
     *
     * NOTE: This implementation will block a webserver process until the user logs in.
     *       You might want to implement this using an Ajax request.
     */
    public function verifyAction()
    {
        set_time_limit(0);
        
        $session = new Zend_Session_Namespace(__CLASS__);                  
        $userId = $session->user->userId;
        Zend_Session::writeClose();

        while (true) {
            $user = $this->_getLDAPUser($userId, array($this->_config->enrollment->ldap->idAttr, $this->_config->enrollment->ldap->displayNameAttr, $this->_config->enrollment->ldap->secretAttr)); 
            $secret = $this->_getLDAPAttribute($user, $this->_config->enrollment->ldap->secretAttr);

            if (!empty($secret)) {
                $this->_helper->redirector->gotoSimple('finished');
                return;
            } else {
                sleep(1);
            }
        }
    }
    
    /**
     * Enrollment finished.
     */
    public function finishedAction()
    {
        $this->_helper->FlashMessenger('Gebruiker succesvol geregistreerd!');
        $this->_helper->redirector->gotoSimple('index');           
    }
}
