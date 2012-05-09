<?php
require_once 'Tiqr/Controller/Enroll/Abstract.php';

/**
 * Enrollment example controller.
 */
class V1_EnrollController extends Tiqr_Controller_Enroll_Abstract
{
    private $_cache;
    
    /**
     * Initialize.
     */
    public function init()
    {
        parent::init();
        
        $manager = $this->getFrontController()
                        ->getParam('bootstrap')
                        ->getResource('cachemanager');
                        
        $this->_cache = $manager->getCache('user');
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
        return $this->view->serverUrl($this->_helper->url('authenticate', 'login'));
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
        $user = $this->_cache->load($userId);
        if ($user === false) {
            throw new Exception("Invalid user");
        }
        
        if ($user->isActive) {
            throw new Exception("User already enrolled");
        }   
        
        $user->secret = $enrollmentData->secret;
        $user->notificationType = $enrollmentData->notificationType;
        $user->notificationAddress = $enrollmentData->notificationAddress;
        $user->isActive = true;
        
        $result = $this->_cache->save($user, $userId);
    }
    
    /**
     * Display the enrollment form.
     */
    public function indexAction()
    {
        $this->view->processURL = $this->_helper->url('process');
    }
    
    /**
     * Process user creation form.
     *
     * @todo Don't let new enrollments overwrite each-other.
     */
    public function processAction()
    {
        $user = $this->_cache->load($this->_request->userId);
        if ($user !== false && $user->isActive) {
            throw new Exception("User already enrolled");
        }
        
        $user = new stdClass();
        $user->userId = $this->_request->userId;
        $user->displayName = $this->_request->displayName;
        $user->isActive = false;

        $this->_cache->save($user, $user->userId);
        
        $session = new Zend_Session_Namespace(__CLASS__);           
        $session->user = $user;
        $this->_helper->redirector->gotoSimple('scan');        
    }
    
    /**
     * Display the scan page.
     */
    public function scanAction()
    {
        parent::scanAction();
        $this->view->verifyURL = $this->_helper->url('verify');
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
            $user = $this->_cache->load($userId);
            if ($user === false) {
                throw new Exception("User doesn't exist");
            } else if ($user->isActive) {
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
        $this->view->loginURL = $this->_helper->url('index', 'login');
    }
}
