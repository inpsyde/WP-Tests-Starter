<?php # -*- coding: utf-8 -*-

namespace WpTestsStarter\Test;
use Requisite;

$localBootstrapFile = __DIR__ . '/bootstrap.php';
if ( file_exists( $localBootstrapFile ) )
	return require_once $localBootstrapFile;

$baseDir = dirname( __DIR__ );
$autoloadFile = $baseDir . '/vendor/autoload.php';

if ( file_exists( $autoloadFile ) )
	require_once $autoloadFile;

$splLoader = new Requisite\SPLAutoLoader;
$splLoader->addRule(
	new Requisite\Rule\NamespaceDirectoryMapper(
		$baseDir . '/src/WpTestsStarter',
		'WpTestsStarter'
	)
);
