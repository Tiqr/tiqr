<?php
class V1_IndexController extends Zend_Controller_Action
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
     * Homepage.
     */
    public function indexAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($identity != null) {
            $this->view->user = $this->_cache->load($identity);
            $this->view->logoutURL = $this->_helper->url('logout', 'login');
        } else {
            $this->view->loginURL = $this->_helper->url('index', 'login');            
        }
    }
}