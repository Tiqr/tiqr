<?php

class sspmod_authTiqr_Store_UserStorage extends Tiqr_UserStorage_GenericStore {

	private $store;

	private $dataType = 'authTiqr:user';


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


	protected function _loadUser($userId, $failIfNotFound = TRUE) {
		$data = $this->store->get($this->dataType, $userId);

		if ($data === NULL) {
			if ($failIfNotFound) {
				throw new Exception('Error loading data for user: ' . var_export($userId, TRUE));
			} else {
				return false;
			}
		} else {
			return $data;
		}
	}


	protected function _saveUser($userId, $data) {
		$this->store->set($this->dataType, $userId, $data, 0);

		return true;
	}


	protected function _deleteUser($userId) {
		$this->store->delete($this->dataType, $userId);
	}
}
