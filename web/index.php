<?php 

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
	'monolog.logfile' => 'php://stderr',
));

// Our web handlers
$app->get('/search', function() use($app) {
	$app['monolog']->addDebug('logging output.');
	require('handler.php');
	return json_encode($ComRes);
});

$app->run();