<?php

require_once( 'vendor/autoload.php' );
require_once( 'Secret.ini.php' );
require_once( 'Auth.php' );
require_once( 'Webhooks.php' );
require_once( 'Slack.php' );

$app = new \Slim\App([
	'settings'=>[
		'displayErrorDetails'=>true,
		'determineRouteBeforeAppMiddleware' => true
	]
]);

//listens for web hooks
$app->post('/complete[/]', 'Webhooks:receiveWebhook')->setName('complete');

//adds webhooks to all lists under the desired Wunderlist folder without currently existing webhooks
$app->get('/update[/]', 'Webhooks:updateWebhooks')->setName('update');

//lists all existing webhooks
$app->get('/list[/]', 'Webhooks:listWebhooks')->setName('list');

$app->get('/', function($request, $response, $params){
	echo 'hi';
})->setName('home');

$app->run();