<?php
require_once 'Zend/Application/Resource/ResourceAbstract.php';
require_once "Tiqr/Service.php";

class Tiqr_Resource_Tiqr extends Zend_Application_Resource_ResourceAbstract
{
    private $_tiqr;

    /**
     * Initialize resource.
     */
    public function init()
    {
        return $this->getTiqr();
    }
    
    /**
     * Flatten Tiqr options to match the required structure.
     *
     * @param array $options Tiqr options
     *
     * @return array Tiqr configuration array
     */
    protected function _flattenOptions($options)
    {
        $config = array();
        
        foreach ($options as $rootKey => $rootValue) {
            if (!is_array($rootValue)) {
                $config[$rootKey] = $rootValue;
            } else if (is_array($rootValue) && in_array($rootKey, array('statestorage', 'devicestorage', 'userstorage'))) {
                $config[$rootKey] = $rootValue;
            } else {
                foreach ($rootValue as $childKey => $childValue) {
                    $config["{$rootKey}.{$childKey}"] = $childValue;
                }
            }
        }
        
        return $config;
    }
    
    /**
     * Returns the Tiqr instance.
     *
     * @return Tiqr_Service Tiqr instance
     */
    public function getTiqr()
    {
        if ($this->_tiqr == null) {
            $options = $this->getOptions();
            $config = $this->_flattenOptions($options);
            $this->_tiqr = new Tiqr_Service($config);
        }
        
        return $this->_tiqr;
    }
}
