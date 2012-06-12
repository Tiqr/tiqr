<?php

require_once(realpath(dirname(__FILE__) . '/OCRA.php'));

class OATH_OCRATest extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider RFCVectorsProvider
	 */
	public function testRFCVectors($ocrasuite, $key, $datainput, $expected_result) {
		$ocra = new OATH_OCRA($ocrasuite, $key, NULL, $datainput['Q']);
		$ocra->setKey($key, 'hexstring');
		$ocra->setQuestion($datainput['Q']);
		if (isset($datainput['C'])) {
			$ocra->setCounter($datainput['C']);
		}
		if (isset($datainput['P'])) {
			$ocra->setPin($datainput['P']);
		} elseif (isset($datainput['P:hexdigest'])) {
			$ocra->setPin($datainput['P:hexdigest'], 'hexdigest');
		}
		if (isset($datainput['T'])) {
			$ocra->setTimestamp($datainput['T']);
		}
		$this->assertTrue($ocra->verifyResponse($expected_result));
	}


	public function RFCVectorsProvider() {
		$pin = '1234';
		$pin_sha1 = '7110eda4d09e062aa5e4a390b0a572ac0d2c0220';

		$key20 = '3132333435363738393031323334353637383930';
		$key32 = '3132333435363738393031323334353637383930313233343536373839303132';
		$key64 = '31323334353637383930313233343536373839303132333435363738393031323334353637383930313233343536373839303132333435363738393031323334';

		$tests = array(
			array(
				'ocrasuite' => 'OCRA-1:HOTP-SHA1-6:QN08',
				'key' => $key20,
				'vectors' => array(
					array('params' => array( 'Q' => '00000000' ), 'result' => '237653' ),
					array('params' => array( 'Q' => '11111111' ), 'result' => '243178' ),
					array('params' => array( 'Q' => '22222222' ), 'result' => '653583' ),
					array('params' => array( 'Q' => '33333333' ), 'result' => '740991' ),
					array('params' => array( 'Q' => '44444444' ), 'result' => '608993' ),
					array('params' => array( 'Q' => '55555555' ), 'result' => '388898' ),
					array('params' => array( 'Q' => '66666666' ), 'result' => '816933' ),
					array('params' => array( 'Q' => '77777777' ), 'result' => '224598' ),
					array('params' => array( 'Q' => '88888888' ), 'result' => '750600' ),
					array('params' => array( 'Q' => '99999999' ), 'result' => '294470' ),
				)
			),
			array(
				'ocrasuite' => 'OCRA-1:HOTP-SHA256-8:C-QN08-PSHA1',
				'key' => $key32,
				'pin_sha1' => $pin_sha1,
				'vectors' => array(
					array('params' => array( 'C' => 0, 'Q' => '12345678' ), 'result' => '65347737' ),
					array('params' => array( 'C' => 1, 'Q' => '12345678' ), 'result' => '86775851' ),
					array('params' => array( 'C' => 2, 'Q' => '12345678' ), 'result' => '78192410' ),
					array('params' => array( 'C' => 3, 'Q' => '12345678' ), 'result' => '71565254' ),
					array('params' => array( 'C' => 4, 'Q' => '12345678' ), 'result' => '10104329' ),
					array('params' => array( 'C' => 5, 'Q' => '12345678' ), 'result' => '65983500' ),
					array('params' => array( 'C' => 6, 'Q' => '12345678' ), 'result' => '70069104' ),
					array('params' => array( 'C' => 7, 'Q' => '12345678' ), 'result' => '91771096' ),
					array('params' => array( 'C' => 8, 'Q' => '12345678' ), 'result' => '75011558' ),
					array('params' => array( 'C' => 9, 'Q' => '12345678' ), 'result' => '08522129' ),
				)
			),
			array(
				'ocrasuite' => 'OCRA-1:HOTP-SHA256-8:QN08-PSHA1',
				'key' => $key32,
				'pin_sha1' => $pin_sha1,
				'vectors' => array(
					array('params' => array( 'Q' => '00000000' ), 'result' => '83238735' ),
					array('params' => array( 'Q' => '11111111' ), 'result' => '01501458' ),
					array('params' => array( 'Q' => '22222222' ), 'result' => '17957585' ),
					array('params' => array( 'Q' => '33333333' ), 'result' => '86776967' ),
					array('params' => array( 'Q' => '44444444' ), 'result' => '86807031' ),
				)
			),
			array(
				'ocrasuite' => 'OCRA-1:HOTP-SHA512-8:C-QN08',
				'key' => $key64,
				'vectors' => array(
					array('params' => array( 'C' => '00000', 'Q' => '00000000' ), 'result' => '07016083' ),
					array('params' => array( 'C' => '00001', 'Q' => '11111111' ), 'result' => '63947962' ),
					array('params' => array( 'C' => '00002', 'Q' => '22222222' ), 'result' => '70123924' ),
					array('params' => array( 'C' => '00003', 'Q' => '33333333' ), 'result' => '25341727' ),
					array('params' => array( 'C' => '00004', 'Q' => '44444444' ), 'result' => '33203315' ),
					array('params' => array( 'C' => '00005', 'Q' => '55555555' ), 'result' => '34205738' ),
					array('params' => array( 'C' => '00006', 'Q' => '66666666' ), 'result' => '44343969' ),
					array('params' => array( 'C' => '00007', 'Q' => '77777777' ), 'result' => '51946085' ),
					array('params' => array( 'C' => '00008', 'Q' => '88888888' ), 'result' => '20403879' ),
					array('params' => array( 'C' => '00009', 'Q' => '99999999' ), 'result' => '31409299' ),
				)
			),
			array(
				'ocrasuite' => 'OCRA-1:HOTP-SHA512-8:QN08-T1M',
				'key' => $key64,
				'vectors' => array(
					array('params' => array( 'Q' => '00000000', 'T' => intval('132d0b6', 16) ), 'result' => '95209754' ),
					array('params' => array( 'Q' => '11111111', 'T' => intval('132d0b6', 16) ), 'result' => '55907591' ),
					array('params' => array( 'Q' => '22222222', 'T' => intval('132d0b6', 16) ), 'result' => '22048402' ),
					array('params' => array( 'Q' => '33333333', 'T' => intval('132d0b6', 16) ), 'result' => '24218844' ),
					array('params' => array( 'Q' => '44444444', 'T' => intval('132d0b6', 16) ), 'result' => '36209546' ),
				)
			),
		);

		$data = array();

		foreach($tests as $test) {
			$ocrasuite = $test['ocrasuite'];
			foreach($test['vectors'] as $vector) {
				$datainput = $vector['params'];
				if (isset($test['pin'])) {
					$datainput['P'] = $test['pin'];
				} elseif (isset($test['pin_sha1'])) {
					$datainput['P:hexdigest'] = $test['pin_sha1'];
				}
				$data[] = array($ocrasuite, $test['key'], $datainput, $vector['result']);
			}
		}

		return $data;
	}

}
