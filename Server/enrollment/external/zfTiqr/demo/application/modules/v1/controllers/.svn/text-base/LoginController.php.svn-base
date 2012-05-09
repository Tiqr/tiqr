<?php
require_once 'Tiqr/Controller/Login/Abstract.php';
require_once 'Tiqr/Auth/Adapter.php';

class V1_LoginController extends Tiqr_Controller_Login_Abstract
{
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
     * Returns the secret for the given user.
     *
     * If the user doesn't exist this method should return null.
     *
     * @param string $userId user identifier
     * 
     * @return string user secret
     */        
    protected function _getUserSecret($userId)
    {
        $user = $this->_cache->load($userId);
        return $user === false || !$user->isActive ? null : $user->secret;
    }
    
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
    protected function _updateNotificationData($userId, $notificationData)
    {
        $user = $this->_cache->load($userId);
        if ($user === false) {
            return;
        }  
        
        $user->notificationType = $notificationData->notificationType;
        $user->notificationAddress = $notificationData->notificationAddress;
        
        $result = $this->_cache->save($user, $userId);
    }    
    
    /**
     * Shows the login form.
     */
    public function indexAction()
    {
        parent::indexAction();
        $this->view->enrollmentURL = $this->_helper->url('index', 'enroll');
        $this->view->verifyURL = $this->_helper->url('verify');        
    }   
    
    /**
     * Check if the user is logged in yet.
     *
     * NOTE: This implementation will block a webserver process until the user logs in.
     *       You might want to implement this using an Ajax request.
     */
    public function verifyAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender();    

        $authAdapter = new Tiqr_Auth_Adapter();
        $auth = Zend_Auth::getInstance();        
        $result = $auth->authenticate($authAdapter);        
            
        if ($result->getCode() == Zend_Auth_Result::SUCCESS) {
            echo "URL:".$this->_helper->url('index', 'index');
            return;
        } else {
            echo "NO";
        }
    }    
    
    /**
     * Logout.
     */
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector->gotoSimple('index', 'index');
    } 
}
