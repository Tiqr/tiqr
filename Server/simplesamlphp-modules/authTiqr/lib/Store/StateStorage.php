<?php

class sspmod_authTiqr_Store_StateStorage extends Tiqr_StateStorage_Abstract {

	private $store;

	private $dataType = 'authTiqr:state';


	public function __construct($config) {
		parent::__construct($config);

		$this->store = SimpleSAML_Store::getInstance();

		if ($this->store === FALSE) {
			throw new Exception('Datastore not configured.');
		}

		if (isset($config['data.type']) && is_string($config['data.type'])) {
			$this->dataType = $config['data.type'];
		}
	}


	public function getValue($key) {
		return $this->store->get($this->dataType, $key);
	}


	public function setValue($key, $value, $expire = 0) {
		if ($expire === 0) {
			$expire = NULL;
		}

		$this->store->set($this->dataType, $key, $value, $expire);
	}


	public function unsetValue($key) {
		$this->store->delete($this->dataType, $key);
	}

}
