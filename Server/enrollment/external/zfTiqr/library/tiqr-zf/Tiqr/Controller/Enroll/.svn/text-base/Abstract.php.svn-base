<?php
require_once 'Tiqr/Controller/Abstract.php';
require_once 'Zend/Session/Namespace.php';

/**
 * Enrollment base controller.
 */
abstract class Tiqr_Controller_Enroll_Abstract extends Tiqr_Controller_Abstract
{
    /**
     * Returns the authentication verification URL, for future logins.
     *
     * Should return a complete server URL including hostname etc.
     *
     * @return string authentication URL
     */
    abstract protected function _getAuthenticationURL();
    
    /**
     * Returns the enrollment user metadata.
     *
     * Object with the following properties:
     * - userId
     * - displayName
     *
     * @return stdClass user metadata
     */
    abstract protected function _getUserData();
    
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
    abstract protected function _storeEnrollmentData($userId, $enrollmentData);

    /**
     * Shows the QR code.
     */
    public function scanAction() 
    {
        $session = new Zend_Session_Namespace(__CLASS__);
        $user = $this->_getUserData();
        $session->key = $this->_getTiqr()->startEnrollmentSession($user->userId, $user->displayName, $this->_getSessionId());
        $metadataURL = $this->view->serverUrl($this->_helper->url('metadata', null, null, array('key' => $session->key)));   
        
        $this->view->user = $user;
        $this->view->qrURL = $this->_helper->url('qr');      
    }

    /**
     * Outputs the enrollment QR.
     */
    public function qrAction() 
    {
        $session = new Zend_Session_Namespace(__CLASS__);     
        $metadataURL = $this->view->serverUrl($this->_helper->url('metadata', null, null, array('key' => $session->key)));   
        
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender();        
        $this->_getTiqr()->generateEnrollmentQR($metadataURL);
    }
    
    /**
     * Output the enrollment metadata JSON.
     *
     * This action is accessed by the mobile device.
     */
    public function metadataAction()
    {
        $secret = $this->_getTiqr()->getEnrollmentSecret($this->_request->key);
        $enrollmentURL = $this->view->serverUrl($this->_helper->url('enroll', null, null, array('enrollmentSecret' => $secret)));
        
        $metadata = $this->_getTiqr()->getEnrollmentMetadata($this->_request->key, $this->_getAuthenticationURL(), $enrollmentURL);
        if (!is_array($metadata)) {
            $metadata = false;
        }
        
        $this->_helper->json->sendJson($metadata);        
    }
    
    /**
     * Enrolls the identity for the mobile device.
     *
     * This action is accessed by the mobile device.
     *
     * @todo Improved error signaling.
     */
    public function enrollAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender();        
        
        if (!isset($this->_request->enrollmentSecret) || !isset($this->_request->secret)) {
            echo 'ERROR1';
            return;
        }

        $userId = $this->_getTiqr()->validateEnrollmentSecret($this->_request->enrollmentSecret);
        if ($userId === false) {
            echo 'ERROR2';
            return;
        }
        
        try {
            $data = new stdClass();
            $data->secret = $this->_request->secret;
            $data->notificationType = isset($this->_request->notificationType) ? $this->_request->notificationType : null;
            $data->notificationAddress = isset($this->_request->notificationAddress) ? $this->_request->notificationAddress : null;            
            
            $this->_storeEnrollmentData(
                $userId, 
                $data
            );
        } catch (Exception $ex) {
            echo 'ERROR3';
            return;
        }
            
        $this->_getTiqr()->finalizeEnrollment($this->_request->enrollmentSecret);                
        
        echo 'OK';
    }
}
