<?php # -*- coding: utf-8 -*-

namespace WpTestsStarter\Test\Unit\Common;
use WpTestsStarter\Common;

class SaltGeneratorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider generateSaltTestProvider
	 * @see SaltGenerator::generateSalt()
	 * @param int $length
	 */
	public function testGenerateSalt( $length ) {

		$testee = new Common\SaltGenerator;

		$salt = $testee->generateSalt( $length );

		$this->assertRegExp(
			'~^[\x20-\x7E]{' . preg_quote( $length ) . '}$~',
			$salt
		);
	}

	/**
	 * @return array
	 */
	public function generateSaltTestProvider() {

		$data = array();

		# 0:
		$data[] = array(
			1
		);

		# 2:
		$data[] = array(
			10
		);

		#3
		$data[] = array(
			42
		);

		return $data;
	}
}
 