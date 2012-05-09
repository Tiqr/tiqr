<?php
require_once 'Tiqr/Controller/Abstract.php';
require_once 'Zend/Session/Namespace.php';

abstract class Tiqr_Controller_Login_Abstract extends Tiqr_Controller_Abstract
{
    /**
     * Returns the secret for the given user.
     *
     * If the user doesn't exist this method should return null.
     *
     * @param string $userId user identifier
     * 
     * @return string user secret
     */
    abstract protected function _getUserSecret($userId);
    
    /**
     * Updates the notification data for the given user.
     *
     * Notification data object contains the following properties:
     * - notificationType
     * - notificationAddress
     *
     * @param string   $userId           user identifier
     * @param stdClass $notificationData notification data
     *
     * @throws Exception throws an exception when the user doesn't exist or the data cannot be stored
     */    
    abstract protected function _updateNotificationData($userId, $data);
    
    /**
     * Returns the authentication result to a string.
     *
     * @param int $result authentication result
     *
     * @return string authentication result string representation
     */
    protected function _authResultToString($result)
    {
        switch ($result) {
            case Tiqr_Service::AUTH_RESULT_AUTHENTICATED:
                return 'OK';
            case Tiqr_Service::AUTH_RESULT_INVALID_CHALLENGE:
                return 'INVALID_CHALLENGE';
            case Tiqr_Service::AUTH_RESULT_INVALID_REQUEST:
                return 'INVALID_REQUEST';
            case Tiqr_Service::AUTH_RESULT_INVALID_RESPONSE:
                return 'INVALID_RESPONSE';
            case Tiqr_Service::AUTH_RESULT_INVALID_USERID:
                return 'INVALID_USERID';
            default:
                return 'ERROR';        
        }
    }    
    
    /**
     * Shows the login form.
     */
    public function indexAction()
    {
        $session = new Zend_Session_Namespace(__CLASS__);
        $session->key = $this->_getTiqr()->startAuthenticationSession(null, $this->_getSessionId()); 
        
        $this->view->authenticationURL = $this->_getTiqr()->generateAuthURL($session->key);
        $this->view->qrURL = $this->_helper->url('qr');
    }
    
    /**
     * Outputs the login QR code.
     */
    public function qrAction()
    {
        $session = new Zend_Session_Namespace(__CLASS__);   
             
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender();             
        $this->_getTiqr()->generateAuthQR($session->key);        
    }
    
    /**
     * Authenticate user.
     *
     * This action is accessed by the mobile device.
     */
    public function authenticateAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender();    
        
        if (empty($this->_request->sessionKey) || empty($this->_request->userId) || empty($this->_request->response)) {
            echo 'INVALID_REQUEST';
            return;
        } 
        
        $sessionKey = $this->_request->sessionKey;
        $userId = $this->_request->userId;
        $response = $this->_request->response;

        $secret = $this->_getUserSecret($userId);
        
        if ($secret == null) {
            echo $this->_authResultToString(Tiqr_Service::AUTH_RESULT_INVALID_USERID);
            return;            
        }
        
        try {
            $result = $this->_getTiqr()->authenticate($userId, $secret, $sessionKey, $response); 
            if ($result == Tiqr_Service::AUTH_RESULT_AUTHENTICATED) {
                $data = new stdClass();
                $data->notificationType = isset($this->_request->notificationType) ? $this->_request->notificationType : null;
                $data->notificationAddress = isset($this->_request->notificationAddress) ? $this->_request->notificationAddress : null;            
                $this->_updateNotificationData($userId, $data);
                                
            }
            
            echo $this->_authResultToString($result);
        } catch (Exception $ex) {
            echo 'ERROR';
        }
    }  
}
