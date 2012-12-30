<?php

class OATH_OCRA {

	private $key = NULL;

	private $OCRASuite = NULL;

	private $OCRAVersion = NULL;

	private $CryptoFunctionType = NULL;
	private $CryptoFunctionHash = NULL;
	private $CryptoFunctionHashLength = NULL;
	private $CryptoFunctionTruncation = NULL;

	private $C = FALSE;
	private $CDataInput = NULL;

	private $Q = FALSE;
	private $QType = 'N';
	private $QLength = 8;
	private $QDataInput = NULL;

	private $P = FALSE;
	private $PType = 'SHA1';
	private $PLength = 20;
	private $PDataInput = NULL;

	private $S = FALSE;
	private $SLength = 64;
	private $SDataInput = NULL;

	private $T = FALSE;
	private $TLength = 60; // 1M
	private $TDataInput = NULL;
	private $TPeriods = array('H' => 3600, 'M' => 60, 'S' => 1);

	private $supportedHashFunctions = array('SHA1' => 20, 'SHA256' => 32, 'SHA512' => 64);


	public function __construct($ocraSuite, $key = NULL, $counter = NULL, $question = NULL, $pin = NULL, $session = NULL, $timestamp = NULL) {
		$this->parseOCRASuite($ocraSuite);

		if ($key !== NULL) {
			$this->setKey($key);
		}
		if ($counter !== NULL) {
			$this->setCounter($counter);
		}
		if ($question !== NULL) {
			$this->setQuestion($question);
		}
		if ($pin !== NULL) {
			$this->setPin($pin);
		}
		if ($session !== NULL) {
			$this->setSessionInformation($session);
		}
		if ($timestamp !== NULL) {
			$this->setTimestamp($timestamp);
		}
	}


	public function setKey($key, $format = NULL) {
		if (!is_string($key) || $key == "") {
			throw new Exception('Invalid key value: ' . var_export($key, TRUE));
		}

		if ($format === NULL) {
			$this->key = $key;
		} elseif ($format == 'hexstring') {
			$this->key = pack("H*", $key);
		} else {
			throw new Exception('Unknown input format: ' . var_export($format, TRUE));
		}
	}


	public function setCounter($c) {
		if (!$this->C) {
			return;
		}

		if ((!is_string($c) && !is_integer($c))) {
			throw new Exception('Invalid counter value: ' . var_export($c, TRUE));
		}

		if (!preg_match('/^\d+$/', $c) || $c < 0 || $c > pow(2, 64)) {
			throw new Exception('Invalid counter value: ' . var_export($c, TRUE));
		}

		$c = pack('N*', $c);
		$c = str_pad($c, 8, "\0", STR_PAD_LEFT);

		$this->CDataInput = $c;
	}


	public function setQuestion($q) {
		if ((!is_string($q) && !is_integer($q)) || $q == "") {
			throw new Exception('Invalid question value');
		}

		$q_len = strlen($q);
		if ($q_len < 4 || $q_len > $this->QLength) {
			throw new Exception('Invalid question value length: ' . var_export($q_len, TRUE));
		}

		switch($this->QType) {
			case 'A':
				if (!preg_match('/^[A-z0-9]+$/', $q)) {
					throw new Exception('Question not alphanumeric: ' . var_export($q, TRUE));
				}
				break;
			case 'H':
				if (!preg_match('/^[a-fA-F0-9]+$/', $q)) {
					throw new Exception('Question not hexadecimal: ' . var_export($q, TRUE));
				}
				$q = pack('H*', $q);
				break;
			case 'N':
				if (!preg_match('/^\d+$/', $q)) {
					throw new Exception('Question not numeric: ' . var_export($q, TRUE));
				}
				$q = pack('H*', self::decStringToHexString($q));
				break;
			default:
				throw new Exception('Unknown question type: ' . var_export($this->QType, TRUE));
				break;
		}

		$q = str_pad($q, 128, "\0", STR_PAD_RIGHT);

		$this->QDataInput = $q;
	}


