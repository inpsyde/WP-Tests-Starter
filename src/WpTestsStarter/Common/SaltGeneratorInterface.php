<?php # -*- coding: utf-8 -*-

namespace WpTestsStarter\Common;

interface SaltGeneratorInterface {

	/**
	 * @param int $length
	 * @return string
	 */
	public function generateSalt( $length );
} 