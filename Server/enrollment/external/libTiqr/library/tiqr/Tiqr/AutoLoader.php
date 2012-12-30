<?php
class Tiqr_AutoLoader {

	protected static $instance;

	protected $tiqrPath;
	protected $qrcodePath;
	protected $zendPath;

	protected function __construct($options) {
		if ($options !== NULL) {
			$this->setOptions($options);
		}
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}

	public static function getInstance($options = NULL) {
		if (null === self::$instance) {
			self::$instance = new self($options);
		}

		return self::$instance;
	}

	public static function autoload($className) {
		if($className === NULL) {
			return;
		}

		$self = self::getInstance();

		$substr5 = substr($className, 0, 5);

		if ($substr5 === 'Tiqr_' || $substr5 === 'OATH_') {
			$file = $self->tiqrPath . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		} elseif ($className === 'QRcode') {
			$file = $self->qrcodePath . DIRECTORY_SEPARATOR . 'phpqrcode.php';
		} elseif ($substr5 === 'Zend_') {
			$file = $self->zendPath . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		} else {
			return;
		}

		if (file_exists($file)) {
			require_once($file);
		}
	}

	public function setOptions($options) {
		if (isset($options["tiqr.path"])) {
			$tiqr_dir = $options["tiqr.path"];
			$tiqr_path = realpath($tiqr_dir);
		} else {
			$tiqr_dir = dirname(__FILE__);
			$tiqr_path = $tiqr_dir;
		}
		if (is_dir($tiqr_path)) {
			$this->tiqrPath = $tiqr_path;
		} else {
			throw new Exception('Directory not found: ' . var_export($tiqr_dir, TRUE));
		}

		if (isset($options["phpqrcode.path"])) {
			$qrcode_dir = $options["phpqrcode.path"];
			$qrcode_path = realpath($qrcode_dir);
		} else {
			$qrcode_dir = dirname(dirname(dirname(__FILE__))) . '/phpqrcode';
			$qrcode_path = $qrcode_dir;
		}

		if (is_dir($qrcode_path)) {
			$this->qrcodePath = $qrcode_path;
		} else {
			throw new Exception('Directory not found: ' . var_export($qrcode_dir, TRUE));
		}

		if (isset($options["zend.path"])) {
			$zend_dir = $options["zend.path"];
			$zend_path = realpath($zend_dir);
		} else {
			$zend_dir = dirname(dirname(dirname(__FILE__))) . "/zend";
			$zend_path = $zend_dir;
		}
		if (is_dir($zend_path)) {
			$this->zendPath = $zend_path;
		} else {
			throw new Exception('Directory not found: ' . var_export($zend_dir, TRUE));
		}
	}


	public function setIncludePath() {
		set_include_path(implode(PATH_SEPARATOR, array(
			$this->tiqrPath,
			$this->zendPath,
			$this->qrcodePath,
			get_include_path(),
		)));
	}
}