	public function setPin($p, $format = NULL) {
		if (!$this->P) {
			return;
		}

		if ((!is_string($p) && !is_integer($p)) || $p == "") {
			throw new Exception('Invalid PIN value');
		}

		$p_algo = $this->PType;
		$p_length = $this->PLength;

		if ($format === NULL) {
			$p = self::hashFunction($p_algo, $p);
		} elseif ($format == 'hexdigest') {
			if (!preg_match('/^[a-fA-F0-9]+$/', $p) || strlen($p) != 2*$p_length) {
				throw new Exception('Invalid PIN hexdigest value: ' . var_export($p, TRUE));
			}

			$p = pack('H*', $p);
		} elseif ($format == 'digest') {
			if (strlen($p) != $p_length) {
				throw new Exception('Invalid PIN digest value length: ' . var_export($p, TRUE));
			}
		} else {
			throw new Exception('Unsupported input format');
		}

		$this->PDataInput = $p;
	}


	public function setSessionInformation($s) {
		if (!$this->S) {
			return;
		}

		$s_len = strlen($s);
		if ($s_len != $this->SLength) {
			throw new Exception('Invalid session information value length: ' . var_export($s_len, TRUE));
		}

		$this->SDataInput = $s;
	}


	public function setTimestamp($t) {
		if (!$this->T) {
			return;
		}

		if (!preg_match('/^\d+$/', $t)) {
			throw new Exception('Invalid value for timestamp: ' . var_export($t, TRUE));
		}

		$t = pack('N*', $t);
		$t = str_pad($t, 8, "\0", STR_PAD_LEFT);

		$this->TDataInput = $t;
	}


	/**
	 * Inspired by https://github.com/bdauvergne/python-oath
	 */
	private function parseOCRASuite($ocraSuite) {
		if (!is_string($ocraSuite)) {
			throw new Exception('OCRASuite not in string format: ' . var_export($ocraSuite, TRUE));
		}

		$ocraSuite = strtoupper($ocraSuite);
		$this->OCRASuite = $ocraSuite;

		$s = explode(':', $ocraSuite);
		if (count($s) != 3) {
			throw new Exception('Invalid OCRASuite format: ' . var_export($ocraSuite, TRUE));
		}

		$algo = explode('-', $s[0]);
		if (count($algo) != 2) {
			throw new Exception('Invalid OCRA version: ' . var_export($s[0], TRUE));
		}

		if ($algo[0] !== 'OCRA') {
			throw new Exception('Unsupported OCRA algorithm: ' . var_export($algo[0], TRUE));
		}

		if ($algo[1] !== '1') {
			throw new Exception('Unsupported OCRA version: ' . var_export($algo[1], TRUE));
		}
		$this->OCRAVersion = $algo[1];

		$cf = explode('-', $s[1]);
		if (count($cf) != 3) {
			throw new Exception('Invalid OCRA suite crypto function: ' . var_export($s[1], TRUE));
		}

		if ($cf[0] !== 'HOTP') {
			throw new Exception('Unsupported OCRA suite crypto function: ' . var_export($cf[0], TRUE));
		}
		$this->CryptoFunctionType = $cf[0];

		if (!array_key_exists($cf[1], $this->supportedHashFunctions)) {
			throw new Exception('Unsupported hash function in OCRA suite crypto function: ' . var_export($cf[1], TRUE));
		}
		$this->CryptoFunctionHash = $cf[1];
		$this->CryptoFunctionHashLength = $this->supportedHashFunctions[$cf[1]];

		if (!preg_match('/^\d+$/', $cf[2]) || (($cf[2] < 4 || $cf[2] > 10) && $cf[2] != 0)) {
			throw new Exception('Invalid OCRA suite crypto function truncation length: ' . var_export($cf[2], TRUE));
		}
		$this->CryptoFunctionTruncation = intval($cf[2]);

		$di = explode('-', $s[2]);
		if (count($cf) == 0) {
			throw new Exception('Invalid OCRA suite data input: ' . var_export($s[2], TRUE));
		}

		$data_input = array();
		foreach($di as $elem) {
			$letter = $elem[0];
			if (array_key_exists($letter, $data_input)) {
				throw new Exception('Duplicate field in OCRA suite data input: ' . var_export($elem, TRUE));
			}
			$data_input[$letter] = 1;

			if ($letter === 'C' && strlen($elem) == 1) {
				$this->C = TRUE;
			} elseif ($letter === 'Q') {
				if (strlen($elem) == 1) {
					$this->Q = TRUE;
				} elseif (preg_match('/^Q([AHN])(\d+)$/', $elem, $match)) {
					$q_len = intval($match[2]);
					if ($q_len < 4 || $q_len > 64) {
						throw new Exception('Invalid OCRA suite data input question length: ' . var_export($q_len, TRUE));
					}
					$this->Q = TRUE;
					$this->QType = $match[1];
					$this->QLength = $q_len;
				} else {
					throw new Exception('Invalid OCRA suite data input question: ' . var_export($elem, TRUE));
				}
			} elseif ($letter === 'P') {
				if (strlen($elem) == 1) {
					$this->P = TRUE;
				} else {
					$p_algo = substr($elem, 1);
					if (!array_key_exists($p_algo, $this->supportedHashFunctions)) {
						throw new Exception('Unsupported OCRA suite PIN hash function: ' . var_export($elem, TRUE));
					}
					$this->P = TRUE;
					$this->PType = $p_algo;
					$this->PLength = $this->supportedHashFunctions[$p_algo];
				}
			} elseif ($letter === 'S') {
				if (strlen($elem) == 1) {
					$this->S = TRUE;
				} elseif (preg_match('/^S(\d+)$/', $elem, $match)) {
					$s_len = intval($match[1]);
					if ($s_len <= 0 || $s_len > 512) {
						throw new Exception('Invalid OCRA suite data input session information length: ' . var_export($s_len, TRUE));
					}

					$this->S = TRUE;
					$this->SLength = $s_len;
				} else {
					throw new Exception('Invalid OCRA suite data input session information length: ' . var_export($elem, TRUE));
				}
			} elseif ($letter === 'T') {
				if (strlen($elem) == 1) {
					$this->T = TRUE;
				} elseif (preg_match('/^T(\d+[HMS])+$/', $elem)) {
					preg_match_all('/(\d+)([HMS])/', $elem, $match);

					if (count($match[1]) !== count(array_unique($match[2]))) {
						throw new Exception('Duplicate definitions in OCRA suite data input timestamp: ' . var_export($elem, TRUE));
					}

					$length = 0;
					for ($i = 0; $i < count($match[1]); $i++) {
						$length += intval($match[1][$i]) * $this->TPeriods[$match[2][$i]];
					}
					if ($length <= 0) {
						throw new Exception('Invalid OCRA suite data input timestamp: ' . var_export($elem, TRUE));
					}

					$this->T = TRUE;
					$this->TLength = $length;
				} else {
					throw new Exception('Invalid OCRA suite data input timestamp: ' . var_export($elem, TRUE));
				}
			} else {
				throw new Exception('Unsupported OCRA suite data input field: ' . var_export($elem, TRUE));
			}
		}

		if (!$this->Q) {
			throw new Exception('OCRA suite data input question not defined: ' . var_export($s[2], TRUE));
		}
	}


