<?php 
// require('../vendor/autoload.php');
require 'handler.php';

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
	'monolog.logfile' => 'php://stderr',
));

// Our web handlers
$app->get('/test', function() use($app) {
	$app['monolog']->addDebug('logging output.');

	return json_encode($ComRes);
});

$app->run();