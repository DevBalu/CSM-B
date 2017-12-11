<?php 
require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

$app->get('/', function() use($app) {
	$app['monolog']->addDebug('cowsay');
	print "print from index.php";

	return $app->render('test.php');;
});