	public function generateOCRA() {
		if ($this->key === NULL) {
			throw new Exception('Key not defined');
		}

		$msg = $this->OCRASuite . "\0";

		if ($this->C) {
			if ($this->CDataInput === NULL) {
				throw new Exception('Counter not defined');
			}

			$msg .= $this->CDataInput;
		}

		if ($this->Q) {
			if ($this->QDataInput === NULL) {
				throw new Exception('Question not defined');
			}

			$msg .= $this->QDataInput;
		}

		if ($this->P) {
			if ($this->PDataInput === NULL) {
				throw new Exception('PIN not defined');
			}

			$msg .= $this->PDataInput;
		}

		if ($this->S) {
			if ($this->SDataInput === NULL) {
				throw new Exception('Session information not defined');
			}

			$msg .= $this->SDataInput;
		}

		if ($this->T) {
			if ($this->TDataInput === NULL) {
				$this->setTimestamp(intval(time() / $this->TLength));
			}

			$msg .= $this->TDataInput;
		}

		$raw_hash = self::cryptoFunction($this->CryptoFunctionHash, $this->key, $msg);

		if ($this->CryptoFunctionTruncation) {
			return self::hotpDec($raw_hash, $this->CryptoFunctionTruncation);
		} else {
			return self::hotpTruncatedValue($raw_hash);
		}
	}


	public function verifyResponse($response) {
		$expected_response = $this->generateOCRA();

		return self::constEqual($expected_response, $response);
	}


	public function generateChallenge() {
		$q_length = $this->QLength;
		$q_type = $this->QType;

		$bytes = self::generateRandomBytes($q_length);

		switch($q_type) {
			case 'A':
				$challenge = base64_encode($bytes);
				$tr = implode("", unpack('H*', $bytes));
				$challenge = rtrim(strtr($challenge, '+/', $tr), '=');
				break;
			case 'H':
				$challenge = implode("", unpack('H*', $bytes));
				break;
			case 'N':
				$challenge = implode("", unpack('N*', $bytes));
				break;
			default:
				throw new Exception('Unsupported OCRASuite challenge type: ' . var_export($q_type, TRUE));
				break;
		}

		$challenge = substr($challenge, 0, $q_length);

		return $challenge;
	}


	public function generateSessionInformation() {
		if (!$this->S) {
			throw new Exception('Session information not defined in OCRASuite: ' . var_export($this->OCRASuite, TRUE));
		}

		$s_length = $this->SLength;
		$bytes = self::generateRandomBytes($s_length);

		$session = base64_encode($bytes);
		// URL safe base64 encode
		$session = rtrim(strtr($session, '+/', '-_'), '=');
		$session = substr($session, 0, $s_length);

		return $session;
	}


	public static function hotpTruncatedValue($raw) {
		$offset = ord($raw[strlen($raw) - 1]) & 0xf;

		$v = (ord($raw[$offset]) & 0x7f) << 24;
		$v |= (ord($raw[$offset+1]) & 0xff) << 16;
		$v |= (ord($raw[$offset+2]) & 0xff) << 8;
		$v |= (ord($raw[$offset+3]) & 0xff);

		return strval($v);
	}


	public static function hotpDec($raw, $length) {
		$v = self::hotpTruncatedValue($raw);

		return substr($v, strlen($v) - $length);
	}


	public static function hashFunction($algo, $data) {
		$algo = strtolower($algo);

		return hash($algo, $data, TRUE);
	}


	public static function cryptoFunction($algo, $key, $data) {
		$algo = strtolower($algo);

		return hash_hmac($algo, $data, $key, TRUE);
	}


	public static function decStringToHexString($number) {
		$hex_digits = array(
			'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
			'A', 'B', 'C', 'D', 'E', 'F'
		);

		$hex = '';
		while ($number != '0') {
			$hex = $hex_digits[bcmod($number, '16')] . $hex;
			$number = bcdiv($number, '16', 0);
		}

		return $hex;
	}


	/**
	 * Borrowed from SimpleSAMLPHP http://simplesamlphp.org/
	 */
	public static function generateRandomBytesMTrand($length) {

		/* Use mt_rand to generate $length random bytes. */
		$data = '';
		for($i = 0; $i < $length; $i++) {
			$data .= chr(mt_rand(0, 255));
		}

		return $data;
	}


	/**
	 * Borrowed from SimpleSAMLPHP http://simplesamlphp.org/
	 */
	public static function generateRandomBytes($length, $fallback = TRUE) {
		static $fp = NULL;

		if (function_exists('openssl_random_pseudo_bytes')) {
			return openssl_random_pseudo_bytes($length);
		}

		if($fp === NULL) {
			if (@file_exists('/dev/urandom')) {
				$fp = @fopen('/dev/urandom', 'rb');
			} else {
				$fp = FALSE;
			}
		}

		if($fp !== FALSE) {
			/* Read random bytes from /dev/urandom. */
			$data = fread($fp, $length);
			if($data === FALSE) {
				throw new Exception('Error reading random data.');
			}
			if(strlen($data) != $length) {
				if ($fallback) {
					$data = self::generateRandomBytesMTrand($length);
				} else {
					throw new Exception('Did not get requested number of bytes from random source. Requested (' . $length . ') got (' . strlen($data) . ')');
				}
			}
		} else {
			/* Use mt_rand to generate $length random bytes. */
			$data = self::generateRandomBytesMTrand($length);
		}

		return $data;
	}


	/**
	 * Constant time string comparison, see http://codahale.com/a-lesson-in-timing-attacks/
	 */
	public static function constEqual($s1, $s2) {
		if (strlen($s1) != strlen($s2)) {
			return FALSE;
		}

		$result = TRUE;
		$length = strlen($s1);
		for ($i = 0; $i < $length; $i++) {
			$result &= ($s1[$i] == $s2[$i]);
		}

		return (boolean)$result;
	}

}
